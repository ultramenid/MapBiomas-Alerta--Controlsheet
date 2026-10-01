<div class="relative z-[45]" x-data="{ isProfileMenuOpen: false }" @click.outside="isProfileMenuOpen = false" @keydown.escape.window="isProfileMenuOpen = false">
    <button title="Profile" aria-label="Account" aria-haspopup="true" :aria-expanded="isProfileMenuOpen"
        @click="isProfileMenuOpen = !isProfileMenuOpen"
        class="block cursor-pointer rounded-full focus:outline-none">
        <img class="object-cover w-8 h-8 rounded-full" src="{{ asset('assets/minecraft.png') }}" alt="" aria-hidden="true" />
    </button>

    {{-- Same surface as the online-users dropdown in partials/header --}}
    <div x-show="isProfileMenuOpen" x-cloak
        class="absolute right-0 mt-2 w-60 overflow-hidden rounded-sm border border-stone-300 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-lg">
        <div class="px-3 py-2.5 border-b border-stone-200 dark:border-slate-700">
            <div class="text-label text-stone-600 dark:text-slate-400">Signed in as</div>
            <div class="mt-1.5 truncate text-sm font-semibold text-stone-900 dark:text-slate-200">{{ session('name') }}</div>
            <div class="truncate text-xs text-stone-500 dark:text-slate-500">{{ session('email') }}</div>
        </div>
        <ul class="py-1.5" aria-label="submenu">
            <li>
                <a href="{{ url('/settings') }}" class="flex items-center gap-2.5 px-3 py-2 text-sm text-stone-700 dark:text-slate-300 hover:bg-stone-100 dark:hover:bg-slate-700 hover:text-stone-900 dark:hover:text-slate-200 transition-none">
                    <svg class="size-4 shrink-0" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Settings</span>
                </a>
            </li>
            <li class="mt-1.5 pt-1.5 border-t border-stone-200 dark:border-slate-700">
                <a href="{{ url('/logout') }}" class="flex items-center gap-2.5 px-3 py-2 text-sm text-stone-700 dark:text-slate-300 hover:bg-stone-100 dark:hover:bg-slate-700 hover:text-stone-900 dark:hover:text-slate-200 transition-none">
                    <svg class="size-4 shrink-0" aria-hidden="true" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Log out</span>
                </a>
            </li>
        </ul>
    </div>
</div>
