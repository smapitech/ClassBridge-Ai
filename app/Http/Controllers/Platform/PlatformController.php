<?php
namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PlatformSetting;
use App\Models\SafetySetting;
use App\Services\AuditLogService;
use App\Services\Organization\OrganizationOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlatformController extends Controller
{
    public function __construct(protected OrganizationOnboardingService $onboarding) {}

    // ==================== PLATFORM SETTINGS ====================
    public function settings()
    {
        $settings = PlatformSetting::pluck('value','key')->toArray();
        return view('platform.admin.settings', compact('settings'));
    }

    public function updateSettings(Request $r)
    {
        foreach ($r->except('_token','_method') as $key => $value) {
            PlatformSetting::updateOrCreate(['key'=>$key], ['value'=>$value,'type'=>'string','group'=>'general']);
        }
        AuditLogService::log('platform_settings_updated','platform','Platform settings updated');
        return back()->with('success','Settings updated.');
    }

    // ==================== SAFETY SETTINGS ====================
    public function safetySettings()
    {
        $settings = SafetySetting::forSchool(Auth::user()->school_id);
        return view('platform.school.safety', compact('settings'));
    }

    public function updateSafetySettings(Request $r)
    {
        $settings = SafetySetting::forSchool(Auth::user()->school_id);
        $settings->update($r->only(['allow_student_chat','allow_student_drawing','allow_private_teacher_student_chat','require_parent_visibility','record_classroom_activity','show_safety_notice']));
        AuditLogService::log('safety_settings_updated','safety','Safety settings updated');
        return back()->with('success','Safety settings updated.');
    }

    // ==================== ONBOARDING ====================
    public function onboarding()
    {
        $school = Auth::user()->school;

        if (!$school) {
            abort(403, 'No organization associated with your account.');
        }

        $steps = $this->onboarding->syncSteps($school);
        $blueprint = collect($this->onboarding->blueprintFor($school->organization_type));
        $total = $blueprint->count();
        $completed = $steps->whereNotNull('completed_at')->count();

        return view('platform.onboarding', [
            'school' => $school,
            'steps' => $steps,
            'blueprint' => $blueprint,
            'total' => $total,
            'completed' => $completed,
        ]);
    }

    // ==================== AUDIT LOGS ====================
    public function auditLogs(Request $r)
    {
        $query = AuditLog::with('school')->latest();
        if ($r->school_id) $query->where('school_id',$r->school_id);
        $logs = $query->paginate(50);
        return view('platform.admin.audit-logs', compact('logs'));
    }

    // ==================== HELP/SUPPORT ====================
    public function help()
    {
        return view('platform.help');
    }
}
