@extends('layouts.dashboard')

@section('content')
    @include('partials.header')
    @include('partials.nav')

    <div class="max-w-3xl mx-auto px-6 py-6">
        <h1 class="text-2xl sm:text-heading text-stone-900 dark:text-slate-100 mb-4">Settings</h1>

        <livewire:profile-settings-component />
        <livewire:change-password-component />

        {{-- theme is a per-browser preference (localStorage), so no server round-trip --}}
        <div class="glass rounded-sm p-5 mb-4 relative z-20 dark:text-slate-400"
            x-data="{ mode: (() => { try { return localStorage.getItem('theme') || 'system' } catch (e) { return 'system' } })() }">
            <div class="grid sm:grid-cols-3 gap-x-6 gap-y-4">
                <div>
                    <div class="text-sm font-semibold text-stone-900 dark:text-slate-100">Appearance</div>
                    <p class="text-xs text-stone-500 dark:text-slate-400 mt-1">Saved in this browser. System follows your device setting.</p>
                </div>
                <div class="sm:col-span-2">
                    <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="Theme">
                        @foreach (['light' => 'Light', 'dark' => 'Dark', 'system' => 'System'] as $value => $text)
                            <button type="button" role="radio" :aria-checked="mode === '{{ $value }}'"
                                @click="mode = '{{ $value }}'; setTheme('{{ $value }}')"
                                :class="mode === '{{ $value }}' ? 'border-stone-900 dark:border-slate-200 bg-white dark:bg-slate-800 font-semibold text-stone-900 dark:text-slate-100' : 'border-stone-300 dark:border-slate-600 text-stone-600 dark:text-slate-400 hover:border-stone-500 dark:hover:border-slate-400'"
                                class="flex flex-col items-center gap-2 rounded-sm border px-3 py-3 text-sm cursor-pointer transition-none">
                                <span class="w-full h-10 rounded-sm border border-stone-300 dark:border-slate-600 overflow-hidden flex">
                                    @if ($value === 'system')
                                        <span class="flex-1 bg-stone-50"></span><span class="flex-1 bg-[#171717]"></span>
                                    @else
                                        <span class="flex-1 {{ $value === 'light' ? 'bg-stone-50' : 'bg-[#171717]' }}"></span>
                                    @endif
                                </span>
                                {{ $text }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
