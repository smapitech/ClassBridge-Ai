@extends('layouts.dashboard')

@section('title', 'Edit Slide')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Landing Page"
        title="Edit hero slide"
        description="Keep the message short, practical, and human."
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('super-admin.landing-page.slides.index') }}">Back to slides</x-secondary-button>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('super-admin.landing-page.slides.update', $slide) }}">
        @csrf
        @method('PUT')
        @include('admin.landing-page.slides.partials.form', ['slide' => $slide])
    </form>
</div>
@endsection
