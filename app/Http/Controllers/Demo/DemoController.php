<?php namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use App\Models\RequestDemoSubmission;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    /** Public: submit demo request form */
    public function submitDemoRequest(Request $r)
    {
        $r->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'organization' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:100',
            'message' => 'nullable|string|max:2000',
        ]);
        RequestDemoSubmission::create($r->only(['name','email','phone','organization','role','message']) + ['status' => 'new']);
        return back()->with('success', 'Thank you! We will contact you shortly to schedule a demo.');
    }

    /** Super Admin: view submissions */
    public function submissions()
    {
        $submissions = RequestDemoSubmission::latest()->paginate(30);
        return view('platform.admin.demo-submissions', compact('submissions'));
    }

    /** Super Admin: update status */
    public function updateSubmission(Request $r, RequestDemoSubmission $submission)
    {
        $submission->update(['status' => $r->status]);
        return back()->with('success', 'Status updated.');
    }
}