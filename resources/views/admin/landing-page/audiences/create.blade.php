@extends('layouts.dashboard')

@section('title', 'Create Audience')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Landing Page"
        title="Create audience card"
        description="Write for a person, not a segment."
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('super-admin.landing-page.audiences.index') }}">Back to audiences</x-secondary-button>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('super-admin.landing-page.audiences.store') }}">
        @csrf
        @include('admin.landing-page.audiences.partials.form')
    </form>
</div>
@endsection
