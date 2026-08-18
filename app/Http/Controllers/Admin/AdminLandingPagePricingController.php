<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPagePricingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminLandingPagePricingController extends Controller
{
    public function index()
    {
        $pricingItems = $this->query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.landing-page.pricing.index', compact('pricingItems'));
    }

    public function create()
    {
        return view('admin.landing-page.pricing.create');
    }

    public function store(Request $request)
    {
        LandingPagePricingItem::create($this->validatePricing($request));

        return redirect()->route('super-admin.landing-page.pricing.index')
            ->with('success', 'Pricing item created.');
    }

    public function edit(LandingPagePricingItem $pricing)
    {
        return view('admin.landing-page.pricing.edit', [
            'pricingItem' => $pricing,
        ]);
    }

    public function update(Request $request, LandingPagePricingItem $pricing)
    {
        $pricing->update($this->validatePricing($request));

        return redirect()->route('super-admin.landing-page.pricing.index')
            ->with('success', 'Pricing item updated.');
    }

    public function destroy(LandingPagePricingItem $pricing)
    {
        $pricing->delete();

        return redirect()->route('super-admin.landing-page.pricing.index')
            ->with('success', 'Pricing item deleted.');
    }

    protected function validatePricing(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_text' => ['nullable', 'string', 'max:255'],
            'features_text' => ['nullable', 'string'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'is_popular' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $features = [];
        if (!empty($validated['features_text'])) {
            $features = collect(preg_split('/\r\n|\r|\n/', $validated['features_text']))
                ->map(fn ($line) => trim($line))
                ->filter()
                ->values()
                ->all();
        }

        return [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price_text' => $validated['price_text'] ?? null,
            'features' => $features ?: null,
            'button_text' => $validated['button_text'] ?? null,
            'button_url' => $validated['button_url'] ?? null,
            'is_popular' => (bool) ($validated['is_popular'] ?? false),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];
    }

    protected function query()
    {
        $table = (new LandingPagePricingItem())->getTable();

        if (!Schema::hasTable($table)) {
            return LandingPagePricingItem::query()->whereRaw('1 = 0');
        }

        return LandingPagePricingItem::query();
    }
}
