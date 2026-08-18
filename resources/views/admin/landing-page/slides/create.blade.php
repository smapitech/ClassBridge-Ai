@extends('layouts.dashboard')

@section('title', 'Create Slide')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Landing Page"
        title="Create hero slide"
        description="Write a short line that helps a parent, tutor, or school owner understand the product quickly."
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('super-admin.landing-page.slides.index') }}">Back to slides</x-secondary-button>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('super-admin.landing-page.slides.store') }}">
        @csrf
        @include('admin.landing-page.slides.partials.form')
    </form>
</div>
@endsection
