@php
    $feature = $feature ?? null;
    $value = fn (string $key, $default = null) => old($key, data_get($feature, $key, $default));
@endphp

<div class="grid gap-6 xl:grid-cols-[1.05fr_.95fr]">
    <x-dashboard-card title="Feature details" description="Use one practical idea per card.">
        <div class="grid gap-4">
            <div>
                <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Title</label>
                <input name="title" value="{{ $value('title') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>
            <div>
                <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Description</label>
                <textarea name="description" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ $value('description') }}</textarea>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Icon</label>
                    <input name="icon" value="{{ $value('icon') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
                <div>
                    <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Feature group</label>
                    <select name="feature_group" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                        <option value="">General</option>
                        @foreach (['core' => 'Core', 'ai' => 'AI', 'learning' => 'Learning', 'family' => 'Family', 'business' => 'Business'] as $valueOption => $label)
                            <option value="{{ $valueOption }}" @selected($value('feature_group') === $valueOption)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </x-dashboard-card>

    <div class="space-y-6">
        <x-dashboard-card title="Link and visibility" description="Optional links are fine when the card needs a call to action.">
            <div class="grid gap-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Link text</label>
                        <input name="link_text" value="{{ $value('link_text') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Link URL</label>
                        <input name="link_url" value="{{ $value('link_url') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Sort order</label>
                        <input name="sort_order" type="number" value="{{ $value('sort_order', 0) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <label class="mt-6 flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3">
                        <input type="checkbox" name="is_active" value="1" @checked((bool) $value('is_active', true)) class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm font-semibold text-slate-700">Active</span>
                    </label>
                </div>
            </div>
        </x-dashboard-card>

        <div class="flex flex-wrap items-center gap-3">
            <x-primary-button type="submit">Save feature</x-primary-button>
            <x-secondary-button href="{{ route('super-admin.landing-page.features.index') }}">Cancel</x-secondary-button>
        </div>
    </div>
</div>
