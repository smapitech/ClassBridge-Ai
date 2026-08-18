<?php

namespace App\Http\Controllers\Classroom;

use App\Enums\LiveLessonMode;
use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Course;
use App\Models\LiveClassroom;
use App\Models\Subject;
use App\Models\User;
use App\Services\Classroom\LiveClassroomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LiveLessonController extends Controller
{
    public function __construct(protected LiveClassroomService $lessonService)
    {
    }

    protected function schoolId(): int
    {
        return (int) Auth::user()->school_id;
    }

    protected function ensureSchoolContext(): void
    {
        abort_unless($this->schoolId() > 0, 403, 'A workspace is required to create a live lesson.');
    }

    public function create()
    {
        $this->ensureSchoolContext();

        $school = Auth::user()->school;

        $courses = Course::forSchool($this->schoolId())->active()->orderBy('name')->get(['id', 'name', 'description']);
        $subjects = Subject::forSchool($this->schoolId())->active()->orderBy('name')->get(['id', 'name', 'description']);
        $classes = Classe::forSchool($this->schoolId())->active()->orderBy('name')->get(['id', 'name', 'description']);
        $students = User::where('school_id', $this->schoolId())
            ->whereHas('role', fn ($query) => $query->where('slug', 'student'))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'name', 'first_name', 'last_name', 'email']);

        $defaultAudienceMode = $school?->isPrivateTutorWorkspace() ? 'learner' : 'group';
        $defaultMode = LiveLessonMode::normalize($school?->preferred_teaching_mode ?? 'whiteboard');
        $prefillCourseId = request()->input('course_id', old('course_id'));
        $prefillSubjectId = request()->input('subject_id', old('subject_id'));
        $prefillClassId = request()->input('class_id', old('class_id'));
        $prefillLearnerIds = collect(request()->input('learner_ids', old('learner_ids', [])))->filter()->values()->all();

        return view('live-lessons.create', compact(
            'school',
            'courses',
            'subjects',
            'classes',
            'students',
            'defaultAudienceMode',
            'defaultMode',
            'prefillCourseId',
            'prefillSubjectId',
            'prefillClassId',
            'prefillLearnerIds'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureSchoolContext();

        $validated = $this->validateLesson($request);
        $classroom = $this->lessonService->createLesson($this->lessonPayload($validated), Auth::user());

        if ($validated['start_option'] === 'start_now') {
            $this->lessonService->startSession($classroom, Auth::user());

            return redirect()
                ->route('classrooms.show', $classroom)
                ->with('success', 'Live lesson started. Your classroom is ready.');
        }

        return redirect()
            ->route('classrooms.show', $classroom)
            ->with('success', $validated['start_option'] === 'schedule'
                ? 'Live lesson scheduled successfully.'
                : 'Live lesson saved as a draft.');
    }

    public function storeCourse(Request $request): JsonResponse|RedirectResponse
    {
        $this->ensureSchoolContext();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $course = Course::firstOrCreate(
            [
                'school_id' => $this->schoolId(),
                'slug' => Str::slug($validated['name']),
            ],
            [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => 'active',
                'created_by' => Auth::id(),
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'course' => [
                    'id' => $course->id,
                    'name' => $course->name,
                    'description' => $course->description,
                ],
            ]);
        }

        return back()->with('success', 'Course saved.')->withInput([
            'course_id' => $course->id,
        ]);
    }

    public function storeSubject(Request $request): JsonResponse|RedirectResponse
    {
        $this->ensureSchoolContext();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'course_id' => [
                'nullable',
                Rule::exists('courses', 'id')->where(fn ($query) => $query->where('school_id', $this->schoolId())),
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $subject = Subject::firstOrCreate(
            [
                'school_id' => $this->schoolId(),
                'slug' => Str::slug($validated['name']),
            ],
            [
                'course_id' => $validated['course_id'] ?? null,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => 'active',
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'subject' => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'description' => $subject->description,
                ],
            ]);
        }

        return back()->with('success', 'Subject saved.')->withInput([
            'subject_id' => $subject->id,
        ]);
    }

    protected function validateLesson(Request $request): array
    {
        $schoolId = $this->schoolId();

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'course_id' => [
                'nullable',
                Rule::exists('courses', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'subject_id' => [
                'nullable',
                Rule::exists('subjects', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'audience_mode' => ['required', Rule::in(['learner', 'group'])],
            'class_id' => [
                'nullable',
                Rule::exists('classes', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
                'required_if:audience_mode,group',
            ],
            'learner_ids' => ['required_if:audience_mode,learner', 'array', 'min:1'],
            'learner_ids.*' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'date_time' => ['required_if:start_option,schedule', 'nullable', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'initial_mode' => ['required', Rule::in(LiveLessonMode::acceptedValues())],
            'start_option' => ['required', Rule::in(['start_now', 'schedule', 'draft'])],
            'permissions' => ['nullable', 'array'],
            'permissions.allow_student_draw' => ['sometimes', 'boolean'],
            'permissions.allow_student_type' => ['sometimes', 'boolean'],
            'permissions.allow_student_code' => ['sometimes', 'boolean'],
            'permissions.allow_student_chat' => ['sometimes', 'boolean'],
            'permissions.show_pointer' => ['sometimes', 'boolean'],
            'permissions.allow_resource_download' => ['sometimes', 'boolean'],
        ]);
    }

    protected function lessonPayload(array $validated): array
    {
        $mode = LiveLessonMode::normalize($validated['initial_mode']);
        $startOption = $validated['start_option'];
        $scheduledAt = $startOption === 'schedule'
            ? ($validated['date_time'] ?? null)
            : null;

        $startsAt = $startOption === 'start_now'
            ? now()
            : ($scheduledAt ? Carbon::parse($scheduledAt) : null);

        $endsAt = $startsAt && ! empty($validated['duration_minutes'])
            ? $startsAt->copy()->addMinutes((int) $validated['duration_minutes'])
            : null;

        $permissions = [
            'allow_student_draw' => (bool) Arr::get($validated, 'permissions.allow_student_draw', true),
            'allow_student_type' => (bool) Arr::get($validated, 'permissions.allow_student_type', true),
            'allow_student_code' => (bool) Arr::get($validated, 'permissions.allow_student_code', true),
            'allow_student_chat' => (bool) Arr::get($validated, 'permissions.allow_student_chat', true),
            'show_pointer' => (bool) Arr::get($validated, 'permissions.show_pointer', true),
            'allow_resource_download' => (bool) Arr::get($validated, 'permissions.allow_resource_download', false),
        ];

        $lessonAudience = [
            'mode' => $validated['audience_mode'],
            'class_id' => $validated['audience_mode'] === 'group' ? ($validated['class_id'] ?? null) : null,
            'learner_ids' => $validated['audience_mode'] === 'learner'
                ? collect($validated['learner_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->values()->all()
                : [],
        ];

        return [
            'school_id' => $this->schoolId(),
            'course_id' => $validated['course_id'] ?? null,
            'class_id' => $validated['audience_mode'] === 'group' ? ($validated['class_id'] ?? null) : null,
            'subject_id' => $validated['subject_id'] ?? null,
            'teacher_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'scheduled_at' => $scheduledAt,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'duration_minutes' => $validated['duration_minutes'],
            'status' => $startOption === 'start_now' ? 'live' : ($startOption === 'schedule' ? 'scheduled' : 'draft'),
            'initial_mode' => $mode,
            'active_mode' => $mode,
            'settings' => array_merge($permissions, [
                'lesson_audience' => $lessonAudience,
                'active_mode' => $mode,
            ]),
            'created_by' => Auth::id(),
        ];
    }
}
