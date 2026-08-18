<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use App\Models\LandingPageAudience;
use App\Models\LandingPageFeature;
use App\Models\LandingPagePricingItem;
use App\Models\LandingPageSection;
use App\Models\LandingPageSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminLandingPageController extends Controller
{
    public function index()
    {
        $slides = $this->activeItems(LandingPageSlide::class, false);
        $features = $this->activeItems(LandingPageFeature::class, false);
        $audiences = $this->activeItems(LandingPageAudience::class, false);
        $pricingItems = $this->activeItems(LandingPagePricingItem::class, false);
        $sections = $this->activeItems(LandingPageSection::class, false);
        $recentDemoRequests = $this->recentDemoRequests(8);

        $stats = [
            'slides' => $slides->count(),
            'features' => $features->count(),
            'audiences' => $audiences->count(),
            'pricing' => $pricingItems->count(),
            'sections' => $sections->count(),
            'demo_requests' => $this->countItems(DemoRequest::class),
            'active_sections' => $sections->where('is_active', true)->count(),
        ];

        return view('admin.landing-page.index', compact(
            'slides',
            'features',
            'audiences',
            'pricingItems',
            'sections',
            'recentDemoRequests',
            'stats',
        ));
    }

    public function sections()
    {
        $sections = $this->activeItems(LandingPageSection::class, false);

        return view('admin.landing-page.sections', compact('sections'));
    }

    public function updateSection(Request $request, LandingPageSection $section)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'secondary_button_text' => ['nullable', 'string', 'max:255'],
            'secondary_button_url' => ['nullable', 'string', 'max:255'],
            'settings_json' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $settings = null;
        if (!empty($validated['settings_json'])) {
            $settings = json_decode($validated['settings_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()
                    ->withErrors(['settings_json' => 'The advanced settings must be valid JSON.'])
                    ->withInput();
            }
        }

        $section->update([
            'title' => $validated['title'] ?? null,
            'subtitle' => $validated['subtitle'] ?? null,
            'content' => $validated['content'] ?? null,
            'image' => $validated['image'] ?? null,
            'button_text' => $validated['button_text'] ?? null,
            'button_url' => $validated['button_url'] ?? null,
            'secondary_button_text' => $validated['secondary_button_text'] ?? null,
            'secondary_button_url' => $validated['secondary_button_url'] ?? null,
            'settings' => $settings,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return back()->with('success', "Section '{$section->section_key}' updated.");
    }

    protected function activeItems(string $modelClass, bool $onlyActive = true, int $limit = 0)
    {
        $instance = new $modelClass();
        $table = method_exists($instance, 'getTable') ? $instance->getTable() : null;

        if (!$table || !Schema::hasTable($table)) {
            return collect();
        }

        $query = $modelClass::query();
        if ($onlyActive && Schema::hasColumn($table, 'is_active')) {
            $query->where('is_active', true);
        }

        if (Schema::hasColumn($table, 'sort_order')) {
            $query->orderBy('sort_order');
        }

        $query->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->get();
    }

    protected function countItems(string $modelClass, bool $onlyActive = false): int
    {
        $instance = new $modelClass();
        $table = method_exists($instance, 'getTable') ? $instance->getTable() : null;

        if (!$table || !Schema::hasTable($table)) {
            return 0;
        }

        $query = $modelClass::query();

        if ($onlyActive && Schema::hasColumn($table, 'is_active')) {
            $query->where('is_active', true);
        }

        return $query->count();
    }

    protected function recentDemoRequests(int $limit = 8)
    {
        $table = (new DemoRequest())->getTable();

        if (!Schema::hasTable($table)) {
            return collect();
        }

        return DemoRequest::query()
            ->latest()
            ->limit($limit)
            ->get();
    }
}
