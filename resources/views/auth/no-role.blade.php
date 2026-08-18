@extends('layouts.app')

@section('title', 'Account Not Configured')

@section('content')
<div class="mx-auto flex min-h-[calc(100vh-80px)] max-w-3xl items-center px-6 py-12 sm:px-8">
    <x-empty-state
        title="Account not configured"
        description="Your login is valid, but the workspace role has not been assigned yet. The platform uses roles for Super Admin, Organization Owner, Center Admin, Teacher / Tutor, Learner, and Parent."
        tone="warning"
        class="w-full"
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('home') }}">
                Go home
            </x-secondary-button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-primary-button type="submit">
                    Sign out
                </x-primary-button>
            </form>
        </x-slot:actions>
    </x-empty-state>
</div>
@endsection
