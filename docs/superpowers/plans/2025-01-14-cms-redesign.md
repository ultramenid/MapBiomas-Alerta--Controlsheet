# CMS UI/UX Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign all CMS pages (login to dashboard and beyond) with glassmorphism, sharp corners, no animations, and professional density while preserving all existing functionality.

**Architecture:** Apply restrained glassmorphism to key elements (stats cards, tables), keep solid backgrounds for interactive elements, fix theme flickering, and redesign all Livewire components with the new design system.

**Tech Stack:** Laravel + Livewire + Tailwind CSS v4 + Alpine.js

---

## File Structure

### Global
- `resources/css/app.css` - Add glass utility classes, typography helpers
- `resources/js/theme.js` - Fix theme flickering (remove auto system override)
- `resources/views/layouts/dashboard.blade.php` - Layout wrapper, dot pattern

### Shared Components
- `resources/views/partials/header.blade.php` - Header with logo, theme toggle
- `resources/views/partials/nav.blade.php` - Navigation bar

### Pages - Phase 1
- `resources/views/login.blade.php` - Login page wrapper
- `resources/views/livewire/login-component.blade.php` - Login form
- `resources/views/dashboard.blade.php` - Dashboard wrapper
- `resources/views/livewire/summary-alert-commponent.blade.php` - Regional table
- `resources/views/livewire/auditor-summary-component.blade.php` - Auditor table
- `resources/views/livewire/filter-dashboard-component.blade.php` - Filter bar
- `resources/views/livewire/validator-task-component.blade.php` - Validator tasks
- `resources/views/livewire/check-alert-analis.blade.php` - Check analysis

### Pages - Phase 2
- `resources/views/alerts.blade.php` - Alerts page
- `resources/views/auditing.blade.php` - Auditor page
- `resources/views/users.blade.php` - Users management
- `resources/views/settings.blade.php` - Settings page
- All other `resources/views/livewire/*` components

---

## Phase 1: Foundation & Core Pages

### Task 1: Fix Theme Flickering Bug

**Files:**
- Modify: `resources/js/theme.js`
- Modify: `resources/views/layouts/dashboard.blade.php` (inline script)

**Context:** Currently `watchSystemTheme()` overrides user preference whenever OS theme changes. This causes flickering.

- [ ] **Step 1: Remove auto system theme override**

Edit `resources/js/theme.js`:
```javascript
const STORAGE_KEY = 'theme';

function applyTheme(theme) {
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function initTheme() {
    const stored = localStorage.getItem(STORAGE_KEY);
    
    if (stored === 'dark' || stored === 'light') {
        applyTheme(stored);
    } else {
        // First visit only - use system preference
        applyTheme(getSystemTheme());
    }
}

export function toggleTheme() {
    const isDark = document.documentElement.classList.contains('dark');
    const newTheme = isDark ? 'light' : 'dark';
    localStorage.setItem(STORAGE_KEY, newTheme);
    applyTheme(newTheme);
}

export function watchSystemTheme() {
    // REMOVED: No longer auto-overrides user preference
    // User choice now persists until manually changed
}
```

- [ ] **Step 2: Verify the fix**

Run: Check browser console for no errors
Expected: Theme toggle works, choice persists after refresh

- [ ] **Step 3: Commit**

```bash
git add resources/js/theme.js
git commit -m "fix: remove auto system theme override to prevent flickering"
```

---

### Task 2: Add Glassmorphism CSS Utilities

**Files:**
- Modify: `resources/css/app.css`

- [ ] **Step 1: Add glass utility classes**

Add to `resources/css/app.css` after existing custom classes:
```css
/* Glassmorphism Utilities */
.glass {
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.dark .glass {
    background: rgba(15, 23, 42, 0.75);
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.glass-dark-accent {
    background: rgba(28, 25, 23, 0.85);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
}

.dark .glass-dark-accent {
    background: rgba(15, 23, 42, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.12);
}

/* Fallback for browsers without backdrop-filter */
@supports not (backdrop-filter: blur(6px)) {
    .glass {
        background: rgba(255, 255, 255, 0.9);
    }
    .dark .glass {
        background: rgba(15, 23, 42, 0.95);
    }
    .glass-dark-accent {
        background: rgba(28, 25, 23, 0.95);
    }
}

/* Typography Utilities */
.text-label {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1.2px;
}

.text-stat {
    font-size: 34px;
    font-weight: 800;
    letter-spacing: -1.5px;
    line-height: 1;
}

.text-heading {
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -0.5px;
    line-height: 1.1;
}

/* Sharp corners utility */
.rounded-sharp {
    border-radius: 2px;
}

/* Status row backgrounds for dark mode */
.dark .status-approved {
    background-color: rgba(22, 101, 52, 0.25);
}

.dark .status-pending {
    background-color: rgba(161, 98, 7, 0.2);
}

.dark .status-rejected {
    background-color: rgba(153, 27, 27, 0.2);
}
```

