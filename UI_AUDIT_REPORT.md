# UI/UX Audit Report - Alerta Progress Blade Files

**Date:** 2026-05-12  
**Total Files Audited:** 46  
**Severity Levels:** Critical (functionality broken), Major (visual inconsistency), Minor (styling mismatch)

---

## Executive Summary

The codebase shows **systematic UI/UX inconsistencies** across 46 blade files. While a glassmorphism design system is partially implemented, multiple competing patterns exist for cards, tables, buttons, form inputs, and status badges. Dark mode support is inconsistent, with some components using semantic Tailwind colors and others using custom colors without dark variants.

### Top Issues by Category
- **7 Critical Issues** - Broken/missing dark mode, duplicate CSS classes
- **12 Major Issues** - Inconsistent component patterns across pages
- **8 Minor Issues** - Spacing, typography, and color variations

---

## 1. Glass Card / Container Inconsistencies

### Standard Pattern (Recommended)
```html
glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400
```

### Variations Found

| Pattern | Files | Severity |
|---------|-------|----------|
| `glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400` | table-analisis, auditor-database-component, analis-database-component, auditor-task-component, check-alert-analis | Standard |
| `glass rounded-sm p-5 mb-5` | summary-alert-commponent, auditor-summary-component | **Major** - Missing `z-20 relative` and `dark:text-slate-400` |
| `glass rounded-sm p-5 mb-5 text-label text-stone-600 dark:text-slate-400` | auditor-alert-component, check-approved-component, check-user-alert-audit | **Minor** - Extra text-label/text-stone-600, inconsistent with standard |
| **NO glass class** - uses `py-6 px-4 border border-stone-200 dark:border-slate-700 z-20 relative bg-stone-50 dark:bg-slate-800 dark:bg-slate-800 dark:border-slate-800 mt-4` | validator-task-component | **Critical** - Completely different card styling, breaks glassmorphism design |

### Specific File Issues

**`validator-task-component.blade.php`** (Line 1)
- **Issue:** Does not use `glass` class
- **Current:** `py-6 px-4 border border-stone-200 dark:border-slate-700 z-20 relative bg-stone-50 dark:bg-slate-800 dark:bg-slate-800 dark:border-slate-800 mt-4`
- **Expected:** `glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400`
- **Severity:** Critical

**`summary-alert-commponent.blade.php`** (Line 1)
- **Issue:** Missing `z-20 relative` and `dark:text-slate-400`
- **Current:** `glass rounded-sm p-5 mb-5`
- **Expected:** `glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400`
- **Severity:** Major

**`auditor-summary-component.blade.php`** (Line 1)
- **Issue:** Missing `z-20 relative` and `dark:text-slate-400`
- **Current:** `glass rounded-sm p-5 mb-5`
- **Expected:** `glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400`
- **Severity:** Major

**`auditor-alert-component.blade.php`** (Line 1)
- **Issue:** Inconsistent text styling applied to card container
- **Current:** `glass rounded-sm p-5 mb-5 text-label text-stone-600 dark:text-slate-400`
- **Expected:** `glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400`
- **Severity:** Minor

---

## 2. Table Inconsistencies

### Standard Pattern
```html
<table class="w-full border-collapse">
  <thead class="text-xs">
    <tr>
      <th class="text-left px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">...</th>
    </tr>
  </thead>
  <tbody class="text-sm">
    <tr>
      <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300">...</td>
    </tr>
  </tbody>
</table>
```

### Variations Found

| Pattern | Files | Severity |
|---------|-------|----------|
| `w-full border-collapse` (no borders) | table-analisis, users-component, auditor-database-component, analis-database-component | Standard |
| `w-full border-collapse` with `border-b` on thead and tbody rows | auditor-summary-component, check-alert-analis | **Major** - Inconsistent row borders |
| `w-full min-w-max border-collapse border-b border-stone-300 dark:border-slate-700 dark:border-slate-800` | validator-task-component | **Critical** - Duplicate dark:border classes |
| Custom sticky columns with different bg colors | auditor-summary-component, validator-task-component | **Major** - Inconsistent sticky column backgrounds |

