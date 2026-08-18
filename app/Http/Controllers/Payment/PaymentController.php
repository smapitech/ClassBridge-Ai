<?php namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentRecord;
use App\Models\SchoolSubscription;
use App\Models\SubscriptionPlan;
use App\Services\AuditLogService;
use App\Services\Payment\PaystackPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /** Show available subscription plans for checkout */
    public function showPlans()
    {
        $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();
        $currentSub = SchoolSubscription::where('school_id', Auth::user()->school_id)
            ->whereIn('status', ['active','trial'])->with('subscriptionPlan')->first();
        return view('payment.plans', compact('plans','currentSub'));
    }

    /** Initialize Paystack checkout for a plan */
    public function checkout(SubscriptionPlan $plan, Request $r)
    {
        $schoolId = Auth::user()->school_id;
        $billingCycle = $r->input('billing_cycle', 'monthly');
        $amount = $billingCycle === 'yearly' ? ($plan->price_yearly ?? $plan->price_monthly) : $plan->price_monthly;
        $reference = 'INV-' . strtoupper(uniqid()) . '-' . $schoolId;

        $invoice = Invoice::create([
            'school_id' => $schoolId, 'subscription_plan_id' => $plan->id,
            'invoice_number' => $reference, 'amount' => $amount, 'currency' => 'USD',
            'status' => 'unpaid', 'billing_period_start' => now(),
            'billing_period_end' => $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth(),
        ]);

        PaymentRecord::create([
            'school_id' => $schoolId, 'invoice_id' => $invoice->id,
            'amount' => $amount, 'currency' => 'USD',
            'payment_method' => 'paystack', 'transaction_reference' => $reference, 'status' => 'pending',
        ]);

        $gateway = new PaystackPaymentService;
        $result = $gateway->initializeTransaction([
            'email' => Auth::user()->email, 'amount' => $amount, 'reference' => $reference,
            'callback_url' => route('payment.callback'), 'currency' => 'USD',
            'metadata' => ['plan_id' => $plan->id, 'school_id' => $schoolId, 'billing_cycle' => $billingCycle],
        ]);

        if (!$result['success']) {
            return redirect()->route('payment.plans')->with('error', $result['error']);
        }

        AuditLogService::log('payment_initiated', 'payment', "Paystack checkout for plan {$plan->name}", ['reference'=>$reference,'amount'=>$amount]);
        return redirect($result['authorization_url']);
    }

    /** Paystack callback after payment */
    public function callback(Request $r)
    {
        $reference = $r->query('reference');
        if (!$reference) return redirect()->route('payment.plans')->with('error', 'No transaction reference found.');

        $gateway = new PaystackPaymentService;
        $result = $gateway->verifyTransaction($reference);

        $payment = PaymentRecord::where('transaction_reference', $reference)->first();
        if (!$payment) return redirect()->route('payment.plans')->with('error', 'Payment record not found.');

        if ($result['success']) {
            $payment->update(['status' => 'successful', 'paid_at' => now(),
                'metadata' => array_merge($payment->metadata ?? [], ['gateway_response' => $result['gateway_response'] ?? []])]);
            if ($payment->invoice) $payment->invoice->update(['status' => 'paid', 'paid_at' => now()]);

            $schoolId = $payment->school_id;
            $invoice = $payment->invoice;
            $planId = $invoice?->subscription_plan_id;

            if ($planId) {
                SchoolSubscription::where('school_id', $schoolId)->where('status', 'active')
                    ->update(['status' => 'cancelled', 'ends_at' => now()]);
                $billingCycle = ($invoice->billing_period_start && $invoice->billing_period_end
                    && $invoice->billing_period_end->diffInMonths($invoice->billing_period_start) > 1) ? 'yearly' : 'monthly';
                SchoolSubscription::create([
                    'school_id' => $schoolId, 'subscription_plan_id' => $planId,
                    'status' => 'active', 'billing_cycle' => $billingCycle,
                    'starts_at' => now(), 'renews_at' => $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth(),
                    'payment_method' => 'paystack', 'payment_reference' => $reference,
                ]);
            }

            AuditLogService::log('payment_completed', 'payment', "Payment successful: {$payment->amount} USD", ['reference'=>$reference]);
            return redirect()->route('billing.school')->with('success', 'Payment successful! Your subscription is now active.');
        }

        $payment->update(['status' => 'failed']);
        if ($payment->invoice) $payment->invoice->update(['status' => 'failed']);
        AuditLogService::log('payment_failed', 'payment', "Payment failed for reference $reference");
        return redirect()->route('payment.plans')->with('error', 'Payment was not successful. ' . ($result['error'] ?? ''));
    }

    /** Webhook handler */
    public function webhook(Request $r)
    {
        $gateway = new PaystackPaymentService;
        $result = $gateway->processWebhook($r->all(), $r->header('x-paystack-signature', ''));
        if (!$result['success']) return response()->json(['status' => 'invalid'], 400);

        if ($result['event'] === 'charge.success') {
            $payment = PaymentRecord::where('transaction_reference', $result['reference'])->first();
            if ($payment && $payment->status === 'pending') {
                $verify = $gateway->verifyTransaction($result['reference']);
                if ($verify['success']) {
                    $payment->update(['status' => 'successful', 'paid_at' => now()]);
                    if ($payment->invoice) $payment->invoice->update(['status' => 'paid', 'paid_at' => now()]);
                    AuditLogService::log('payment_webhook', 'payment', "Webhook confirmed: {$result['reference']}");
                }
            }
        }
        return response()->json(['status' => 'ok']);
    }
}