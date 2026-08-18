<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    /**
     * Get the current user's school ID (scoping helper).
     */
    protected function schoolId(): int
    {
        return Auth::user()->school_id;
    }

    protected function filterByRole($query)
    {
        if (Auth::user()->isSuperAdmin()) return $query;
        if (Auth::user()->isTeacher()) {
            // Teacher sees only classes assigned to them
            return $query->whereHas('teachers', fn($q) => $q->where('teacher_id', Auth::id()));
        }
        return $query->forSchool($this->schoolId());
    }

    /**
     * List classes (scoped).
     */
    public function index()
    {
        $query = $this->filterByRole(Classe::with(['creator', 'teachers', 'students', 'course'])->withCount(['teachers', 'students']));
        if (request('search')) {
            $q = request('search');
            $query->where(fn($b) => $b->where('name', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"));
        }
        $classes = $query->latest()->paginate(20);

        // For super admin adds school filter
        $schools = Auth::user()->isSuperAdmin() ? \App\Models\School::orderBy('name')->get() : collect();
        return view('school.classes.index', compact('classes', 'schools'));
    }

    /**
     * Create form.
     */
    public function create()
    {
        $courses = Course::forSchool($this->schoolId())->active()->orderBy('name')->get();
        $subjects = Subject::forSchool($this->schoolId())->active()->get();
        $teachers = User::where('school_id', $this->schoolId())->whereHas('role', fn($q) => $q->where('slug', 'teacher'))->get();
        return view('school.classes.create', compact('courses', 'subjects', 'teachers'));
    }

    /**
     * Store new class.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'course_id' => [
                'nullable',
                Rule::exists('courses', 'id')->where(fn ($query) => $query->where('school_id', $this->schoolId())),
            ],
            'description' => 'nullable|string|max:1000',
            'age_group' => 'nullable|string|max:50',
            'level' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ]);

        $class = Classe::create([
            'school_id' => $this->schoolId(),
            'course_id' => $validated['course_id'] ?? null,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'age_group' => $validated['age_group'] ?? null,
            'level' => $validated['level'] ?? null,
            'status' => $validated['status'],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('school.classes.show', $class)
            ->with('success', "Class '{$class->name}' created successfully.");
    }

    /**
     * Show class details.
     */
    public function show(Classe $class)
    {
        if (!Auth::user()->isSuperAdmin() && $class->school_id !== $this->schoolId()) {
            abort(403);
        }

        $class->load(['creator', 'teachers', 'students', 'subjects', 'course']);
        $allTeachers = User::where('school_id', $class->school_id)
            ->whereHas('role', fn($q) => $q->where('slug', 'teacher'))->get();
        $allStudents = User::where('school_id', $class->school_id)
            ->whereHas('role', fn($q) => $q->where('slug', 'student'))->get();
        $allSubjects = Subject::forSchool($class->school_id)->active()->get();

        return view('school.classes.show', compact('class', 'allTeachers', 'allStudents', 'allSubjects'));
    }

    /**
     * Edit form.
     */
    public function edit(Classe $class)
    {
        if (!Auth::user()->isSuperAdmin() && $class->school_id !== $this->schoolId()) abort(403);
        $courses = Course::forSchool($this->schoolId())->active()->orderBy('name')->get();
        return view('school.classes.edit', compact('class', 'courses'));
    }

    /**
     * Update class.
     */
    public function update(Request $request, Classe $class)
    {
        if (!Auth::user()->isSuperAdmin() && $class->school_id !== $this->schoolId()) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'course_id' => [
                'nullable',
                Rule::exists('courses', 'id')->where(fn ($query) => $query->where('school_id', $this->schoolId())),
            ],
            'description' => 'nullable|string|max:1000',
            'age_group' => 'nullable|string|max:50',
            'level' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ]);

        $class->update($validated);

        return redirect()->route('school.classes.show', $class)
            ->with('success', "Class '{$class->name}' updated.");
    }

    /**
     * Delete class.
     */
    public function destroy(Classe $class)
    {
        if (!Auth::user()->isSuperAdmin() && $class->school_id !== $this->schoolId()) abort(403);
        $name = $class->name;
        $class->delete();
        return redirect()->route('school.classes.index')->with('success', "Class '{$name}' deleted.");
    }

    /**
     * Assign teacher to class.
     */
    public function assignTeacher(Request $request, Classe $class)
    {
        if (!Auth::user()->isSuperAdmin() && $class->school_id !== $this->schoolId()) abort(403);

        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $exists = $class->teachers()->where('teacher_id', $request->teacher_id)
            ->wherePivot('subject_id', $request->subject_id)->exists();

        if (!$exists) {
            $class->teachers()->attach($request->teacher_id, [
                'school_id' => $class->school_id,
                'subject_id' => $request->subject_id,
            ]);
        }

        return back()->with('success', 'Teacher assigned to class.');
    }

    /**
     * Remove teacher from class.
     */
    public function removeTeacher(Classe $class, User $teacher)
    {
        if (!Auth::user()->isSuperAdmin() && $class->school_id !== $this->schoolId()) abort(403);
        $class->teachers()->detach($teacher->id);
        return back()->with('success', 'Teacher removed from class.');
    }

    /**
     * Assign student to class.
     */
    public function assignStudent(Request $request, Classe $class)
    {
        if (!Auth::user()->isSuperAdmin() && $class->school_id !== $this->schoolId()) abort(403);

        $request->validate(['student_id' => 'required|exists:users,id']);

        if (!$class->students()->where('student_id', $request->student_id)->exists()) {
            $class->students()->attach($request->student_id, ['school_id' => $class->school_id]);
        }

        return back()->with('success', 'Student added to class.');
    }

    /**
     * Remove student from class.
     */
    public function removeStudent(Classe $class, User $student)
    {
        if (!Auth::user()->isSuperAdmin() && $class->school_id !== $this->schoolId()) abort(403);
        $class->students()->detach($student->id);
        return back()->with('success', 'Student removed from class.');
    }
}
