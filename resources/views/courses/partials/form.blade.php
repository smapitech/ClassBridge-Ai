@props([
    'course' => null,
    'action',
    'method' => 'POST',
    'submitLabel' => 'Save Course',
])

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-5">
        <div>
            <label class="text-sm font-semibold text-slate-700">Course name</label>
            <input
                name="name"
                value="{{ old('name', $course?->name) }}"
                required
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                placeholder="Primary 4 English"
            >
            @error('name')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Description</label>
            <textarea
                name="description"
                rows="4"
                class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                placeholder="Short note about what this course covers."
            >{{ old('description', $course?->description) }}</textarea>
            @error('description')
                <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="text-sm font-semibold text-slate-700">Status</label>
                <select
                    name="status"
                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-slate-300 focus:ring-2 focus:ring-slate-200"
                >
                    @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'archived' => 'Archived'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $course?->status ?? 'active') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Tip</p>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Courses keep subjects, groups, learners, and live lessons in one line so teaching stays simple.
                </p>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <x-primary-button type="submit">
            {{ $submitLabel }}
        </x-primary-button>
        <x-secondary-button href="{{ route('courses.index') }}">
            Cancel
        </x-secondary-button>
    </div>
</form>
