@php
    $slide = $slide ?? null;
    $value = fn (string $key, $default = null) => old($key, data_get($slide, $key, $default));
@endphp

<div class="grid gap-6 xl:grid-cols-[1.05fr_.95fr]">
    <x-dashboard-card title="Slide copy" description="Short copy keeps the homepage calm and easy to understand.">
        <div class="grid gap-4">
            <div>
                <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Label</label>
                <input name="label" value="{{ $value('label') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>

            <div>
                <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Headline</label>
                <input name="headline" value="{{ $value('headline') }}" required class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
            </div>

            <div>
                <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Subtitle</label>
                <textarea name="subtitle" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">{{ $value('subtitle') }}</textarea>
            </div>
        </div>
    </x-dashboard-card>

    <div class="space-y-6">
        <x-dashboard-card title="Buttons and style" description="Link the slide to the next helpful action.">
            <div class="grid gap-4">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Primary button text</label>
                        <input name="primary_button_text" value="{{ $value('primary_button_text') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Primary button URL</label>
                        <input name="primary_button_url" value="{{ $value('primary_button_url') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Secondary button text</label>
                        <input name="secondary_button_text" value="{{ $value('secondary_button_text') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Secondary button URL</label>
                        <input name="secondary_button_url" value="{{ $value('secondary_button_url') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Background style</label>
                        <select name="background_style" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
                            @foreach (['teal' => 'Teal', 'sky' => 'Sky', 'slate' => 'Slate'] as $valueOption => $label)
                                <option value="{{ $valueOption }}" @selected($value('background_style', 'teal') === $valueOption)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Image</label>
                        <input name="image" value="{{ $value('image') }}" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3">
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
            <x-primary-button type="submit">Save slide</x-primary-button>
            <x-secondary-button href="{{ route('super-admin.landing-page.slides.index') }}">Cancel</x-secondary-button>
        </div>
    </div>
</div>
