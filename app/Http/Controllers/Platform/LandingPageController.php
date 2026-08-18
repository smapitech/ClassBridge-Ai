<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use App\Models\LandingPageAudience;
use App\Models\LandingPageFeature;
use App\Models\LandingPagePricingItem;
use App\Models\LandingPageSection;
use App\Models\LandingPageSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LandingPageController extends Controller
{
    public function index()
    {
        return view('landing.index', [
            'slides' => $this->activeItems(LandingPageSlide::class),
            'features' => $this->activeItems(LandingPageFeature::class),
            'audiences' => $this->activeItems(LandingPageAudience::class),
            'pricingItems' => $this->activeItems(LandingPagePricingItem::class),
            'sections' => $this->activeItems(LandingPageSection::class)->keyBy('section_key'),
        ]);
    }

    public function storeDemoRequest(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'organization' => ['nullable', 'string', 'max:255'],
            'role_type' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        if (Schema::hasTable((new DemoRequest())->getTable())) {
            DemoRequest::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'organization' => $validated['organization'] ?? null,
                'role_type' => $validated['role_type'] ?? null,
                'message' => $validated['message'] ?? null,
                'status' => 'new',
            ]);
        }

        return redirect()->to(route('home') . '#request-demo')
            ->with('success', 'Thanks. We received your request and will contact you soon.');
    }

    protected function activeItems(string $modelClass)
    {
        $instance = new $modelClass();
        $table = method_exists($instance, 'getTable') ? $instance->getTable() : null;

        if (!$table || !Schema::hasTable($table)) {
            return collect();
        }

        return $modelClass::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