### Specific File Issues

**`check-alert-analis.blade.php`** (Lines 20-30)
- **Issue:** Duplicate `dark:border-slate-800` classes alongside `dark:border-slate-700`
- **Example:** `class="cursor-pointer border-b border-stone-300 dark:border-slate-700 dark:border-slate-800 px-2 py-2 capitalize"`
- **Impact:** CSS specificity issues, potential visual bugs
- **Severity:** Critical
- **Count:** 11 instances of duplicate dark:border classes

**`validator-task-component.blade.php`** (Line 35)
- **Issue:** Duplicate `dark:bg-slate-800` and `dark:border-slate-800` on card container
- **Current:** `bg-stone-50 dark:bg-slate-800 dark:bg-slate-800 dark:border-slate-800`
- **Severity:** Critical

**`validator-task-component.blade.php`** (Lines 48-52)
- **Issue:** Table headers use inconsistent border colors
- **Current:** `border-r border-stone-300 dark:border-slate-700 border-l border-b border-t` and `dark:bg-slate-700 dark:bg-slate-600`
- **Expected:** Consistent `border-stone-200 dark:border-slate-700`
- **Severity:** Major

**`auditor-summary-component.blade.php`** (Lines 15, 35)
- **Issue:** Uses `border-b border-stone-300 dark:border-slate-700` on thead but `border-b border-stone-200 dark:border-slate-800` on tbody rows
- **Severity:** Major - Inconsistent border colors between head and body

**`validator-task-component.blade.php`** (Lines 65-75)
- **Issue:** Sticky columns use hardcoded background colors without glassmorphism
- **Current:** `bg-stone-100 dark:bg-slate-800` and `bg-[#a3c9af] dark:bg-[#3a5142]`
- **Expected:** Should match glassmorphism design or use consistent semantic colors
- **Severity:** Major

---

## 3. Button Inconsistencies

### Standard Primary Button Pattern
```html
<button class="bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 py-2 px-4 text-sm font-semibold rounded-sm cursor-pointer hover:bg-stone-800 dark:hover:bg-slate-300 transition-none">
```

### Variations Found

| Pattern | Files | Severity |
|---------|-------|----------|
| `bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900` with hover | change-password-component, edit-alert-component, auditing partial | Standard |
| `bg-black rounded-full` (no dark mode) | users.blade.php, alerts.blade.php | **Critical** - Missing dark mode support |
| `bg-stone-900 dark:bg-slate-200 rounded-full` | dashboard.blade.php | **Major** - Uses rounded-full instead of rounded-sm |
| `bg-red-600 text-white hover:bg-red-700` | deleterModal, deleterAlert | Standard for destructive actions |
| `bg-white dark:bg-slate-700 text-stone-700 dark:text-slate-200` | deleterModal, deleterAlert | Standard for secondary actions |

### Specific File Issues

**`users.blade.php`** (Lines 18-25)
- **Issue:** Floating action button uses `bg-black` without dark mode variant
- **Current:** `sm:px-4 px-2 sm:py-4 py-2 border border-white bg-black rounded-full bgrmi flex items-center justify-center`
- **Expected:** `w-12 h-12 bg-stone-900 dark:bg-slate-200 rounded-full flex items-center justify-center hover:bg-stone-800 dark:hover:bg-slate-300`
- **Severity:** Critical
- **Note:** Also uses custom `bgrmi` class

**`alerts.blade.php`** (Lines 17-24)
- **Issue:** Same as users.blade.php - missing dark mode
- **Current:** `sm:px-4 px-2 sm:py-4 py-2 border border-white bg-black rounded-full bgrmi flex items-center justify-center`
- **Expected:** Should match dashboard.blade.php pattern
- **Severity:** Critical

