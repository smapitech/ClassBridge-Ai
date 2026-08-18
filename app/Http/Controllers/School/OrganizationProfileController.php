<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\Organization\OrganizationOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrganizationProfileController extends Controller
{
    public function __construct(protected OrganizationOnboardingService $onboarding) {}

    public function edit()
    {
        $school = $this->school();
        $steps = $this->onboarding->syncSteps($school);

        return view('organization.profile', [
            'school' => $school,
            'organizationTypes' => $this->onboarding->organizationTypes(),
            'teachingModes' => $this->onboarding->teachingModes(),
            'onboardingSteps' => $steps,
            'onboardingBlueprint' => collect($this->onboarding->blueprintFor($school->organization_type)),
        ]);
    }

    public function update(Request $request)
    {
        $school = $this->school();
        $organizationTypes = collect($this->onboarding->organizationTypes())->pluck('value')->all();
        $teachingModes = collect($this->onboarding->teachingModes())->pluck('value')->all();

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'organization_type' => ['required', Rule::in($organizationTypes)],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'timezone' => ['required', 'string', 'max:100'],
            'preferred_teaching_mode' => ['required', Rule::in($teachingModes)],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'max:4096'],
        ]);

        $school->fill([
            'name' => $validated['display_name'],
            'display_name' => $validated['display_name'],
            'organization_type' => $validated['organization_type'],
            'email' => $validated['contact_email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'country' => $validated['country'] ?? null,
            'timezone' => $validated['timezone'],
            'preferred_teaching_mode' => $validated['preferred_teaching_mode'],
            'website' => $validated['website'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        if ($request->hasFile('logo')) {
            $school->logo_path = $request->file('logo')->store('organization-logos', 'public');
        }

        $school->save();

        $this->onboarding->syncSteps($school, ['organization_profile', 'tutor_profile']);

        return back()->with('success', 'Organization profile updated.');
    }

    protected function school(): School
    {
        $school = Auth::user()->school;

        if (!$school) {
            abort(403, 'No organization associated with your account.');
        }

        return $school;
    }
}
