@extends('layouts.dashboard')

@push('vendor-scripts')
    <script src="{{ asset('tinymce/tinymce.min.js') }}" referrerpolicy="origin"></script>
@endpush


@section('content')
    @include('partials.header')
    @include('partials.nav')
    <div class="max-w-7xl mx-auto px-6 py-6">
        <livewire:alert-analis-component :id=$id />
    </div>

@endsection
