# Active/Inactive User Management Design

## Date: 2026-05-12
## Status: Approved

---

## Overview

Add visual indication and management of user active status in the CMS. Non-active users are blocked from logging in.

## Background

The `is_active` column already exists on the `users` table (added via migration `2025_11_24_110648_users_active.php`). Login blocking is already implemented in `LoginComponent.php`. The remaining work is to:
1. Display user status in the users list
2. Allow admins to toggle status in the edit user form

## Requirements

1. Users list must show each user's active/inactive status
2. Edit user form must include a status selector
3. Only admin users (role_id = 0) can see and manage user status
4. Non-active users must be blocked from logging in (already implemented)

## Architecture

### Data Model

Existing `users` table schema:
- `is_active` (string, default "1") — "1" = active, "0" = inactive

### Components

| Component | File | Change |
|-----------|------|--------|
| Users List View | `resources/views/livewire/users-component.blade.php` | Add Status column with color-coded badge |
| Edit User View | `resources/views/livewire/edit-user-component.blade.php` | Add Status dropdown |
| Edit User Component | `app/Livewire/EditUserComponent.php` | Add `is_active` property, load/save it |
| Users List Component | `app/Livewire/UsersComponent.php` | Include `is_active` in query select |

### Data Flow

**Load:**
1. `UsersComponent::getDatabase()` selects `is_active` field
2. `EditUserComponent::mount()` loads `is_active` into component property

**Save:**
1. Admin changes status in edit form
2. `EditUserComponent::storeUser()` updates `is_active` in database
3. Redirect back to users list

### UI Design

#### Users List
- New "Status" column between "Level" and actions
- Active: green badge with text "Active"
- Inactive: red badge with text "Inactive"
- Read-only display only

#### Edit User Form
- Status field below Level dropdown
- Dropdown options: Active (1) / Inactive (0)
- Label: "Status"

### Error Handling

- No additional error states needed
- Standard validation for required fields applies

### Security

- Only admin users (role_id = 0) can edit user status
- Existing role-based access controls apply
- Non-active users are blocked at login (already implemented)

## Dependencies

- `2025_11_24_110648_users_active.php` migration must have run
- Existing `LoginComponent` login logic

## Out of Scope

- Toggle from users list (one-click deactivation)
- Filter users by status
- Bulk status changes
- Soft delete integration

## Testing Notes

- Verify active user can log in normally
- Verify inactive user is blocked at login
- Verify admin can change user status
- Verify status displays correctly in list
