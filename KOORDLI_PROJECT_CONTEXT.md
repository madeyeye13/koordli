# KOORDLI PROJECT CONTEXT
# Read this ENTIRE file before responding to ANYTHING
# This is a continuation from a previous chat session

---

## CRITICAL RULES FOR CLAUDE — READ FIRST

1. **ALWAYS ask permission before moving to next phase/step**
2. **ALWAYS ask for any file content you need before editing** — never assume file contents
3. **NEVER dump the entire application at once** — build progressively
4. **ALWAYS give complete ready-to-paste files** — no partial edits, no "add this line" without full context
5. **ALWAYS wait for confirmation after each step before continuing**
6. **NEVER use native browser select** — always custom dropdown
7. **NEVER use alert/confirm** — always toast notifications
8. **ALWAYS think about mobile responsiveness** — every list/table view MUST have desktop table + mobile cards (non-negotiable)
9. **ALWAYS think about security** — rate limiting, hashing, CSRF, honeypots, input sanitization
10. **ALWAYS think about SEO** — meta tags, canonical URLs, structured data where relevant
11. **ALWAYS follow the existing UI design system** — use `krd-` CSS classes, never inline everything
12. **NEVER install Alpine.js separately** — it is bundled with Livewire v4
13. **Business logic NEVER goes in controllers** — Services + Actions only
14. **UI is 100% Livewire** — controllers for API only
15. **Direct, output-first communication** — no verbosity, no over-explanation
16. **Use PHP 8.1+ enums** — never magic strings for statuses/types
17. **Always use UUIDs for public-facing URLs** — never sequential IDs
18. **Hard delete only** — no soft deletes, activity log covers audit trail
19. **Always queue emails** — never synchronous mail in production
20. **INSTANT UI** — Alpine handles all visual updates immediately, Livewire syncs in background. Zero perceived delay. Use `#[Renderless]` + `$wire.set()` pattern
21. **RESPONSIVE RULE** — Every list/table view must have: desktop table (hidden on mobile via CSS) + mobile cards (hidden on desktop via CSS). NEVER just a table alone
22. **CURRENCY** — Never hardcode ₦. Always use `CurrencyHelper::forTenant()` or `CurrencyHelper::symbol($currency)`. Currency auto-detected from IP on registration
23. **DROPDOWN RULE** — Never use `krdDropdown` Alpine component when the selection needs to trigger instant UI updates (like country→currency). Use plain Alpine dropdown with `pick()` method instead to avoid Livewire re-render lag

---

## WHAT IS KOORDLI

Koordli is a **multi-tenant SaaS Event Operations & Client Experience Platform** for event planning companies.

**NOT just a wedding platform.** Supports:
- Weddings, birthdays, conferences, corporate events, concerts, private events, social events
- Any future event categories — nothing hardcoded

**Product Vision:** "An operating system for event companies."

Replaces: WhatsApp chaos, spreadsheets, scattered notes, disorganized workflows.

**Koordli is evolving into:**
- An event operations ecosystem
- A lead capture platform
- A client experience platform
- A guest experience platform
- A vendor coordination platform
- A business operating system for event companies

---

## PROJECT LOCATION

`C:\Users\Bezalel Koncept\Downloads\Coding\koordli`

---

## DEVELOPMENT ENVIRONMENT

- **OS:** Windows PowerShell
- **Local server:** `php artisan serve`
- **Local URL:** `http://127.0.0.1:8000`
- **Three PowerShell windows always running:**
  - Window 1: `npm run dev`
  - Window 2: `php artisan serve`
  - Window 3: `php artisan queue:work`

---

## TECH STACK

- Laravel 12
- Livewire v4 (Alpine.js bundled — NEVER install separately)
- Tailwind CSS v4
- Blade
- MySQL
- NOT React

---

## DESIGN SYSTEM — NON-NEGOTIABLE

### Fonts
- **Satoshi** — self-hosted in `public/fonts/` (primary UI font)
- **Spline Sans** — available for RSVP form customization (Phase 5)
- Serif options for RSVP pairing: Playfair Display, Cormorant Garamond (Phase 5)

### Brand Colors
Violet:      #7C3AED  (primary)

Amber:       #F59E0B  (accent)