**`dashboard.blade.php`** (Lines 28-35)
- **Issue:** Uses `rounded-full` instead of `rounded-sm` for action button
- **Current:** `w-12 h-12 bg-stone-900 dark:bg-slate-200 rounded-full`
- **Note:** This is actually acceptable for FAB pattern, but inconsistent with other pages
- **Severity:** Minor

**`auditing.blade.php`** (Lines 120-130)
- **Issue:** Action buttons don't use consistent hover states
- **Current:** Primary button has no hover:bg class defined in some instances
- **Expected:** Consistent `hover:bg-stone-800 dark:hover:bg-slate-300`
- **Severity:** Minor

---

## 4. Form Input Inconsistencies

### Standard Input Pattern
```html
<input class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm focus:outline-none transition-none" />
```

### Variations Found

| Pattern | Files | Severity |
|---------|-------|----------|
| `bg-white dark:bg-slate-800` | change-password-component, analis-database-component (search input) | Standard |
| `bg-white dark:bg-slate-900` | auditor-database-component, auditor-task-component, check-alert-analis, table-analisis, add-alert-component | **Major** - Inconsistent dark background |
| `bg-stone-200 dark:bg-slate-700` (disabled) | edit-alert-component | Standard for disabled state |
| Select dropdowns: `bg-white dark:bg-slate-900` | Most select elements | **Minor** - Should match input bg |

### Specific File Issues

**`auditor-database-component.blade.php`** (Line 6)
- **Issue:** Input uses `dark:bg-slate-900` instead of `dark:bg-slate-800`
- **Current:** `bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-600`
- **Expected:** `bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600`
- **Severity:** Major

**`auditor-task-component.blade.php`** (Line 15)
- **Issue:** Input uses `dark:bg-slate-900`
- **Current:** `bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-600`
- **Expected:** `bg-white dark:bg-slate-800`
- **Severity:** Major

**`check-alert-analis.blade.php`** (Line 6)
- **Issue:** Input uses `dark:bg-slate-900`
- **Current:** `bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-600`
- **Expected:** `bg-white dark:bg-slate-800`
- **Severity:** Major

**`table-analisis.blade.php`** (Line 5)
- **Issue:** Input uses `dark:bg-slate-900`
- **Current:** `bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-600`
- **Expected:** `bg-white dark:bg-slate-800`
- **Severity:** Major

**`add-alert-component.blade.php`** (Lines 25, 45)
- **Issue:** Autocomplete dropdown inputs use `dark:bg-slate-900`
- **Current:** `bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-600`
- **Expected:** `bg-white dark:bg-slate-800`
- **Severity:** Major

**`analis-database-component.blade.php`** (Line 7)
- **Issue:** Select dropdowns use `dark:bg-slate-900` while search input uses `dark:bg-slate-800`
- **Current:** Input: `dark:bg-slate-800`, Select: `dark:bg-slate-900`
- **Expected:** Both should use `dark:bg-slate-800`
- **Severity:** Major

---

## 5. Status Badge Inconsistencies

### Two Competing Systems

**System A: Custom Colors (No Dark Mode)**
```html
<!-- table-analisis.blade.php, alert-test-component.blade.php, users-component.blade.php -->
<a class="rounded-sm px-2 py-1 bg-green-alerta text-white">approved</a>
<a class="rounded-sm px-2 py-1 bg-merah-alerta text-white">rejected</a>
<a class="rounded-sm px-2 py-1 bg-yellow-alerta text-white">pending</a>
<a class="rounded-sm px-2 py-1 bg-stone-300 text-stone-700 dark:bg-slate-700 dark:text-slate-300">Pending</a>
```

**System B: Tailwind Semantic Colors (With Dark Mode)**
```html
<!-- auditor-database-component.blade.php, analis-database-component.blade.php -->
<span class="bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-700">approved</span>
<span class="bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-700">rejected</span>
<span class="bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 border border-stone-300 dark:border-stone-600">pending</span>
```

### Specific File Issues

