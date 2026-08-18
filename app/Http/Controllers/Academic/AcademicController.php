<?php
namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Classe;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\ParentReport;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Subject;
use App\Models\TeacherFeedback;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicController extends Controller
{
    protected function schoolId(): int { return Auth::user()->school_id; }
    protected function isTeacher() { return Auth::user()->isTeacher()||Auth::user()->isSchoolAdmin()||Auth::user()->isSchoolOwner(); }

    // ==================== HOMEWORK (Teacher) ====================
    public function homeworks()
    {
        $query = Homework::forSchool($this->schoolId())->with(['classe','subject','submissions'])->latest();
        if (Auth::user()->isTeacher()) $query->where('teacher_id', Auth::id());
        $homeworks = $query->paginate(20);
        $classes = Classe::forSchool($this->schoolId())->active()->orderBy('name')->get();
        return view('academic.homeworks.index', compact('homeworks','classes'));
    }
    public function createHomework()
    {
        $classes = Classe::forSchool($this->schoolId())->active()->orderBy('name')->get();
        $subjects = Subject::forSchool($this->schoolId())->orderBy('name')->get();
        return view('academic.homeworks.create', compact('classes','subjects'));
    }
    public function storeHomework(Request $r)
    {
        $v = $r->validate(['title'=>'required|string|max:255','instructions'=>'nullable|string','class_id'=>'required|exists:classes,id','subject_id'=>'nullable|exists:subjects,id','due_at'=>'nullable|date','status'=>'required|in:draft,published']);
        Homework::create(['school_id'=>$this->schoolId(),'teacher_id'=>Auth::id()]+$v);
        return redirect()->route('academic.homeworks')->with('success','Homework created.');
    }
    public function showHomework(Homework $homework)
    {
        if ($homework->school_id !== $this->schoolId()) abort(403);
        $homework->load(['classe','subject','teacher','submissions.student']);
        $students = $homework->classe?->students ?? collect();
        return view('academic.homeworks.show', compact('homework','students'));
    }
    public function gradeSubmission(Request $r, HomeworkSubmission $submission)
    {
        if ($submission->homework->teacher_id !== Auth::id() && !Auth::user()->isSuperAdmin()) abort(403);
        $v = $r->validate(['score'=>'nullable|numeric|min:0|max:100','teacher_feedback'=>'nullable|string','status'=>'required|in:reviewed,returned']);
        $submission->update($v);
        return back()->with('success','Submission graded.');
    }

    // ==================== STUDENT HOMEWORK ====================
    public function myHomework()
    {
        $submissions = HomeworkSubmission::where('student_id', Auth::id())
            ->with(['homework.classe','homework.teacher','homework.subject'])
            ->latest()->paginate(20);
        return view('academic.homeworks.student', compact('submissions'));
    }
    public function submitHomework(Request $r, Homework $homework)
    {
        $sub = HomeworkSubmission::firstOrCreate(
            ['homework_id'=>$homework->id,'student_id'=>Auth::id()],
            ['school_id'=>$homework->school_id,'status'=>'draft']
        );
        $sub->update(['answer'=>$r->answer,'status'=>'submitted','submitted_at'=>now()]);
        return back()->with('success','Homework submitted.');
    }

    // ==================== QUIZZES (Teacher) ====================
    public function quizzes()
    {
        $query = Quiz::forSchool($this->schoolId())->with(['classe','questions','attempts'])->latest();
        if (Auth::user()->isTeacher()) $query->where('teacher_id',Auth::id());
        return view('academic.quizzes.index', ['quizzes'=>$query->paginate(20)]);
    }
    public function createQuiz()
    {
        $classes = Classe::forSchool($this->schoolId())->active()->orderBy('name')->get();
        return view('academic.quizzes.create', compact('classes'));
    }
    public function storeQuiz(Request $r)
    {
        $v = $r->validate(['title'=>'required|string|max:255','description'=>'nullable|string','class_id'=>'required|exists:classes,id','subject_id'=>'nullable|exists:subjects,id','duration_minutes'=>'nullable|integer','status'=>'required|in:draft,published']);
        $quiz = Quiz::create(['school_id'=>$this->schoolId(),'teacher_id'=>Auth::id()]+$v);
        return redirect()->route('academic.quizzes.questions',$quiz)->with('success','Quiz created. Add questions.');
    }
    public function quizQuestions(Quiz $quiz)
    {
        if ($quiz->school_id!==$this->schoolId()) abort(403);
        $quiz->load('questions');
        return view('academic.quizzes.questions', compact('quiz'));
    }
    public function storeQuestion(Request $r, Quiz $quiz)
    {
        $v = $r->validate([
            'question_text'=>'required|string','question_type'=>'required|in:multiple_choice,true_false,short_answer',
            'options'=>'nullable|string','correct_answer'=>'nullable|string','marks'=>'nullable|numeric','explanation'=>'nullable|string'
        ]);
        QuizQuestion::create([
            'school_id'=>$quiz->school_id,'quiz_id'=>$quiz->id,
            'question_text'=>$v['question_text'],'question_type'=>$v['question_type'],
            'options'=>$v['options'] ? array_map('trim',explode("\n",$v['options'])) : null,
            'correct_answer'=>$v['correct_answer']??null,'marks'=>$v['marks']??1,
            'explanation'=>$v['explanation']??null,
            'sort_order'=>($quiz->questions()->max('sort_order')??0)+1
        ]);
        return back()->with('success','Question added.');
    }
    public function quizAttempts(Quiz $quiz)
    {
        if ($quiz->school_id!==$this->schoolId()) abort(403);
        $quiz->load('attempts.student');
        return view('academic.quizzes.attempts', compact('quiz'));
    }

    // ==================== STUDENT QUIZ ====================
    public function takeQuiz(Quiz $quiz)
    {
        if ($quiz->status !== 'published') abort(403);
        $quiz->load('questions');
        $attempt = QuizAttempt::firstOrCreate(
            ['quiz_id'=>$quiz->id,'student_id'=>Auth::id()],
            ['school_id'=>$quiz->school_id,'started_at'=>now(),'status'=>'in_progress']
        );
        return view('academic.quizzes.take', compact('quiz','attempt'));
    }
    public function submitQuiz(Request $r, Quiz $quiz)
    {
        $attempt = QuizAttempt::where('quiz_id',$quiz->id)->where('student_id',Auth::id())->firstOrFail();
        $answers = $r->input('answers',[]);
        $score = 0; $total = 0;
        foreach ($quiz->questions as $q) {
            $given = $answers[$q->id] ?? '';
            if (in_array($q->question_type,['multiple_choice','true_false'])) {
                if (trim(strtolower($given)) === trim(strtolower($q->correct_answer??''))) $score += $q->marks;
            }
            $total += $q->marks;
        }
        $attempt->update(['answers'=>$answers,'score'=>$score,'submitted_at'=>now(),'status'=>'submitted']);
        return redirect()->route('academic.quizzes.result',$quiz)->with('success',"Quiz submitted! Score: $score/$total");
    }
    public function quizResult(Quiz $quiz)
    {
        $attempt = QuizAttempt::where('quiz_id',$quiz->id)->where('student_id',Auth::id())->first();
        $quiz->load('questions');
        return view('academic.quizzes.result', compact('quiz','attempt'));
    }

    // ==================== ATTENDANCE ====================
    public function attendance(Request $r)
    {
        $query = AttendanceRecord::forSchool($this->schoolId())->with('student.classe')->latest();
        if ($r->class_id) $query->where('class_id',$r->class_id);
        if ($r->date) $query->whereDate('attendance_date',$r->date);
        $records = $query->paginate(50);
        $classes = Classe::forSchool($this->schoolId())->active()->get();
        return view('academic.attendance.index', compact('records','classes'));
    }
    public function markAttendance(Request $r)
    {
        $r->validate(['class_id'=>'required|exists:classes,id','date'=>'required|date','students'=>'required|array']);
        foreach ($r->students as $sid => $status) {
            AttendanceRecord::updateOrCreate(
                ['student_id'=>$sid,'attendance_date'=>$r->date,'class_id'=>$r->class_id],
                ['school_id'=>$this->schoolId(),'teacher_id'=>Auth::id(),'status'=>$status]
            );
        }
        return back()->with('success','Attendance saved.');
    }

    // ==================== FEEDBACK ====================
    public function feedback(Request $r)
    {
        $query = TeacherFeedback::forSchool($this->schoolId())->with(['student','teacher','classe'])->latest();
        if (Auth::user()->isTeacher()) $query->where('teacher_id',Auth::id());
        if (Auth::user()->isParent()) {
            $childrenIds = Auth::user()->parentLinks()->pluck('student_id');
            $query->whereIn('student_id',$childrenIds)->visibleToParents();
        }
        $feedbacks = $query->paginate(20);
        return view('academic.feedback.index', compact('feedbacks'));
    }
    public function storeFeedback(Request $r)
    {
        $v = $r->validate(['student_id'=>'required|exists:users,id','feedback_type'=>'required|in:general,homework,quiz,behavior,progress','title'=>'nullable|string|max:255','comment'=>'required|string','visibility'=>'required|in:internal,parent_visible','class_id'=>'nullable|exists:classes,id']);
        TeacherFeedback::create(['school_id'=>$this->schoolId(),'teacher_id'=>Auth::id()]+$v);
        return back()->with('success','Feedback saved.');
    }

    // ==================== REPORTS ====================
    public function reports()
    {
        $query = ParentReport::forSchool($this->schoolId())->with(['student','classe','generator'])->latest();
        if (Auth::user()->isTeacher()) $query->where('generated_by',Auth::id());
        if (Auth::user()->isParent()) {
            $childrenIds = Auth::user()->parentLinks()->pluck('student_id');
            $query->whereIn('student_id',$childrenIds)->published();
        }
        return view('academic.reports.index', ['reports'=>$query->paginate(20)]);
    }
    public function generateReport(Request $r)
    {
        $v = $r->validate(['student_id'=>'required|exists:users,id','class_id'=>'nullable|exists:classes,id','report_period_start'=>'nullable|date','report_period_end'=>'nullable|date','teacher_comments'=>'nullable|string']);
        $student = User::findOrFail($v['student_id']);
        $start = $v['report_period_start'] ?: now()->subMonth();
        $end = $v['report_period_end'] ?: now();
        $att = AttendanceRecord::where('student_id',$student->id)->whereBetween('attendance_date',[$start,$end])->get();
        $hw = HomeworkSubmission::where('student_id',$student->id)->whereBetween('created_at',[$start,$end])->with('homework')->get();
        $qz = QuizAttempt::where('student_id',$student->id)->whereBetween('created_at',[$start,$end])->with('quiz')->get();
        $report = ParentReport::create([
            'school_id'=>$this->schoolId(),'student_id'=>$student->id,'class_id'=>$v['class_id']??null,'generated_by'=>Auth::id(),
            'report_period_start'=>$start,'report_period_end'=>$end,
            'attendance_summary'=>['total'=>count($att),'present'=>$att->where('status','present')->count(),'absent'=>$att->where('status','absent')->count(),'late'=>$att->where('status','late')->count()],
            'homework_summary'=>['total'=>count($hw),'submitted'=>$hw->where('status','submitted')->count(),'reviewed'=>$hw->where('status','reviewed')->count(),'avg_score'=>$hw->avg('score')],
            'quiz_summary'=>['total'=>count($qz),'avg_score'=>$qz->avg('score')],
            'teacher_comments'=>$v['teacher_comments']??null,'status'=>'draft',
        ]);
        return redirect()->route('academic.reports.show',$report)->with('success','Report generated.');
    }
    public function showReport(ParentReport $report)
    {
        if ($report->school_id!==$this->schoolId()) abort(403);
        if (Auth::user()->isParent()) {
            $childrenIds = Auth::user()->parentLinks()->pluck('student_id');
            if (!in_array($report->student_id,$childrenIds->toArray())) abort(403);
        }
        $report->load(['student','classe','generator']);
        return view('academic.reports.show', compact('report'));
    }
    public function publishReport(ParentReport $report)
    {
        if ($report->school_id!==$this->schoolId()) abort(403);
        $report->update(['status'=>'published','published_at'=>now()]);
        return back()->with('success','Report published for parent view.');
    }
}