- [ ] **Step 2: Verify CSS compiles**

Run: `npm run build` or `npm run dev`
Expected: No CSS compilation errors

- [ ] **Step 3: Commit**

```bash
git add resources/css/app.css
git commit -m "feat: add glassmorphism utilities and typography helpers"
```

---

### Task 3: Update Layout Structure

**Files:**
- Modify: `resources/views/layouts/dashboard.blade.php`

- [ ] **Step 1: Update layout wrapper**

Replace `resources/views/layouts/dashboard.blade.php`:
```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'Page Title'}}</title>
    <script>
    (function () {
        const theme = localStorage.getItem('theme');
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else if (theme === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            }
        }
    })();
    </script>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    @livewireStyles
    @livewireScripts
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="{{ asset('tinymce/tinymce.min.js') }}" referrerpolicy="origin"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body class="dot-pattern dark:selection-bg selection-bg font-sans dark:bg-slate-900 bg-stone-50">
    @yield('content')
    <x-toaster-hub />
    <img src="{{ asset('assets/logo.png') }}" alt="" class="fixed sm:block hidden sm:bottom-6 sm:left-6 rotate-90 z-10 w-32 opacity-60">
    @stack('scripts')
</body>
</html>
```

Changes:
- Removed `bgrmi` class (no longer needed)
- Added `bg-stone-50` for light mode
- Adjusted logo position to be more visible

- [ ] **Step 2: Verify layout loads**

Run: Load any page
Expected: No errors, dot pattern visible

- [ ] **Step 3: Commit**

```bash
git add resources/views/layouts/dashboard.blade.php
git commit -m "feat: update layout with stone background and improved logo placement"
```

---

### Task 4: Redesign Header

**Files:**
- Modify: `resources/views/partials/header.blade.php`

- [ ] **Step 1: Implement new header design**

Replace `resources/views/partials/header.blade.php`:
```blade
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
```

Changes:
- `max-w-6xl` → `max-w-7xl` for more space
- Removed `bg-white dark:bg-slate-900` - header sits on dot pattern
- `rounded-full` → `rounded-sm` for sharp corners
- Added `transition-none` to prevent any hover animations

- [ ] **Step 2: Verify header appearance**

Run: Load dashboard page
Expected: Clean header on dot pattern, no background color

- [ ] **Step 3: Commit**

```bash
git add resources/views/partials/header.blade.php
git commit -m "feat: redesign header with sharp corners and transparent background"
```

---

### Task 5: Redesign Navigation

**Files:**
- Modify: `resources/views/partials/nav.blade.php`

- [ ] **Step 1: Implement new navigation**

Replace `resources/views/partials/nav.blade.php`:
```blade
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
```

Changes:
- `space-x-4` → `space-x-1` for tighter nav
- `rounded` → `rounded-sm`
- `text-gray-500` → `text-stone-500` for warm grays
- `border-gray-300` → `border-stone-300`
- Added `transition-none` to all links
- Simplified active state styling

- [ ] **Step 2: Verify navigation**

Run: Load dashboard, check nav appearance
Expected: Clean nav with bottom border active indicator

- [ ] **Step 3: Commit**

```bash
git add resources/views/partials/nav.blade.php
git commit -m "feat: redesign navigation with sharp corners and stone colors"
```

---

### Task 6: Redesign Login Page

**Files:**
- Modify: `resources/views/login.blade.php`
- Modify: `resources/views/livewire/login-component.blade.php`

- [ ] **Step 1: Update login wrapper**

`resources/views/login.blade.php`:
```blade
@extends('layouts.dashboard')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4">
        <livewire:login-component />
    </div>
@endsection
```

