@extends('layouts.dashboard')
@section('title', 'Organization Profile')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Organization settings"
        title="{{ $school->displayLabel() }}"
        description="Keep the organization identity aligned for your school, tutoring business, or private teaching workspace."
        badge="{{ $school->organizationTypeLabel() }}"
        badgeTone="info"
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('organization.onboarding') }}">
                Open onboarding
            </x-secondary-button>
        </x-slot:actions>
    </x-page-header>

    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">Profile settings</h2>
            <p class="mt-1 text-sm text-slate-500">These settings shape how the workspace presents itself to teachers, learners, and parents.</p>

            <form method="POST" action="{{ route('organization.profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700">Display name</label>
                        <input name="display_name" value="{{ old('display_name', $school->display_name) }}" required class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400 @error('display_name') border-rose-500 @enderror">
                        @error('display_name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Organization type</label>
                        <select name="organization_type" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">
                            @foreach ($organizationTypes as $option)
                                <option value="{{ $option['value'] }}" {{ old('organization_type', $school->organization_type) === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Preferred teaching mode</label>
                        <select name="preferred_teaching_mode" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">
                            @foreach ($teachingModes as $option)
                                <option value="{{ $option['value'] }}" {{ old('preferred_teaching_mode', $school->preferred_teaching_mode) === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Contact email</label>
                        <input name="contact_email" type="email" value="{{ old('contact_email', $school->email) }}" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Phone</label>
                        <input name="phone" value="{{ old('phone', $school->phone) }}" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Country</label>
                        <input name="country" value="{{ old('country', $school->country) }}" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Timezone</label>
                        <input name="timezone" value="{{ old('timezone', $school->timezone) }}" required class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400 @error('timezone') border-rose-500 @enderror">
                        @error('timezone')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Website</label>
                        <input name="website" type="url" value="{{ old('website', $school->website) }}" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Logo</label>
                        <input name="logo" type="file" accept="image/*" class="mt-2 block w-full rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-3 text-sm text-slate-600">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700">Address</label>
                        <textarea name="address" rows="3" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">{{ old('address', $school->address) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button type="submit">
                        Save profile
                    </x-primary-button>
                    <x-secondary-button href="{{ route('organization.onboarding') }}">
                        View onboarding
                    </x-secondary-button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Current summary</h2>
                <div class="mt-5 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Display name</span>
                        <span class="font-semibold text-slate-900">{{ $school->displayLabel() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Organization type</span>
                        <span class="font-semibold text-slate-900">{{ $school->organizationTypeLabel() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Teaching mode</span>
                        <span class="font-semibold text-slate-900">{{ $school->preferredTeachingModeLabel() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Timezone</span>
                        <span class="font-semibold text-slate-900">{{ $school->timezone }}</span>
                    </div>
                </div>
                @if ($school->logo_path)
                    <div class="mt-5 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Logo preview</p>
                        <img src="{{ asset('storage/' . $school->logo_path) }}" alt="{{ $school->displayLabel() }}" class="mt-3 h-16 w-auto rounded-xl object-contain">
                    </div>
                @endif
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-slate-950 p-6 text-white shadow-lg shadow-slate-950/20">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-300">Protected workspace</p>
                <p class="mt-4 text-sm leading-6 text-slate-300">
                    ClassBridge AI is a protected learning workspace. Teachers and students interact only inside this classroom. Teachers cannot access the student&apos;s computer, files, desktop, browser history, or other applications.
                </p>
                <a href="{{ route('organization.onboarding') }}" class="mt-6 inline-flex rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-slate-100">
                    Continue setup
                </a>
            </div>

            <div class="rounded-[2rem] border border-slate-200 bg-white/95 p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">Onboarding progress</h2>
                <div class="mt-5 space-y-3">
                    @foreach ($onboardingBlueprint as $step)
                        @php $record = $onboardingSteps->get($step['step_key']); @endphp
                        <div class="rounded-2xl border px-4 py-3 {{ $record?->completed_at ? 'border-emerald-200 bg-emerald-50/70' : 'border-slate-100 bg-slate-50' }}">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $step['title'] }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $step['description'] }}</p>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $record?->completed_at ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $record?->completed_at ? 'Done' : 'Next' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
