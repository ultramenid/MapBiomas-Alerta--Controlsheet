<div class="border-b border-stone-300 dark:border-slate-700 z-10">
    <div class="max-w-7xl mx-auto px-6" x-data="{ pages: false }">
        <nav class="-mb-px flex space-x-1 text-sm leading-5 overflow-x-auto scrollbar-hide">
            <div class="py-3 px-4 rounded-sm @if($nav == 'dashboard') border-b-2 border-stone-900 dark:border-slate-400 @endif">
                <a href="{{url('/dashboard')}}" class="px-0.5 @if($nav == 'dashboard') text-stone-900 dark:text-slate-300 font-semibold @else text-stone-500 dark:text-slate-500 @endif hover:text-stone-900 dark:hover:text-slate-300 cursor-pointer transition-none">
                    Dashboard
                </a>
            </div>

            <div class="py-3 px-4 rounded-sm @if($nav == 'alerts') border-b-2 border-stone-900 dark:border-slate-400 @endif">
                <a href="{{url('/alerts')}}" class="px-0.5 @if($nav == 'alerts') text-stone-900 dark:text-slate-300 font-semibold @else text-stone-500 dark:text-slate-500 @endif hover:text-stone-900 dark:hover:text-slate-300 cursor-pointer transition-none">
                    Alerts
                </a>
            </div>

            @if (session('role_id') == 0)
                <div class="py-3 px-4 rounded-sm @if($nav == 'users') border-b-2 border-stone-900 dark:border-slate-400 @endif">
                    <a href="{{url('/users')}}" class="px-0.5 @if($nav == 'users') text-stone-900 dark:text-slate-300 font-semibold @else text-stone-500 dark:text-slate-500 @endif hover:text-stone-900 dark:hover:text-slate-300 cursor-pointer transition-none">
                        Users
                    </a>
                </div>
            @endif

            <div class="py-3 px-4 rounded-sm @if($nav == 'settings') border-b-2 border-stone-900 dark:border-slate-400 @endif">
                <a href="{{url('/settings')}}" class="px-0.5 @if($nav == 'settings') text-stone-900 dark:text-slate-300 font-semibold @else text-stone-500 dark:text-slate-500 @endif hover:text-stone-900 dark:hover:text-slate-300 cursor-pointer transition-none">
                    Settings
                </a>
            </div>
        </nav>
    </div>
</div>