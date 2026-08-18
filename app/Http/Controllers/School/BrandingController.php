<?php namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use App\Models\SchoolBrandingSetting;
use App\Models\StudentCertificate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BrandingController extends Controller
{
    /** BRANDING SETTINGS */
    public function branding()
    {
        $settings = SchoolBrandingSetting::forSchool(Auth::user()->school_id);
        return view('school.branding.index', compact('settings'));
    }

    public function updateBranding(Request $r)
    {
        $settings = SchoolBrandingSetting::forSchool(Auth::user()->school_id);
        $settings->update($r->only(['primary_color','secondary_color','accent_color','login_background','portal_theme','email_sender_name','support_email','certificate_signature']));
        if ($r->hasFile('logo')) $settings->update(['logo' => $r->file('logo')->store('branding', 'public')]);
        if ($r->hasFile('favicon')) $settings->update(['favicon' => $r->file('favicon')->store('branding', 'public')]);
        return back()->with('success', 'Branding settings updated.');
    }

    /** CUSTOM DOMAIN */
    public function domains()
    {
        $domains = \App\Models\CustomDomain::where('school_id', Auth::user()->school_id)->latest()->get();
        return view('school.branding.domains', compact('domains'));
    }

    public function requestDomain(Request $r)
    {
        $r->validate(['domain' => 'required|string|unique:custom_domains,domain']);
        \App\Models\CustomDomain::create([
            'school_id' => Auth::user()->school_id,
            'domain' => $r->domain,
            'status' => 'pending',
            'verification_token' => Str::random(32),
        ]);
        return back()->with('success', 'Domain request submitted. DNS instructions shown below.');
    }

    /** CERTIFICATES */
    public function certificates(Request $r)
    {
        $query = StudentCertificate::where('school_id', Auth::user()->school_id)->with(['student','issuer'])->latest();
        if ($r->student_id) $query->where('student_id', $r->student_id);
        $certificates = $query->paginate(20);
        $students = User::where('school_id', Auth::user()->school_id)->whereHas('role', fn($q)=>$q->where('slug','student'))->get();
        $templates = CertificateTemplate::where(fn($q)=>$q->whereNull('school_id')->orWhere('school_id', Auth::user()->school_id))->active()->get();
        return view('school.branding.certificates', compact('certificates','students','templates'));
    }

    public function issueCertificate(Request $r)
    {
        $r->validate([
            'student_id' => 'required|exists:users,id',
            'certificate_template_id' => 'nullable|exists:certificate_templates,id',
            'title' => 'required|string|max:255',
            'course_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        StudentCertificate::create([
            'school_id' => Auth::user()->school_id,
            'student_id' => $r->student_id,
            'issued_by' => Auth::id(),
            'certificate_template_id' => $r->certificate_template_id,
            'title' => $r->title,
            'course_name' => $r->course_name,
            'description' => $r->description,
            'certificate_number' => StudentCertificate::generateNumber(),
            'verification_code' => StudentCertificate::generateVerificationCode(),
            'issued_at' => now(),
            'status' => 'issued',
        ]);
        return back()->with('success', 'Certificate issued!');
    }

    public function revokeCertificate(StudentCertificate $cert)
    {
        if ($cert->school_id !== Auth::user()->school_id) abort(403);
        $cert->update(['status' => 'revoked']);
        return back()->with('success', 'Certificate revoked.');
    }

    /** PUBLIC VERIFICATION */
    public function verifyCertificate(Request $r)
    {
        $code = $r->query('code');
        $cert = $code ? StudentCertificate::where('verification_code', $code)->with('student')->first() : null;
        return view('school.branding.verify', compact('cert', 'code'));
    }
}