- [ ] **Step 2: Implement glass login card**

`resources/views/livewire/login-component.blade.php`:
```blade
<div class="w-full max-w-md">
    <div class="glass rounded-sm p-8 sm:p-10">
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-green-alerta rounded-sm mx-auto mb-4"></div>
            <h1 class="text-heading text-stone-900 dark:text-slate-100 mb-2">Welcome Back</h1>
            <p class="text-sm text-stone-500 dark:text-slate-400">Sign in to your account</p>
        </div>

        <form wire:submit.prevent="login" class="space-y-5">
            <div>
                <label class="text-label text-stone-500 dark:text-slate-400 block mb-2">Username</label>
                <input 
                    type="text" 
                    wire:model.defer="username"
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
                class="w-full bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 rounded-sm py-3 text-sm font-semibold hover:bg-stone-800 dark:hover:bg-slate-300 cursor-pointer transition-none"
            >
                Sign In
            </button>
        </form>
    </div>
</div>
```

- [ ] **Step 3: Verify login page**

Run: Navigate to login page
Expected: Centered glass card, clean form, no animations

- [ ] **Step 4: Commit**

```bash
git add resources/views/login.blade.php resources/views/livewire/login-component.blade.php
git commit -m "feat: redesign login page with glassmorphism card"
```

---

### Task 7: Redesign Dashboard Layout

**Files:**
- Modify: `resources/views/dashboard.blade.php`

- [ ] **Step 1: Update dashboard wrapper**

`resources/views/dashboard.blade.php`:
```blade
@extends('layouts.dashboard')

@section('content')
    @include('partials.header')
    @include('partials.nav')

    <div class="max-w-7xl mx-auto px-6 py-6">
        @if (session('role_id') == 0)
            <livewire:filter-dashboard-component>
            <livewire:summary-alert-commponent />
            <livewire:auditor-summary-component>
            <livewire:validator-task-component />
            <livewire:check-alert-analis />
        @endif

        @if (session('role_id') == 1)
            <livewire:auditor-task-component />
            <livewire:filter-dashboard-component>
            <livewire:check-alert-analis />
        @endif

        @if (session('role_id') == 2)
            <livewire:filter-dashboard-component>
            <livewire:sumary-alert-analis />
            <livewire:validator-task-component />
            <livewire:table-analisis />
        @endif
    </div>

    @if (session('role_id') == 2)
        <div class="fixed z-30 bottom-6 right-6">
            <a href="{{url('/addalert')}}">
                <div class="w-12 h-12 bg-stone-900 dark:bg-slate-200 rounded-full flex items-center justify-center hover:bg-stone-800 dark:hover:bg-slate-300 cursor-pointer transition-none shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white dark:text-stone-900" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                </div>
            </a>
        </div>
    @endif
@endsection
```

Changes:
- `max-w-6xl` → `max-w-7xl`
- `px-7 py-4 mt-12 z-20` → `px-6 py-6` (tighter)
- FAB: Simplified positioning, added shadow, sharp hover

- [ ] **Step 2: Verify dashboard layout**

Run: Load dashboard
Expected: Clean layout with proper spacing

- [ ] **Step 3: Commit**

```bash
git add resources/views/dashboard.blade.php
git commit -m "feat: update dashboard layout with max-width and FAB styling"
```

---

### Task 8: Redesign Filter Component

**Files:**
- Modify: `resources/views/livewire/filter-dashboard-component.blade.php`

- [ ] **Step 1: Implement glass filter bar**

`resources/views/livewire/filter-dashboard-component.blade.php`:
```blade
<div class="glass rounded-sm p-4 mb-5">
    <div class="text-label text-stone-600 dark:text-slate-400 mb-3">Filter</div>
    <div class="flex gap-3">
        <div class="sm:w-36 w-full relative">
            <select 
                wire:ignore 
                id='date-dropdown' 
                wire:model.live="yearAlert" 
                class="w-full appearance-none bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 px-3 py-2 text-sm rounded-sm focus:outline-none focus:border-stone-500 cursor-pointer transition-none"
            >
                <option value="all">All Years</option>
            </select>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-3 top-2.5 size-4 text-stone-500">
                <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
            </svg>
        </div>
        <button 
            wire:click='filter' 
            class="bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 py-2 px-4 text-sm font-semibold rounded-sm cursor-pointer hover:bg-stone-800 dark:hover:bg-slate-300 transition-none"
        >
            Apply
        </button>
    </div>

    <script>
        let dateDropdown = document.getElementById('date-dropdown');
        let currentYear = new Date().getFullYear();
        let earliestYear = 2020;
        while (currentYear >= earliestYear) {
            let dateOption = document.createElement('option');
            dateOption.text = currentYear;
            dateOption.value = currentYear;
            dateDropdown.add(dateOption);
            currentYear -= 1;
        }
    </script>
</div>
```

