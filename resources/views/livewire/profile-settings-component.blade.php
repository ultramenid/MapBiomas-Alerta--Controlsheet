<form wire:submit="saveProfile" class="glass rounded-sm p-5 mb-4 relative z-20 dark:text-slate-400" novalidate>
    @php
        $input = 'w-full bg-white dark:bg-slate-800 border text-stone-900 dark:text-slate-100 rounded-sm px-3 py-2 text-sm focus:outline-none focus:border-stone-900 dark:focus:border-slate-300 transition-none';
        $label = 'block text-label text-stone-600 dark:text-slate-400 mb-1.5';
        $initials = collect(preg_split('/\s+/', trim((string) $name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    @endphp
    <div class="grid sm:grid-cols-3 gap-x-6 gap-y-4">
        <div>
            <div class="text-sm font-semibold text-stone-900 dark:text-slate-100">Profile</div>
            <p class="text-xs text-stone-500 dark:text-slate-400 mt-1">Your name appears in dashboards, audit logs and the online list.</p>
        </div>

        <div class="sm:col-span-2 space-y-4">
            <div class="flex items-center gap-3 pb-4 border-b border-stone-200 dark:border-slate-700">
                <span class="shrink-0 size-10 rounded-full bg-stone-200 dark:bg-slate-600 flex items-center justify-center text-xs font-semibold text-stone-700 dark:text-slate-300">{{ $initials }}</span>
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold text-stone-900 dark:text-slate-100">{{ $email }}</div>
                    <div class="text-xs text-stone-500 dark:text-slate-400">
                        {{ $role }}@if ($createdAt) · member since {{ \Carbon\Carbon::parse($createdAt)->format('M Y') }}@endif
                    </div>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="profile-name" class="{{ $label }}">Name</label>
                    <input id="profile-name" type="text" wire:model="name" autocomplete="name" class="{{ $input }} {{ $errors->has('name') ? 'border-red-500' : 'border-stone-300 dark:border-slate-600' }}">
                    @error('name') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="profile-contact" class="{{ $label }}">Contact</label>
                    <input id="profile-contact" type="tel" wire:model="contact" autocomplete="tel" class="{{ $input }} {{ $errors->has('contact') ? 'border-red-500' : 'border-stone-300 dark:border-slate-600' }}">
                    @error('contact') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>
            <p class="text-xs text-stone-500 dark:text-slate-400">Email and role can only be changed by an admin.</p>

            <div class="flex justify-end">
                <button type="submit" wire:loading.attr="disabled" wire:target="saveProfile"
                    class="bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 py-2 px-4 text-sm font-semibold rounded-sm cursor-pointer hover:bg-stone-800 dark:hover:bg-slate-300 transition-none disabled:opacity-60 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="saveProfile">Save profile</span>
                    <span wire:loading wire:target="saveProfile">Saving…</span>
                </button>
            </div>
        </div>
    </div>
</form>