Stone Black: #1C1917

Warm White:  #FAFAF9

Success:     #10B981

Danger:      #EF4444

Info:        #3B82F6

Muted:       #78716C

Ghost:       #D6D3D1

Border:      #E7E5E4

### Dark Mode Colors
Background:  #0C0A09

Surface:     #1C1917

Border:      #292524

Muted:       #A8A29E

Text:        #FAFAF9

### Design Rules (absolutely non-negotiable)
- **No box shadows**
- **Minimal border radius** — max 8px
- **No native browser select** — always custom dropdown
- **No alert/confirm** — always toast notifications
- **CSS prefix:** `krd-`
- **Tenant branding via CSS variables:** `--tenant-primary`, `--tenant-accent`
- **Dark mode:** intentionally designed, not inverted
- **Mobile responsive** on everything — desktop table + mobile cards pattern

### Logo Component
- `color="light"` — white logo (auth left panels, dark backgrounds)
- `color="dark"` — dark logo (auth right panels, mobile)
- `color="auto"` — switches automatically with dark mode toggle (sidebars)
- Logo files: `public/images/logoonblack.png` and `public/images/logoonwhite.png`
- CSS classes `krd-logo-for-light` and `krd-logo-for-dark` handle switching

### Dark Mode Implementation
- Alpine store: `Alpine.store('theme')` with `dark`, `toggle()`, `init()`
- Initialized in `document.addEventListener('alpine:init')`
- Inline script in `<head>` prevents flash on load
- CSS `.dark` class on `<html>` drives all dark mode styles

---

## ARCHITECTURE

### Multi-tenancy
- Package: `stancl/tenancy`
- Strategy: **Single database**, tenant resolved from authenticated user session
- `BelongsToTenant` trait auto-scopes all tenant models
- `TenantContext` service singleton holds current tenant
- Future: subdomain + custom domain support already architected

### Authentication
Two completely separate guards:
- `platform` guard → `PlatformUser` model → `/platform/login`
- `web` guard → Tenant `User` model → `/login`

### Permissions
- Spatie Laravel Permission with `teams: true`
- `team_foreign_key` = `tenant_id`
- Platform roles: `platform_owner`
- Tenant roles: `company_owner`, `coordinator`, `finance`, `operations`, `social_media_manager`, `client`, `vendor`

### Business Logic
- **Never in controllers**
- Services + Actions + DTOs
- UI = 100% Livewire
- API = Controllers only (scaffolded, not yet built)

### Security Architecture
- Rate limiting on all auth endpoints
- Honeypot on registration form
- CSRF on all forms
- Email verification codes: hashed, 15min expiry, one-time use
- Password requirements: min 8 chars, uppercase + lowercase + number
- UUIDs on all public-facing entities
- Activity log for full audit trail
- Tenant isolation via `BelongsToTenant` trait — zero cross-tenant data leakage

---

## SEEDED CREDENTIALS

**Platform owner:**
- Email: `admin@koordli.com`
- Password: `Koordli@Admin2026`

---

## MAIL CONFIG
MAIL_MAILER=smtp

MAIL_HOST=smtp.hostinger.com

MAIL_PORT=465

MAIL_USERNAME=info@cenbabusinessaward.com

MAIL_ENCRYPTION=ssl

MAIL_FROM_ADDRESS=info@cenbabusinessaward.com

MAIL_FROM_NAME=Koordli

---

## DATABASE

- Engine: MySQL
- Database: `koordli`
- Charset: `utf8mb4_unicode_ci`

### Central Tables
platform_users

tenants                    ← has country (ISO2) + billing_currency columns

plans

plan_prices

feature_flags

plan_features

tenant_feature_overrides

subscriptions

subscription_invoices

currency_settings

email_verification_codes

### Tenant-Scoped Tables
users

event_types

tenant_event_statuses

tenant_task_categories

tenant_labels

label_assignments

events                     ← slug, client_name, client_phone, client_email, agreed_budget, start_time, end_date, end_time, location

event_team

tasks                      ← event_id nullable (NULL=company task), created_by

vendor_categories

vendors                    ← company directory (name, contact, phone, email, website, instagram, rating, is_preferred, is_active)

