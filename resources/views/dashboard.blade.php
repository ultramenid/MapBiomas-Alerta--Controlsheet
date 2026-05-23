@extends('layouts.dashboard')


@section('content')
    @include('partials.header')
    @include('partials.nav')

    <div class="max-w-7xl mx-auto px-6 py-6">

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
            <livewire:validator-task-component />
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
        <div class="fixed z-30 bottom-6 right-6">
            <a href="{{url('/addalert')}}">
                <div class="w-12 h-12 bg-stone-900 dark:bg-slate-200 rounded-full flex items-center justify-center hover:bg-stone-800 dark:hover:bg-slate-300 cursor-pointer transition-none shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white dark:text-stone-900" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                </div>
            </a>
        </div>
    @endif
@endsection
