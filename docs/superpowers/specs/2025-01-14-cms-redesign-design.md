# CMS UI/UX Redesign - Design Specification

**Date:** 2025-01-14
**Project:** Alerta CMS Dashboard Redesign
**Scope:** Login page, Dashboard, and all subsequent pages

---

## Design Philosophy

- **Glassmorphism with restraint** - Apply frosted glass effect only to key elements (stats cards, table containers, primary cards). Use solid backgrounds for buttons, inputs, and secondary elements.
- **Sharp & intentional** - 2px border radius maximum. No excessive rounding.
- **No animations** - Instant rendering, no fade-ins, transitions, or hover animations.
- **Human-crafted feel** - Tight spacing (10-16px), bold typography with tight letter-spacing, asymmetric layouts, connected elements.
- **Professional density** - Information-dense without clutter.

---

## Color Palette

### Primary Colors (Preserve existing)
- **Green accent:** `#86B897` - Brand color, keep as-is
- **Dark text:** `#1c1917` - Near-black for headings
- **Body text:** `#44403c` - Warm gray for content
- **Muted text:** `#78716c` - Labels, secondary info
- **Borders:** `#d6d3d1` - Subtle warm gray

### Status Colors (Preserve existing conditional logic)
- **Approved:** `#166534` (green)
- **Pending/Re-export:** `#a16207` (amber/yellow)
- **Rejected:** `#991b1b` (red)
- **Pre-approved:** Gray tones
- **Refined:** Blue tones (existing `#87BED3`)

### Background
- **Page:** Dot pattern at 25% opacity (preserve existing)
- **Light mode:** `#fafaf9` warm off-white
- **Dark mode:** `#0f172a` slate-900 (existing)

### Glassmorphism
- **Light glass:** `rgba(255,255,255,0.6)` with `backdrop-filter: blur(6px)`
- **Dark glass:** `rgba(28,25,23,0.8)` with `backdrop-filter: blur(6px)`
- **Borders:** `1px solid rgba(0,0,0,0.05-0.06)`

---

## Layout System

### Navigation
- **Top navigation** (improved from current)
- Horizontal nav bar below header
- Clean links with active state indicator (bottom border)
- No background color on nav - sits on dot pattern

### Header
- Logo left, controls right
- Theme toggle button (fix flickering issue)
- Profile dropdown
- No glass effect on header - solid or transparent

### Page Structure
```
[Header]
[Navigation Bar]
[Content Area - max-width with padding]
  [Stats/Glass Cards]
  [Main Content + Side Panel]
[Floating Action Button - bottom right]
[Logo - bottom left fixed]
```

### Spacing
- Section gaps: 20-24px
- Element gaps: 10-16px
- Card padding: 16-20px
- Table cell padding: 10px 14px

---

## Typography

- **Page titles:** 28px, weight 800, letter-spacing -0.5px
- **Card titles:** 11px, weight 700, uppercase, letter-spacing 1.2px
- **Stats numbers:** 34px, weight 800, letter-spacing -1.5px
- **Table headers:** 10px, weight 600-700, uppercase, letter-spacing 0.5px
- **Body text:** 12px, weight 400-600
- **Labels:** 10-11px, weight 600, uppercase, letter-spacing 1px+

---

## Component Specifications

### Stats Cards
- Glass effect: `bg-white/60 backdrop-blur-md border border-black/5`
- Sharp corners: `rounded-sm` (2px)
- No shadows - glass effect provides depth
- Connected layout with 2px gaps
- Dark inverted card for "Total" to create focus

### Tables
- Glass container wrapping the table
- Real borders: `border-b border-black/5-8`
- Alternating row backgrounds for status colors (at 0.7 opacity)
- Sticky first/last columns preserved
- Bold status labels with color coding

### Buttons
- Primary: Solid dark `#1c1917`, white text, 2px radius
- Secondary: White background, dark border
- No glass on buttons - must be clickable and solid

### Inputs
- White background, subtle border
- Focus: Border color change, no glow
- 2px radius

### Floating Action Button (Add Alert)
- Keep existing position (bottom right)
- Style: Dark background, white icon, 2px radius or full round

---

## Theme System

### Light Mode
- Background: `#fafaf9` with dot pattern
- Cards: Glass effect with white tint
- Text: Dark warm tones
- Borders: Warm gray

### Dark Mode
- Background: `#0f172a` with dot pattern (existing)
- Cards: Dark glass `rgba(15, 23, 42, 0.75)` with `backdrop-filter: blur(8px)`
- Text: `#e2e8f0` (slate-200) for body, `#f8fafc` (slate-50) for headings
- Borders: `rgba(255,255,255,0.08)` subtle white borders
- Table headers: `rgba(30, 41, 59, 0.9)` semi-transparent slate
- Status row backgrounds in dark (reduced opacity):
  - Approved: `rgba(22, 101, 52, 0.25)`
  - Pending: `rgba(161, 98, 7, 0.2)`
  - Rejected: `rgba(153, 27, 27, 0.2)`
- Glass fallback: `rgba(15, 23, 42, 0.9)` for unsupported browsers

### Theme Toggle Fix
- **CRITICAL:** Remove `watchSystemTheme()` auto-override
- User preference should persist in localStorage
- Only apply system theme on FIRST visit (no stored preference)
- Manual toggle should always override system

---

## Pages to Redesign

### Phase 1 (Core)
1. **Login Page** - Centered card, clean form, no animations
2. **Dashboard** - Stats cards, regional table, auditor activity panel

### Phase 2 (Subsequent)
3. **Alerts Management** - List view, filters, action buttons
4. **Auditor Panel** - Review interface, approve/reject actions
5. **Users Management** - Table with roles, admin only
6. **Settings** - Profile, password, preferences

---

## Animation Policy

**NO ANIMATIONS.** 
- No fade-ins on page load
- No transition effects
- No hover animations
- No loading spinners (or minimal)
- Instant state changes
- Snappy, responsive feel

---

## Accessibility

- WCAG 4.5:1 contrast ratio minimum
- Glass panels must have sufficient opacity for text readability
- Focus states visible and clear
- Keyboard navigation preserved

---

## Technical Notes

- Tailwind CSS v4 with custom config
- Livewire components - preserve all existing logic
- Glass utility classes with dark mode support:
  ```css
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
  ```
- Feature detection fallback:
  ```css
  @supports not (backdrop-filter: blur(6px)) {
    .glass { background: rgba(255, 255, 255, 0.9); }
    .dark .glass { background: rgba(15, 23, 42, 0.95); }
  }
  ```
- Preserve all existing PHP/Livewire functionality
- Preserve role-based conditional rendering
- Preserve all existing color conditional classes

---

## Files to Modify

### Global
- `resources/css/app.css` - Add glass utility classes, update colors
- `resources/js/theme.js` - Fix flickering bug
- `resources/views/layouts/dashboard.blade.php` - Layout structure

### Shared Components
- `resources/views/partials/header.blade.php` - Header redesign
- `resources/views/partials/nav.blade.php` - Navigation redesign

### Pages
- `resources/views/login.blade.php` - Login page
- `resources/views/dashboard.blade.php` - Dashboard layout
- `resources/views/livewire/*` - All Livewire components

---

## Success Criteria

- [ ] No visible animations or transitions
- [ ] Theme toggle works without flickering
- [ ] Glass effect visible but not overdone
- [ ] Dot pattern visible through glass panels
- [ ] Logo visible bottom left
- [ ] All role-based content preserved
- [ ] Tables are readable with clear status colors
- [ ] Professional, non-template appearance
- [ ] Dark mode works correctly
- [ ] Mobile responsive