**`table-analisis.blade.php`** (Lines 85-100)
- **Issue:** Uses custom colors without dark mode
- **Current:** `bg-green-alerta text-white`, `bg-merah-alerta text-white`, `bg-yellow-alerta text-white`, `bg-stone-300 text-stone-700 dark:bg-slate-700 dark:text-slate-300`
- **Expected:** Should use Tailwind semantic colors with dark mode
- **Severity:** Critical
- **Note:** Only "Pending" has dark mode, others don't

**`alert-test-component.blade.php`** (Lines 75-95)
- **Issue:** Uses custom colors and hardcoded hex colors
- **Current:** `bg-green-alerta`, `bg-merah-alerta`, `bg-yellow-alerta`, `bg-black`, `bg-[#87bed3]`, `bg-blue-100`
- **Expected:** Consistent semantic color system
- **Severity:** Critical

**`users-component.blade.php`** (Lines 65-75)
- **Issue:** Uses `bg-stone-500 text-white dark:bg-slate-600 dark:text-slate-200` for role badges
- **Current:** Custom styling without semantic meaning
- **Expected:** Should use consistent badge system
- **Severity:** Major

**`auditor-database-component.blade.php`** (Lines 95-115)
- **Issue:** Actually uses correct Tailwind semantic colors
- **Pattern:** `bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-700`
- **Note:** This is the correct pattern that should be adopted everywhere
- **Severity:** N/A (Correct implementation)

---

## 6. Loading State Inconsistencies

### Variations Found

| Pattern | Files | Severity |
|---------|-------|----------|
| `bg-red-400 dark:bg-red-900 py-2 animate-pulse text-xs px-4 text-white rounded-sm` | auditor-database-component, analis-database-component, alert-analis-component | Standard |
| `border border-stone-300 dark:border-slate-600 px-3 py-2 text-stone-500 dark:text-slate-400 rounded-sm` | alert-test-component | **Major** - Different visual treatment |
| Inline spinner button | users-component | Standard |

### Specific File Issues

**`alert-test-component.blade.php`** (Lines 15-20)
- **Issue:** Loading state uses border style instead of background
- **Current:** `border border-stone-300 dark:border-slate-600 px-3 py-2 text-stone-500 dark:text-slate-400 rounded-sm`
- **Expected:** `bg-red-400 dark:bg-red-900 py-2 animate-pulse text-xs px-4 text-white rounded-sm`
- **Severity:** Major

---

## 7. Layout Structure Inconsistencies

### Standard Page Pattern
```html
<div class="max-w-7xl mx-auto px-6 py-6">
  <!-- content -->
</div>
```

### Variations Found

| Pattern | Files | Severity |
|---------|-------|----------|
| `max-w-7xl mx-auto px-6 py-6` | dashboard, alerts, users, addalert, adduser, editalert, edituser | Standard |
| `<main class="h-screen sm:mt-28 mt-4"><div class="max-w-7xl px-6 mx-auto mt-4">` | settings.blade.php | **Minor** - Different wrapper structure |
| `max-w-3xl mx-auto` | edit-alert-component, edit-user-component | Minor - Narrower container for forms |

### Specific File Issues

**`settings.blade.php`** (Lines 6-18)
- **Issue:** Uses `<main>` wrapper with `h-screen` and different margin structure
- **Current:** `<main class="h-screen sm:mt-28 mt-4"><div class="max-w-7xl px-6 mx-auto mt-4">`
- **Expected:** `<div class="max-w-7xl mx-auto px-6 py-6">`
- **Severity:** Minor

---

## 8. Pagination Component Issues

**`pagination.blade.php`** (Lines 15-25)
- **Issue:** Multiple duplicate classes
- **Current:** 
  - `dark:border-slate-700 dark:border-slate-700` (duplicate)
  - `bg-white dark:bg-slate-900 bg-white dark:bg-slate-900` (duplicate bg)
  - `text-stone-700 dark:text-slate-300 dark:text-slate-400` (conflicting text colors)
  - `active:bg-stone-100 dark:bg-slate-800` (missing active:text)
