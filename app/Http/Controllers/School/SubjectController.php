<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
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
        $query = $this->scopeQuery(Subject::with('course')->latest());
        if (request('search')) {
            $q = request('search');
            $query->where(fn($b) => $b->where('name', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"));
        }
        $subjects = $query->paginate(20);
        $schools = Auth::user()->isSuperAdmin() ? \App\Models\School::orderBy('name')->get() : collect();
        return view('school.subjects.index', compact('subjects', 'schools'));
    }

    public function create()
    {
        $courses = Course::forSchool($this->schoolId())->active()->orderBy('name')->get();
        return view('school.subjects.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'course_id' => [
                'nullable',
                Rule::exists('courses', 'id')->where(fn ($query) => $query->where('school_id', $this->schoolId())),
            ],
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        $subject = Subject::create([
            'school_id' => $this->schoolId(),
            'course_id' => $validated['course_id'] ?? null,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('school.subjects.index')->with('success', "Subject '{$subject->name}' created.");
    }

    public function edit(Subject $subject)
    {
        if (!Auth::user()->isSuperAdmin() && $subject->school_id !== $this->schoolId()) abort(403);
        $courses = Course::forSchool($this->schoolId())->active()->orderBy('name')->get();
        return view('school.subjects.edit', compact('subject', 'courses'));
    }

    public function update(Request $request, Subject $subject)
    {
        if (!Auth::user()->isSuperAdmin() && $subject->school_id !== $this->schoolId()) abort(403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'course_id' => [
                'nullable',
                Rule::exists('courses', 'id')->where(fn ($query) => $query->where('school_id', $this->schoolId())),
            ],
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);
        $subject->update($validated);
        return redirect()->route('school.subjects.index')->with('success', 'Subject updated.');
    }

    public function destroy(Subject $subject)
    {
        if (!Auth::user()->isSuperAdmin() && $subject->school_id !== $this->schoolId()) abort(403);
        $name = $subject->name;
        $subject->delete();
        return redirect()->route('school.subjects.index')->with('success', "Subject '{$name}' deleted.");
    }
}
