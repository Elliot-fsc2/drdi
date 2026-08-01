# Notification System Implementation Plan

**Project**: DRDI NCST Research Portal (Thesis/Research Management System)  
**Roles**: Students, Instructors, RDO/Staff, Admins  
**Stack**: Laravel 12, Filament v5, Livewire v4, Tailwind CSS v4

---

## Overview

Implement a comprehensive in-app notification system for all significant actions across the platform. The infrastructure is already in place — `notifications` table exists, Filament's `->databaseNotifications()` is enabled, `@livewire('notifications')` is present in all layouts, and 4 in-app notification classes already exist (proposal approve/reject, research approve/reject).

**Goal**: Every meaningful action in the system triggers a real-time database notification to the appropriate recipients.

---

## 1. Architecture

| Layer | Approach |
|-------|----------|
| **Model** | A `NotificationType` enum to centralize notification type keys |
| **Storage** | Laravel's existing `notifications` table (UUID PK, polymorphic notifiable) |
| **Channels** | `['database']` for in-app notifications; `['mail']` conditionally added based on user's `notify_email` preference |
| **Queuing** | All notifications implement `ShouldQueue` with `Queueable` trait — maintain this pattern |
| **Email Preference** | `notify_email` boolean column on `users` table (default `true`); toggled via Settings page |
| **Frontend (Admin)** | Filament's built-in notification dropdown (already enabled) — no changes needed |
| **Frontend (Non-admin)** | Custom Livewire `NotificationDropdown` component with Alpine.js dropdown |
| **Real-time** | Livewire polling (`wire:poll.10s`) for auto-refresh of unread count |
| **Deep linking** | Each notification carries an `action_url` pointing to the relevant page |

---

## 2. Notification Data Structure (Standardized Format)

Every notification class must return this consistent structure from `toDatabase()`:

```php
[
    'type'        => 'group_created',        // machine-readable key (from NotificationType enum)
    'title'       => 'New Group Created',     // short display title
    'message'     => 'You have been added to Juan Dela Cruz\'s Group.',  // body text
    'action_url'  => '/student/group-detail', // deep link to relevant page
    'action_text' => 'View Group',            // CTA button text
    'actor_id'    => 1,                       // user ID who triggered the action
    'actor_name'  => 'Dr. Smith',             // display name of the actor
    'icon'        => 'heroicon-o-user-group', // icon identifier for UI
    'color'       => 'primary',               // badge/theme color
]
```

Existing notification classes (`ApproveProposal`, `RejectProposal`, etc.) should be updated to conform to this format.

---

## 3. Email Notification Preference

Users can control whether they receive email copies of notifications via the **Account Settings** page.

### 3.1 Database

A `notify_email` boolean column (default `true`) was added to the `users` table via migration `2026_07_28_130455_add_notify_email_to_users_table`.

The `User` model includes `notify_email` in `$fillable` and casts it to `boolean`.

### 3.2 Toggle in Settings

The Account Settings page (`resources/views/pages/⚡account-settings.blade.php`) has a "Notifications" tab with an email toggle. The Livewire component calls `UserSettings::toggleEmailNotifications()`, which flips the flag and logs the change via Spatie Activity Log.

### 3.3 `HasEmailPreference` Trait

**File**: `app/Traits/HasEmailPreference.php`

A reusable trait that notification classes use to conditionally include the `mail` channel:

```php
trait HasEmailPreference
{
    public function viaWithEmail(object $notifiable): array
    {
        return $notifiable->notify_email
            ? ['database', 'mail']
            : ['database'];
    }
}
```

Usage in any notification class:

```php
use App\Traits\HasEmailPreference;

class ApproveProposal extends Notification implements ShouldQueue
{
    use HasEmailPreference, Queueable;

    public function via(object $notifiable): array
    {
        return $this->viaWithEmail($notifiable);
    }
}
```

When `notify_email` is `true`, the notification is queued for both `database` and `mail` channels. When `false`, only the `database` channel is used. Each notification class must also define a `toMail()` method.

