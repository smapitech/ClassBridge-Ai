<?php

namespace App\Services\Classroom;

use App\Events\Classroom\WhiteboardBackgroundChanged;
use App\Events\Classroom\WhiteboardPageChanged;
use App\Events\Classroom\WhiteboardPageCreated;
use App\Events\Classroom\WhiteboardPageDeleted;
use App\Events\Classroom\WhiteboardPermissionChanged;
use App\Events\Classroom\WhiteboardSnapshotCreated;
use App\Models\ClassroomSession;
use App\Models\User;
use App\Models\Whiteboard;
use App\Models\WhiteboardElement;
use App\Models\WhiteboardPage;
use App\Models\WhiteboardSnapshot;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WhiteboardService
{
    public function defaultState(): array
    {
        return [
            'active_page' => 'page-1',
            'zoom' => 100,
            'viewport' => [
                'x' => 0,
                'y' => 0,
            ],
            'settings' => $this->defaultSettings(),
            'pages' => [
                $this->defaultPageState(1),
            ],
        ];
    }

    public function defaultSettings(): array
    {
        return [
            'board_locked' => false,
            'follow_teacher_page' => true,
            'follow_teacher_viewport' => false,
            'presentation_mode' => false,
            'allow_learner_page_switch' => true,
            'allow_learner_page_create' => false,
            'allow_learner_object_move' => true,
            'allow_learner_draw' => true,
            'allow_learner_erase' => false,
            'allow_learner_comments' => true,
            'allow_learner_images' => false,
        ];
    }

    public function ensureWhiteboard(ClassroomSession $session, ?User $actor = null): Whiteboard
    {
        $board = Whiteboard::firstOrCreate(
            ['classroom_session_id' => $session->id],
            [
                'school_id' => $session->school_id,
                'live_classroom_id' => $session->live_classroom_id,
                'title' => $session->classroom()->first()?->title ?? 'Whiteboard',
                'created_by' => $actor?->id,
                'settings' => $this->defaultSettings(),
            ]
        );

        if (! $board->pages()->exists()) {
            $initialState = $this->normalizeState(data_get($session->metadata, 'whiteboard_state', $this->defaultState()));
            foreach ($initialState['pages'] as $index => $pageState) {
                $board->pages()->create([
                    'page_key' => $pageState['key'],
                    'title' => $pageState['title'],
                    'page_number' => $index + 1,
                    'background_type' => $pageState['background_type'],
                    'background_value' => $pageState['background_value'],
                    'thumbnail_path' => $pageState['thumbnail_path'],
                    'is_locked' => $pageState['is_locked'],
                    'settings' => $pageState['settings'],
                ]);
            }

            $board->current_page_id = $board->pages()->orderBy('page_number')->value('id');
            $board->save();
        }

        return $board->load([
            'pages' => fn ($query) => $query->orderBy('page_number'),
            'currentPage',
        ]);
    }

    public function normalizeState(array $state): array
    {
        $fallback = $this->defaultState();
        $settings = array_replace($this->defaultSettings(), (array) data_get($state, 'settings', []));
        $pages = array_values(array_filter(array_map(function ($page, $index) {
            $candidate = is_array($page) ? $page : [];
            $key = $this->normalizePageKey($candidate['key'] ?? $candidate['page_key'] ?? null, $index);

            return [
                'id' => isset($candidate['id']) ? (int) $candidate['id'] : null,
                'key' => $key,
                'title' => trim((string) ($candidate['title'] ?? $candidate['name'] ?? "Page " . ($index + 1))) ?: "Page " . ($index + 1),
                'page_number' => (int) ($candidate['page_number'] ?? $candidate['sort_order'] ?? ($index + 1)),
                'background_type' => $this->normalizeBackgroundType($candidate['background_type'] ?? null),
                'background_value' => $this->normalizeBackgroundValue($candidate['background_value'] ?? null),
                'thumbnail_path' => isset($candidate['thumbnail_path']) ? (string) $candidate['thumbnail_path'] : null,
                'is_locked' => (bool) ($candidate['is_locked'] ?? false),
                'settings' => is_array($candidate['settings'] ?? null) ? $candidate['settings'] : [],
            ];
        }, (array) data_get($state, 'pages', $fallback['pages']), array_keys((array) data_get($state, 'pages', $fallback['pages'])))));

        if ($pages === []) {
            $pages = $fallback['pages'];
        }

        usort($pages, static function (array $left, array $right) {
            return ($left['page_number'] <=> $right['page_number'])
                ?: strcmp($left['key'], $right['key']);
        });

        foreach ($pages as $index => &$page) {
            $page['page_number'] = $index + 1;
            $page['title'] = trim((string) $page['title']) ?: "Page " . ($index + 1);
        }
        unset($page);

        $activePage = $this->normalizePageKey(data_get($state, 'active_page', $pages[0]['key'] ?? 'page-1'));
        if (! in_array($activePage, array_column($pages, 'key'), true)) {
            $activePage = $pages[0]['key'] ?? 'page-1';
        }

        return [
            'active_page' => $activePage,
            'zoom' => (int) data_get($state, 'zoom', $fallback['zoom']),
            'viewport' => [
                'x' => (float) data_get($state, 'viewport.x', 0),
                'y' => (float) data_get($state, 'viewport.y', 0),
            ],
            'settings' => $settings,
            'pages' => $pages,
        ];
    }

    public function workspaceState(ClassroomSession $session): array
    {
        $board = $this->ensureWhiteboard($session);
        $metadata = (array) ($session->metadata ?? []);
        $fallback = $this->normalizeState(data_get($metadata, 'whiteboard_state', $this->defaultState()));
        $pages = $board->pages->map(fn (WhiteboardPage $page) => $this->pageToState($page))->values()->all();
        $activePage = $board->currentPage?->page_key
            ?? data_get($fallback, 'active_page')
            ?? $pages[0]['key']
            ?? 'page-1';

        if ($pages === []) {
            $pages = $fallback['pages'];
        }

        return [
            'whiteboard_id' => $board->id,
            'title' => $board->title,
            'current_page_id' => $board->current_page_id,
            'active_page' => $activePage,
            'zoom' => (int) data_get($fallback, 'zoom', 100),
            'viewport' => data_get($fallback, 'viewport', ['x' => 0, 'y' => 0]),
            'settings' => array_replace($this->defaultSettings(), (array) data_get($board->settings, 'settings', data_get($board->settings, []))),
            'pages' => $pages,
        ];
    }

    public function syncFromState(ClassroomSession $session, array $state, ?User $actor = null): Whiteboard
    {
        return DB::transaction(function () use ($session, $state, $actor) {
            $board = $this->ensureWhiteboard($session, $actor);
            $normalized = $this->normalizeState($state);
            $previous = $this->workspaceState($session);

            $board->fill([
                'title' => $board->title ?: ($session->classroom()->first()?->title ?? 'Whiteboard'),
                'settings' => array_replace($this->defaultSettings(), $normalized['settings'] ?? []),
                'live_classroom_id' => $session->live_classroom_id,
            ]);
            $board->save();

            $existingPages = $board->pages()->get()->keyBy('page_key');
            $seen = [];
            $created = [];
            $updated = [];

            foreach ($normalized['pages'] as $index => $pageState) {
                $seen[] = $pageState['key'];
                $page = $existingPages->get($pageState['key']) ?: new WhiteboardPage(['whiteboard_id' => $board->id, 'page_key' => $pageState['key']]);
                $wasNew = ! $page->exists;
                $before = $page->exists ? $page->replicate()->toArray() : [];

                $page->fill([
                    'whiteboard_id' => $board->id,
                    'page_key' => $pageState['key'],
                    'title' => $pageState['title'],
                    'page_number' => $index + 1,
                    'background_type' => $pageState['background_type'],
                    'background_value' => $pageState['background_value'],
                    'thumbnail_path' => $pageState['thumbnail_path'],
                    'is_locked' => $pageState['is_locked'],
                    'settings' => $pageState['settings'],
                ]);
                $page->save();

                if ($wasNew) {
                    $created[] = $page->fresh();
                    if ($actor) {
                        event(new WhiteboardPageCreated($session->id, $board->id, $page->id, $page->page_key, $page->title, $page->page_number, $actor->id, $actor->displayName(), $this->pageToState($page)));
                    }
                } elseif (
                    ($before['title'] ?? null) !== $page->title
                    || ($before['page_number'] ?? null) !== $page->page_number
                    || ($before['background_type'] ?? null) !== $page->background_type
                    || ($before['background_value'] ?? null) !== $page->background_value
                    || (bool) ($before['is_locked'] ?? false) !== (bool) $page->is_locked
                ) {
                    $updated[] = $page->fresh();
                    if ($actor && (($before['background_type'] ?? null) !== $page->background_type || ($before['background_value'] ?? null) !== $page->background_value)) {
                        event(new WhiteboardBackgroundChanged($session->id, $board->id, $page->id, $page->page_key, $actor->id, $actor->displayName(), $page->background_type, $page->background_value));
                    }
                }
            }

            $missingPages = $existingPages->reject(fn (WhiteboardPage $page) => in_array($page->page_key, $seen, true));
            foreach ($missingPages as $page) {
                if ($actor) {
                    event(new WhiteboardPageDeleted($session->id, $board->id, $page->id, $page->page_key, $page->title, $page->page_number, $actor->id, $actor->displayName()));
                }
                $page->delete();
            }

            $currentPage = $board->pages()->where('page_key', $normalized['active_page'])->first()
                ?? $board->pages()->orderBy('page_number')->first();
            $board->current_page_id = $currentPage?->id;
            $board->save();

            $metadata = (array) ($session->metadata ?? []);
            $metadata['whiteboard_state'] = [
                ...$normalized,
                'whiteboard_id' => $board->id,
                'current_page_id' => $board->current_page_id,
                'settings' => array_replace($this->defaultSettings(), (array) data_get($board->settings, 'settings', $board->settings ?? [])),
            ];
            $session->metadata = $metadata;
            $session->save();

            if ($actor && ($previous['active_page'] ?? null) !== ($normalized['active_page'] ?? null)) {
                event(new WhiteboardPageChanged($session->id, $board->id, $board->current_page_id, $normalized['active_page'], $actor->id, $actor->displayName(), $normalized));
            }

            if ($actor && (($previous['settings'] ?? []) !== ($normalized['settings'] ?? []))) {
                event(new WhiteboardPermissionChanged($session->id, $board->id, $actor->id, $actor->displayName(), $normalized['settings']));
            }

            return $board->fresh(['pages' => fn ($query) => $query->orderBy('page_number'), 'currentPage']);
        });
    }

    public function createSnapshot(ClassroomSession $session, User $actor, ?string $name = null, ?string $reason = null, ?string $pageKey = null): WhiteboardSnapshot
    {
        $board = $this->ensureWhiteboard($session, $actor);
        $workspace = $this->workspaceState($session);
        $page = $pageKey
            ? $board->pages()->where('page_key', $pageKey)->first()
            : $board->currentPage;

        $snapshot = WhiteboardSnapshot::create([
            'school_id' => $session->school_id,
            'whiteboard_id' => $board->id,
            'whiteboard_page_id' => $page?->id,
            'snapshot_data' => [
                'whiteboard_state' => $workspace,
                'elements' => $this->snapshotElements($session, $page?->page_key),
                'created_at' => now()->toIso8601String(),
            ],
            'created_by' => $actor->id,
            'name' => $name ?: ($page ? $page->title : 'Board snapshot'),
            'reason' => $reason,
        ]);

        $this->enforceSnapshotLimit($board->id);

        if ($actor) {
            event(new WhiteboardSnapshotCreated($session->id, $board->id, $snapshot->id, $actor->id, $actor->displayName(), $snapshot->name, $reason, $page?->page_key, $workspace));
        }

        return $snapshot->load(['creator', 'page', 'whiteboard']);
    }

    public function restoreSnapshot(ClassroomSession $session, WhiteboardSnapshot $snapshot, User $actor): Whiteboard
    {
        return DB::transaction(function () use ($session, $snapshot, $actor) {
            $payload = (array) ($snapshot->snapshot_data ?? []);
            $workspace = $this->normalizeState((array) data_get($payload, 'whiteboard_state', $this->defaultState()));

            $board = $this->syncFromState($session, $workspace, $actor);

            $session->whiteboardElements()->delete();
            $pageMap = $board->pages->keyBy('page_key');

            foreach ((array) data_get($payload, 'elements', []) as $elementData) {
                if (! is_array($elementData)) {
                    continue;
                }

                $pageKey = $this->normalizePageKey(data_get($elementData, 'data.page_key', data_get($elementData, 'page_key', $workspace['active_page'] ?? 'page-1')));
                $page = $pageMap->get($pageKey) ?? $board->pages->first();

                $element = new WhiteboardElement([
                    'school_id' => $session->school_id,
                    'classroom_session_id' => $session->id,
                    'whiteboard_id' => $board->id,
                    'whiteboard_page_id' => $page?->id,
                    'element_uuid' => data_get($elementData, 'element_uuid') ?: (string) Str::uuid(),
                    'user_id' => data_get($elementData, 'user_id'),
                    'updated_by' => data_get($elementData, 'updated_by'),
                    'element_type' => data_get($elementData, 'element_type', 'whiteboard_object'),
                    'z_index' => data_get($elementData, 'z_index', 0),
                    'is_locked' => (bool) data_get($elementData, 'is_locked', false),
                    'data' => (array) data_get($elementData, 'data', []),
                ]);
                $element->save();
            }

            return $board->fresh(['pages' => fn ($query) => $query->orderBy('page_number'), 'currentPage']);
        });
    }

    protected function enforceSnapshotLimit(int $whiteboardId, int $limit = 50): void
    {
        $ids = WhiteboardSnapshot::where('whiteboard_id', $whiteboardId)
            ->latest('id')
            ->skip($limit)
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            WhiteboardSnapshot::whereIn('id', $ids)->delete();
        }
    }

    protected function snapshotElements(ClassroomSession $session, ?string $pageKey = null): array
    {
        return $session->whiteboardElements()
            ->with('user')
            ->when($pageKey, fn ($query) => $query->where('data->page_key', $pageKey))
            ->orderBy('z_index')
            ->latest('id')
            ->get()
            ->map(static function (WhiteboardElement $element) {
                return [
                    'id' => $element->id,
                    'element_uuid' => $element->element_uuid,
                    'whiteboard_id' => $element->whiteboard_id,
                    'whiteboard_page_id' => $element->whiteboard_page_id,
                    'user_id' => $element->user_id,
                    'updated_by' => $element->updated_by,
                    'element_type' => $element->element_type,
                    'z_index' => $element->z_index,
                    'is_locked' => $element->is_locked,
                    'data' => $element->data,
                    'created_at' => $element->created_at?->toIso8601String(),
                    'updated_at' => $element->updated_at?->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }

    protected function defaultPageState(int $index = 1): array
    {
        return [
            'id' => null,
            'key' => 'page-' . $index,
            'title' => 'Page ' . $index,
            'page_number' => $index,
            'background_type' => 'plain_white',
            'background_value' => '#ffffff',
            'thumbnail_path' => null,
            'is_locked' => false,
            'settings' => [],
        ];
    }

    protected function pageToState(WhiteboardPage $page): array
    {
        return [
            'id' => $page->id,
            'key' => $page->page_key,
            'title' => $page->title,
            'page_number' => $page->page_number,
            'background_type' => $page->background_type ?: 'plain_white',
            'background_value' => $page->background_value,
            'thumbnail_path' => $page->thumbnail_path,
            'is_locked' => (bool) $page->is_locked,
            'settings' => $page->settings ?? [],
        ];
    }

    protected function normalizePageKey(mixed $key, int $index = 0): string
    {
        $value = strtolower(trim((string) $key));
        $value = preg_replace('/\s+/', '-', $value) ?? $value;
        $value = preg_replace('/[^a-z0-9._-]/', '', $value) ?? $value;
        $value = trim(preg_replace('/-+/', '-', $value) ?? $value, '-');

        return $value !== '' ? $value : 'page-' . ($index + 1);
    }

    protected function normalizeBackgroundType(?string $type): string
    {
        $value = strtolower(trim((string) $type));

        return in_array($value, [
            'plain_white',
            'soft_grey',
            'dark_board',
            'grid',
            'graph_paper',
            'ruled_paper',
            'dotted_paper',
            'custom_colour',
            'uploaded_background',
            'pdf_page',
        ], true) ? $value : 'plain_white';
    }

    protected function normalizeBackgroundValue(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return $text !== '' ? $text : null;
    }
}
