<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardSummaryService;
use Illuminate\Support\Facades\Auth;

class ParentDashboardController extends Controller
{
    public function __construct(protected DashboardSummaryService $dashboard) {}

    public function index()
    {
        $user = Auth::user();

        return view('dashboard.parent', $this->dashboard->parent($user));
    }
}
