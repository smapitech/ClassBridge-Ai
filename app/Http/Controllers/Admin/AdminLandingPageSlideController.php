<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPageSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminLandingPageSlideController extends Controller
{
    public function index()
    {
        $slides = $this->query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.landing-page.slides.index', compact('slides'));
    }

    public function create()
    {
        return view('admin.landing-page.slides.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateSlide($request);
        LandingPageSlide::create($data);

        return redirect()->route('super-admin.landing-page.slides.index')
            ->with('success', 'Slide created.');
    }

    public function edit(LandingPageSlide $slide)
    {
        return view('admin.landing-page.slides.edit', compact('slide'));
    }

    public function update(Request $request, LandingPageSlide $slide)
    {
        $data = $this->validateSlide($request);
        $slide->update($data);

        return redirect()->route('super-admin.landing-page.slides.index')
            ->with('success', 'Slide updated.');
    }

    public function destroy(LandingPageSlide $slide)
    {
        $slide->delete();

        return redirect()->route('super-admin.landing-page.slides.index')
            ->with('success', 'Slide deleted.');
    }

    protected function validateSlide(Request $request): array
    {
        return $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'headline' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string'],
            'primary_button_text' => ['nullable', 'string', 'max:255'],
            'primary_button_url' => ['nullable', 'string', 'max:255'],
            'secondary_button_text' => ['nullable', 'string', 'max:255'],
            'secondary_button_url' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:255'],
            'background_style' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    protected function query()
    {
        $table = (new LandingPageSlide())->getTable();

        if (!Schema::hasTable($table)) {
            return LandingPageSlide::query()->whereRaw('1 = 0');
        }

        return LandingPageSlide::query();
    }
}
