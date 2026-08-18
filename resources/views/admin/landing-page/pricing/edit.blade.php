@extends('layouts.dashboard')

@section('title', 'Edit Pricing Item')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Landing Page"
        title="Edit pricing item"
        description="Keep the plan simple so the public page stays easy to scan."
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('super-admin.landing-page.pricing.index') }}">Back to pricing</x-secondary-button>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('super-admin.landing-page.pricing.update', $pricingItem) }}">
        @csrf
        @method('PUT')
        @include('admin.landing-page.pricing.partials.form', ['pricingItem' => $pricingItem])
    </form>
</div>
@endsection
