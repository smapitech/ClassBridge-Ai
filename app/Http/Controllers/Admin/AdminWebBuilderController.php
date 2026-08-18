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

class AdminWebBuilderController extends Controller
{
    public function index()
    {
        $this->ensureRequiredSections();

        $slides = $this->activeItems(LandingPageSlide::class, false);
        $features = $this->activeItems(LandingPageFeature::class, false);
        $audiences = $this->activeItems(LandingPageAudience::class, false);
        $pricingItems = $this->activeItems(LandingPagePricingItem::class, false);
        $sections = $this->activeItems(LandingPageSection::class, false)->keyBy('section_key');
        $recentDemoRequests = $this->recentDemoRequests(6);

        $stats = [
            'slides' => $slides->count(),
            'features' => $features->count(),
            'audiences' => $audiences->count(),
            'pricing' => $pricingItems->count(),
            'sections' => $sections->count(),
            'demo_requests' => $this->countItems(DemoRequest::class),
            'active_sections' => $sections->where('is_active', true)->count(),
        ];

        return view('admin.web-builder.index', compact(
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
        $this->ensureRequiredSections();

        $sections = $this->activeItems(LandingPageSection::class, false)->keyBy('section_key');

        return view('admin.web-builder.sections', compact('sections'));
    }

    public function updateSection(Request $request, LandingPageSection $section)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
            'section_label' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'secondary_button_text' => ['nullable', 'string', 'max:255'],
            'secondary_button_url' => ['nullable', 'string', 'max:255'],
            'badge_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
            'settings_json' => ['nullable', 'string'],
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'room_code' => ['nullable', 'string', 'max:255'],
            'mode_label' => ['nullable', 'string', 'max:255'],
            'status_label' => ['nullable', 'string', 'max:255'],
            'badge_one_text' => ['nullable', 'string', 'max:255'],
            'badge_two_text' => ['nullable', 'string', 'max:255'],
            'video_label' => ['nullable', 'string', 'max:255'],
            'form_title' => ['nullable', 'string', 'max:255'],
            'form_subtitle' => ['nullable', 'string', 'max:255'],
            'nav_links_text' => ['nullable', 'string'],
            'hero_chips_text' => ['nullable', 'string'],
            'hero_code_lines_text' => ['nullable', 'string'],
            'steps_text' => ['nullable', 'string'],
            'stats_text' => ['nullable', 'string'],
            'testimonials_text' => ['nullable', 'string'],
            'footer_links_text' => ['nullable', 'string'],
        ]);

        $settings = $this->normalizeSettings($section, $validated);

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
            'sort_order' => $validated['sort_order'] ?? $section->sort_order ?? 0,
            'is_active' => array_key_exists('is_active', $validated)
                ? (bool) $validated['is_active']
                : (bool) $section->is_active,
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

    protected function recentDemoRequests(int $limit = 6)
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

