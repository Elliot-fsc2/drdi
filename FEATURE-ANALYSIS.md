# DRDI NCST Research Portal — Feature Analysis & Recommendations

> **DRDI** stands for **Department of Research Development and Innovation**.
> This is a thesis/project lifecycle management system for NCST (a college/university), built with **Laravel 12**, **Filament v5**, **Livewire v4**, **Tailwind CSS v4**, and **Alpine.js**.

---

## Available Features

The platform currently has **63+ features** organized across these domains:

### Academic Structure Management
| Feature | Description |
|---------|-------------|
| **Department Management** | CRUD for academic departments |
| **Program Management** | Programs associated with departments |
| **Semester Management** | Academic semesters with date ranges, active/inactive tracking |
| **Section/Class Management** | Classes assigned to instructors under a program/semester with enrolled students |
| **Student Management** | Student records with numbers, program enrollment, section enrollment |
| **Instructor Management** | Instructor profiles with department assignment and role (RDO, Staff, Instructor) |

### User Roles & Access Control
| Role | Access Level |
|------|-------------|
| **Super Admin** | Full system access via Filament admin panel (`/admin`) |
| **Admin** | Student & Instructor CRUD via Filament |
| **RDO** (Research Development Officer) | Everything Instructor has + group masterlist, thesis fees, semester tracking, schedule management, research approvals, repository management |
| **Instructor** | Classes, groups, proposals, consultations, schedules, library submissions, announcements |
| **Student** | Group detail, proposals, consultations, fees |
| **Staff** | Supporting role with granular permissions |

- **RBAC**: 6 roles with 30+ granular permissions via Spatie Laravel Permission
- **Rate-limited login** (5 attempts/min/IP)
- **Role-based redirect** on login
- **Password change tracking**

### Group & Team Management
| Feature | Description |
|---------|-------------|
| Group creation within sections | |
| Group leader designation | |
| Add/remove members | |
| Auto-assignment of personnel | Statistician and Language Critic automatically assigned from available instructors on group creation |
| Per-group settings configuration | |
| Cross-section student cleanup | Removes student from all groups when removed from section |

### Thesis Proposal Workflow
| Feature | Description |
|---------|-------------|
| **Title Proposal Submission** | Students submit title proposals for their group |
| **Proposal Approval** | Instructors/RDO approve with feedback |
| **Proposal Rejection** | With feedback to students |
| **Final Title Selection** | Mark an approved proposal as the group's final title |
| **Notifications** | Database notifications to all group members on approval/rejection |

### Presentation & Schedule Management
| Feature | Description |
|---------|-------------|
| **6 Presentation Types** | Title Proposal, Oral Defense, Mock Defense, Final Defense, Thesis B - Oral Defense, Thesis B - Mock Defense, Thesis B - Final Defense |
| **Presentation Scheduling** | Schedule thesis presentations with venue, date, time |
| **Venue Conflict Detection** | Prevents double-booking venues at the same time |
| **Bulk Scheduling** | Auto-schedule all groups in a section with configurable time slots and gaps |
| **Panelist Management** | Assign panelists with roles (chairperson, guest panelist, member) |
| **Status Tracking** | Passed, redefense, failed, scheduled |
| **Re-schedule Control** | Only allows re-scheduling if previous was Failed or Redefense |
| **Activity Logging** | All schedule changes logged via Spatie Activitylog |

### Consultation Management
| Feature | Description |
|---------|-------------|
| Students schedule consultations with instructors | |
| Status, remarks, and type tracking | |

### Thesis Fee Management
| Feature | Description |
|---------|-------------|
| **Rate Configuration** | Configure fixed-per-group and per-personnel rates |
| **Group Fee Ledger** | Base fee, honorarium, total merger amount tracking |
| **Honorarium Sync** | Auto-calculate based on assigned personnel count |
| **Bulk Fee Update** | Update all groups in a semester with new rates |
| **Ledger Initialization** | Auto-initialize on group creation |
| **Fee Statistics** | Total collectibles, expenses, and savings per semester |

