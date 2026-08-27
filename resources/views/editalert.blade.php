@extends('layouts.dashboard')

@push('vendor-styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendor/flatpickr/themes/airbnb.css') }}">
@endpush

@push('vendor-scripts')
    <script src="{{ asset('assets/vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('tinymce/tinymce.min.js') }}" referrerpolicy="origin"></script>
@endpush


@section('content')
    @include('partials.header')
    @include('partials.nav')
    <livewire:edit-alert-component :id=$id />

@endsection
