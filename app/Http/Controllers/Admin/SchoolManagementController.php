<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\School;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Organization\OrganizationOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SchoolManagementController extends Controller
{
    public function __construct(protected OrganizationOnboardingService $onboarding) {}

    /**
     * List all schools.
     */
    public function index()
    {
        $schools = School::with(['owner', 'subscriptionPlan'])
            ->withCount('users')
            ->latest()
            ->paginate(20);

        return view('admin.schools.index', compact('schools'));
    }

    /**
     * Show create school form.
     */
    public function create()
    {
        $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();
        return view('admin.schools.create', [
            'plans' => $plans,
            'organizationTypes' => $this->onboarding->organizationTypes(),
            'teachingModes' => $this->onboarding->teachingModes(),
        ]);
    }

    /**
     * Store a new school with owner.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'organization_type' => ['required', Rule::in(array_column($this->onboarding->organizationTypes(), 'value'))],
            'contact_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'timezone' => 'required|string|max:100',
            'preferred_teaching_mode' => ['required', Rule::in(array_column($this->onboarding->teachingModes(), 'value'))],
            'plan_id' => 'required|exists:subscription_plans,id',
            'status' => 'required|in:active,trial,suspended,inactive',

            // Owner fields
            'owner_first_name' => 'required|string|max:255',
            'owner_last_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'owner_password' => 'required|string|min:8',
        ]);

        // Create school
        $slug = Str::slug($validated['display_name']);
        $originalSlug = $slug;
        $counter = 1;
        while (School::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        $school = School::create([
            'name' => $validated['display_name'],
            'display_name' => $validated['display_name'],
            'slug' => $slug,
            'organization_type' => $validated['organization_type'],
            'email' => $validated['contact_email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'country' => $validated['country'] ?? null,
            'state' => $validated['state'] ?? null,
            'city' => $validated['city'] ?? null,
            'address' => $validated['address'] ?? null,
            'website' => $validated['website'] ?? null,
            'timezone' => $validated['timezone'],
            'preferred_teaching_mode' => $validated['preferred_teaching_mode'],
            'status' => $validated['status'],
            'subscription_plan_id' => $plan->id,
            'trial_ends_at' => $validated['status'] === 'trial' ? now()->addDays(14) : null,
            'settings' => ['allow_student_signup' => true],
        ]);

        // Create owner
        $ownerRole = Role::where('slug', 'school_owner')->firstOrFail();
        $owner = User::create([
            'name' => $validated['owner_first_name'] . ' ' . $validated['owner_last_name'],
            'first_name' => $validated['owner_first_name'],
            'last_name' => $validated['owner_last_name'],
            'email' => $validated['owner_email'],
            'password' => Hash::make($validated['owner_password']),
            'role_id' => $ownerRole->id,
            'school_id' => $school->id,
            'status' => 'active',
        ]);

        $school->owner_user_id = $owner->id;
        $school->save();

        // Create subscription record
        $school->subscriptions()->create([
            'subscription_plan_id' => $plan->id,
            'status' => $validated['status'] === 'trial' ? 'trial' : 'active',
            'trial_ends_at' => $validated['status'] === 'trial' ? now()->addDays(14) : null,
            'starts_at' => now(),
        ]);

        $this->onboarding->syncSteps($school, ['organization_profile']);

        return redirect()->route('super-admin.schools.index')
            ->with('success', "Organization '{$school->displayLabel()}' created successfully with owner account.");
    }

    /**
     * Show edit school form.
     */
    public function edit(School $school)
    {
        $plans = SubscriptionPlan::active()->orderBy('sort_order')->get();
        return view('admin.schools.edit', [
            'school' => $school,
            'plans' => $plans,
            'organizationTypes' => $this->onboarding->organizationTypes(),
            'teachingModes' => $this->onboarding->teachingModes(),
        ]);
    }

    /**
     * Update school details.
     */
    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'organization_type' => ['required', Rule::in(array_column($this->onboarding->organizationTypes(), 'value'))],
            'contact_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'timezone' => 'required|string|max:100',
            'preferred_teaching_mode' => ['required', Rule::in(array_column($this->onboarding->teachingModes(), 'value'))],
            'plan_id' => 'required|exists:subscription_plans,id',
            'status' => 'required|in:active,trial,suspended,inactive',
        ]);

        $school->update([
            'name' => $validated['display_name'],
            'display_name' => $validated['display_name'],
            'organization_type' => $validated['organization_type'],
            'email' => $validated['contact_email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'country' => $validated['country'] ?? null,
            'state' => $validated['state'] ?? null,
            'city' => $validated['city'] ?? null,
            'address' => $validated['address'] ?? null,
            'website' => $validated['website'] ?? null,
            'timezone' => $validated['timezone'],
            'preferred_teaching_mode' => $validated['preferred_teaching_mode'],
            'status' => $validated['status'],
            'subscription_plan_id' => $validated['plan_id'],
        ]);

        $this->onboarding->syncSteps($school, ['organization_profile']);

        return redirect()->route('super-admin.schools.index')
            ->with('success', "Organization '{$school->displayLabel()}' updated successfully.");
    }

    /**
     * Toggle school suspension.
     */
    public function toggleSuspend(School $school)
    {
        $school->status = $school->status === 'suspended' ? 'active' : 'suspended';
        $school->save();

        $action = $school->status === 'suspended' ? 'suspended' : 'reactivated';
        return redirect()->route('super-admin.schools.index')
            ->with('success', "Organization '{$school->displayLabel()}' {$action}.");
    }

    /**
     * Delete a school (soft delete).
     */
    public function destroy(School $school)
    {
        $name = $school->displayLabel();
        $school->delete(); // soft delete

        return redirect()->route('super-admin.schools.index')
            ->with('success', "Organization '{$name}' has been deleted.");
    }
}