- **Severity:** Critical
- **Count:** 8+ instances of duplicate/conflicting classes

---

## 9. Dark Mode Support Gaps

### Components Missing Dark Mode

| Component | Missing Dark Classes | Severity |
|-----------|---------------------|----------|
| `welcome.blade.php` | Has its own dark mode (separate issue) | Minor |
| `alert-test-component.blade.php` | Status badges use custom colors without dark variants | Critical |
| `table-analisis.blade.php` | Most status badges missing dark mode | Critical |
| `users-component.blade.php` | Role badges could use better dark mode | Major |
| `validator-task-component.blade.php` | Hardcoded hex colors with dark variants | Major |

### Components with Good Dark Mode
- `auditor-database-component.blade.php` - Excellent implementation
- `analis-database-component.blade.php` - Good implementation
- `change-password-component.blade.php` - Good implementation
- `deleterModal.blade.php` / `deleterAlert.blade.php` - Good implementation

---

## 10. Custom Color Classes Without Dark Mode

The following custom Tailwind classes are used but don't have dark mode variants:

- `bg-green-alerta` / `bg-green-alerta-table-full`
- `bg-merah-alerta` / `bg-merah-alerta-table-full`
- `bg-yellow-alerta` / `bg-yellow-alerta-table-full`
- `bg-refined-alerta-table-full`
- `bg-[#87bed3]` (hardcoded light blue)
- `bg-[#a3c9af]` / `dark:bg-[#3a5142]` (validator task)
- `bg-[#bfcec3]` / `dark:bg-[#617c6a]` (validator task)

**Recommendation:** Replace all custom colors with Tailwind semantic colors that have built-in dark mode support.

---

## 11. Typography Inconsistencies

### Label Text Patterns

| Pattern | Usage | Severity |
|---------|-------|----------|
| `text-label text-stone-600 dark:text-slate-400` | Most labels | Standard |
| `text-label text-stone-500 dark:text-slate-400` | Some table headers, filter labels | Minor |
| `text-label text-stone-900 dark:text-slate-100` | Card titles | Standard |
| `text-sm` | Some descriptions | Minor |

### Heading Inconsistencies

**`auditor-database-component.blade.php`** (Line 2)
- Uses `<h1>` for card title

**`analis-database-component.blade.php`** (Line 2)
- Uses `<a>` for card title

**`table-analisis.blade.php`** (Line 2)
- Uses `<a>` for card title

**Recommendation:** Standardize on `<h1 class="text-label text-stone-900 dark:text-slate-100">` for all card titles.

---

## 12. Spacing and Sizing Inconsistencies

### Card Padding
- Most: `p-5`
- Some: `px-6 py-8` (modals)

### Table Cell Padding
- Most headers: `px-3 py-2.5`
- Most cells: `px-3 py-2.5`
- **Exception:** `table-analisis.blade.php` status cell uses `px-6 py-4` (Line 87)
- **Exception:** `auditor-database-component.blade.php` status cell uses `px-6 py-2` (Line 97)

### Button Padding
- Primary: `py-2 px-4`
- Delete: `px-6 py-2.5`
- Modal actions: `px-4 py-2`

**Recommendation:** Standardize button padding to `py-2 px-4` for all buttons.

---

## Priority Recommendations

### Immediate Action (Critical)
1. **Fix duplicate CSS classes** in `pagination.blade.php` and `validator-task-component.blade.php`
2. **Add dark mode support** to floating action buttons in `users.blade.php` and `alerts.blade.php`
3. **Replace custom color badges** with Tailwind semantic colors in `table-analisis.blade.php` and `alert-test-component.blade.php`
4. **Apply glass class** to `validator-task-component.blade.php`

