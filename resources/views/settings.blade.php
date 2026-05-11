@extends('layouts.dashboard')

@section('content')
    @include('partials.header')
    @include('partials.nav')

    <main class="h-screen sm:mt-28 mt-4">
        <div class="max-w-7xl mx-auto px-6 py-6">
            <div class="glass rounded-sm p-5 mb-5">
                <div class="sm:pt-8 pt-4">
                    <div class="grid grid-cols-12">
                        <ul class="sm:space-y-3 space-y-0 sm:space-x-0 mb-6 space-x-3 sm:col-span-2 col-span-12 subpixel-antialiased sm:flex sm:flex-col flex flex-row">
                            @include('partials.sidebarSettings')
                        </ul>
                        <div class="sm:col-span-10 col-span-12 space-y-1 mb-6">
                            <livewire:change-password-component />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