### Research Library / Repository
| Feature | Description |
|---------|-------------|
| **Library Submission** | Groups submit completed theses (eligibility: must pass Thesis B Final Defense) |
| **Approval/Rejection Workflow** | RDO reviews with review notes |
| **Publish/Unpublish** | Control public visibility |
| **Public Repository** | Browseable research library with detail view and search |
| **Notifications** | Students notified on approval/rejection |

### Announcements & Posts
| Feature | Description |
|---------|-------------|
| Global instructor announcements | |
| Student announcements | |
| Section-specific targeting | |
| Rich content with images | |

### Task Management
| Feature | Description |
|---------|-------------|
| **Kanban Board** | Drag-and-drop (FlowForge package) |
| **Columns** | To Do, In Progress, Completed |
| **Reordering** | Position tracking within columns |

### Activity Logging & Auditing
| Feature | Description |
|---------|-------------|
| Comprehensive CRUD logging on Groups, Schedules, Research Library, Proposals, Fee operations |
| Filterable log viewer (log name, event, subject type, causer type, date range) |
| Dashboard widget showing recent activity |

### Admin Panel (Filament v5 SPA)
| Feature | Description |
|---------|-------------|
| Student CRUD with forms, tables, infolists | |
| Instructor CRUD with User account creation & welcome emails | |
| Dashboard with stats widgets | |

### Data Export
| Feature | Description |
|---------|-------------|
| **Group Masterlist Export** | Excel (Maatwebsite), formatted, legal landscape, with all group details, personnel, and fees breakdown |

### Infrastructure
| Feature | Description |
|---------|-------------|
| Queue jobs for notifications | |
| File uploads (images, PDFs) | |
| Database transactions on critical operations | |
| Eager loading (N+1 prevention) | |
| Caching (dashboard stats, 5-15 min TTL) | |
| Production safeguards (strong passwords, destructive command blocking) | |

---

## Recommended Additions

### 1. REST API Layer (Sanctum)
**Why:** Currently no API exists. Adding Laravel Sanctum-powered API would enable:
- Mobile app development (React Native / Flutter)
- Third-party integrations (Google Classroom, Moodle, LMS)
- Webhook capabilities for external services

### 2. Real-time Notifications (Broadcasting)
**Why:** Only database/polling notifications exist currently. Laravel Reverb (first-party WebSocket server) or Pusher would provide:
- Instant browser alerts for new proposals, schedule changes, consultation confirmations
- Live updates on the task board (multi-user collaboration)
- Real-time status changes on the repository approval workflow

### 3. Dashboard Analytics & Charts
**Why:** Current dashboards show only raw stat counts (total students, instructors, etc.). Adding Filament Chart Widgets with Chart.js would surface:
- Pass/fail rates per semester (line chart)
- Enrollment trends across programs
- Personnel workload distribution (bar chart)
- Fee collection summaries (pie chart)
- Presentation outcome breakdowns

### 4. Evaluation & Grading Rubrics
**Why:** No evaluation module exists. This would add:
- Configurable rubric templates per presentation type
- Panelist scoring forms (tablet/phone friendly)
- Automatic grade computation and averaging
- Per-criterion feedback comments
- Historical grade tracking per group across all presentations

### 5. Document Management with Version Control
**Why:** No centralized document system. Building on existing file uploads:
- Chapter-by-chapter submission with version history
- Instructor feedback & annotations per document
- File type validation (PDF, DOCX)
- Plagiarism check integration (Turnitin API / local checker)
- Watermarking for draft documents

### 6. Faculty / Instructor Public Profiles
**Why:** Instructors lack visible profiles. Adding CV-style profiles with:
- Research interests and expertise areas
- Publication list
- Current advising load visibility
- Availability calendar for consultations
- Smart panelist recommendation based on expertise match

