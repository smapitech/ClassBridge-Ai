<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    protected function schoolId(): int
    {
        return Auth::user()->school_id;
    }

    protected function scopeQuery($query)
    {
        if (Auth::user()->isSuperAdmin()) return $query;
        return $query->forSchool($this->schoolId());
    }

    public function index()
    {
        $query = $this->scopeQuery(TeacherProfile::with('user')->latest());
        if (request('search')) {
            $q = request('search');
            $query->whereHas('user', fn($b) => $b->where('first_name', 'like', "%{$q}%")
                ->orWhere('last_name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }
        $teachers = $query->paginate(20);
        $schools = Auth::user()->isSuperAdmin() ? \App\Models\School::orderBy('name')->get() : collect();
        return view('school.teachers.index', compact('teachers', 'schools'));
    }

    public function create()
    {
        return view('school.teachers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'bio' => 'nullable|string|max:2000',
            'qualification' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|integer|min:0',
        ]);

        $teacherRole = Role::where('slug', 'teacher')->firstOrFail();

        $user = User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $teacherRole->id,
            'school_id' => $this->schoolId(),
            'status' => 'active',
        ]);

        TeacherProfile::create([
            'user_id' => $user->id,
            'school_id' => $this->schoolId(),
            'bio' => $validated['bio'] ?? null,
            'qualification' => $validated['qualification'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'years_of_experience' => $validated['years_of_experience'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->route('school.teachers.index')->with('success', 'Teacher account created.');
    }

    public function show(TeacherProfile $teacher)
    {
        if (!Auth::user()->isSuperAdmin() && $teacher->school_id !== $this->schoolId()) abort(403);
        $teacher->load('user');
        // Get assigned classes through class_teacher pivot
        $assignedClasses = \App\Models\Classe::whereHas('teachers', fn($q) => $q->where('teacher_id', $teacher->user_id))->get();
        return view('school.teachers.show', compact('teacher', 'assignedClasses'));
    }

    public function edit(TeacherProfile $teacher)
    {
        if (!Auth::user()->isSuperAdmin() && $teacher->school_id !== $this->schoolId()) abort(403);
        $teacher->load('user');
        return view('school.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, TeacherProfile $teacher)
    {
        if (!Auth::user()->isSuperAdmin() && $teacher->school_id !== $this->schoolId()) abort(403);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:2000',
            'qualification' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'years_of_experience' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $teacher->user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'status' => $validated['status'],
        ]);

        $teacher->update([
            'bio' => $validated['bio'] ?? null,
            'qualification' => $validated['qualification'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'years_of_experience' => $validated['years_of_experience'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('school.teachers.show', $teacher)->with('success', 'Teacher updated.');
    }

    public function destroy(TeacherProfile $teacher)
    {
        if (!Auth::user()->isSuperAdmin() && $teacher->school_id !== $this->schoolId()) abort(403);
        $name = $teacher->user->displayName();
        $teacher->user->delete(); // soft delete
        $teacher->delete();
        return redirect()->route('school.teachers.index')->with('success', "Teacher '{$name}' deleted.");
    }
}