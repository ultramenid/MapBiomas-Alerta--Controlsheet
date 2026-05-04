@extends('layouts.dashboard')


@section('content')
    @include('partials.header')
    @include('partials.nav')

    <div class="max-w-6xl mx-auto px-7 py-4  mt-12 z-20">

        @if (session('role_id') == 0)
            <livewire:filter-dashboard-component>
            <livewire:summary-alert-commponent />
            <livewire:auditor-summary-component>
            <livewire:validator-task-component />
            <livewire:check-alert-analis />
        @endif

        @if (session('role_id') == 1)

            <livewire:auditor-task-component />
            <livewire:filter-dashboard-component>
            <livewire:check-alert-analis />

        @endif

        @if (session('role_id') == 2)
            <livewire:filter-dashboard-component>
            <livewire:sumary-alert-analis />
            <livewire:validator-task-component />
            <livewire:table-analisis />
        @endif
    </div>

    @if (session('role_id') == 2)
    <div>
        <div class="fixed z-30 sm:bottom-10  bottom-6 right-12 cursor-pointer " >
            <a href="{{url('/addalert')}}">
                <div class="sm:px-4 px-2 sm:py-4 py-2 border border-white bg-black rounded-full bgrmi flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                </div>
            </a>
        </div>
    </div>
@endif
@endsection