Changes:
- Wrapped in `glass` container
- `text-label` utility for title
- Sharp corners (`rounded-sm`)
- Stone color palette
- No animations (`transition-none`)

- [ ] **Step 2: Verify filter bar**

Run: Check dashboard filter
Expected: Glass panel with dropdown and button

- [ ] **Step 3: Commit**

```bash
git add resources/views/livewire/filter-dashboard-component.blade.php
git commit -m "feat: redesign filter component with glassmorphism"
```

---

### Task 9: Redesign Summary Alert Component

**Files:**
- Modify: `resources/views/livewire/summary-alert-commponent.blade.php`

- [ ] **Step 1: Implement glass table container**

Keep all existing PHP/Livewire logic, only update HTML structure:

Replace outer div and styling:
```blade
<div class="glass rounded-sm p-5 mb-5">
    <div class="text-label text-stone-600 dark:text-slate-400 mb-4">Alert Status by Region</div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="border-b border-stone-300 dark:border-slate-700">
                    <th class="text-left px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Status</th>
                    <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Bali</th>
                    <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Java</th>
                    <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Kalim</th>
                    <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Mal</th>
                    <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Pap</th>
                    <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Sul</th>
                    <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Sum</th>
                    <th class="text-right px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Total</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach ($alerts as $item)
                    <tr class="border-b border-stone-200 dark:border-slate-800">
                        <td class="px-3 py-2.5 font-semibold 
                            @if($item['auditorStatus'] === 'Grand Total') text-stone-900 dark:text-slate-200 bg-stone-100 dark:bg-slate-800
                            @elseif($item['auditorStatus'] === 'approved') text-green-700 dark:text-green-400
                            @elseif($item['auditorStatus'] === 'rejected' || $item['auditorStatus'] === 'duplicate') text-red-700 dark:text-red-400
                            @elseif($item['auditorStatus'] === 'pre-approved') text-stone-600 dark:text-slate-400
                            @elseif($item['auditorStatus'] === 'refined') text-sky-700 dark:text-sky-400
                            @elseif($item['auditorStatus'] === 'reexportimage' || $item['auditorStatus'] === 'reclassification') text-amber-700 dark:text-amber-400
                            @endif">
                            {{$item['auditorStatus']}}
                        </td>
                        @foreach(['Balinusatenggara', 'Java', 'Kalimantan', 'Maluku', 'Papua', 'Sulawesi', 'Sumatra'] as $region)
                            <td class="text-center px-3 py-2.5 text-stone-700 dark:text-slate-300">{{$item[$region]}}</td>
                        @endforeach
                        <td class="text-right px-3 py-2.5 font-bold text-stone-900 dark:text-slate-200">{{$item['TOTAL']}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
```

Note: The original component has very repetitive conditional classes. Consider creating a helper method in the component class to reduce repetition, but for now, keep the logic and update the styling.

- [ ] **Step 2: Verify table appearance**

Run: Check regional table on dashboard
Expected: Glass container with clean table, status colors visible

- [ ] **Step 3: Commit**

```bash
git add resources/views/livewire/summary-alert-commponent.blade.php
git commit -m "feat: redesign summary alert table with glassmorphism"
```

---

### Task 10: Redesign Auditor Summary Component

**Files:**
- Modify: `resources/views/livewire/auditor-summary-component.blade.php`

- [ ] **Step 1: Implement glass auditor panel**

Update the outer structure while preserving all existing logic:

