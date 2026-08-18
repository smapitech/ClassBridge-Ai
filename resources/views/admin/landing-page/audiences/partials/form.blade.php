@php
    $audience = $audience ?? null;
    $value = fn (string $key, $default = null) => old($key, data_get($audience, $key, $default));
@endphp

<div class="grid gap-6 xl:grid-cols-[1.05fr_.95fr]">
    <x-dashboard-card title="Audience details" description="Write for a real person who might visit the homepage.">
        <div class="grid gap-4">
            <div>
                <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Title</label>
                <input name="title" value="{{ $value('title') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>
            <div>
                <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Description</label>
                <textarea name="description" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ $value('description') }}</textarea>
            </div>
        </div>
    </x-dashboard-card>

    <div class="space-y-6">
        <x-dashboard-card title="Visibility" description="Use the icon field for simple category hints.">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Icon</label>
                    <input name="icon" value="{{ $value('icon') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
                <div>
                    <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Sort order</label>
                    <input name="sort_order" type="number" value="{{ $value('sort_order', 0) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 md:col-span-2">
                    <input type="checkbox" name="is_active" value="1" @checked((bool) $value('is_active', true)) class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm font-semibold text-slate-700">Active</span>
                </label>
            </div>
        </x-dashboard-card>

        <div class="flex flex-wrap items-center gap-3">
            <x-primary-button type="submit">Save audience</x-primary-button>
            <x-secondary-button href="{{ route('super-admin.landing-page.audiences.index') }}">Cancel</x-secondary-button>
        </div>
    </div>
</div>
