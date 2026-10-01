@extends('layouts.dashboard')


@section('content')
    @include('partials.header')
    @include('partials.nav')
    <div class="max-w-3xl mx-auto px-6 py-6">
        <livewire:add-user-component />
    </div>

@endsection