vendor_event_assignments   ← links vendors to events (amount_agreed, amount_paid, status, notes)

budgets                    ← HasOne per event (total_amount, client_paid, currency)

budget_items               ← (category, estimated, actual, paid, notes)

client_payments            ← payments received from client (amount, paid_on, payment_method, description)

guests

rsvp_responses

runsheets

runsheet_items

documents

notification_preferences

forms

form_fields

form_submissions

form_submission_values

form_redirects

### Key Migrations Added
- `add_extra_fields_to_events_table` — client info, times, location, agreed_budget
- `add_created_by_to_tasks_table` — created_by, event_id nullable
- `restructure_vendors_for_company_directory` — dropped old vendors, new directory + assignments
- `add_client_payments_to_budgets_table` — client_paid on budgets, client_payments table
- `add_country_to_tenants_table` — country ISO2 column

---

## IMPORTANT PRODUCT DECISIONS (locked in)

1. **Tenant self-registration** — visitors register at `/register`
2. **Free trial** — 30 days, configurable per plan by platform owner
3. **Plans fully dynamic** — platform owner creates/edits from dashboard, zero hardcoding
4. **Onboarding flow** — after registration: password change + company branding (lightweight)
5. **Hybrid tenant creation** — both self-serve and manual by platform owner
6. **Event statuses are tenant-defined** — `tenant_event_statuses` table, not hardcoded
7. **Vendor ecosystem** — company-level directory, assigned to events, Phase 4 gets accounts/portal
8. **No WhatsApp/SMS in MVP** — email only for notifications
9. **UUIDs on all public-facing entities** — never expose sequential IDs in URLs
10. **Hard deletes only** — activity log covers audit trail
11. **Queued emails always** — never synchronous in production
12. **Nothing hardcoded** — tenants can customize event types, statuses, vendor categories, task categories, labels
13. **Multi-currency** — NGN, GHS, GBP, USD, EUR, KES, ZAR via CurrencyHelper
14. **Currency auto-detected from IP** on registration using `stevebauman/location`
15. **Country stored on tenant** — `tenants.country` (ISO2 code)
16. **Budget = manual payment recording only** — no payment gateway (planners deal with high amounts)
17. **Client payments recorded manually** — company logs what was received
18. **Vendor accounts Phase 4** — vendor registration page, approval, login portal
19. **Vendor account created once only** — subsequent assignments just send notification email
20. **Tasks: single table, event_id nullable** — NULL = company task, NOT NULL = event task

---

## CURRENCY SYSTEM

### CurrencyHelper (`app/Helpers/CurrencyHelper.php`)
```php
CurrencyHelper::symbol('NGN')        // → ₦
CurrencyHelper::format(1000, 'GHS')  // → ₵1,000.00
CurrencyHelper::forTenant()          // → symbol for current auth user's tenant
CurrencyHelper::fromCountry('NG')    // → 'NGN'
CurrencyHelper::countries()          // → ['NG' => 'Nigeria', ...]
```

### Supported Currencies
NGN → ₦  (Nigeria)

GHS → ₵  (Ghana)

GBP → £  (United Kingdom)

USD → $  (US, CA, AU, AE, SA, IN, SG)

EUR → €  (DE, FR, IT, ES, NL, BE, PT, AT, FI, IE)

KES → KSh (Kenya)

ZAR → R  (South Africa)

### Country Detection Flow
1. On registration: IP detected via `stevebauman/location`
2. Country pre-fills in dropdown (plain Alpine, NOT krdDropdown)
3. Currency auto-maps from country via Alpine `currencyMap` object (instant, no Livewire lag)
4. Stored on tenant: `billing_currency` + `country` columns
5. `CurrencyHelper::forTenant()` used everywhere in tenant UI

### IMPORTANT — Country Dropdown Rule
- The country dropdown on registration uses **plain Alpine** (not krdDropdown)
- Reason: krdDropdown calls `$wire.set()` which triggers Livewire re-render, resetting Alpine state
- Plain Alpine `pick()` method updates `selectedCountry` first, then calls `$wire.set('country', code)`
- This guarantees instant currency display update with zero lag

---

## REUSABLE UI COMPONENTS

