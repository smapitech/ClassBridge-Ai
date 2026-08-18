<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardSummaryService;

class SuperAdminDashboardController extends Controller
{
    public function __construct(protected DashboardSummaryService $dashboard) {}

    public function index()
    {
        return view('dashboard.super-admin', $this->dashboard->superAdmin());
    }
}
