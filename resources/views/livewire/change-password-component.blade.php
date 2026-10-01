<form wire:submit="storePassword" class="glass rounded-sm p-5 mb-4 relative z-20 dark:text-slate-400" novalidate x-data="{ show: false }">
    @php
        $input = 'w-full bg-white dark:bg-slate-800 border text-stone-900 dark:text-slate-100 rounded-sm px-3 py-2 text-sm focus:outline-none focus:border-stone-900 dark:focus:border-slate-300 transition-none';
        $label = 'block text-label text-stone-600 dark:text-slate-400 mb-1.5';
    @endphp
    <div class="grid sm:grid-cols-3 gap-x-6 gap-y-4">
        <div>
            <div class="text-sm font-semibold text-stone-900 dark:text-slate-100">Password</div>
            <p class="text-xs text-stone-500 dark:text-slate-400 mt-1">At least 8 characters. Forgot your current one? Ask an admin to reset it.</p>
        </div>

        <div class="sm:col-span-2 space-y-4">
            @foreach ([
                ['oldpassword', 'Current password', 'current-password'],
                ['newpassword', 'New password', 'new-password'],
                ['newpassword_confirmation', 'Confirm new password', 'new-password'],
            ] as [$field, $text, $autocomplete])
                <div>
                    <label for="{{ $field }}" class="{{ $label }}">{{ $text }}</label>
                    <input id="{{ $field }}" :type="show ? 'text' : 'password'" type="password" wire:model="{{ $field }}" autocomplete="{{ $autocomplete }}"
                        class="{{ $input }} {{ $errors->has($field) ? 'border-red-500' : 'border-stone-300 dark:border-slate-600' }}">
                    @error($field) <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            @endforeach

            <div class="flex items-center justify-between gap-3">
                <label class="inline-flex items-center gap-2 text-sm text-stone-600 dark:text-slate-400 cursor-pointer">
                    <input type="checkbox" x-model="show" class="size-4 accent-stone-900 dark:accent-slate-200">
                    Show passwords
                </label>
                <button type="submit" wire:loading.attr="disabled" wire:target="storePassword"
                    class="bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 py-2 px-4 text-sm font-semibold rounded-sm cursor-pointer hover:bg-stone-800 dark:hover:bg-slate-300 transition-none disabled:opacity-60 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="storePassword">Update password</span>
                    <span wire:loading wire:target="storePassword">Updating…</span>
                </button>
            </div>
        </div>
    </div>
</form>
