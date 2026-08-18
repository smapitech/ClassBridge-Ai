@extends('layouts.dashboard')

@section('title', 'Create Pricing Item')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Landing Page"
        title="Create pricing item"
        description="Keep each plan short, practical, and easy to compare."
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('super-admin.landing-page.pricing.index') }}">Back to pricing</x-secondary-button>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('super-admin.landing-page.pricing.store') }}">
        @csrf
        @include('admin.landing-page.pricing.partials.form')
    </form>
</div>
@endsection
