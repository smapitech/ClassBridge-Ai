@php
    $pricingItem = $pricingItem ?? null;
    $value = fn (string $key, $default = null) => old($key, data_get($pricingItem, $key, $default));
@endphp

<div class="grid gap-6 xl:grid-cols-[1.05fr_.95fr]">
    <x-dashboard-card title="Plan details" description="This preview is only meant to help visitors compare options quickly.">
        <div class="grid gap-4">
            <div>
                <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Name</label>
                <input name="name" value="{{ $value('name') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>
            <div>
                <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Description</label>
                <textarea name="description" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ $value('description') }}</textarea>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Price text</label>
                    <input name="price_text" value="{{ $value('price_text') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
                <div>
                    <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Button text</label>
                    <input name="button_text" value="{{ $value('button_text') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                </div>
            </div>
            <div>
                <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Button URL</label>
                <input name="button_url" value="{{ $value('button_url') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>
        </div>
    </x-dashboard-card>

    <div class="space-y-6">
        <x-dashboard-card title="Features and visibility" description="Use one feature per line. The public page will render them as a short list.">
            <div class="grid gap-4">
                <div>
                    <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Features</label>
                    <textarea name="features_text" rows="7" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('features_text', implode("\n", (array) $value('features', []))) }}</textarea>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Sort order</label>
                        <input name="sort_order" type="number" value="{{ $value('sort_order', 0) }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <label class="mt-6 flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3">
                        <input type="checkbox" name="is_popular" value="1" @checked((bool) $value('is_popular', false)) class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm font-semibold text-slate-700">Popular</span>
                    </label>
                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 md:col-span-2">
                        <input type="checkbox" name="is_active" value="1" @checked((bool) $value('is_active', true)) class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm font-semibold text-slate-700">Active</span>
                    </label>
                </div>
            </div>
        </x-dashboard-card>

        <div class="flex flex-wrap items-center gap-3">
            <x-primary-button type="submit">Save pricing item</x-primary-button>
            <x-secondary-button href="{{ route('super-admin.landing-page.pricing.index') }}">Cancel</x-secondary-button>
        </div>
    </div>
</div>
