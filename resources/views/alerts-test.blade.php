@extends('layouts.dashboard')


@section('content')
    @include('partials.header')
    @include('partials.nav')
    <div class="max-w-6xl mx-auto px-7 py-4 mt-6">
        <livewire:alert-test-component>
    </div>

@endsection