### Custom Dropdown (`resources/views/components/ui/dropdown.blade.php`)
- Alpine-powered, no native select
- Uses `krdDropdown` Alpine data component registered in `app.js`
- Props: `wire`, `placeholder`, `selected`, `max-width`
- Options rendered as `<div class="krd-dropdown-option">` inside slot
- `select(label, value)` method calls `$wire.set(wire, value)`
- Uses `template x-if="open"` to prevent duplication (NOT `x-show`)
- **DO NOT use for country/currency selection** — use plain Alpine pick() pattern instead

### Toast System (`resources/js/app.js`)
- `window.showToast(message, type)` — global function
- Types: `success`, `error`, `warning`, `info`
- Auto-dismiss: 4 seconds
- Livewire event listener: `toast-success`, `toast-error`, `toast-warning`, `toast-info`
- Container: `#krd-toast-container` fixed top-right

### WithToast Trait (`app/Traits/WithToast.php`)
- `$this->toastSuccess('message')`
- `$this->toastError('message')`
- `$this->toastWarning('message')`

### Alpine Stores (`resources/js/app.js`)
```javascript
Alpine.store('theme')           // dark mode: dark, toggle(), apply()
Alpine.data('featureToggle')    // plan feature toggles
Alpine.data('krdDropdown')      // universal dropdown
```

### `livewire:navigated` event
- Re-applies dark mode class after Livewire navigation
- Re-inits toast container
- Registered in `app.js`

---

## INSTANT UI PATTERN

All status changes, toggles, and preference updates must be instant:

```php
// Livewire component
#[Renderless]
public function togglePreferred(int $id): void
{
    $vendor = Vendor::find($id);
    $vendor->update(['is_preferred' => !$vendor->is_preferred]);
}
```

```html
<!-- Alpine handles visual instantly, Livewire syncs in background -->
<button x-data="{ preferred: {{ $vendor->is_preferred ? 'true' : 'false' }} }"
    x-on:click="preferred = !preferred; $wire.togglePreferred({{ $vendor->id }})">
    <span x-text="preferred ? '⭐' : '☆'"></span>
</button>
```

---

## RESPONSIVENESS PATTERN (NON-NEGOTIABLE)

Every page with a list or table MUST have both:

```html
{{-- Desktop Table --}}
<div id="xxx-desktop" class="krd-card" style="padding:0;overflow:hidden;">
    <div class="krd-table-wrap">
        <table class="krd-table">...</table>
    </div>
</div>

{{-- Mobile Cards --}}
<div id="xxx-mobile" style="display:flex;flex-direction:column;gap:10px;">
    @foreach($items as $item)
    <div class="krd-card" style="padding:16px;">
        <!-- card content -->
    </div>
    @endforeach
</div>

<style>
@media (min-width: 768px) {
    #xxx-desktop { display: block !important; }
    #xxx-mobile  { display: none !important; }
}
@media (max-width: 767px) {
    #xxx-desktop { display: none !important; }
    #xxx-mobile  { display: flex !important; }
}
</style>
```

---

## SIDEBAR — MOBILE FIX

Mobile sidebar controlled entirely by Alpine `sidebarOpen` — NOT DOM classList.

```javascript
// html tag has x-data="{ sidebarOpen: window.innerWidth >= 768 }"
// sidebar: x-bind:class="{ 'krd-sidebar--collapsed': !sidebarOpen }"
// hamburger: x-on:click="sidebarOpen = !sidebarOpen"
// overlay: x-on:click="sidebarOpen = false"
// close btn: x-on:click="sidebarOpen = false"
```

CSS hides sidebar on mobile by default (before Alpine loads = no flash):
```css
@media (max-width: 768px) {
    .krd-sidebar {
        width: var(--krd-sidebar-width) !important;
        transform: translateX(-100%);
        transition: transform 200ms ease;
    }
    .krd-sidebar:not(.krd-sidebar--collapsed) {
        transform: translateX(0);
    }
}
```

---

## WHAT HAS BEEN BUILT — COMPLETE

### Phase 1 — Foundation ✅
- Laravel 12 + all packages installed
- Tenancy (single DB, session-based)
- All enums, models, migrations (47+ tables)
- Two auth guards (platform + web)
- Spatie permissions (all roles + permissions)
- Complete design system (`app.css`)
- Dark mode (Alpine store + CSS)
- Toast system
- Custom dropdown component
- Responsive CSS (mobile hamburger, sidebar overlay, grid breakpoints)