### 3.4 `UserSettings` Service Update

**File**: `app/Service/UserSettings.php`

Added `toggleEmailNotifications($userId): bool` — toggles the `notify_email` flag for the given user and returns the new value.

---

## 5. Notification Type Enum

Create `app/Enums/NotificationType.php`:

```php
<?php

namespace App\Enums;

enum NotificationType: string
{
    case GROUP_CREATED = 'group_created';
    case GROUP_UPDATED = 'group_updated';
    case GROUP_DELETED = 'group_deleted';
    case MEMBER_ADDED = 'member_added';
    case MEMBER_REMOVED = 'member_removed';
    case PERSONNEL_ASSIGNED = 'personnel_assigned';
    case PROPOSAL_SUBMITTED = 'proposal_submitted';
    case PROPOSAL_APPROVED = 'proposal_approved';
    case PROPOSAL_REJECTED = 'proposal_rejected';
    case SCHEDULE_CREATED = 'schedule_created';
    case SCHEDULE_UPDATED = 'schedule_updated';
    case SCHEDULE_RESULT = 'schedule_result';
    case CONSULTATION_BOOKED = 'consultation_booked';
    case CONSULTATION_UPDATED = 'consultation_updated';
    case RESEARCH_SUBMITTED = 'research_submitted';
    case RESEARCH_APPROVED = 'research_approved';
    case RESEARCH_REJECTED = 'research_rejected';
    case RESEARCH_PUBLISHED = 'research_published';
    case FEE_LEDGER_INITIALIZED = 'fee_ledger_initialized';
    case FEE_UPDATED = 'fee_updated';
    case NEW_ANNOUNCEMENT = 'new_announcement';
    case SEMESTER_RATE_UPDATED = 'semester_rate_updated';
    case GROUP_STATUS_CHANGED = 'group_status_changed';
    case WELCOME = 'welcome';
}
```

---

## 6. Centralized Notification Dispatcher

Create `app/Services/NotificationService.php` to reduce duplication across service classes:

```php
<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class NotificationService
{
    /** Send to a single user */
    public function send(User $user, Notification $notification): void
    {
        $user->notify($notification);
    }

    /** Send to multiple users */
    public function sendMany(Collection $users, Notification $notification): void
    {
        \Illuminate\Support\Facades\Notification::send($users, $notification);
    }

    /** Send to all student-members of a group */
    public function sendToGroupMembers(Group $group, Notification $notification): void
    {
        $users = User::query()
            ->where('profileable_type', Student::class)
            ->whereIn('profileable_id', $group->members()->pluck('students.id'))
            ->get();

        $this->sendMany($users, $notification);
    }

    /** Send to group members + the section instructor/adviser */
    public function sendToGroupMembersAndAdviser(Group $group, Notification $notification): void
    {
        $studentUsers = User::query()
            ->where('profileable_type', Student::class)
            ->whereIn('profileable_id', $group->members()->pluck('students.id'))
            ->get();

        $adviserUser = $group->section?->instructor?->user;

        $all = $studentUsers;
        if ($adviserUser) {
            $all->push($adviserUser);
        }

        $this->sendMany($all, $notification);
    }

    /** Send to all RDO/Staff users */
    public function sendToRdo(Notification $notification): void
    {
        $users = User::query()
            ->where('profileable_type', Instructor::class)
            ->whereHas('profileable', fn ($q) => $q->whereIn('role', ['RDO', 'Staff']))
            ->get();

        $this->sendMany($users, $notification);
    }

    /** Send to a group's section instructor */
    public function sendToGroupAdviser(Group $group, Notification $notification): void
    {
        $adviserUser = $group->section?->instructor?->user;
        if ($adviserUser) {
            $this->send($adviserUser, $notification);
        }
    }
}
```

---

## 7. Notification Classes — Complete List

### 7.1 Already Exist (updated with `HasEmailPreference` + `toMail()`)

