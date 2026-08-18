@extends('layouts.dashboard')


@section('content')
    @include('partials.header')
    @include('partials.nav')
    <div class="max-w-7xl mx-auto px-6 py-6">
        <livewire:auditor-alert-component :id=$id />
    </div>

@endsection
