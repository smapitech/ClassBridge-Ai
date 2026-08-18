<?php
namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentRecord;
use App\Models\School;
use App\Models\SchoolSubscription;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    protected SubscriptionService $subService;

    public function __construct(SubscriptionService $subService)
    {
        $this->subService = $subService;
    }

    // ==================== SUPER ADMIN ====================

    public function plans()
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        return view('billing.admin.plans.index', compact('plans'));
    }

    public function createPlan()
    {
        return view('billing.admin.plans.create');
    }

    public function storePlan(Request $r)
    {
        $v = $r->validate([
            'name'=>'required|string|max:255','slug'=>'required|unique:subscription_plans,slug',
            'description'=>'nullable|string','price_monthly'=>'nullable|numeric',
            'price_yearly'=>'nullable|numeric','max_teachers'=>'nullable|integer',
            'max_students'=>'nullable|integer','max_classes'=>'nullable|integer',
            'max_live_classrooms'=>'nullable|integer','ai_requests_per_month'=>'nullable|integer',
            'max_storage_mb'=>'nullable|integer','features'=>'nullable|string',
            'is_popular'=>'boolean','is_active'=>'boolean','sort_order'=>'integer'
        ]);
        $v['features'] = $v['features'] ? json_decode($v['features'],true) : null;
        SubscriptionPlan::create($v);
        return redirect()->route('billing.admin.plans')->with('success','Plan created.');
    }

    public function editPlan(SubscriptionPlan $plan)
    {
        return view('billing.admin.plans.edit', compact('plan'));
    }

    public function updatePlan(Request $r, SubscriptionPlan $plan)
    {
        $plan->update($r->validate([
            'name'=>'required|string|max:255','description'=>'nullable|string',
            'price_monthly'=>'nullable|numeric','price_yearly'=>'nullable|numeric',
            'max_teachers'=>'nullable|integer','max_students'=>'nullable|integer',
            'ai_requests_per_month'=>'nullable|integer','max_live_classrooms'=>'nullable|integer',
            'is_active'=>'boolean','is_popular'=>'boolean','sort_order'=>'integer'
        ]));
        return redirect()->route('billing.admin.plans')->with('success','Plan updated.');
    }

    public function subscriptions()
    {
        $subscriptions = SchoolSubscription::with(['school','subscriptionPlan'])->latest()->paginate(30);
        $schools = School::orderBy('name')->get();
        $plans = SubscriptionPlan::active()->get();
        return view('billing.admin.subscriptions', compact('subscriptions','schools','plans'));
    }

    public function assignPlan(Request $r)
    {
        $v = $r->validate(['school_id'=>'required|exists:schools,id','subscription_plan_id'=>'required|exists:subscription_plans,id','billing_cycle'=>'required|in:monthly,yearly,manual']);
        SchoolSubscription::create(['status'=>'active','starts_at'=>now()]+$v);
        return back()->with('success','Plan assigned.');
    }

    public function invoices()
    {
        $invoices = Invoice::with(['school','plan'])->latest()->paginate(30);
        return view('billing.admin.invoices', compact('invoices'));
    }

    public function payments()
    {
        $payments = PaymentRecord::with(['school','invoice'])->latest()->paginate(30);
        return view('billing.admin.payments', compact('payments'));
    }

    public function markPaymentPaid(PaymentRecord $payment)
    {
        $payment->update(['status'=>'successful','paid_at'=>now()]);
        if ($payment->invoice) $payment->invoice->update(['status'=>'paid','paid_at'=>now()]);
        return back()->with('success','Payment marked as paid.');
    }

    // ==================== SCHOOL ====================
    public function schoolBilling()
    {
        $school = School::with('activeSubscription.subscriptionPlan')->find(Auth::user()->school_id);
        $sub = $school->activeSubscription();
        $plan = $sub?->subscriptionPlan;
        $usage = $this->subService->getUsageCounter($school);
        $invoices = Invoice::where('school_id',$school->id)->latest()->limit(10)->get();
        $payments = PaymentRecord::where('school_id',$school->id)->latest()->limit(10)->get();
        $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();
        $teachersCount = $school->teachers()->count();
        $studentsCount = $school->students()->count();

        return view('billing.school.index', compact('school','sub','plan','usage','invoices','payments','plans','teachersCount','studentsCount'));
    }
}