    protected function normalizeSettings(LandingPageSection $section, array $validated): array
    {
        if (!empty($validated['settings_json'])) {
            $settings = json_decode($validated['settings_json'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $section->settings ?? [];
            }

            return is_array($settings) ? $settings : [];
        }

        $settings = is_array($section->settings) ? $section->settings : [];

        switch ($section->section_key) {
            case 'features':
            case 'pricing_preview':
            case 'demo_preview':
            case 'how_it_works':
            case 'social_proof':
            case 'request_demo':
                if (array_key_exists('section_label', $validated)) {
                    $settings['label'] = $validated['section_label'] ?? null;
                }
                break;

            case 'site_header':
                if (array_key_exists('nav_links_text', $validated)) {
                    $settings['nav_links'] = $this->parseLinkLines($validated['nav_links_text'] ?? '');
                }
                break;

            case 'hero':
                if (array_key_exists('eyebrow', $validated)) {
                    $settings['eyebrow'] = $validated['eyebrow'] ?? null;
                }
                if (array_key_exists('room_code', $validated)) {
                    $settings['room_code'] = $validated['room_code'] ?? null;
                }
                if (array_key_exists('mode_label', $validated)) {
                    $settings['mode_label'] = $validated['mode_label'] ?? null;
                }
                if (array_key_exists('status_label', $validated)) {
                    $settings['status_label'] = $validated['status_label'] ?? null;
                }
                if (array_key_exists('badge_one_text', $validated)) {
                    $settings['badge_one_text'] = $validated['badge_one_text'] ?? null;
                }
                if (array_key_exists('badge_two_text', $validated)) {
                    $settings['badge_two_text'] = $validated['badge_two_text'] ?? null;
                }
                if (array_key_exists('hero_chips_text', $validated)) {
                    $settings['chips'] = $this->parseSimpleLines($validated['hero_chips_text'] ?? '');
                }
                if (array_key_exists('hero_code_lines_text', $validated)) {
                    $settings['code_lines'] = $this->parseSimpleLines($validated['hero_code_lines_text'] ?? '');
                }
                break;

            case 'demo_preview':
                if (array_key_exists('video_label', $validated)) {
                    $settings['video_label'] = $validated['video_label'] ?? null;
                }
                break;

            case 'request_demo':
                if (array_key_exists('badge_text', $validated)) {
                    $settings['badge_text'] = $validated['badge_text'] ?? null;
                }
                if (array_key_exists('form_title', $validated)) {
                    $settings['form_title'] = $validated['form_title'] ?? null;
                }
                if (array_key_exists('form_subtitle', $validated)) {
                    $settings['form_subtitle'] = $validated['form_subtitle'] ?? null;
                }
                break;

            case 'how_it_works':
                if (array_key_exists('steps_text', $validated)) {
                    $settings['steps'] = $this->parseStepLines($validated['steps_text'] ?? '');
                }
                break;

            case 'social_proof':
                if (array_key_exists('stats_text', $validated)) {
                    $settings['stats'] = $this->parseStatsLines($validated['stats_text'] ?? '');
                }
                if (array_key_exists('testimonials_text', $validated)) {
                    $settings['testimonials'] = $this->parseTestimonialLines($validated['testimonials_text'] ?? '');
                }
                break;

            case 'footer':
                if (array_key_exists('footer_links_text', $validated)) {
                    $settings['links'] = $this->parseLinkLines($validated['footer_links_text'] ?? '');
                }
                break;
        }

        return $settings;
    }

    protected function parseSimpleLines(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    protected function parseLinkLines(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text) ?: [])
            ->map(function ($line) {
                $parts = array_map('trim', explode('|', $line, 2));
                return [
                    'label' => $parts[0] ?? '',
                    'url' => $parts[1] ?? '',
                ];
            })
            ->filter(fn ($item) => $item['label'] !== '')
            ->values()
            ->all();
    }

