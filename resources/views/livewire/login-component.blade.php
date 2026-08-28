<div class="w-full max-w-md">
    <div class="glass rounded-sm p-8 sm:p-10">
        <div class="text-center mb-8">
            <img src="{{ asset('assets/logo-alerta.png') }}" alt="Alerta" class="w-36 mx-auto mb-4 dark:hidden">
            <img src="{{ asset('assets/logo-alerta-dark.png') }}" alt="Alerta" class="w-36 mx-auto mb-4 hidden dark:block">
        </div>

        <form wire:submit.prevent="login" class="space-y-5">
            <div>
                <label class="text-label text-stone-500 dark:text-slate-400 block mb-2">Username</label>
                <input
                    type="text"
                    wire:model.defer="email"
                    class="w-full bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 rounded-sm px-4 py-3 text-sm text-stone-900 dark:text-slate-100 focus:outline-none focus:border-stone-500 dark:focus:border-slate-400 transition-none"
                    placeholder="Enter your username"
                >
                @error('username')
                    <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="text-label text-stone-500 dark:text-slate-400 block mb-2">Password</label>
                <input
                    type="password"
                    wire:model.defer="password"
                    class="w-full bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 rounded-sm px-4 py-3 text-sm text-stone-900 dark:text-slate-100 focus:outline-none focus:border-stone-500 dark:focus:border-slate-400 transition-none"
                    placeholder="Enter your password"
                >
                @error('password')
                    <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="login"
                class="w-full bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 py-2 px-4 text-sm font-semibold rounded-sm cursor-pointer hover:bg-stone-800 dark:hover:bg-slate-300 transition-none disabled:opacity-60 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="login">Sign In</span>
                <span wire:loading wire:target="login" class="flex items-center justify-center gap-2">Signing in...</span>
            </button>
        </form>
    </div>
</div>