| # | Class | File | Channels |
|---|-------|------|----------|
| 1 | `ApproveProposal` | `app/Notifications/ApproveProposal.php` | `database` + `mail` (opt-in) |
| 2 | `RejectProposal` | `app/Notifications/RejectProposal.php` | `database` + `mail` (opt-in) |
| 3 | `ApproveResearchLibrary` | `app/Notifications/ApproveResearchLibrary.php` | `database` + `mail` (opt-in) |
| 4 | `RejectResearchLibrary` | `app/Notifications/RejectResearchLibrary.php` | `database` + `mail` (opt-in) |

These 4 notifications now use `HasEmailPreference` trait and have `toMail()` methods. The user's `notify_email` flag controls whether the `mail` channel is included.

### 7.2 New In-App Notification Classes

**Group Domain** (triggered in `GroupService`):

| # | Class | Trigger | Recipients |
|---|-------|---------|------------|
| 5 | `GroupCreated` | Group created | Group members + section instructor |
| 6 | `GroupUpdated` | Group details changed | Group members + section instructor |
| 7 | `GroupDeleted` | Group deleted | Group members |
| 8 | `MemberAdded` | Student(s) added to group | The added student(s) |
| 9 | `MemberRemoved` | Student removed from group | The removed student |
| 10 | `PersonnelAssigned` | Personnel assigned (auto/manual) | The assigned instructor + group leader |

**Proposal Domain** (triggered in `ProposalService`):

| # | Class | Trigger | Recipients |
|---|-------|---------|------------|
| 11 | `ProposalSubmitted` | Student submits a proposal | Section instructor + RDO/Staff |

**Schedule/Presentation Domain** (triggered in `PresentationService`):

| # | Class | Trigger | Recipients |
|---|-------|---------|------------|
| 12 | `ScheduleCreated` | Presentation scheduled | Group members + panelists |
| 13 | `ScheduleUpdated` | Schedule date/time/venue changed | Group members + panelists |
| 14 | `ScheduleResult` | Schedule marked passed/failed/redefense | Group members + section instructor |

**Consultation Domain** (triggered wherever consultations are managed):

| # | Class | Trigger | Recipients |
|---|-------|---------|------------|
| 15 | `ConsultationBooked` | Consultation created | Instructor + group members |
| 16 | `ConsultationUpdated` | Consultation rescheduled/cancelled | Instructor + group members |

**Research Library Domain** (triggered in `LibraryService`):

| # | Class | Trigger | Recipients |
|---|-------|---------|------------|
| 17 | `ResearchSubmitted` | Instructor submits library entry | RDO/Staff |
| 18 | `ResearchPublished` | Library item published | Group members + instructor |
| 19 | `ResearchUnpublished` | Library item unpublished | Group members + instructor |

**Fees Domain** (triggered in `FeeService`):

| # | Class | Trigger | Recipients |
|---|-------|---------|------------|
| 20 | `FeeLedgerInitialized` | Fee ledger created for group | Group members |
| 21 | `FeeUpdated` | Fee totals recalculated | Group members |

**Announcement Domain** (triggered in `PostService`):

| # | Class | Trigger | Recipients |
|---|-------|---------|------------|
| 22 | `NewAnnouncement` | Post created (targeted) | Targeted users (by role/section) |

**Semester Domain:**

| # | Class | Trigger | Recipients |
|---|-------|---------|------------|
| 23 | `SemesterRateUpdated` | Thesis rates changed | All affected groups |

**Group Status Domain:**

| # | Class | Trigger | Recipients |
|---|-------|---------|------------|
| 24 | `GroupStatusChanged` | Final grade/status assigned | Group members + section instructor |

**Account Domain:**

| # | Class | Trigger | Recipients |
|---|-------|---------|------------|
| 25 | `WelcomeNotification` | Account created (in-app + mail) | New user |

---

## 8. Frontend: Livewire Notification Dropdown

