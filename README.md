# SitePro — Construction Project Management System

A modern, role-based construction project management platform built for residential and commercial teams. SitePro covers the full project lifecycle — from planning and budgeting to change orders, daily logs, and client visibility.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 |
| Frontend | Vue 3 (Composition API + `<script setup>`) |
| Bridge | Inertia.js 2 |
| Styling | Tailwind CSS v4 |
| Auth & RBAC | Laravel Breeze + Spatie Laravel Permission v7 |
| Database | MySQL (via DBngin) |
| Local Server | Laravel Herd |
| Icons | Lucide Vue Next |
| Notifications (UI) | Vue Sonner |
| Activity Logging | Spatie Laravel Activitylog |
| PDF Export | barryvdh/laravel-dompdf |
| Excel Export | maatwebsite/excel |
| Gantt Chart | @infectoone/vue-ganttastic |
| Routing (JS) | Tightenco Ziggy |

---

## Features

### Role-Based Access Control
- **11 roles:** CEO, Admin, Project Manager, Site Engineer, Architect, Accountant, Finance, HR, Procurement, Supervisor, Site Worker
- **34 granular permissions** across projects, tasks, expenses, resources, vendors, users, and system settings
- Single role per user — enforced via `syncRoles`
- Policy-level self-protection (users cannot delete or demote themselves)

### Projects
- Create, edit, and archive projects with budget, timeline, and status tracking
- Project detail page with tabbed sections: Tasks, Expenses, Members, Resources
- Real-time budget utilisation (approved expenses vs. project budget)
- Budget alerts at 50%, 75%, and 100% thresholds

### Tasks
- Full CRUD with priority levels and status workflow
- Overdue indicators and status filters
- Assignee-based notifications on creation and status changes

### Expenses
- Submit, approve, and reject expenses with audit trail
- Inline approve/reject for authorised roles
- Filters: project, status, search
- Summary cards for quick financial overview

### Change Orders
- Formal scope/budget change workflow: Draft → Submitted → Approved/Rejected
- Approved change orders automatically update the project budget
- Cost impact (positive/negative) and timeline impact (days)
- Rejection reason captured on decline
- Filterable by project and status

### Resources
- Track equipment, materials, and labour allocations per project
- Live value preview in create/edit modal
- Dynamic type filtering

### Vendors
- Contact directory with type-based filtering
- Direct mailto/tel links from the table

### Users & Admin
- Three-modal design: Create, Edit, Assign Role
- Optional welcome email on user creation
- Admin cannot modify their own role or delete their own account

### Notifications
- Database-backed notifications via Laravel's built-in system
- Bell dropdown in the navbar + dedicated `/notifications` page
- Triggered by: expense submitted, expense approved/rejected, task assigned, task status changed, project status changed, task overdue
- Read/unread state with mark-all-read action

### Activity Log
- Powered by Spatie Laravel Activitylog
- Tracks all meaningful model changes across the application

### Gantt Chart
- Visual project timeline via `@infectoone/vue-ganttastic`

### Reports & Export
- PDF export via Laravel DomPDF
- Excel/CSV export via Maatwebsite Excel

### Dashboard
- Live stat cards from real database queries
- Recent projects, upcoming tasks, and quick-action shortcuts

---

## UI & Design

- Dark sidebar layout (slate-900) with indigo accents
- Neutral grays throughout — color reserved for semantic status indicators only
- Modal-based CRUD across all modules (no separate create/edit pages)
- Responsive design with mobile drawer navigation
- Dark mode toggle via `useDarkMode.js` composable
- Flash toasts via Vue Sonner in `AppLayout.vue`

---

## Database Seeding

Run `php artisan db:seed` to populate:
- All 11 roles and 34 permissions
- Sample users across every role
- 7 projects with realistic statuses and budgets
- 37+ tasks with varied priorities and assignees
- Expenses, resources, vendors, and change orders

---

## Local Development

**Requirements:** PHP 8.2+, Node.js 20+, MySQL, [Laravel Herd](https://herd.laravel.com), [DBngin](https://dbngin.com)

```bash
git clone <repo-url> sitepro
cd sitepro

composer install
npm install

cp .env.example .env
php artisan key:generate

# Configure DB credentials in .env, then:
php artisan migrate --seed

npm run dev
```

Or use the composer shortcut which does everything in one step:

```bash
composer run setup
```

Access the app at `http://sitepro.test` (Herd).

---

## Roadmap

- [ ] Daily Log — site activity logging per project/day
- [ ] Punch List — pre-handover checklist per project
- [ ] Client Portal — read-only project view for homeowners/clients
- [ ] Time Tracking — log hours per task and user

---

## License

Private. Built for client use. Not open source.
