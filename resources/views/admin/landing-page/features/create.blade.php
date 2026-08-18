@extends('layouts.dashboard')

@section('title', 'Create Feature')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Landing Page"
        title="Create feature"
        description="Pick one clear product idea and keep the wording short."
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('super-admin.landing-page.features.index') }}">Back to features</x-secondary-button>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('super-admin.landing-page.features.store') }}">
        @csrf
        @include('admin.landing-page.features.partials.form')
    </form>
</div>
@endsection
