<header class="max-w-7xl px-6 mx-auto pt-4 flex items-center justify-between py-6">
    <div class="flex items-center">
        <div class="px-2">
            <img src="{{ asset('assets/logo-alerta.png') }}" alt="Alerta" class="w-36 dark:hidden">
            <img src="{{ asset('assets/logo-alerta-dark.png') }}" alt="Alerta" class="w-36 hidden dark:block">
        </div>
    </div>

    <div class="flex gap-3 items-center">
        {{-- Who is online: distinct logged-in users with a page open, live from the
             Reverb presence channel (one user in several tabs is listed once). --}}
        <div class="relative z-30" x-data="onlineUsers" @click.outside="open = false" @keydown.escape.window="open = false">
            <button title="Online users" aria-label="Online users" aria-haspopup="true" :aria-expanded="open"
                @click="open = !open"
                class="relative p-2 rounded-sm bg-stone-900 hover:bg-stone-700 dark:bg-slate-700 dark:hover:bg-slate-600 cursor-pointer transition-none">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                <span x-show="users.length" x-cloak x-text="users.length"
                    class="absolute -top-1.5 -right-1.5 min-w-4 h-4 px-1 rounded-full bg-green-700 dark:bg-green-400 text-white dark:text-slate-900 text-[10px] leading-4 text-center font-semibold tabular-nums ring-2 ring-stone-50 dark:ring-slate-900"></span>
            </button>

            <div x-show="open" x-cloak
                class="absolute right-0 mt-2 w-60 overflow-hidden rounded-sm border border-stone-300 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-lg">
                <div class="flex items-center justify-between px-3 py-2.5 border-b border-stone-200 dark:border-slate-700">
                    <span class="text-label text-stone-600 dark:text-slate-400">Online now</span>
                    <span class="flex items-center gap-1.5 text-xs font-semibold tabular-nums text-green-700 dark:text-green-400">
                        <span class="size-1.5 rounded-full bg-current"></span>
                        <span x-text="users.length"></span>
                    </span>
                </div>
                <ul class="max-h-72 overflow-y-auto py-1.5" aria-label="Online users">
                    <template x-for="user in users" :key="user.id">
                        <li class="flex items-center gap-2.5 px-3 py-1.5">
                            <span class="relative shrink-0 size-7 rounded-full bg-stone-200 dark:bg-slate-600 flex items-center justify-center text-[10px] font-semibold text-stone-700 dark:text-slate-300">
                                <span x-text="user.initials"></span>
                                <span class="absolute -bottom-0.5 -right-0.5 size-2.5 rounded-full bg-green-700 dark:bg-green-400 ring-2 ring-white dark:ring-slate-800"></span>
                            </span>
                            <span class="truncate text-sm text-stone-700 dark:text-slate-300" x-text="user.name"></span>
                            <span x-show="user.me" class="ml-auto text-label text-stone-400 dark:text-slate-500">You</span>
                        </li>
                    </template>
                    <li x-show="!users.length" class="px-3 py-2 text-xs text-stone-500 dark:text-slate-500">Connecting…</li>
                </ul>
            </div>
        </div>

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