Create `app/Livewire/NotificationDropdown.php`:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationDropdown extends Component
{
    public int $unreadCount = 0;
    public array $notifications = [];

    protected $listeners = ['notificationRefresh' => '$refresh'];

    public function mount(): void
    {
        $this->refresh();
    }

    public function refresh(): void
    {
        $user = auth()->user();

        $this->unreadCount = $user->unreadNotifications()->count();

        $this->notifications = $user->unreadNotifications()
            ->latest()
            ->take(10)
            ->get()
            ->toArray();
    }

    public function markAsRead(string $notificationId): void
    {
        auth()->user()->notifications()
            ->where('id', $notificationId)
            ->update(['read_at' => now()]);

        $this->refresh();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        $this->refresh();
    }

    public function render()
    {
        return view('livewire.notification-dropdown');
    }
}
```

Create `resources/views/livewire/notification-dropdown.blade.php` — a bell icon with dropdown, polling every 10 seconds:

```blade
<div x-data="{ open: false }" class="relative" wire:poll.10s="refresh">
    <button @click="open = !open"
            @click.away="open = false"
            class="relative p-2 rounded-xl transition hover:bg-blue-700/50 text-blue-200 hover:text-white">
        <x-heroicon-o-bell class="w-6 h-6" />
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center ring-2 ring-blue-800">
                {{ min($unreadCount, 9) }}{{ $unreadCount > 9 ? '+' : '' }}
            </span>
        @endif
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 max-h-[32rem] overflow-hidden">

        <div class="p-3 border-b border-slate-100 flex justify-between items-center bg-slate-50/80">
            <h3 class="font-semibold text-slate-900 text-sm">Notifications</h3>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs text-cyan-600 hover:text-cyan-800 font-medium transition">
                    Mark all read
                </button>
            @endif
        </div>

        <div class="overflow-y-auto max-h-80">
            @forelse($notifications as $notif)
                <div class="flex items-start gap-3 p-3 border-b border-slate-50 transition hover:bg-slate-50 {{ is_null($notif['read_at']) ? 'bg-cyan-50/50' : '' }}">
                    {{-- Icon --}}
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-cyan-100 flex items-center justify-center text-cyan-600">
                        <x-dynamic-component :component="$notif['data']['icon'] ?? 'heroicon-o-bell'"
                                             class="w-5 h-5" />
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-900 {{ is_null($notif['read_at']) ? 'text-slate-900' : 'text-slate-600' }}">
                            {{ $notif['data']['title'] ?? 'Notification' }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">
                            {{ $notif['data']['message'] ?? '' }}
                        </p>
                        <p class="text-xs text-slate-400 mt-1">
                            {{ \Carbon\Carbon::parse($notif['created_at'])->diffForHumans() }}
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex-shrink-0 flex items-start gap-1">
                        @if(isset($notif['data']['action_url']))
                            <a href="{{ $notif['data']['action_url'] }}"
                               wire:navigate
                               class="text-xs text-cyan-600 hover:text-cyan-800 font-medium px-2 py-1 rounded-lg hover:bg-cyan-50 transition">
                                {{ $notif['data']['action_text'] ?? 'View' }}
                            </a>
                        @endif
                        @if(is_null($notif['read_at']))
                            <button wire:click="markAsRead('{{ $notif['id'] }}')"
                                    class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition"
                                    title="Mark as read">
                                <x-heroicon-o-check class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-8 text-slate-400">
                    <x-heroicon-o-bell-slash class="w-10 h-10 mb-2" />
                    <p class="text-sm font-medium">No notifications</p>
                    <p class="text-xs mt-1">You're all caught up!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
```

---

## 9. Integration Into Layouts

### Register the Livewire Component

In `AppServiceProvider.php` (or a dedicated provider):

```php
\Livewire\Livewire::component('notification-dropdown', \App\Livewire\NotificationDropdown::class);
```

### Update All 4 Layout Files

Replace `@livewire('notifications')` at the bottom with `@livewire('notification-dropdown')`.

Additionally, add the bell icon button in the **header** (top bar) area of each layout, next to the profile dropdown:

**In `layouts/app.blade.php`** (line ~624, before the profile dropdown):
```blade
<div class="flex min-w-0 items-center gap-3">
    @livewire('notification-dropdown')
    {{-- existing profile dropdown --}}
    ...
</div>
```

**In `layouts/rdo/app.blade.php`** (line ~363, before the profile dropdown):
```blade
<div class="flex min-w-0 items-center gap-3">
    @livewire('notification-dropdown')
    {{-- existing profile dropdown --}}
    ...
</div>
```

**In `layouts/instructor/app.blade.php`** (line ~247, before the profile dropdown):
```blade
<div class="flex min-w-0 items-center gap-3">
    @livewire('notification-dropdown')
    {{-- existing profile dropdown --}}
    ...
</div>
```

**In `layouts/student/app.blade.php`** (line ~153, before the profile dropdown area):
```blade
<div class="flex items-center gap-2 sm:gap-3 ml-auto lg:ml-0">
    @livewire('notification-dropdown')
    {{-- existing profile dropdown --}}
    ...
</div>
```

---

## 10. Integration Points in Services

### `GroupService` — Add notifications after each operation

- `create()` → dispatch `GroupCreated` to members + adviser
- `update()` → dispatch `GroupUpdated` to members + adviser
- `delete()` → dispatch `GroupDeleted` to members
- `addMembers()` → dispatch `MemberAdded` to each added student
- `removeMembers()` → dispatch `MemberRemoved` to each removed student
- `assignRandomPersonnel()` / `createPersonnelAssignment()` → dispatch `PersonnelAssigned` to assigned instructor + group leader

### `ProposalService` — Add notification for submission

- `create()` → dispatch `ProposalSubmitted` to section instructor + RDO/Staff
- `approve()` / `reject()` → already dispatch, just update format

### `PresentationService` — Add notifications

- `create()` → dispatch `ScheduleCreated` to group members + panelists
- `update()` → dispatch `ScheduleUpdated` to group members + panelists
- `bulkSchedule()` → dispatch `ScheduleCreated` to each group's members
- When status is updated (passed/failed/redefense) → dispatch `ScheduleResult`

### `LibraryService` — Add notifications

- `create()` / submit flow → dispatch `ResearchSubmitted` to RDO/Staff
- `publish()` → dispatch `ResearchPublished` to group members + instructor
- `unpublish()` → dispatch `ResearchUnpublished` to group members + instructor

### `PostService` — Add notifications

- `createForStudents()` → dispatch `NewAnnouncement` to all students
- `createForInstructors()` → dispatch `NewAnnouncement` to all instructors
- `createForSection()` → dispatch `NewAnnouncement` to students in those sections

### `FeeService` — Add notifications

- `initializeGroupLedger()` → dispatch `FeeLedgerInitialized` to group members
- `syncHonorarium()` / `syncPanelFees()` / `updateAllGroupsInSemester()` → dispatch `FeeUpdated` to affected group members

---

## 11. Implementation Order

### ✅ Phase 0 — Email Notification Foundation (Done)
- Migration `add_notify_email_to_users_table` — adds `notify_email` boolean (default `true`)
- `User` model — `notify_email` in `$fillable` + `boolean` cast
- `HasEmailPreference` trait — reusable `viaWithEmail()` method for notification classes
- `UserSettings::toggleEmailNotifications()` — flips the flag, logs change
- Account Settings page — functional toggle wired via `wire:click`
- `ApproveProposal`, `RejectProposal`, `ApproveResearchLibrary`, `RejectResearchLibrary` — all updated with `HasEmailPreference` + `toMail()`

### Phase 1 — Foundation
- Create `NotificationType` enum
- Create `NotificationService`
- Create `NotificationDropdown` Livewire component + view
- Register component in `AppServiceProvider`
- Update all 4 layout files to include bell icon and replace `@livewire('notifications')`
- Implement Mark as Read / Mark All as Read

### Phase 2 — Core Domain Notifications (most impactful)
- Group CRUD notifications (`GroupCreated`, `MemberAdded`, `PersonnelAssigned`, etc.)
- Schedule notifications (`ScheduleCreated`, `ScheduleUpdated`, `ScheduleResult`)
- Consultation notifications (`ConsultationBooked`, `ConsultationUpdated`)
- Fee notifications (`FeeLedgerInitialized`, `FeeUpdated`)

### Phase 3 — Remaining Notifications
- `ProposalSubmitted` — notify instructor/RDO when student submits
- `ResearchSubmitted` / `ResearchPublished` / `ResearchUnpublished`
- `NewAnnouncement` — notify targeted users
- `GroupStatusChanged`
- `SemesterRateUpdated`
- `WelcomeNotification`

### Phase 4 — Polish & Testing
- Run `php artisan test --compact` to verify no regressions
- Run `vendor/bin/pint --format agent` for code style
- Manual QA of each notification flow
- Update existing notifications (`ApproveProposal`, etc.) to standardized data format (align with NotificationType enum)

---

## 12. Files Synopsis

### ✅ Already created

```
database/migrations/2026_07_28_130455_add_notify_email_to_users_table.php
app/Traits/HasEmailPreference.php
```

### Already modified

```
app/Models/User.php                             + notify_email fillable + cast
app/Service/UserSettings.php                    + toggleEmailNotifications method
resources/views/pages/⚡account-settings.blade.php + functional toggle
app/Notifications/ApproveProposal.php           + HasEmailPreference + toMail()
app/Notifications/RejectProposal.php            + HasEmailPreference + toMail()
app/Notifications/ApproveResearchLibrary.php    + HasEmailPreference + toMail()
app/Notifications/RejectResearchLibrary.php     + HasEmailPreference + toMail()
```

### New files to create (~30 files)

```
app/Enums/NotificationType.php
app/Services/NotificationService.php
app/Livewire/NotificationDropdown.php
resources/views/livewire/notification-dropdown.blade.php
app/Notifications/GroupCreated.php
app/Notifications/GroupUpdated.php
app/Notifications/GroupDeleted.php
app/Notifications/MemberAdded.php
app/Notifications/MemberRemoved.php
app/Notifications/PersonnelAssigned.php
app/Notifications/ProposalSubmitted.php
app/Notifications/ScheduleCreated.php
app/Notifications/ScheduleUpdated.php
app/Notifications/ScheduleResult.php
app/Notifications/ConsultationBooked.php
app/Notifications/ConsultationUpdated.php
app/Notifications/ResearchSubmitted.php
app/Notifications/ResearchPublished.php
app/Notifications/ResearchUnpublished.php
app/Notifications/FeeLedgerInitialized.php
app/Notifications/FeeUpdated.php
app/Notifications/NewAnnouncement.php
app/Notifications/SemesterRateUpdated.php
app/Notifications/GroupStatusChanged.php
app/Notifications/WelcomeNotification.php
```

### Existing files to modify (~12 files)

```
app/Providers/AppServiceProvider.php            + register Livewire component
app/Services/GroupService.php                   + inject NotificationService, add dispatch calls
app/Services/ProposalService.php                + add ProposalSubmitted notification
app/Services/PresentationService.php            + add notification dispatch calls
app/Services/LibraryService.php                 + add notification dispatch calls
app/Services/PostService.php                    + add NewAnnouncement dispatch calls
app/Services/FeeService.php                     + add notification dispatch calls
resources/views/layouts/app.blade.php           + add bell icon, update @livewire
resources/views/layouts/rdo/app.blade.php       + add bell icon, update @livewire
resources/views/layouts/instructor/app.blade.php + add bell icon, update @livewire
resources/views/layouts/student/app.blade.php   + add bell icon, update @livewire
```

---

## 13. Testing Strategy

- **Unit tests**: Each new notification class → assert `toDatabase()` returns the standardized structure
- **Feature tests**: Each service method that dispatches notifications → assert `assertDatabaseHas('notifications', [...])` after the action
- **Livewire tests**: `NotificationDropdown` → assert `assertSee()`, `assertSet('unreadCount', $expected)`, `markAsRead` and `markAllAsRead` behavior
- **No regressions**: Run full `php artisan test --compact` after each phase
