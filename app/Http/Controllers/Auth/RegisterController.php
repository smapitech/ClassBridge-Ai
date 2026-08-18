<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Organization\OrganizationOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function __construct(protected OrganizationOnboardingService $onboarding) {}

    /**
     * Show the school registration form.
     */
    public function showRegistrationForm()
    {
        $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();
        return view('auth.register', [
            'plans' => $plans,
            'organizationTypes' => $this->onboarding->organizationTypes(),
            'teachingModes' => $this->onboarding->teachingModes(),
        ]);
    }

    /**
     * Handle school + admin registration.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            // School details
            'display_name' => ['required', 'string', 'max:255'],
            'organization_type' => ['required', Rule::in(array_column($this->onboarding->organizationTypes(), 'value'))],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'timezone' => ['required', 'string', 'max:100'],
            'preferred_teaching_mode' => ['required', Rule::in(array_column($this->onboarding->teachingModes(), 'value'))],
            'address' => ['nullable', 'string', 'max:500'],

            // Admin details
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],

            // Plan selection
            'plan_id' => ['required', 'exists:subscription_plans,id'],
        ]);

        // Create the school
        $slug = Str::slug($validated['display_name']);
        $originalSlug = $slug;
        $counter = 1;
        while (School::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $school = School::create([
            'name' => $validated['display_name'],
            'display_name' => $validated['display_name'],
            'slug' => $slug,
            'organization_type' => $validated['organization_type'],
            'email' => $validated['contact_email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'country' => $validated['country'] ?? null,
            'timezone' => $validated['timezone'],
            'preferred_teaching_mode' => $validated['preferred_teaching_mode'],
            'address' => $validated['address'] ?? null,
            'status' => 'trial',
            'settings' => [
                'allow_student_signup' => true,
                'default_language' => 'en',
            ],
        ]);

        // Get the organization owner role
        $schoolOwnerRole = Role::where('slug', 'school_owner')->firstOrFail();

        // Create the admin user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $schoolOwnerRole->id,
            'school_id' => $school->id,
            'status' => 'active',
        ]);

        // Assign a trial subscription
        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        SchoolSubscription::create([
            'school_id' => $school->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
            'starts_at' => now(),
        ]);

        $this->onboarding->syncSteps($school, ['organization_profile']);

        // Log the admin in
        Auth::login($user);
        $user->recordLogin();

        return redirect()->route('organization.onboarding')
            ->with('success', 'Welcome to ClassBridge AI! Your organization workspace has been created. You are on a 14-day free trial.');
    }
}
