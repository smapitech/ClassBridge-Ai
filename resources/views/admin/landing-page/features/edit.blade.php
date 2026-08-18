@extends('layouts.dashboard')

@section('title', 'Edit Feature')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Landing Page"
        title="Edit feature"
        description="Keep the feature message plain and practical."
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('super-admin.landing-page.features.index') }}">Back to features</x-secondary-button>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('super-admin.landing-page.features.update', $feature) }}">
        @csrf
        @method('PUT')
        @include('admin.landing-page.features.partials.form', ['feature' => $feature])
    </form>
</div>
@endsection