### 7. Internal Messaging System
**Why:** No direct communication channel. Adding:
- Student ↔ Instructor direct messaging
- Group chat per thesis group
- File sharing within messages
- Email digests for offline users
- Read receipts and typing indicators (via broadcasting)

### 8. Public-facing Landing Page
**Why:** The login page is the entry point; the repository is the only public page. Adding:
- Department welcome page with mission/vision
- Featured research publications
- Faculty directory
- News and announcements feed
- Contact / inquiry form

### 9. Multi-language / Localization (i18n)
**Why:** No internationalization support. Adding Laravel's localization would:
- Support Filipino (Tagalog) as primary alternative
- Allow future expansion to other languages
- Make the repository accessible to international researchers

### 10. Thesis Template & Formatting Validator
**Why:** No quality check before library submission. Integration with:
- Document parsing to check margins, fonts, spacing
- Citation format validation (APA, MLA, IEEE)
- Required sections checklist (abstract, TOC, references)
- Automatic header/footer verification

### 11. Calendar Sync (iCal / Google Calendar)
**Why:** No calendar integration. Adding:
- Export presentation schedules as .ics files
- One-click "Add to Google Calendar"
- Auto-sync consultations to instructor's calendar
- Calendar feed URL per user

### 12. Automated Reminder System
**Why:** No proactive reminders. Scheduled notifications for:
- Upcoming presentations (24h, 1h before)
- Pending proposal reviews (for instructors/RDO)
- Overdue consultations
- Unpaid fee balances
- Library submission deadlines

### 13. CAPTCHA / reCAPTCHA on Login
**Why:** Only IP-based rate limiting protects the login. Adding Google reCAPTCHA v3 (invisible) or Cloudflare Turnstile would:
- Prevent automated brute-force attacks
- Add frictionless bot detection
- Reduce login form abuse

### 14. Mobile Companion App
**Why:** 100% web-based currently. With the API layer (#1), build:
- React Native or Flutter app
- Push notifications for all alert types
- Quick consultation booking
- Schedule viewing with calendar integration
- Document upload from device camera/storage
- QR code attendance for presentations

---

## Architecture Summary

```
                    ┌─────────────────────────┐
                    │    Public Repository     │
                    │  (browseable research)   │
                    └─────────────────────────┘
                              │
    ┌──────────────┬──────────┴──────────┬──────────────┐
    │              │                     │              │
┌───────┐   ┌──────────┐   ┌────────┐   ┌────────┐   ┌────────┐
│Student│   │Instructor │   │  RDO   │   │ Admin  │   │Super-  │
│       │   │           │   │        │   │        │   │Admin   │
└───┬───┘   └─────┬─────┘   └───┬────┘   └───┬────┘   └───┬────┘
    │             │             │           │            │
    └─────────────┴─────────────┴───────────┴────────────┘
                              │
                    ┌─────────┴─────────┐
                    │   Livewire v4     │
                    │   (Blade/Blaze)   │
                    └─────────┬─────────┘
                              │
                    ┌─────────┴─────────┐
                    │  Laravel 12 +     │
                    │  Services Layer   │
                    └─────────┬─────────┘
                              │
                    ┌─────────┴─────────┐
                    │     MySQL DB      │
                    │  (17 tables)      │
                    └───────────────────┘

Panel (Filament v5):  /admin
Instructor Portal:    /instructor/*
RDO Portal:           /rdo/*
Student Portal:       /student/*
```

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 8.3, Laravel 12 |
| **Admin Panel** | Filament v5 (SPA mode) |
| **Frontend** | Livewire v4 + Blaze, Alpine.js |
| **Styling** | Tailwind CSS v4 |
| **Database** | MySQL |
| **Testing** | Pest PHP v4 |
| **Queue** | Laravel Queue (ShouldQueue) |
| **Logging** | Spatie Activitylog v4 |
| **RBAC** | Spatie Laravel Permission v8 |
| **Exports** | Maatwebsite Laravel Excel v3 |
| **PDF** | Barryvdh DomPDF v3 |
| **Kanban** | Relaticle FlowForge v4 |