### Phase 2 — Auth & Platform ✅
- Tenant login (`/login`)
- Platform login (`/platform/login`)
- 4-step registration (`/register`):
  - Step 1: company name, **country (IP auto-detected)**, **billing currency (instant Alpine update)**, name, email, password, terms
  - Step 2: 6-digit email verification (queued, rate-limited, hashed)
  - Step 3: Plan selection
  - Step 4: Onboarding questions (skippable)
- Tenant + user created on registration with correct `billing_currency` + `country`
- Tenant onboarding flow (`/onboarding`)
- Tenant dashboard scaffold
- Platform dashboard (KPI cards, companies table)
- `Platform\Tenants\CreateTenant` + `TenantList`
- `Platform\Plans` (create/edit plans)
- Welcome email for manually created tenants (queued)

### Phase 3 — Core Event Operations ✅

#### Event Management
- Routes: `/events`, `/events/create`, `/events/{slug}/edit`, `/events/{slug}`
- Slug format: `event-name-mon-yyyy` (unique per tenant)
- Components: `EventList`, `CreateEvent`, `EventDetail`
- Fields: name, type, status, client info, dates/times, venue, location, max_guests, agreed_budget, notes
- Event detail: KPI strip, info panel, status quick-change, tasks panel, vendors panel, guests panel, budget quick view
- List/grid toggle (instant Alpine)
- Status update (instant Alpine + Renderless)

#### Task Management
- Routes: `/tasks`, `/tasks/create`, `/tasks/{id}/edit`
- 4 views: All Tasks, Event Tasks, Company Tasks, My Tasks (tab switching = instant Alpine)
- `event_id` nullable: NULL = company task, NOT NULL = event task
- Statuses: todo, in_progress, blocked, done, cancelled (`TaskStatus` enum)
- Priorities: low, normal, high, urgent (`TaskPriority` enum)
- Status circle picker (instant Alpine popup, no Livewire round-trip)
- Components: `TaskCenter`, `CreateTask`

#### Staff Management
- Routes: `/staff`, `/staff/invite`, `/staff/{id}/edit`
- Components: `StaffList`, `InviteStaff`
- Invite with temp password, activate/deactivate
- Email: `StaffInviteMail` → `SendStaffInviteJob` (queued)
- Template: `resources/views/emails/staff-invite.blade.php`
- Spatie roles with explicit `team_id` assignment

#### Budget Tracking
- Routes: `/budget` (overview), `/events/{slug}/budget` (event budget)
- Components: `EventBudget`, `BudgetOverview`
- Models: `Budget` (HasOne per event), `BudgetItem`, `ClientPayment`
- Financial picture:
  - Agreed Budget (from event.agreed_budget)
  - Client Paid (sum of ClientPayment records)
  - Client Outstanding (auto-calculated)
  - Total Estimated (sum of budget items)
  - Total Actual Spent (sum of actual costs)
  - Vendor Paid (sum of paid to vendors)
  - Vendor Balance (actual - vendor paid)
  - Gross Profit (client paid - actual spent)
  - Projected Profit (agreed - estimated)
- Progress bars: collection % + spend %
- Two tabs: Cost Breakdown + Client Payments
- Outstanding reminder email: `OutstandingReminderMail` → `SendOutstandingReminderJob`
- Email template: `resources/views/emails/outstanding-reminder.blade.php`
- Send reminder button only shows when outstanding > 0 AND client_email exists

#### Vendor Management
- Routes: `/vendors`, `/vendors/create`, `/vendors/{id}/edit`, `/vendors/{id}`
- Components: `VendorDirectory`, `CreateVendor`, `VendorDetail`
- Models: `Vendor` (company directory), `VendorEventAssignment`
- Architecture: company-level directory + separate event assignments
- Features:
  - Grid/list toggle (instant Alpine, Renderless)
  - Preferred vendor toggle (⭐, instant Alpine + Renderless)
  - 1-5 star rating
  - Assign vendor to events with amount_agreed, amount_paid, status
  - Amount agreed + paid both entered at assignment time (no separate step)
  - Edit/delete assignments from vendor detail page
  - Vendor assignments shown on event detail page
