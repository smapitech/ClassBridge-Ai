<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Course;
use App\Models\Homework;
use App\Models\InteractiveWorksheet;
use App\Models\LiveClassroom;
use App\Models\Quiz;
use App\Models\Subject;
use App\Models\TeachingMaterial;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    protected function schoolId(): int
    {
        return (int) Auth::user()->school_id;
    }

    protected function ensureSchoolContext(): void
    {
        abort_unless($this->schoolId() > 0, 403, 'A workspace is required.');
    }

    protected function authorizeCourse(Course $course): void
    {
        abort_unless(Auth::user()->isSuperAdmin() || $course->school_id === $this->schoolId(), 403);
    }

    public function index()
    {
        $this->ensureSchoolContext();

        $schoolId = $this->schoolId();

        $courses = Course::forSchool($schoolId)
            ->withCount(['subjects', 'classes', 'learners', 'liveClassrooms'])
            ->with([
                'subjects' => fn ($query) => $query->orderBy('name'),
                'classes' => fn ($query) => $query->orderBy('name'),
                'learners' => fn ($query) => $query->orderBy('first_name')->orderBy('last_name'),
                'liveClassrooms' => fn ($query) => $query->with(['teacher', 'subject', 'classe'])->orderByDesc('starts_at')->orderByDesc('scheduled_at'),
            ])
            ->latest()
            ->paginate(12);

        $unassignedSubjects = Subject::forSchool($schoolId)->whereNull('course_id')->count();
        $unassignedClasses = Classe::forSchool($schoolId)->whereNull('course_id')->count();
        $liveLessons = LiveClassroom::forSchool($schoolId)
            ->with(['course', 'teacher', 'subject', 'classe'])
            ->whereNotNull('course_id')
            ->latest('starts_at')
            ->limit(6)
            ->get();

        return view('courses.index', compact(
            'courses',
            'unassignedSubjects',
            'unassignedClasses',
            'liveLessons'
        ));
    }

    public function create()
    {
        $this->ensureSchoolContext();

        return view('courses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureSchoolContext();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $course = Course::create([
            'school_id' => $this->schoolId(),
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('courses.show', $course)
            ->with('success', "Course '{$course->name}' created.");
    }

    public function show(Course $course)
    {
        $this->authorizeCourse($course);

        $course->load([
            'subjects' => fn ($query) => $query->orderBy('name'),
            'classes' => fn ($query) => $query->orderBy('name'),
            'learners' => fn ($query) => $query->orderBy('first_name')->orderBy('last_name'),
            'liveClassrooms' => fn ($query) => $query->with(['teacher', 'subject', 'classe'])->latest('starts_at')->latest('scheduled_at'),
            'teachingMaterials' => fn ($query) => $query->latest(),
        ]);

        $schoolId = $course->school_id;
        $subjectIds = $course->subjects->pluck('id')->all();
        $classIds = $course->classes->pluck('id')->all();

        $homeworks = Schema::hasTable((new Homework())->getTable())
            ? $this->assignmentQuery(Homework::forSchool($schoolId), $classIds, $subjectIds)->latest()->limit(6)->get()
            : collect();

        $quizzes = Schema::hasTable((new Quiz())->getTable())
            ? $this->assignmentQuery(Quiz::forSchool($schoolId), $classIds, $subjectIds)->latest()->limit(6)->get()
            : collect();

        $worksheets = Schema::hasTable((new InteractiveWorksheet())->getTable())
            ? $this->assignmentQuery(InteractiveWorksheet::forSchool($schoolId), $classIds, $subjectIds)->latest()->limit(6)->get()
            : collect();

        $materials = Schema::hasTable((new TeachingMaterial())->getTable())
            ? $course->teachingMaterials()->latest()->limit(6)->get()
            : collect();
        $assignments = collect()
            ->merge($homeworks->map(fn ($item) => [
                'type' => 'Homework',
                'title' => $item->title,
                'status' => $item->status,
                'date' => $item->due_at ?? $item->created_at,
            ]))
            ->merge($quizzes->map(fn ($item) => [
                'type' => 'Quiz',
                'title' => $item->title,
                'status' => $item->status,
                'date' => $item->created_at,
            ]))
            ->merge($worksheets->map(fn ($item) => [
                'type' => 'Worksheet',
                'title' => $item->title,
                'status' => $item->status,
                'date' => $item->due_at ?? $item->created_at,
            ]))
            ->sortByDesc(fn ($item) => optional($item['date'])->timestamp ?? 0)
            ->values();

        $upcomingSessions = $course->liveClassrooms
            ->filter(fn (LiveClassroom $session) => in_array($session->status, ['live', 'scheduled'], true))
            ->sortBy(fn (LiveClassroom $session) => sprintf(
                '%d|%012d',
                $session->status === 'live' ? 0 : 1,
                optional($session->starts_at ?? $session->scheduled_at ?? $session->created_at)->timestamp ?? 0
            ))
            ->values();

        $previousSessions = $course->liveClassrooms
            ->filter(fn (LiveClassroom $session) => in_array($session->status, ['ended', 'archived'], true))
            ->sortByDesc(fn (LiveClassroom $session) => optional($session->starts_at ?? $session->scheduled_at ?? $session->created_at)->timestamp ?? 0)
            ->values();

        return view('courses.show', compact(
            'course',
            'homeworks',
            'quizzes',
            'worksheets',
            'materials',
            'assignments',
            'upcomingSessions',
            'previousSessions'
        ));
    }

    public function edit(Course $course)
    {
        $this->authorizeCourse($course);

        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'inactive', 'archived'])],
        ]);

        $course->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('courses.show', $course)
            ->with('success', 'Course updated.');
    }

    public function archive(Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);

        $course->update(['status' => 'archived']);

        return redirect()
            ->route('courses.index')
            ->with('success', "Course '{$course->name}' archived.");
    }

    public function assign(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);

        $schoolId = $this->schoolId();

        $validated = $request->validate([
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => [
                'integer',
                Rule::exists('classes', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'learner_ids' => ['nullable', 'array'],
            'learner_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
        ]);

        $classIds = collect($validated['class_ids'] ?? [])->filter()->unique()->values();
        if ($classIds->isNotEmpty()) {
            Classe::forSchool($schoolId)
                ->whereIn('id', $classIds->all())
                ->update(['course_id' => $course->id]);
        }

        $learnerIds = collect($validated['learner_ids'] ?? [])->filter()->unique()->values();
        if ($learnerIds->isNotEmpty()) {
            $course->learners()->syncWithoutDetaching(
                $learnerIds->mapWithKeys(fn ($learnerId) => [
                    $learnerId => [
                        'school_id' => $schoolId,
                        'created_by' => Auth::id(),
                    ],
                ])->all()
            );
        }

        return back()->with('success', 'Course audience updated.');
    }

    public function storeSubject(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $subject = Subject::create([
            'school_id' => $this->schoolId(),
            'course_id' => $course->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? null,
            'status' => $validated['status'],
        ]);

        return back()->with('success', "Subject '{$subject->name}' added to {$course->name}.");
    }

    protected function assignmentQuery($query, array $classIds, array $subjectIds)
    {
        if (empty($classIds) && empty($subjectIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($builder) use ($classIds, $subjectIds) {
            if (! empty($classIds)) {
                $builder->whereIn('class_id', $classIds);
            }

            if (! empty($subjectIds)) {
                if (! empty($classIds)) {
                    $builder->orWhereIn('subject_id', $subjectIds);
                } else {
                    $builder->whereIn('subject_id', $subjectIds);
                }
            }
        });
    }
}
