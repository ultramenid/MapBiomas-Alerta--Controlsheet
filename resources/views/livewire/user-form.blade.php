<div class="dark:text-slate-400">
    @php
        $roles = [
            '2' => ['Validator', 'Inputs and refines alerts'],
            '1' => ['Auditor', 'Audits validated alerts'],
            '0' => ['Admin', 'Full access, including users'],
        ];
        $input = 'w-full bg-white dark:bg-slate-800 border text-stone-900 dark:text-slate-100 rounded-sm px-3 py-2 text-sm focus:outline-none focus:border-stone-900 dark:focus:border-slate-300 transition-none';
        $ok = 'border-stone-300 dark:border-slate-600';
        $bad = 'border-red-500 dark:border-red-500';
        $label = 'block text-label text-stone-600 dark:text-slate-400 mb-1.5';
        $error = 'mt-1 text-xs text-red-600 dark:text-red-400';
        $section = 'grid sm:grid-cols-3 gap-x-6 gap-y-3 py-5 border-t border-stone-200 dark:border-slate-700 first:border-t-0 first:pt-0';
    @endphp

    <a href="{{ url('/users') }}" class="inline-flex items-center gap-1 text-sm text-stone-500 dark:text-slate-400 hover:text-stone-900 dark:hover:text-slate-200 mb-3">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
            <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
        </svg>
        Users
    </a>

    <div class="flex flex-wrap items-baseline justify-between gap-2 mb-4">
        <h1 class="text-2xl sm:text-heading text-stone-900 dark:text-slate-100">{{ $editing ? 'Edit user' : 'Add user' }}</h1>
        @if ($editing && $createdAt)
            <span class="text-xs text-stone-500 dark:text-slate-400">Added {{ \Carbon\Carbon::parse($createdAt)->format('d M Y') }}</span>
        @endif
    </div>

    <form wire:submit="storeUser" class="glass rounded-sm p-5 relative z-20" novalidate>
        {{-- ===== PROFILE ===== --}}
        <div class="{{ $section }}">
            <div>
                <div class="text-sm font-semibold text-stone-900 dark:text-slate-100">Profile</div>
                <p class="text-xs text-stone-500 dark:text-slate-400 mt-1">Shown across dashboards and audit logs.</p>
            </div>
            <div class="sm:col-span-2 grid sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label for="name" class="{{ $label }}">Name</label>
                    <input id="name" type="text" wire:model="name" autocomplete="off" class="{{ $input }} {{ $errors->has('name') ? $bad : $ok }}">
                    @error('name') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="{{ $label }}">Email</label>
                    <input id="email" type="email" wire:model="email" autocomplete="off" placeholder="user@email.com" class="{{ $input }} {{ $errors->has('email') ? $bad : $ok }}">
                    @error('email') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="contact" class="{{ $label }}">Contact</label>
                    <input id="contact" type="tel" wire:model="contact" autocomplete="off" placeholder="08…" class="{{ $input }} {{ $errors->has('contact') ? $bad : $ok }}">
                    @error('contact') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- ===== ACCESS ===== --}}
        <div class="{{ $section }}">
            <div>
                <div class="text-sm font-semibold text-stone-900 dark:text-slate-100">Access</div>
                <p class="text-xs text-stone-500 dark:text-slate-400 mt-1">
                    @if ($isSelf)
                        This is your account, so role and status are locked.
                    @else
                        What this user can do once signed in.
                    @endif
                </p>
            </div>
            <div class="sm:col-span-2 space-y-4">
                <fieldset>
                    <legend class="{{ $label }}">Role</legend>
                    <div class="grid sm:grid-cols-3 gap-2">
                        @foreach ($roles as $value => [$roleName, $roleNote])
                            @php $picked = (string) $level === $value; @endphp
                            <label class="relative flex flex-col gap-0.5 rounded-sm border px-3 py-2.5 transition-none
                                {{ $isSelf ? 'cursor-not-allowed' : 'cursor-pointer hover:border-stone-500 dark:hover:border-slate-400' }}
                                {{ $picked ? 'border-stone-900 dark:border-slate-200 bg-white dark:bg-slate-800' : ($errors->has('level') ? $bad : $ok) }}
                                {{ $isSelf && ! $picked ? 'opacity-50' : '' }}">
                                <input type="radio" wire:model.live="level" value="{{ $value }}" class="sr-only" @disabled($isSelf)>
                                <span class="flex items-center justify-between text-sm font-semibold text-stone-900 dark:text-slate-100">
                                    {{ $roleName }}
                                    @if ($picked)
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </span>
                                <span class="text-xs text-stone-500 dark:text-slate-400">{{ $roleNote }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('level') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </fieldset>

                @if ($editing)
                    <div>
                        <span class="{{ $label }}">Status</span>
                        <label class="inline-flex items-center gap-3 {{ $isSelf ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }}">
                            <input type="checkbox" class="sr-only peer" @checked((string) $is_active === '1') @disabled($isSelf)
                                wire:change="$set('is_active', $event.target.checked ? '1' : '0')">
                            <span class="relative w-9 h-5 rounded-full transition-none {{ (string) $is_active === '1' ? 'bg-green-700 dark:bg-green-500' : 'bg-stone-300 dark:bg-slate-600' }}">
                                <span class="absolute top-0.5 size-4 rounded-full bg-white shadow-sm {{ (string) $is_active === '1' ? 'left-[1.125rem]' : 'left-0.5' }}"></span>
                            </span>
                            <span class="text-sm text-stone-700 dark:text-slate-300">
                                {{ (string) $is_active === '1' ? 'Active — can sign in' : 'Inactive — sign-in blocked, alerts hidden from lists' }}
                            </span>
                        </label>
                        @error('is_active') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== PASSWORD ===== --}}
        <div class="{{ $section }}">
            <div>
                <div class="text-sm font-semibold text-stone-900 dark:text-slate-100">Password</div>
                <p class="text-xs text-stone-500 dark:text-slate-400 mt-1">
                    {{ $editing ? 'Leave blank to keep the current password.' : 'At least 8 characters.' }}
                </p>
            </div>
            <div class="sm:col-span-2" x-data="{ show: false }">
                <label for="password" class="{{ $label }}">{{ $editing ? 'New password' : 'Password' }}</label>
                <div class="relative">
                    <input id="password" :type="show ? 'text' : 'password'" type="password" wire:model="password" autocomplete="new-password"
                        class="{{ $input }} pr-10 {{ $errors->has('password') ? $bad : $ok }}">
                    <button type="button" @click="show = !show" :aria-label="show ? 'Hide password' : 'Show password'"
                        class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-stone-400 hover:text-stone-900 dark:hover:text-slate-200 cursor-pointer">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('password') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- ===== FOOTER ===== --}}
        <div class="flex justify-end gap-2 pt-4 border-t border-stone-200 dark:border-slate-700">
            <a href="{{ url('/users') }}" class="border border-stone-300 dark:border-slate-600 text-stone-700 dark:text-slate-300 py-2 px-4 text-sm font-semibold rounded-sm cursor-pointer hover:bg-stone-100 dark:hover:bg-slate-800 transition-none">Cancel</a>
            <button type="submit" wire:loading.attr="disabled" wire:target="storeUser"
                class="bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 py-2 px-4 text-sm font-semibold rounded-sm cursor-pointer hover:bg-stone-800 dark:hover:bg-slate-300 transition-none disabled:opacity-60 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="storeUser">{{ $editing ? 'Save changes' : 'Add user' }}</span>
                <span wire:loading wire:target="storeUser">Saving…</span>
            </button>
        </div>
    </form>
</div>