```blade
<div class="glass rounded-sm p-5 mb-5">
    <div class="flex flex-col sm:flex-row sm:gap-6 gap-3 mb-5 items-start">
        <div>
            <div class="text-label text-stone-600 dark:text-slate-400 mb-3">Alert by Auditor</div>
            <div wire:ignore x-init="
                flatpickr('#rangeAuditor', {
                    mode:'range',
                    dateFormat: 'Y-m-d',
                    onChange: function(selectedDates) {
                        if (selectedDates.length === 2) {
                            let options = { timeZone: 'Asia/Jakarta', year: 'numeric', month: '2-digit', day: '2-digit' };
                            function formatDate(d) {
                                let parts = new Intl.DateTimeFormat('id-ID', options).formatToParts(d);
                                let y = parts.find(p => p.type === 'year').value;
                                let m = parts.find(p => p.type === 'month').value;
                                let day = parts.find(p => p.type === 'day').value;
                                return `${y}-${m}-${day}`;
                            }
                            let startDate = formatDate(selectedDates[0]);
                            let endDate = formatDate(selectedDates[1]);
                            $wire.set('startDate', startDate);
                            $wire.set('endDate', endDate);
                            $wire.call('filter');
                        }
                    }
                });
            ">
                <input 
                    id="rangeAuditor" 
                    type="text" 
                    class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm focus:outline-none transition-none" 
                    wire:model.defer='rangeAuditor' 
                    placeholder="Select date range"
                >
            </div>
        </div>
        
        <div>
            <div class="text-label text-stone-600 dark:text-slate-400 mb-3">Find Auditor</div>
            <input 
                wire:keydown.enter="find" 
                type="text" 
                class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm focus:outline-none transition-none" 
                wire:model.defer='alertCode' 
                placeholder="Type alert ID"
            >
        </div>
        
        <div>
            <div class="text-label text-stone-600 dark:text-slate-400 mb-3">Find Validator</div>
            <input 
                wire:keydown.enter="findValidator" 
                type="text" 
                class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm focus:outline-none transition-none" 
                wire:model.defer='alertCodeValidator' 
                placeholder="Type alert ID"
            >
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="border-b border-stone-300 dark:border-slate-700">
                    <th class="sticky left-0 bg-stone-100 dark:bg-slate-800 text-left px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 z-10">Auditor</th>
                    @if (!empty($results))
                        @foreach (array_keys($results[array_key_first($results)]) as $key)
                            @if ($key !== 'auditorName' && $key !== 'auditorId' && $key !== 'Total')
                                <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('{{ $key }}')">
                                    {{ $key }}
                                    @if ($dataField === $key)
                                        <span>{{ $dataOrder === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </th>
                            @endif
                        @endforeach
                        <th class="sticky right-0 bg-stone-100 dark:bg-slate-800 text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 z-10 cursor-pointer" wire:click="sortBy('Total')">
                            Total
                            @if ($dataField === 'Total')
                                <span>{{ $dataOrder === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach ($results as $row)
                    <tr class="border-b border-stone-200 dark:border-slate-800">
                        <td class="sticky left-0 bg-white dark:bg-slate-900 px-3 py-2.5 z-10">
                            <a href="{{ url('/auditor-alert/'.$row['auditorId']) }}" class="text-green-700 dark:text-green-400 hover:underline transition-none">
                                {{ $row['auditorName'] }}
                            </a>
                        </td>
                        @foreach ($row as $key => $val)
                            @if ($key !== 'auditorName' && $key !== 'auditorId' && $key !== 'Total')
                                <td class="text-center px-3 py-2.5 text-stone-700 dark:text-slate-300">{{ $val }}</td>
                            @endif
                        @endforeach
                        <td class="sticky right-0 bg-white dark:bg-slate-900 text-center px-3 py-2.5 font-bold text-stone-900 dark:text-slate-200 z-10">{{ $row['Total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
```

- [ ] **Step 2: Verify auditor table**

Run: Check auditor summary on dashboard
Expected: Glass panel with date picker and table

- [ ] **Step 3: Commit**

```bash
git add resources/views/livewire/auditor-summary-component.blade.php
git commit -m "feat: redesign auditor summary with glassmorphism and improved layout"
```

---

## Phase 2: Remaining Pages

### Task 11: Redesign Alerts Page

**Files:**
- Modify: `resources/views/alerts.blade.php`
- Modify: `resources/views/livewire/alert-analis-component.blade.php`

