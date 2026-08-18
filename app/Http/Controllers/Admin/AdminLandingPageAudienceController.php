<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPageAudience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminLandingPageAudienceController extends Controller
{
    public function index()
    {
        $audiences = $this->query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.landing-page.audiences.index', compact('audiences'));
    }

    public function create()
    {
        return view('admin.landing-page.audiences.create');
    }

    public function store(Request $request)
    {
        LandingPageAudience::create($this->validateAudience($request));

        return redirect()->route('super-admin.landing-page.audiences.index')
            ->with('success', 'Audience card created.');
    }

    public function edit(LandingPageAudience $audience)
    {
        return view('admin.landing-page.audiences.edit', compact('audience'));
    }

    public function update(Request $request, LandingPageAudience $audience)
    {
        $audience->update($this->validateAudience($request));

        return redirect()->route('super-admin.landing-page.audiences.index')
            ->with('success', 'Audience card updated.');
    }

    public function destroy(LandingPageAudience $audience)
    {
        $audience->delete();

        return redirect()->route('super-admin.landing-page.audiences.index')
            ->with('success', 'Audience card deleted.');
    }

    protected function validateAudience(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    protected function query()
    {
        $table = (new LandingPageAudience())->getTable();

        if (!Schema::hasTable($table)) {
            return LandingPageAudience::query()->whereRaw('1 = 0');
        }

        return LandingPageAudience::query();
    }
}
