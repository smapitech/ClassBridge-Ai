<?php

namespace App\Http\Controllers\Coding;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\CodeFile;
use App\Models\CodeProject;
use App\Models\CodingAssignment;
use App\Models\CodingAssignmentSubmission;
use App\Models\CodingSession;
use App\Models\Subject;
use App\Models\User;
use App\Services\Coding\CodingStudioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CodingController extends Controller
{
    public function __construct(protected CodingStudioService $studio) {}

    protected function schoolId(): int { return Auth::user()->school_id; }

    // ==================== ASSIGNMENTS CRUD (Teacher) ====================

    public function assignments()
    {
        $query = CodingAssignment::forSchool($this->schoolId())->with(['teacher', 'classe', 'subject'])->latest();
        if (Auth::user()->isTeacher()) { $query->where('teacher_id', Auth::id()); }
        if (request('status') && in_array(request('status'), ['draft', 'published', 'closed'])) {
            $query->where('status', request('status'));
        }
        $assignments = $query->paginate(20);
        $classes = Classe::forSchool($this->schoolId())->active()->orderBy('name')->get();
        return view('coding.assignments.index', compact('assignments', 'classes'));
    }

    public function createAssignment()
    {
        $classes = Classe::forSchool($this->schoolId())->active()->orderBy('name')->get();
        $subjects = Subject::forSchool($this->schoolId())->orderBy('name')->get();
        return view('coding.assignments.create', compact('classes', 'subjects'));
    }

    public function storeAssignment(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'starter_html' => 'nullable|string',
            'starter_css' => 'nullable|string',
            'starter_js' => 'nullable|string',
            'class_id' => 'nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'due_at' => 'nullable|date',
            'status' => 'required|in:draft,published',
        ]);

        CodingAssignment::create([
            'school_id' => $this->schoolId(),
            'teacher_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'starter_html' => $validated['starter_html'] ?? null,
            'starter_css' => $validated['starter_css'] ?? null,
            'starter_js' => $validated['starter_js'] ?? null,
            'class_id' => $validated['class_id'] ?? null,
            'subject_id' => $validated['subject_id'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('coding.assignments.index')->with('success', 'Assignment created successfully.');
    }

    public function editAssignment(CodingAssignment $assignment)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isSchoolOwner() && !Auth::user()->isSchoolAdmin()
            && $assignment->teacher_id !== Auth::id()) {
            abort(403);
        }
        $classes = Classe::forSchool($this->schoolId())->active()->orderBy('name')->get();
        $subjects = Subject::forSchool($this->schoolId())->orderBy('name')->get();
        return view('coding.assignments.edit', compact('assignment', 'classes', 'subjects'));
    }

    public function updateAssignment(Request $request, CodingAssignment $assignment)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isSchoolOwner() && !Auth::user()->isSchoolAdmin()
            && $assignment->teacher_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'starter_html' => 'nullable|string',
            'starter_css' => 'nullable|string',
            'starter_js' => 'nullable|string',
            'class_id' => 'nullable|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'due_at' => 'nullable|date',
            'status' => 'required|in:draft,published,closed',
        ]);

        $assignment->update($validated);

        return redirect()->route('coding.assignments.index')->with('success', 'Assignment updated successfully.');
    }

    public function deleteAssignment(CodingAssignment $assignment)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isSchoolOwner() && !Auth::user()->isSchoolAdmin()
            && $assignment->teacher_id !== Auth::id()) {
            abort(403);
        }
        $assignment->delete();
        return redirect()->route('coding.assignments.index')->with('success', 'Assignment deleted.');
    }

    public function previewAssignment(CodingAssignment $assignment)
    {
        if ($assignment->school_id !== $this->schoolId()) abort(403);
        return view('coding.assignments.preview', compact('assignment'));
    }

    // ==================== STUDENT WORKSPACE ====================

    public function workspace(Request $request, CodingAssignment $assignment)
    {
        if (!Auth::user()->isSuperAdmin() && $assignment->school_id !== $this->schoolId()) abort(403);
        if (Auth::user()->isStudent() && $assignment->status !== 'published') abort(403, 'Assignment not available.');
        if (Auth::user()->isStudent() && $assignment->class_id) {
            $inClass = Auth::user()->classesAsStudent()->where('class_id', $assignment->class_id)->exists();
            if (!$inClass && !Auth::user()->isSuperAdmin()) abort(403, 'You are not in this class.');
        }

        $targetStudent = null;
        if (Auth::user()->isStudent()) {
            $targetStudent = Auth::user();
        } elseif ($request->filled('student_id')) {
            $targetStudent = User::whereKey($request->integer('student_id'))
                ->where('school_id', $assignment->school_id)
                ->firstOrFail();
        } else {
            return redirect()->route('coding.review', $assignment);
        }

        // Get or create submission + project for this student
        $submission = CodingAssignmentSubmission::firstOrCreate(
            ['coding_assignment_id' => $assignment->id, 'student_id' => $targetStudent->id],
            ['school_id' => $assignment->school_id, 'status' => 'draft']
        );

        // Get or create project
        if (!$submission->code_project_id) {
            $project = CodeProject::create([
                'school_id' => $assignment->school_id,
                'student_id' => $targetStudent->id,
                'teacher_id' => $assignment->teacher_id,
                'class_id' => $assignment->class_id,
                'subject_id' => $assignment->subject_id,
                'title' => 'Submission: ' . $assignment->title,
                'slug' => Str::slug('submission-' . $assignment->id . '-' . $targetStudent->id),
                'status' => 'draft',
                'visibility' => 'teacher',
                'created_by' => Auth::id(),
            ]);
            $submission->code_project_id = $project->id;
            $submission->save();

            // Populate starter files
            CodeFile::create(['school_id' => $assignment->school_id, 'code_project_id' => $project->id, 'filename' => 'index.html', 'language' => 'html', 'content' => $assignment->starter_html ?? '', 'sort_order' => 1]);
            CodeFile::create(['school_id' => $assignment->school_id, 'code_project_id' => $project->id, 'filename' => 'style.css', 'language' => 'css', 'content' => $assignment->starter_css ?? '', 'sort_order' => 2]);
            CodeFile::create(['school_id' => $assignment->school_id, 'code_project_id' => $project->id, 'filename' => 'script.js', 'language' => 'javascript', 'content' => $assignment->starter_js ?? '', 'sort_order' => 3]);
        }

        $project = $submission->project;
        $project->load('files');

        $session = CodingSession::firstOrCreate(
            [
                'coding_assignment_id' => $assignment->id,
                'student_id' => $targetStudent->id,
            ],
            [
                'school_id' => $assignment->school_id,
                'teacher_id' => $assignment->teacher_id,
                'class_id' => $assignment->class_id,
                'subject_id' => $assignment->subject_id,
                'title' => 'Live Coding Studio: ' . $assignment->title,
                'slug' => Str::slug('coding-session-' . $assignment->id . '-' . $targetStudent->id),
                'join_code' => CodingSession::generateJoinCode(),
                'status' => 'waiting',
                'lesson_mode' => $this->determineLessonMode($assignment, $project),
                'permissions' => $this->studio->defaultPermissions(new CodingSession(), Auth::user()->isTeacher()),
                'lesson_steps' => $this->defaultLessonSteps($assignment),
                'metadata' => [
                    'assignment_id' => $assignment->id,
                    'student_id' => $targetStudent->id,
                    'project_id' => $project->id,
                ],
                'created_by' => Auth::id(),
            ]
        );

        $session->loadMissing(['teacher', 'student', 'assignment', 'files', 'participants.user', 'messages.user', 'events.user']);
        $this->studio->seedFiles($session, $assignment);

        $participantRole = Auth::user()->isTeacher() ? 'teacher' : (Auth::user()->isParent() ? 'observer' : 'student');
        $participantPermissions = $this->studio->participantPermissions($session, Auth::user());
        $this->studio->ensureParticipant($session, Auth::user(), $participantRole, $participantPermissions);

        return redirect()->route('coding.sessions.show', $session);
    }

    // ==================== STUDENT MY SUBMISSIONS ====================

    public function mySubmissions()
    {
        $submissions = CodingAssignmentSubmission::where('student_id', Auth::id())
            ->with(['assignment.teacher', 'assignment.classe', 'project'])
            ->latest()
            ->paginate(20);
        return view('coding.my-submissions', compact('submissions'));
    }

    // ==================== SAVE PROJECT (AJAX) ====================

    public function saveProject(Request $request, CodeProject $project)
    {
        if ($project->student_id !== Auth::id() && !Auth::user()->isSchoolAdmin()
            && !Auth::user()->isSchoolOwner() && !Auth::user()->isSuperAdmin()
            && $project->teacher_id !== Auth::id()) abort(403);

        // Don't allow saving if already submitted/reviewed (student)
        if (Auth::user()->isStudent()) {
            $submission = CodingAssignmentSubmission::where('code_project_id', $project->id)
                ->where('student_id', Auth::id())->first();
            if ($submission && in_array($submission->status, ['submitted', 'reviewed'])) {
                return response()->json(['success' => false, 'message' => 'Cannot edit a submitted assignment.'], 403);
            }
        }

        $request->validate([
            'html' => 'nullable|string',
            'css' => 'nullable|string',
            'js' => 'nullable|string',
        ]);

        foreach (['html' => 'index.html', 'css' => 'style.css', 'js' => 'script.js'] as $lang => $filename) {
            $content = $request->input($lang, '');
            CodeFile::updateOrCreate(
                ['code_project_id' => $project->id, 'filename' => $filename],
                ['school_id' => $project->school_id, 'language' => $lang === 'js' ? 'javascript' : $lang, 'content' => $content, 'sort_order' => $lang === 'html' ? 1 : ($lang === 'css' ? 2 : 3)]
            );
        }

        $project->touch();

        return response()->json(['success' => true, 'saved_at' => now()->toDateTimeString()]);
    }

    // ==================== RESET TO STARTER ====================

    public function resetToStarter(CodeProject $project)
    {
        if ($project->student_id !== Auth::id() && !Auth::user()->isSchoolAdmin()
            && !Auth::user()->isSchoolOwner() && !Auth::user()->isSuperAdmin()) abort(403);

        // Find the associated submission to get starter code from assignment
        $submission = CodingAssignmentSubmission::where('code_project_id', $project->id)->first();
        if (!$submission) {
            return back()->with('error', 'No associated assignment found.');
        }

        $assignment = $submission->assignment;

        // Reset files to starter code
        CodeFile::updateOrCreate(
            ['code_project_id' => $project->id, 'filename' => 'index.html'],
            ['school_id' => $project->school_id, 'language' => 'html', 'content' => $assignment->starter_html ?? '', 'sort_order' => 1]
        );
        CodeFile::updateOrCreate(
            ['code_project_id' => $project->id, 'filename' => 'style.css'],
            ['school_id' => $project->school_id, 'language' => 'css', 'content' => $assignment->starter_css ?? '', 'sort_order' => 2]
        );
        CodeFile::updateOrCreate(
            ['code_project_id' => $project->id, 'filename' => 'script.js'],
            ['school_id' => $project->school_id, 'language' => 'javascript', 'content' => $assignment->starter_js ?? '', 'sort_order' => 3]
        );

        $project->touch();

        return back()->with('success', 'Code reset to starter template.');
    }

    // ==================== SUBMIT ASSIGNMENT ====================

    public function submitAssignment(CodingAssignmentSubmission $submission)
    {
        if ($submission->student_id !== Auth::id()) abort(403);

        if ($submission->status === 'submitted' || $submission->status === 'reviewed') {
            return back()->with('error', 'Assignment already submitted.');
        }

        $submission->status = 'submitted';
        $submission->submitted_at = now();
        $submission->save();

        if ($submission->project) {
            $submission->project->status = 'submitted';
            $submission->project->save();
        }

        return back()->with('success', 'Assignment submitted successfully!');
    }

    // ==================== TEACHER REVIEW ====================

    public function reviewSubmissions(CodingAssignment $assignment)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isSchoolOwner() && !Auth::user()->isSchoolAdmin()
            && $assignment->teacher_id !== Auth::id()) abort(403);
        $assignment->load(['submissions.student', 'submissions.project', 'classe']);
        return view('coding.assignments.review', compact('assignment'));
    }

    public function viewSubmission(CodingAssignmentSubmission $submission)
    {
        $assignment = $submission->assignment;
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isSchoolOwner() && !Auth::user()->isSchoolAdmin()
            && $assignment->teacher_id !== Auth::id()) abort(403);
        $submission->load(['student', 'project.files']);
        return view('coding.assignments.view-submission', compact('assignment', 'submission'));
    }

    public function saveReview(Request $request, CodingAssignmentSubmission $submission)
    {
        $assignment = $submission->assignment;
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isSchoolOwner() && !Auth::user()->isSchoolAdmin()
            && $assignment->teacher_id !== Auth::id()) abort(403);

        $validated = $request->validate([
            'teacher_feedback' => 'nullable|string',
            'score' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:reviewed,returned',
        ]);

        $submission->update($validated);

        // Update project status
        if ($submission->project) {
            $submission->project->status = $validated['status'] === 'reviewed' ? 'reviewed' : 'active';
            $submission->project->save();
        }

        return redirect()->route('coding.review', $assignment)->with('success', 'Review saved successfully.');
    }

    // ==================== SCHOOL ADMIN: PROGRESS REPORT ====================

    public function progressReport()
    {
        $schoolId = $this->schoolId();

        $assignments = CodingAssignment::forSchool($schoolId)
            ->with(['teacher', 'classe', 'submissions' => function($q) {
                $q->with('student');
            }])
            ->latest()
            ->get();

        $totalAssignments = $assignments->count();

        // Get student counts
        $studentsWithSubmissions = User::where('school_id', $schoolId)
            ->whereHas('role', fn($q) => $q->where('slug', 'student'))
            ->whereHas('submissions')
            ->count();

        $totalSubmissions = CodingAssignmentSubmission::where('school_id', $schoolId)->count();
        $gradedSubmissions = CodingAssignmentSubmission::where('school_id', $schoolId)
            ->where('status', 'reviewed')->count();
        $pendingSubmissions = CodingAssignmentSubmission::where('school_id', $schoolId)
            ->where('status', 'submitted')->count();

        return view('coding.progress', compact(
            'assignments', 'totalAssignments', 'studentsWithSubmissions',
            'totalSubmissions', 'gradedSubmissions', 'pendingSubmissions'
        ));
    }

    protected function determineLessonMode(CodingAssignment $assignment, ?CodeProject $project = null): string
    {
        $mode = data_get($project?->metadata ?? [], 'lesson_mode');

        if (is_string($mode) && $mode !== '') {
            return $mode;
        }

        if ($assignment->starter_js || $assignment->starter_css || $assignment->starter_html) {
            return 'mixed';
        }

        return 'coding';
    }

    protected function defaultLessonSteps(CodingAssignment $assignment): array
    {
        return [
            ['title' => 'Read the goal', 'description' => $assignment->description ?: 'Study the task and the starter code.'],
            ['title' => 'Edit together', 'description' => 'Teacher and student work inside the same protected coding workspace.'],
            ['title' => 'Run and review', 'description' => 'Preview the output, fix issues, and submit the work.'],
        ];
    }
}