- `vendor_event_assignments` table: unique(vendor_id, event_id)
- Phase 4: vendor accounts, registration page, notifications, login portal

#### Dashboard
- Real KPIs: total events, total tasks (overdue count), active vendors, guests
- Recent events list
- Pending tasks list (with overdue highlighting)
- Budget summary: 4 cards (total agreed, collected, outstanding, actual spent)
- All amounts use `CurrencyHelper::forTenant()`

#### Currency System
- `app/Helpers/CurrencyHelper.php` — `symbol()`, `format()`, `forTenant()`, `fromCountry()`, `countries()`
- Used in: EventDetail, EventBudget, BudgetOverview, Dashboard
- Budget overview: uses `$sym` per-event from `CurrencyHelper::symbol($b->currency)`
- Event detail: `$symbol` passed from component via `CurrencyHelper::forTenant()`

#### Country + IP Detection
- Package: `stevebauman/location` installed
- `tenants.country` VARCHAR(2) column added
- Registration Step 1: country dropdown pre-filled from IP detection
- Plain Alpine dropdown (NOT krdDropdown) to prevent re-render lag
- `pick(code, name)` method: updates `selectedCountry` first, then `$wire.set('country', code)`
- Alpine `currencyMap` object maps country codes to currency label strings
- `get currencyLabel()` computed Alpine property updates instantly

#### Email Jobs (all queued)
- `SendVerificationCodeJob` → `VerificationCodeMail`
- `SendWelcomeEmailJob` → `WelcomeMail`
- `SendStaffInviteJob` → `StaffInviteMail`
- `SendOutstandingReminderJob` → `OutstandingReminderMail`

---

## ROUTES (complete current state)
/ → redirect tenant.login
Tenant routes (tenant.resolve middleware):

/login                    → Tenant\Auth\Login

/register                 → Auth\Register
Authenticated + onboarding:

/dashboard                → Tenant\Dashboard

/onboarding               → Tenant\Onboarding

/events                   → Tenant\Events\EventList

/events/create            → Tenant\Events\CreateEvent

/events/{slug}/edit       → Tenant\Events\CreateEvent

/events/{slug}            → Tenant\Events\EventDetail

/events/{slug}/budget     → Tenant\Budget\EventBudget

/tasks                    → Tenant\Tasks\TaskCenter

/tasks/create             → Tenant\Tasks\CreateTask

/tasks/{id}/edit          → Tenant\Tasks\CreateTask

/staff                    → Tenant\Staff\StaffList

/staff/invite             → Tenant\Staff\InviteStaff

/staff/{id}/edit          → Tenant\Staff\InviteStaff

/budget                   → Tenant\Budget\BudgetOverview

/vendors                  → Tenant\Vendors\VendorDirectory

/vendors/create           → Tenant\Vendors\CreateVendor

/vendors/{id}/edit        → Tenant\Vendors\CreateVendor

/vendors/{id}             → Tenant\Vendors\VendorDetail

/logout (POST)            → tenant.logout
Platform routes (auth.platform):

/platform/login

/platform/dashboard

/platform/tenants

/platform/tenants/create

/platform/plans

/platform/plans/create

/platform/plans/{plan}/edit

/platform/logout (POST)

---

## SIDEBAR NAVIGATION (tenant)
Overview:   Dashboard

Operations: Events, Tasks, Vendors, Budget

Business:   Forms & Bookings, Staff, Settings

---

## PENDING (Phase 4+)

### Phase 4 — Client + Vendor Portals
- Client portal (credentials login, invited from event detail)
- Client dashboard (their event view only)
- Vendor registration page (public URL per tenant)
- Vendor application review (approve/reject)
- Vendor account creation (once only — checks if email already has account)
- Vendor login portal
- Vendor dashboard (assigned events, tasks, runsheet items, payment status)
- Vendor assignment notification email (sent on assignment, not on account creation)
- Guest management + RSVP

### Phase 5 — RSVP & Guest Experience
- RSVP engine (own module, uses shared Form Field Engine)
- Guest management
- QR tickets + check-in
- Attendance analytics
- Public RSVP pages: `koordli.com/rsvp/{slug}`

