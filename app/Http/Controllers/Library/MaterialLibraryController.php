<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LessonTemplate;
use App\Models\MaterialFolder;
use App\Models\TeachingMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MaterialLibraryController extends Controller
{
    /** List folders and materials */
    public function index(Request $request)
    {
        $schoolId = Auth::user()->school_id;

        $folders = MaterialFolder::forSchool($schoolId)
            ->with('children')
            ->whereNull('parent_id')
            ->get();

        $query = TeachingMaterial::forSchool($schoolId)
            ->with(['user', 'folder', 'course'])
            ->latest();

        $selectedCourse = null;

        if ($request->filled('course_id')) {
            $selectedCourse = Course::forSchool($schoolId)->find($request->course_id);
            if ($selectedCourse) {
                $query->where('course_id', $selectedCourse->id);
            }
        }

        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }

        if ($request->filled('type')) {
            $query->where('material_type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $materials = $query->paginate(20);

        return view('library.index', compact('folders', 'materials', 'selectedCourse'));
    }

    /** Create folder */
    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:material_folders,id',
        ]);

        MaterialFolder::create([
            'school_id' => Auth::user()->school_id,
            'user_id' => Auth::id(),
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'visibility' => 'school',
        ]);

        return back()->with('success', 'Folder created.');
    }

    /** Upload material */
    public function store(Request $request)
    {
        $v = $request->validate([
            'title' => 'required|string|max:255',
            'course_id' => [
                'nullable',
                Rule::exists('courses', 'id')->where(fn ($query) => $query->where('school_id', Auth::user()->school_id)),
            ],
            'folder_id' => 'nullable|exists:material_folders,id',
            'description' => 'nullable|string',
            'material_type' => 'required|in:note,pdf,image,video,link,code,whiteboard,worksheet,quiz,homework,ai_lesson,other',
            'content' => 'nullable|string',
            'external_url' => 'nullable|url',
            'visibility' => 'required|in:private,teacher,school',
        ]);

        $v['school_id'] = Auth::user()->school_id;
        $v['user_id'] = Auth::id();
        $v['slug'] = Str::slug($v['title']);
        $v['status'] = $request->input('status', 'published');

        if ($request->hasFile('file')) {
            $v['file_path'] = $request->file('file')->store('library', 'public');
        }

        TeachingMaterial::create($v);

        return back()->with('success', 'Material saved to library.');
    }

    /** Store AI-generated content to library */
    public function saveFromAI(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'course_id' => [
                'nullable',
                Rule::exists('courses', 'id')->where(fn ($query) => $query->where('school_id', Auth::user()->school_id)),
            ],
            'content' => 'required|string',
            'material_type' => 'required|in:ai_lesson,note,worksheet,quiz,homework',
        ]);

        TeachingMaterial::create([
            'school_id' => Auth::user()->school_id,
            'course_id' => $request->course_id,
            'user_id' => Auth::id(),
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'material_type' => $request->material_type,
            'content' => $request->content,
            'visibility' => 'teacher',
            'status' => 'published',
        ]);

        return response()->json(['success' => true, 'message' => 'Saved to library!']);
    }

    /** Templates list */
    public function templates(Request $request)
    {
        $query = LessonTemplate::forSchool(Auth::user()->school_id)->active()->latest();

        if ($request->filled('subject')) {
            $query->where('subject', $request->subject);
        }

        $templates = $query->paginate(20);

        return view('library.templates', compact('templates'));
    }

    /** Save template */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'template_content' => 'required|string',
            'subject' => 'nullable|string',
        ]);

        LessonTemplate::create([
            'school_id' => Auth::user()->school_id,
            'user_id' => Auth::id(),
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'subject' => $request->subject,
            'topic' => $request->topic,
            'age_group' => $request->age_group,
            'template_content' => $request->template_content,
            'visibility' => 'school',
            'status' => 'active',
        ]);

        return back()->with('success', 'Template saved.');
    }
}
