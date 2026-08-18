<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminDemoRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->query();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $demoRequests = $query->latest()->paginate(20)->withQueryString();
        $stats = [
            'new' => $this->countByStatus('new'),
            'contacted' => $this->countByStatus('contacted'),
            'closed' => $this->countByStatus('closed'),
        ];

        return view('admin.demo-requests.index', compact('demoRequests', 'stats'));
    }

    public function updateStatus(Request $request, DemoRequest $demoRequest)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:new,contacted,closed'],
        ]);

        $demoRequest->update(['status' => $validated['status']]);

        return back()->with('success', 'Demo request status updated.');
    }

    protected function query()
    {
        $table = (new DemoRequest())->getTable();

        if (!Schema::hasTable($table)) {
            return DemoRequest::query()->whereRaw('1 = 0');
        }

        return DemoRequest::query();
    }

    protected function countByStatus(string $status): int
    {
        return (clone $this->query())->where('status', $status)->count();
    }
}