### Phase 6 — Runsheet & Live Operations
- Runsheet engine
- Minute-by-minute timelines
- Live event coordination

### Phase 7 — Form Engine UI
- Booking + consultation forms builder
- External form submission endpoint
- WhatsApp redirect after submission
- Embedded form support

### Phase 8 — Vendor Ecosystem
- Vendor marketplace
- Vendor ratings/reviews
- Vendor availability calendars
- Vendor contracts + invoicing

### Phase 9 — Billing & Subscriptions
- Paystack + Flutterwave integration
- Multi-currency display
- Exchange rates via Frankfurter API (cached 24hrs)
- Self-serve plan upgrades
- Trial expiry notifications

### Phase 10 — Public Facing
- Landing page (SEO optimized)
- Public booking/consultation pages
- API v1

### Still Pending from Phase 2
- Platform tenant management (view, edit, suspend, activate)
- Platform plans management (full CRUD from dashboard)
- Welcome email for manually created tenants

---

## PACKAGES INSTALLED
livewire/livewire

stancl/tenancy

spatie/laravel-permission

spatie/laravel-activitylog

spatie/laravel-medialibrary

spatie/laravel-sitemap

spatie/laravel-sluggable

stevebauman/location

barryvdh/laravel-debugbar (dev)

---

## FOLDER STRUCTURE (key locations)
app/

├── Helpers/

│   └── CurrencyHelper.php     ← symbol(), format(), forTenant(), fromCountry(), countries()

├── Enums/                     ← TaskStatus, TaskPriority, RSVPStatus, VendorStatus etc.

├── Jobs/                      ← SendVerificationCodeJob, SendStaffInviteJob, SendOutstandingReminderJob, SendWelcomeEmailJob

├── Livewire/

│   ├── Auth/Register.php      ← 4-step registration with IP detection

│   ├── Platform/              ← Platform owner UI

│   ├── Tenant/

│   │   ├── Dashboard.php

│   │   ├── Events/            ← EventList, CreateEvent, EventDetail

│   │   ├── Tasks/             ← TaskCenter, CreateTask

│   │   ├── Staff/             ← StaffList, InviteStaff

│   │   ├── Budget/            ← EventBudget, BudgetOverview

│   │   └── Vendors/           ← VendorDirectory, CreateVendor, VendorDetail

│   └── Shared/

├── Mail/                      ← OutstandingReminderMail, StaffInviteMail, WelcomeMail

├── Models/

│   ├── Central/               ← Tenant, Plan, PlatformUser

│   └── Tenant/                ← Event, Task, Vendor, VendorEventAssignment, Budget, BudgetItem, ClientPayment etc.

├── Services/

└── Traits/

└── WithToast.php          ← toastSuccess(), toastError(), toastWarning()
resources/

├── css/app.css                ← Complete design system + responsive + dark mode

├── js/app.js                  ← Alpine stores, krdDropdown, toast system, livewire:navigated

└── views/

├── components/

│   ├── layout/            ← tenant-sidebar, tenant-topbar

│   └── ui/                ← logo, dropdown, trial-banner

├── emails/                ← staff-invite, outstanding-reminder, verification-code, welcome

├── layouts/               ← auth, platform, tenant

└── livewire/

├── auth/register.blade.php

├── platform/

└── tenant/

├── dashboard.blade.php

├── events/

├── tasks/

├── staff/

├── budget/

└── vendors/
public/

├── fonts/                     ← Satoshi-Variable.woff2, Satoshi-VariableItalic.woff2

└── images/                    ← logoonblack.png, logoonwhite.png

---

## WHEN CONTINUING IN NEW CHAT

1. Paste this entire context document first
2. Claude reads it fully before responding
3. Next phase: **Phase 4 — Client Portal**
4. Start by asking to see relevant existing files before building anything
5. Ask permission at every major step

**Key URLs:**
- Registration: `http://127.0.0.1:8000/register`
- Tenant login: `http://127.0.0.1:8000/login`
- Platform login: `http://127.0.0.1:8000/platform/login`
- Dashboard: `http://127.0.0.1:8000/dashboard`