- [ ] **Step 1: Update alerts page wrapper**

`resources/views/alerts.blade.php`:
```blade
@extends('layouts.dashboard')

@section('content')
    @include('partials.header')
    @include('partials.nav')

    <div class="max-w-7xl mx-auto px-6 py-6">
        <livewire:alert-analis-component />
    </div>
@endsection
```

- [ ] **Step 2: Redesign alert analysis component**

Apply same glassmorphism patterns:
- Glass containers for sections
- Sharp corners (rounded-sm)
- Stone color palette
- No animations
- Clean table styling

- [ ] **Step 3: Commit**

```bash
git add resources/views/alerts.blade.php resources/views/livewire/alert-analis-component.blade.php
git commit -m "feat: redesign alerts page with glassmorphism"
```

---

### Task 12: Redesign Users Page

**Files:**
- Modify: `resources/views/users.blade.php`
- Modify: `resources/views/livewire/users-component.blade.php`

- [ ] **Step 1: Apply glassmorphism to users table**

Same patterns:
- Glass wrapper
- Clean table with sharp corners
- Stone colors
- No animations

- [ ] **Step 2: Commit**

```bash
git add resources/views/users.blade.php resources/views/livewire/users-component.blade.php
git commit -m "feat: redesign users page with glassmorphism"
```

---

### Task 13: Redesign Settings Page

**Files:**
- Modify: `resources/views/settings.blade.php`
- Modify: `resources/views/livewire/change-password-component.blade.php`

- [ ] **Step 1: Apply glassmorphism to settings**

- [ ] **Step 2: Commit**

```bash
git add resources/views/settings.blade.php resources/views/livewire/change-password-component.blade.php
git commit -m "feat: redesign settings page with glassmorphism"
```

---

### Task 14: Redesign Remaining Livewire Components

**Files:**
- All remaining `resources/views/livewire/*.blade.php`

- [ ] **Step 1: Apply consistent styling to all components**

For each component:
1. Wrap main content in `glass rounded-sm p-5 mb-5`
2. Update titles to use `text-label`
3. Replace `rounded` with `rounded-sm`
4. Replace gray colors with stone colors
5. Add `transition-none` to interactive elements
6. Add dark mode variants

Components to update:
- `validator-task-component.blade.php`
- `check-alert-analis.blade.php`
- `auditor-task-component.blade.php`
- `sumary-alert-analis.blade.php`
- `table-analisis.blade.php`
- `add-alert-component.blade.php`
- `add-user-component.blade.php`
- `edit-alert-component.blade.php`
- `edit-user-component.blade.php`
- `auditor-alert-component.blade.php`
- `auditor-database-component.blade.php`
- `auditor-task-component.blade.php`
- `alert-test-component.blade.php`
- `analis-database-component.blade.php`
- All other components

- [ ] **Step 2: Commit in batches**

```bash
git add resources/views/livewire/
git commit -m "feat: apply glassmorphism design to all remaining livewire components"
```

---

## Testing & Verification

### Task 15: Final Verification

- [ ] **Step 1: Test all pages**

Check each page:
- [ ] Login page - glass card visible
- [ ] Dashboard - stats cards, tables, filters
- [ ] Alerts page - list view
- [ ] Users page - table view
- [ ] Settings page - forms
- [ ] Auditor pages - review interface

- [ ] **Step 2: Test theme toggle**

- Toggle between light/dark
- Refresh page - theme should persist
- No flickering
- Dot pattern visible in both modes

- [ ] **Step 3: Test responsiveness**

- Mobile view (320px+)
- Tablet view (768px+)
- Desktop view (1024px+)

- [ ] **Step 4: Check for animations**

- No fade-ins on page load
- No hover transitions
- No loading spinners
- Instant state changes

- [ ] **Step 5: Verify accessibility**

- Contrast ratios meet WCAG
- Focus states visible
- Keyboard navigation works

---

## Rollback Plan

If issues arise:
```bash
# Revert last commit
git revert HEAD

# Or reset to pre-redesign state
git reset --hard <commit-before-redesign>
```

---

## Notes

- Preserve all existing PHP/Livewire logic
- Only modify HTML structure and CSS classes
- Test each page after modification
- Commit frequently after each component
- Keep backup of original files until verified