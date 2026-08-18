@extends('layouts.app')

@section('title', 'Create Workspace')

@section('content')
<div class="mx-auto max-w-7xl px-6 py-10 lg:px-8 lg:py-16">
    <div class="mx-auto max-w-3xl text-center">
        <x-status-badge tone="info">
            Start a protected learning workspace
        </x-status-badge>
        <h1 class="mt-6 text-4xl font-black tracking-tight text-slate-950 sm:text-5xl">
            Create a workspace for your school, tutoring center, private tutoring business, homeschool setup, or coding academy.
        </h1>
        <p class="mt-5 text-lg leading-8 text-slate-600">
            The existing school model stays intact in the database, while the product language stays broad enough for tutors, parents, and learning centers.
        </p>
    </div>

    <div class="mt-12 grid gap-8 xl:grid-cols-[1fr_0.92fr]">
        <div class="rounded-[2rem] border border-white/70 bg-white/95 p-8 shadow-2xl shadow-slate-950/10">
            <form method="POST" action="{{ route('register') }}" class="space-y-8">
                @csrf

                <section class="space-y-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Organization details</h2>
                        <p class="mt-1 text-sm text-slate-500">Use the name your learners and parents will recognize.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="display_name" class="block text-sm font-semibold text-slate-700">Display name</label>
                            <input id="display_name" name="display_name" type="text" value="{{ old('display_name') }}" required
                                class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400 @error('display_name') border-rose-500 @enderror">
                            @error('display_name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="contact_email" class="block text-sm font-semibold text-slate-700">Contact email</label>
                            <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email') }}"
                                class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-semibold text-slate-700">Phone</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone') }}"
                                class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        </div>

                        <div>
                            <label for="organization_type" class="block text-sm font-semibold text-slate-700">Organization type</label>
                            <select id="organization_type" name="organization_type" required class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">
                                @foreach ($organizationTypes as $option)
                                    <option value="{{ $option['value'] }}" {{ old('organization_type', 'private_tutor') === $option['value'] ? 'selected' : '' }}>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="preferred_teaching_mode" class="block text-sm font-semibold text-slate-700">Preferred teaching mode</label>
                            <select id="preferred_teaching_mode" name="preferred_teaching_mode" required class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">
                                @foreach ($teachingModes as $option)
                                    <option value="{{ $option['value'] }}" {{ old('preferred_teaching_mode', 'whiteboard') === $option['value'] ? 'selected' : '' }}>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="country" class="block text-sm font-semibold text-slate-700">Country</label>
                            <input id="country" name="country" type="text" value="{{ old('country') }}"
                                class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        </div>

                        <div>
                            <label for="timezone" class="block text-sm font-semibold text-slate-700">Timezone</label>
                            <input id="timezone" name="timezone" type="text" value="{{ old('timezone', 'Africa/Lagos') }}" required
                                class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="address" class="block text-sm font-semibold text-slate-700">Address</label>
                            <textarea id="address" name="address" rows="3"
                                class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">{{ old('address') }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="space-y-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Your account</h2>
                        <p class="mt-1 text-sm text-slate-500">This becomes the first organization owner or tutor account.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="block text-sm font-semibold text-slate-700">Full name</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required
                                class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400 @error('name') border-rose-500 @enderror">
                            @error('name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="email" class="block text-sm font-semibold text-slate-700">Email address</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                                class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400 @error('email') border-rose-500 @enderror">
                            @error('email')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                            <input id="password" name="password" type="password" required
                                class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400 @error('password') border-rose-500 @enderror">
                            @error('password')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Confirm password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400">
                        </div>
                    </div>
                </section>

                <section class="space-y-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Choose a plan</h2>
                        <p class="mt-1 text-sm text-slate-500">Start with the plan that fits your teaching setup.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        @foreach ($plans as $plan)
                            <label class="relative flex cursor-pointer rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition has-[:checked]:border-slate-900 has-[:checked]:ring-2 has-[:checked]:ring-slate-900">
                                <input type="radio" name="plan_id" value="{{ $plan->id }}" class="sr-only" {{ $loop->first ? 'checked' : '' }}>
                                <span class="flex flex-1 flex-col">
                                    <span class="text-sm font-bold text-slate-900">{{ $plan->name }}</span>
                                    <span class="mt-1 text-xs text-slate-500">{{ $plan->description }}</span>
                                    @if ($plan->price_monthly > 0)
                                        <span class="mt-3 text-lg font-black text-slate-900">${{ number_format($plan->price_monthly, 0) }}/month</span>
                                    @else
                                        <span class="mt-3 text-lg font-black text-emerald-600">Free</span>
                                    @endif
                                    <span class="mt-2 text-xs text-slate-400">{{ $plan->max_students }} learners - {{ $plan->max_teachers }} teachers</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('plan_id')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                </section>

                <x-primary-button type="submit" class="w-full justify-center">
                    Create workspace
                </x-primary-button>
            </form>
        </div>

        <aside class="rounded-[2rem] border border-slate-200 bg-slate-950 p-8 text-white shadow-2xl shadow-slate-950/20">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-300">What you get</p>
            <div class="mt-6 space-y-4 text-sm leading-6 text-slate-300">
                <div class="rounded-2xl bg-white/10 p-4">
                    <p class="font-semibold text-white">Protected live classroom</p>
                    <p class="mt-1">Teacher and learner work in the same workspace with no remote device access.</p>
                </div>
                <div class="rounded-2xl bg-white/10 p-4">
                    <p class="font-semibold text-white">Shared teaching tools</p>
                    <p class="mt-1">Whiteboard, code editor, text pad, pointers, chat, and replays stay inside the platform.</p>
                </div>
                <div class="rounded-2xl bg-white/10 p-4">
                    <p class="font-semibold text-white">Broader positioning</p>
                    <p class="mt-1">Built for schools, tutoring centers, private tutors, academies, lesson teachers, and parents who hire tutors.</p>
                </div>
            </div>

            <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-300">
                Already have an account? <a href="{{ route('login') }}" class="font-semibold text-white hover:text-sky-300">Sign in</a>
            </div>
        </aside>
    </div>
</div>
@endsection
