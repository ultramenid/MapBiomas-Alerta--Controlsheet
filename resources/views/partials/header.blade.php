<header class="dot-pattern max-w-7xl px-6 mx-auto pt-4 flex items-center justify-between py-6">
    <div class="flex items-center">
        <div class="px-2">
            <img src="{{ asset('assets/logo-alerta.png') }}" alt="Alerta" class="w-36">
        </div>
    </div>

    <div class="flex gap-3 items-center">
        <button onclick="toggleTheme()"
            class="p-2 rounded-sm bg-stone-900 hover:bg-stone-700 dark:bg-slate-700 dark:hover:bg-slate-600 cursor-pointer transition-none">
            <span class="dark:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" fill="yellow" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 cursor-pointer text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                </svg>
            </span>
            <span class="hidden dark:inline">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 cursor-pointer dark:text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>
            </span>
        </button>

        @include('partials.toogleprofile')
    </div>
</header>
