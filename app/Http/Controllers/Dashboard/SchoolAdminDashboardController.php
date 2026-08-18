<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\Dashboard\DashboardSummaryService;
use App\Services\Organization\OrganizationOnboardingService;

class SchoolAdminDashboardController extends Controller
{
    public function __construct(
        protected OrganizationOnboardingService $onboarding,
        protected DashboardSummaryService $dashboard
    ) {}

    public function index()
    {
        $user = Auth::user();
        $dashboard = $this->dashboard->organizationOwner($user);
        $school = $dashboard['school'];

        $onboardingRecords = collect();
        $onboardingBlueprint = collect();
        $onboardingSteps = 0;

        if ($school) {
            $onboardingRecords = $this->onboarding->syncSteps($school);
            $onboardingSteps = $onboardingRecords->whereNull('completed_at')->count();
            $onboardingBlueprint = collect($this->onboarding->blueprintFor($school->organization_type));
        }

        return view('dashboard.school-admin', array_merge($dashboard, [
            'onboardingSteps' => $onboardingSteps,
            'onboardingRecords' => $onboardingRecords,
            'onboardingBlueprint' => $onboardingBlueprint,
        ]));
    }
}
