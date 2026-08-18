@extends('layouts.dashboard')

@section('title', 'Edit Audience')

@section('content')
<div class="space-y-8">
    <x-page-header
        eyebrow="Super Admin / Landing Page"
        title="Edit audience card"
        description="Keep the audience copy short and practical."
    >
        <x-slot:actions>
            <x-secondary-button href="{{ route('super-admin.landing-page.audiences.index') }}">Back to audiences</x-secondary-button>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('super-admin.landing-page.audiences.update', $audience) }}">
        @csrf
        @method('PUT')
        @include('admin.landing-page.audiences.partials.form', ['audience' => $audience])
    </form>
</div>
@endsection
