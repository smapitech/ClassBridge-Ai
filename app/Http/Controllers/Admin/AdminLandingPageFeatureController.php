<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPageFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminLandingPageFeatureController extends Controller
{
    public function index()
    {
        $features = $this->query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.landing-page.features.index', compact('features'));
    }

    public function create()
    {
        return view('admin.landing-page.features.create');
    }

    public function store(Request $request)
    {
        LandingPageFeature::create($this->validateFeature($request));

        return redirect()->route('super-admin.landing-page.features.index')
            ->with('success', 'Feature created.');
    }

    public function edit(LandingPageFeature $feature)
    {
        return view('admin.landing-page.features.edit', compact('feature'));
    }

    public function update(Request $request, LandingPageFeature $feature)
    {
        $feature->update($this->validateFeature($request));

        return redirect()->route('super-admin.landing-page.features.index')
            ->with('success', 'Feature updated.');
    }

    public function destroy(LandingPageFeature $feature)
    {
        $feature->delete();

        return redirect()->route('super-admin.landing-page.features.index')
            ->with('success', 'Feature deleted.');
    }

    protected function validateFeature(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'link_text' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'feature_group' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    protected function query()
    {
        $table = (new LandingPageFeature())->getTable();

        if (!Schema::hasTable($table)) {
            return LandingPageFeature::query()->whereRaw('1 = 0');
        }

        return LandingPageFeature::query();
    }
}
