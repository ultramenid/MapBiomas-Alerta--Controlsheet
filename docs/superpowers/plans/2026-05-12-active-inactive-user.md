# Active/Inactive User Management Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add visual status display in users list and status management in edit user form.

**Architecture:** Extend existing Livewire components to read/write `is_active` field. Status displayed as color-coded badges in list, managed via dropdown in edit form.

**Tech Stack:** Laravel, Livewire, Blade, Tailwind CSS, Alpine.js

---

## File Structure

| File | Responsibility |
|------|---------------|
| `app/Livewire/UsersComponent.php` | Query users including `is_active` field |
| `resources/views/livewire/users-component.blade.php` | Display status badge in table |
| `app/Livewire/EditUserComponent.php` | Load and save `is_active` property |
| `resources/views/livewire/edit-user-component.blade.php` | Status dropdown UI |

---

### Task 1: Include is_active in Users Query

**Files:**
- Modify: `app/Livewire/UsersComponent.php:48`

- [ ] **Step 1: Add is_active to select statement**

```php
public function getDatabase(){
      $sc = '%' . $this->search . '%';
      try {
        return  DB::table('users')
                    ->select('name', 'email','contact', 'role_id', 'id', 'is_active')
                    ->where('name', 'like', $sc)
                    ->orderBy($this->dataField, $this->dataOrder)
                    ->paginate($this->paginate);
    } catch (\Throwable $th) {
        return [];
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Livewire/UsersComponent.php
git commit -m "feat: include is_active in users query"
```

---

### Task 2: Add Status Column to Users List

**Files:**
- Modify: `resources/views/livewire/users-component.blade.php:40-50`

- [ ] **Step 1: Add Status table header**

Add after the Level column header (line 45), before the empty actions header (line 47):

```blade
                     <th  class="text-left px-3 py-2 text-label text-stone-500 dark:text-slate-400">
                        <div class=" space-x-1 " >
                            <a >Status</a>
                         </div>
                     </th>
```

- [ ] **Step 2: Add Status badge to table rows**

Add after the Level cell (line 75), before the actions cell (line 77):

```blade
                     <td class="px-3 py-2 text-stone-700 dark:text-slate-300">
                         @if ($item->is_active == 1)
                             <span class="inline-flex items-center justify-center text-center w-[10rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 border border-green-300 dark:border-green-600 px-3 py-1.5">Active</span>
                         @else
                             <span class="inline-flex items-center justify-center text-center w-[10rem] appearance-none rounded-sm text-xs font-semibold uppercase tracking-wider bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 border border-red-300 dark:border-red-600 px-3 py-1.5">Inactive</span>
                         @endif
                     </td>
```

- [ ] **Step 3: Update colspan for empty state**

Change line 101 from `colspan="5"` to `colspan="6"`:

```blade
                     <td colspan="6" class="px-3 py-2 text-stone-500 dark:text-slate-400">
```

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/users-component.blade.php
git commit -m "feat: add status column to users list"
```

---

### Task 3: Add is_active Property to EditUserComponent

**Files:**
- Modify: `app/Livewire/EditUserComponent.php`

- [ ] **Step 1: Add is_active property**

Add `$is_active` to the properties declaration (line 13):

```php
    public $email, $name, $password, $contact, $level, $idUser, $is_active;
```

- [ ] **Step 2: Load is_active in mount()**

Add after line 23 in `mount()` method:

```php
        $this->is_active = $data->is_active;
```

- [ ] **Step 3: Save is_active in storeUser() - no password**

Add `'is_active' => $this->is_active,` to the first update block (after line 39):

```php
                DB::table('users')
                ->where('id', $this->idUser)
                ->update([
                    'name' => $this->name,
                    'email' => $this->email,
                    'contact' => $this->contact,
                    'role_id' => $this->level,
                    'is_active' => $this->is_active,
                    'updated_at' => Carbon::now('Asia/Jakarta')
            ]);
```

- [ ] **Step 4: Save is_active in storeUser() - with password**

Add `'is_active' => $this->is_active,` to the second update block (after line 50):

```php
                DB::table('users')
                ->where('id', $this->idUser)
                ->update([
                    'name' => $this->name,
                    'email' => $this->email,
                    'contact' => $this->contact,
                    'password' => Hash::make($this->password),
                    'role_id' => $this->level,
                    'is_active' => $this->is_active,
                    'updated_at' => Carbon::now('Asia/Jakarta')
            ]);
```

- [ ] **Step 5: Commit**

```bash
git add app/Livewire/EditUserComponent.php
git commit -m "feat: add is_active management to edit user component"
```

---

### Task 4: Add Status Dropdown to Edit User Form

**Files:**
- Modify: `resources/views/livewire/edit-user-component.blade.php`

- [ ] **Step 1: Add Status field below Level dropdown**

Replace lines 32-50 (the Level section) with a two-column layout that includes Status:

```blade
    <div class="mt-6 flex sm:flex-row flex-col gap-4">
        <div class="sm:w-6/12 w-full">
            <label class="w-full"  >
                <div class="relative flex w-full flex-col  text-neutral-600 dark:text-neutral-300">
                    <label for="os" class="w-fit pl-0.5 text-stone-700 dark:text-slate-300 mb-1">Level</label>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-4 top-9 size-5">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                    <select wire:ignore wire:model='level' class="w-full appearance-none bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 px-3 py-2 text-sm rounded-sm focus:outline-none cursor-pointer transition-none">
                        <option selected>Please Select</option>
                        <option value="2">Validator</option>
                        <option value="1">Auditor</option>
                        <option value="0">Admin</option>

                    </select>
                </div>
            </label>
        </div>
        <div class="sm:w-6/12 w-full">
            <label class="w-full"  >
                <div class="relative flex w-full flex-col  text-neutral-600 dark:text-neutral-300">
                    <label for="status" class="w-fit pl-0.5 text-stone-700 dark:text-slate-300 mb-1">Status</label>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-4 top-9 size-5">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                    <select wire:model='is_active' class="w-full appearance-none bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 px-3 py-2 text-sm rounded-sm focus:outline-none cursor-pointer transition-none">
                        <option value="1" @if($is_active == 1) selected @endif>Active</option>
                        <option value="0" @if($is_active == 0) selected @endif>Inactive</option>
                    </select>
                </div>
            </label>
        </div>
    </div>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/livewire/edit-user-component.blade.php
git commit -m "feat: add status dropdown to edit user form"
```

---

## Spec Coverage Check

| Requirement | Task |
|-------------|------|
| Users list shows status | Task 2 |
| Edit user form includes status selector | Task 4 |
| Admin can change user status | Tasks 3-4 |
| Non-active users blocked from login | Already implemented |

## Placeholder Scan

- [x] No TBD, TODO, or placeholders
- [x] All code is complete and copy-pasteable
- [x] All file paths are exact
- [x] All line numbers reference current file state
- [x] No vague instructions

## Type Consistency Check

- `is_active` used consistently as string property in component
- `is_active` compared with `== 1` or `== 0` in Blade (matches database string type)
- Wire model binding uses string values "1" and "0" in select options
