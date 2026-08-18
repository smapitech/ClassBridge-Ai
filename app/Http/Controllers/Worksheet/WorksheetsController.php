<?php namespace App\Http\Controllers\Worksheet;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\InteractiveWorksheet;
use App\Models\Subject;
use App\Models\WorksheetAttempt;
use App\Services\AI\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorksheetsController extends Controller
{
    /** Teacher: list worksheets */
    public function index(Request $r)
    {
        $query = InteractiveWorksheet::forSchool(Auth::user()->school_id)->with(['teacher','classe','subject'])->latest();
        if (Auth::user()->isTeacher()) $query->where('teacher_id', Auth::id());
        if ($r->class_id) $query->where('class_id', $r->class_id);
        $worksheets = $query->paginate(20);
        $classes = Classe::forSchool(Auth::user()->school_id)->active()->get();
        $subjects = Subject::forSchool(Auth::user()->school_id)->get();
        return view('worksheets.index', compact('worksheets','classes','subjects'));
    }

    /** Teacher: create worksheet */
    public function create()
    {
        $classes = Classe::forSchool(Auth::user()->school_id)->active()->get();
        $subjects = Subject::forSchool(Auth::user()->school_id)->get();
        return view('worksheets.create', compact('classes','subjects'));
    }

    /** Teacher: store worksheet */
    public function store(Request $r)
    {
        $v = $r->validate([
            'title'=>'required|string|max:255','class_id'=>'nullable|exists:classes,id','subject_id'=>'nullable|exists:subjects,id',
            'age_group'=>'nullable|string','worksheet_type'=>'required|in:mixed,multiple_choice,fill_blank,matching,drag_drop,ordering,short_answer,drawing',
            'instructions'=>'nullable|string','content_json'=>'required|json','answer_key'=>'nullable|json',
            'status'=>'required|in:draft,published','due_at'=>'nullable|date',
        ]);
        InteractiveWorksheet::create(['school_id'=>Auth::user()->school_id,'teacher_id'=>Auth::id()]+$v+['content_json'=>json_decode($v['content_json'],true),'answer_key'=>$v['answer_key']?json_decode($v['answer_key'],true):null]);
        return redirect()->route('worksheets.index')->with('success','Worksheet created.');
    }

    /** AI generate worksheet */
    public function generateAI(Request $r)
    {
        $r->validate(['subject'=>'required|string','topic'=>'required|string','age_group'=>'nullable|string','type'=>'required|string','count'=>'required|integer|min:1|max:20']);
        try {
            $aiService = app(AIService::class);
            $prompt = "Generate a {$r->type} worksheet in JSON format for {$r->subject}: {$r->topic}, age {$r->age_group}, {$r->count} questions. ".
                "Return ONLY valid JSON: {\"title\":\"...\",\"questions\":[{\"type\":\"multiple_choice|fill_blank|matching|short_answer\",\"question\":\"...\",\"options\":[...],\"answer\":\"...\"}]}";
            $json = $aiService->generate($prompt);
            $content = json_decode($json, true);
            if (!$content) $content = ['title'=>$r->topic, 'questions'=>[['type'=>'short_answer','question'=>'AI generated content unavailable. Please add questions manually.','answer'=>'Manual review needed']]];
        } catch (\Exception $e) {
            $content = ['title'=>$r->topic, 'questions'=>[['type'=>'short_answer','question'=>'AI generation failed. Please add questions manually.','answer'=>'Manual review needed']]];
        }
        return response()->json(['success'=>true,'content'=>$content]);
    }

    /** Teacher: view attempts */
    public function attempts(InteractiveWorksheet $worksheet)
    {
        if ($worksheet->school_id !== Auth::user()->school_id) abort(403);
        $attempts = $worksheet->attempts()->with('student')->latest()->get();
        return view('worksheets.attempts', compact('worksheet','attempts'));
    }

    /** Teacher: grade attempt */
    public function grade(Request $r, WorksheetAttempt $attempt)
    {
        if ($attempt->school_id !== Auth::user()->school_id) abort(403);
        $attempt->update(['score'=>$r->score,'teacher_feedback'=>$r->feedback,'status'=>'graded']);
        return back()->with('success','Attempt graded.');
    }

    /** Student: my worksheets */
    public function studentIndex()
    {
        $student = Auth::user();
        $attempts = WorksheetAttempt::where('student_id', $student->id)->with('worksheet.teacher')->latest()->paginate(15);
        return view('worksheets.student', compact('attempts'));
    }

    /** Student: attempt worksheet */
    public function attempt(InteractiveWorksheet $worksheet)
    {
        $attempt = WorksheetAttempt::firstOrCreate(
            ['worksheet_id'=>$worksheet->id,'student_id'=>Auth::id()],
            ['school_id'=>$worksheet->school_id,'status'=>'in_progress','started_at'=>now()]
        );
        return view('worksheets.attempt', compact('worksheet','attempt'));
    }

    /** Student: submit attempt */
    public function submit(Request $r, WorksheetAttempt $attempt)
    {
        if ($attempt->student_id !== Auth::id()) abort(403);
        $attempt->update(['answers_json'=>$r->input('answers',[]),'status'=>'submitted','submitted_at'=>now()]);
        // Auto-score multiple choice
        $worksheet = $attempt->worksheet;
        $score = 0; $total = 0;
        $answers = $r->input('answers',[]);
        foreach (($worksheet->content_json['questions'] ?? []) as $i => $q) {
            if (in_array($q['type']??'',['multiple_choice','fill_blank'])) {
                if (isset($answers[$i]) && strtolower(trim($answers[$i])) === strtolower(trim($q['answer']??''))) $score++;
                $total++;
            }
        }
        if ($total > 0) $attempt->update(['score'=>round($score/$total*100,2),'status'=>'graded']);
        return redirect()->route('worksheets.student')->with('success','Worksheet submitted! Score: '.($total>0?round($score/$total*100).'%':'Pending review'));
    }
}