### Short Term (Major)
5. **Standardize form input dark backgrounds** - All should use `dark:bg-slate-800`
6. **Standardize table border styles** - Use consistent `border-b border-stone-200 dark:border-slate-700`
7. **Add missing glass card attributes** to `summary-alert-commponent.blade.php` and `auditor-summary-component.blade.php`
8. **Standardize loading state styling** across all components
9. **Fix status badge inconsistencies** - Adopt Tailwind semantic color system everywhere

### Long Term (Minor)
10. **Standardize typography** - Use consistent heading elements and label colors
11. **Standardize button padding** and hover states
12. **Review and remove unused custom classes** like `bgrmi`, `rounded-sm-xs`
13. **Standardize page wrapper structure**

---

## Files Requiring Updates

### Critical Priority
- `resources/views/livewire/pagination.blade.php`
- `resources/views/livewire/validator-task-component.blade.php`
- `resources/views/users.blade.php`
- `resources/views/alerts.blade.php`
- `resources/views/livewire/table-analisis.blade.php`
- `resources/views/livewire/alert-test-component.blade.php`

### Major Priority
- `resources/views/livewire/auditor-database-component.blade.php` (input dark bg)
- `resources/views/livewire/auditor-task-component.blade.php` (input dark bg)
- `resources/views/livewire/check-alert-analis.blade.php` (input dark bg, duplicate classes)
- `resources/views/livewire/add-alert-component.blade.php` (input dark bg)
- `resources/views/livewire/analis-database-component.blade.php` (select/input mismatch)
- `resources/views/livewire/summary-alert-commponent.blade.php` (missing glass attributes)
- `resources/views/livewire/auditor-summary-component.blade.php` (missing glass attributes, table borders)
- `resources/views/livewire/users-component.blade.php` (role badges)
- `resources/views/livewire/alert-analis-component.blade.php` (loading state)

### Minor Priority
- `resources/views/livewire/auditor-alert-component.blade.php`
- `resources/views/livewire/check-approved-component.blade.php`
- `resources/views/livewire/check-user-alert-audit.blade.php`
- `resources/views/settings.blade.php`
- `resources/views/dashboard.blade.php`
- `resources/views/livewire/edit-alert-component.blade.php`
- `resources/views/partials/auditing.blade.php`

---

## Appendix: Recommended Standard Patterns

### Standard Glass Card
```html
<div class="glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400">
  <h1 class="text-label text-stone-900 dark:text-slate-100">Title</h1>
  <!-- content -->
</div>
```

### Standard Table
```html
<table class="w-full border-collapse">
  <thead class="text-xs">
    <tr>
      <th class="text-left px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Column</th>
    </tr>
  </thead>
  <tbody class="text-sm">
    <tr>
      <td class="px-3 py-2.5 text-stone-700 dark:text-slate-300">Value</td>
    </tr>
  </tbody>
</table>
```

### Standard Input
```html
<input class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm focus:outline-none transition-none" />
```

### Standard Primary Button
```html
<button class="bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 py-2 px-4 text-sm font-semibold rounded-sm cursor-pointer hover:bg-stone-800 dark:hover:bg-slate-300 transition-none">
  Action
</button>
```

### Standard Status Badge
```html
<!-- Success -->
<span class="inline-flex items-center justify-center text-center min-w-[7rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-700 px-3 py-1.5">
  Approved
</span>

<!-- Danger -->
<span class="inline-flex items-center justify-center text-center min-w-[7rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-700 px-3 py-1.5">
  Rejected
</span>

<!-- Warning -->
<span class="inline-flex items-center justify-center text-center min-w-[7rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-700 px-3 py-1.5">
  Pending
</span>

<!-- Neutral -->
<span class="inline-flex items-center justify-center text-center min-w-[7rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 border border-stone-300 dark:border-stone-600 px-3 py-1.5">
  Draft
</span>
```

### Standard Loading State
```html
<div wire:loading class="flex w-full justify-center text-center bg-red-400 dark:bg-red-900 py-2 animate-pulse text-xs px-4 text-white rounded-sm">
  Loading...
</div>
```

---

*Report generated by systematic audit of 46 blade files across resources/views/ directory.*
