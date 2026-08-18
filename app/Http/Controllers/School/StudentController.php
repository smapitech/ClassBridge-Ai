<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    protected function schoolId(): int
    {
        return Auth::user()->school_id;
    }

    protected function scopeQuery($query)
    {
        if (Auth::user()->isSuperAdmin()) return $query;
        if (Auth::user()->isTeacher()) {
            // Teacher sees only students in their assigned classes
            return $query->whereHas('user.classesAsStudent', fn($q) =>
                $q->whereHas('teachers', fn($t) => $t->where('teacher_id', Auth::id()))
            );
        }
        if (Auth::user()->isParent()) {
            // Parent sees only their linked children
            return $query->whereHas('user.parentLinks', fn($q) => $q->where('parent_id', Auth::id()));
        }
        return $query->forSchool($this->schoolId());
    }

    public function index()
    {
        $query = $this->scopeQuery(StudentProfile::with('user')->latest());
        if (request('search')) {
            $q = request('search');
            $query->whereHas('user', fn($b) => $b->where('first_name', 'like', "%{$q}%")
                ->orWhere('last_name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }
        $students = $query->paginate(20);
        $schools = Auth::user()->isSuperAdmin() ? \App\Models\School::orderBy('name')->get() : collect();
        return view('school.students.index', compact('students', 'schools'));
    }

    public function create()
    {
        $classes = \App\Models\Classe::forSchool($this->schoolId())->active()->get();
        return view('school.students.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'admission_number' => 'nullable|string|max:100|unique:student_profiles',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'learning_level' => 'nullable|string|max:50',
            'class_id' => 'nullable|exists:classes,id',
        ]);

        $studentRole = Role::where('slug', 'student')->firstOrFail();

        $user = User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $studentRole->id,
            'school_id' => $this->schoolId(),
            'status' => 'active',
        ]);

        $profile = StudentProfile::create([
            'user_id' => $user->id,
            'school_id' => $this->schoolId(),
            'admission_number' => $validated['admission_number'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'learning_level' => $validated['learning_level'] ?? null,
            'class_id' => $validated['class_id'] ?? null,
            'status' => 'active',
        ]);

        // Assign to class if selected
        if ($validated['class_id'] ?? null) {
            \DB::table('class_student')->insert([
                'school_id' => $this->schoolId(),
                'class_id' => $validated['class_id'],
                'student_id' => $user->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return redirect()->route('school.students.index')->with('success', 'Student account created.');
    }

    public function show(StudentProfile $student)
    {
        if (!Auth::user()->isSuperAdmin() && $student->school_id !== $this->schoolId()) abort(403);
        $student->load('user');
        $enrolledClasses = \App\Models\Classe::whereHas('students', fn($q) => $q->where('student_id', $student->user_id))->get();
        $linkedParents = \DB::table('parent_student')
            ->where('student_id', $student->user_id)
            ->join('users', 'users.id', '=', 'parent_student.parent_id')
            ->select('users.*', 'parent_student.relationship')
            ->get();
        return view('school.students.show', compact('student', 'enrolledClasses', 'linkedParents'));
    }

    public function edit(StudentProfile $student)
    {
        if (!Auth::user()->isSuperAdmin() && $student->school_id !== $this->schoolId()) abort(403);
        $student->load('user');
        $classes = \App\Models\Classe::forSchool($student->school_id)->active()->get();
        return view('school.students.edit', compact('student', 'classes'));
    }

    public function update(Request $request, StudentProfile $student)
    {
        if (!Auth::user()->isSuperAdmin() && $student->school_id !== $this->schoolId()) abort(403);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'admission_number' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'learning_level' => 'nullable|string|max:50',
            'class_id' => 'nullable|exists:classes,id',
            'status' => 'required|in:active,inactive',
        ]);

        $student->user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'status' => $validated['status'],
        ]);

        $student->update([
            'admission_number' => $validated['admission_number'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'learning_level' => $validated['learning_level'] ?? null,
            'class_id' => $validated['class_id'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('school.students.show', $student)->with('success', 'Student updated.');
    }

    public function destroy(StudentProfile $student)
    {
        if (!Auth::user()->isSuperAdmin() && $student->school_id !== $this->schoolId()) abort(403);
        $name = $student->user->displayName();
        $student->user->delete();
        $student->delete();
        return redirect()->route('school.students.index')->with('success', "Student '{$name}' deleted.");
    }
}