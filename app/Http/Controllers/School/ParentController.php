<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ParentController extends Controller
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
        $query = $this->scopeQuery(ParentProfile::with('user')->latest());
        if (request('search')) {
            $q = request('search');
            $query->whereHas('user', fn($b) => $b->where('first_name', 'like', "%{$q}%")
                ->orWhere('last_name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }
        $parents = $query->paginate(20);
        $schools = Auth::user()->isSuperAdmin() ? \App\Models\School::orderBy('name')->get() : collect();
        return view('school.parents.index', compact('parents', 'schools'));
    }

    public function create()
    {
        return view('school.parents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'relationship' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:255',
            'emergency_contact' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
        ]);

        $parentRole = Role::where('slug', 'parent')->firstOrFail();

        $user = User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $parentRole->id,
            'school_id' => $this->schoolId(),
            'status' => 'active',
        ]);

        ParentProfile::create([
            'user_id' => $user->id,
            'school_id' => $this->schoolId(),
            'relationship' => $validated['relationship'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->route('school.parents.index')->with('success', 'Parent account created.');
    }

    public function show(ParentProfile $parent)
    {
        if (!Auth::user()->isSuperAdmin() && $parent->school_id !== $this->schoolId()) abort(403);
        $parent->load('user');
        $children = DB::table('parent_student')
            ->where('parent_id', $parent->user_id)
            ->join('users', 'users.id', '=', 'parent_student.student_id')
            ->select('users.*', 'parent_student.relationship')
            ->get();
        $allStudents = User::where('school_id', $parent->school_id)
            ->whereHas('role', fn($q) => $q->where('slug', 'student'))->get();
        return view('school.parents.show', compact('parent', 'children', 'allStudents'));
    }

    public function edit(ParentProfile $parent)
    {
        if (!Auth::user()->isSuperAdmin() && $parent->school_id !== $this->schoolId()) abort(403);
        $parent->load('user');
        return view('school.parents.edit', compact('parent'));
    }

    public function update(Request $request, ParentProfile $parent)
    {
        if (!Auth::user()->isSuperAdmin() && $parent->school_id !== $this->schoolId()) abort(403);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'relationship' => 'nullable|string|max:50',
            'occupation' => 'nullable|string|max:255',
            'emergency_contact' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        $parent->user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'status' => $validated['status'],
        ]);

        $parent->update([
            'relationship' => $validated['relationship'] ?? null,
            'occupation' => $validated['occupation'] ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'address' => $validated['address'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('school.parents.show', $parent)->with('success', 'Parent updated.');
    }

    public function destroy(ParentProfile $parent)
    {
        if (!Auth::user()->isSuperAdmin() && $parent->school_id !== $this->schoolId()) abort(403);
        $name = $parent->user->displayName();
        $parent->user->delete();
        $parent->delete();
        return redirect()->route('school.parents.index')->with('success', "Parent '{$name}' deleted.");
    }

    public function linkChild(Request $request, ParentProfile $parent)
    {
        if (!Auth::user()->isSuperAdmin() && $parent->school_id !== $this->schoolId()) abort(403);

        $request->validate(['student_id' => 'required|exists:users,id', 'relationship' => 'nullable|string|max:50']);

        $exists = DB::table('parent_student')
            ->where('parent_id', $parent->user_id)
            ->where('student_id', $request->student_id)
            ->exists();

        if (!$exists) {
            DB::table('parent_student')->insert([
                'school_id' => $parent->school_id,
                'parent_id' => $parent->user_id,
                'student_id' => $request->student_id,
                'relationship' => $request->relationship,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Child linked to parent.');
    }

    public function unlinkChild(ParentProfile $parent, User $student)
    {
        if (!Auth::user()->isSuperAdmin() && $parent->school_id !== $this->schoolId()) abort(403);

        DB::table('parent_student')
            ->where('parent_id', $parent->user_id)
            ->where('student_id', $student->id)
            ->delete();

        return back()->with('success', 'Child unlinked from parent.');
    }
}