    protected function parseStepLines(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text) ?: [])
            ->map(function ($line) {
                $parts = array_map('trim', explode('|', $line, 2));

                return [
                    'title' => $parts[0] ?? '',
                    'copy' => $parts[1] ?? '',
                ];
            })
            ->filter(fn ($item) => $item['title'] !== '')
            ->values()
            ->all();
    }

    protected function parseStatsLines(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text) ?: [])
            ->map(function ($line) {
                $parts = array_map('trim', explode('|', $line, 2));

                return [
                    'value' => $parts[0] ?? '',
                    'label' => $parts[1] ?? '',
                ];
            })
            ->filter(fn ($item) => $item['value'] !== '')
            ->values()
            ->all();
    }

    protected function parseTestimonialLines(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text) ?: [])
            ->map(function ($line) {
                $parts = array_map('trim', explode('|', $line, 3));

                return [
                    'quote' => $parts[0] ?? '',
                    'name' => $parts[1] ?? '',
                    'role' => $parts[2] ?? '',
                ];
            })
            ->filter(fn ($item) => $item['quote'] !== '')
            ->values()
            ->all();
    }

    protected function ensureRequiredSections(): void
    {
        $table = (new LandingPageSection())->getTable();

        if (!Schema::hasTable($table)) {
            return;
        }

        $required = [
            'site_header' => [
                'title' => 'ClassBridge AI',
                'subtitle' => 'Safe live teaching workspace',
                'button_text' => 'Start Free Trial',
                'button_url' => '/register',
                'secondary_button_text' => 'Login',
                'secondary_button_url' => '/login',
                'sort_order' => 1,
                'settings' => ['nav_links' => []],
            ],
            'hero' => [
                'title' => 'Teach online like you are sitting beside the child -',
                'subtitle' => 'ClassBridge AI gives schools, tutors, and online teachers a protected live classroom where teacher and student can write, draw, code, point, explain, and learn together in real time.',
                'content' => 'without remote access risk.',
                'button_text' => 'Start Free Trial',
                'button_url' => '/register',
                'secondary_button_text' => 'Try Demo Classroom',
                'secondary_button_url' => '/live-classroom-demo',
                'sort_order' => 2,
                'settings' => ['chips' => []],
            ],
            'demo_preview' => [
                'title' => 'A full learning environment in your browser',
                'subtitle' => 'Watch how a teacher leads a live lesson.',
                'button_text' => 'Try demo classroom',
                'button_url' => '/live-classroom-demo',
                'sort_order' => 3,
                'settings' => [
                    'label' => 'See it in action',
                    'video_label' => 'Platform walkthrough - 2 min',
                ],
            ],
            'features' => [
                'title' => 'Built for serious teaching',
                'subtitle' => 'Every tool a tutor needs to deliver a live session - no tab switching, no plugins.',
                'sort_order' => 4,
                'settings' => ['label' => 'Everything you need'],
            ],
            'how_it_works' => [
                'title' => 'From sign-up to first lesson in minutes',
                'subtitle' => 'A simple flow that parents understand quickly.',
                'content' => 'Create a session, share the link, work live in the room, then send progress notes home.',
                'sort_order' => 5,
                'settings' => ['label' => 'How it works', 'steps' => []],
            ],
            'social_proof' => [
                'title' => 'Numbers that make the room easy to trust',
                'subtitle' => 'Parents, tutors, and school owners see exactly what the platform protects and supports.',
                'sort_order' => 6,
                'settings' => ['label' => 'Trust matters', 'stats' => [], 'testimonials' => []],
            ],
            'request_demo' => [
                'title' => 'Request a demo',
                'subtitle' => 'Show us how you teach and we will walk you through the workspace.',
                'content' => 'We will tailor the preview for schools, tutors, homeschool teachers, or coding programs.',
                'button_text' => 'Request demo',
                'button_url' => '#request-demo',
                'sort_order' => 7,
                'settings' => [
                    'label' => 'Request demo',
                    'badge_text' => 'Protected workspace',
                    'form_title' => 'Tell us about your teaching setup.',
                    'form_subtitle' => 'We will follow up with a demo for your school, tutoring center, private teaching business, homeschool setup, or coding academy.',
                ],
            ],
            'site_footer' => [
                'title' => 'ClassBridge AI',
                'subtitle' => 'Safe live teaching workspace.',
                'content' => 'Teach online like you are beside the learner, without remote access risk.',
                'sort_order' => 8,
                'settings' => ['links' => []],
            ],
            'pricing_preview' => [
                'title' => 'Plans shaped for tutors, teams, and organizations',
                'subtitle' => 'Choose the size that fits your teaching business, then grow later.',
                'sort_order' => 9,
                'settings' => ['label' => 'Pricing preview'],
            ],
        ];

        foreach ($required as $sectionKey => $payload) {
            LandingPageSection::firstOrCreate(['section_key' => $sectionKey], $payload);
        }
    }
}
