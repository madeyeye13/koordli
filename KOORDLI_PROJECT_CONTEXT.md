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
23. **DROPDOWN RULE** — Never use `krdDropdown` Alpine component when the selection needs to trigger instant UI updates. Use plain Alpine dropdown with `pick()` method instead
24. **TOGGLE PATTERN** — Hidden checkbox with `wire:model` + Alpine visual div dispatching `change` event — avoids `$wire.set` re-render loop
25. **BOOLEAN VALIDATION** — Always include `'field' => 'boolean'` in validate() for bool properties or Laravel throws foreach error
26. **RSVP COMPONENT NAME** — Public RSVP Livewire component is `RsvpFormPage` (NOT `RsvpForm`) to avoid collision with `RsvpForm` model
27. **QR CODES** — SimpleSoftwareIO SVG format. Store token string only (`qr_payload`), regenerate on demand. Never store image files.
28. **DARK MODE INLINE STYLES** — Never hardcode `color:#1C1917` in inline styles across tenant blade views — use CSS classes. A global dark mode override exists in `app.css` for legacy inline styles.

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
- **Fraunces** — serif font for RSVP pages (loaded from Google Fonts in `layouts/rsvp.blade.php`)
- **Spline Sans** — body font for RSVP pages (loaded from Google Fonts in `layouts/rsvp.blade.php`)
- Fraunces replaces Playfair Display / Cormorant Garamond — decision locked in

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
- Global dark mode CSS override in `app.css` handles legacy inline `color:#1C1917` styles across all tenant views

---

## ARCHITECTURE

### Multi-tenancy
- Package: `stancl/tenancy`
- Strategy: **Single database**, tenant resolved from authenticated user session
- `BelongsToTenant` trait auto-scopes all tenant models
- `TenantContext` service singleton holds current tenant
- Future: subdomain + custom domain support already architected

### Authentication Guards (4 total)
- `platform` guard → `PlatformUser` → `/platform/login`
- `web` guard → Tenant `User` → `/login`
- `client` guard → `Client` (Central) → `/client/login`
- `vendor` guard → `VendorAccount` (Central) → `/vendor/login`

All registered in `config/auth.php` and `bootstrap/app.php` middleware aliases:
`auth.platform`, `auth.tenant`, `auth.client`, `auth.vendor`, `tenant.resolve`, `onboarding.check`, `vendor.password.check`, `client.password.check`

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

---

## SIDEBAR NAVIGATION (tenant)
Overview:   Dashboard
Operations: Events, Tasks, Vendors, Applications, Budget
Experience: Clients, Guests & RSVP, Runsheet
Business:   Forms & Bookings, Staff, Settings

## SECURITY ARCHITECTURE
- Rate limiting on all auth endpoints
- Honeypot on registration form
- CSRF on all forms
- Email verification codes: hashed, 15min expiry, one-time use
- Password: min 8 chars, uppercase + lowercase + number
- UUIDs on all public-facing entities
- Activity log for full audit trail
- Tenant isolation via BelongsToTenant trait

## DEFAULT SEEDER
- TenantService::create() calls DefaultTenantSeeder
- Seeds: default event types, statuses, task categories, vendor categories
- Runs on both self-registration and manual platform creation

## DATABASE

### Central Tables
platform_users

tenants                    ← country (ISO2), billing_currency, branding JSON, slug

plans

plan_prices

feature_flags

plan_features

tenant_feature_overrides

subscriptions

subscription_invoices

currency_settings

email_verification_codes

clients                    ← password_changed bool

vendor_accounts            ← password_changed bool, vendor_id FK, vendor_application_id FK

### Tenant-Scoped Tables
users

event_types

tenant_event_statuses

tenant_task_categories

tenant_labels

label_assignments

events                     ← slug, client_name, client_phone, client_email, agreed_budget,

start_time, end_date, end_time, location, venue, rsvp_enabled bool

event_team

tasks                      ← event_id nullable, vendor_account_id nullable FK → vendor_accounts

vendor_categories

vendors

vendor_event_assignments

vendor_applications        ← available_to_travel bool, status: pending/approved/rejected

budgets

budget_items

client_payments

guests                     ← event_id FK, category, rsvp_status, checked_in, checked_in_at

rsvp_forms                 ← slug, branding JSON (cover_image, cover_image_path, accent_color, bg_color),

questions JSON, ticket_settings JSON, deadline, guest_limit, is_active

rsvp_questions             ← rsvp_form_id, label, field_type, is_required, options JSON, sort_order

rsvp_responses             ← rsvp_form_id, guest_id nullable, respondent_name/email/phone,

status, plus_one_count, qr_token, edit_token, response_data JSON, checked_in_at

rsvp_response_answers      ← rsvp_response_id, rsvp_question_id, answer

runsheets                  ← title, date, status: draft/active/completed

runsheet_items             ← start_time, end_time, assigned_to (users FK), vendor_id (vendors FK),

status, sort_order, depends_on

---

## KEY FILE LOCATIONS

### Livewire Components
app/Livewire/Auth/Register.php

app/Livewire/Platform/Dashboard.php

app/Livewire/Platform/Tenants/TenantList.php

app/Livewire/Platform/Tenants/CreateTenant.php

app/Livewire/Platform/Plans/PlanList.php

app/Livewire/Platform/Plans/CreatePlan.php

app/Livewire/Tenant/Dashboard.php

app/Livewire/Tenant/Events/EventList.php

app/Livewire/Tenant/Events/CreateEvent.php      ← rsvp_enabled bool, hidden checkbox toggle pattern

app/Livewire/Tenant/Events/EventDetail.php

app/Livewire/Tenant/Tasks/TaskCenter.php

app/Livewire/Tenant/Tasks/CreateTask.php

app/Livewire/Tenant/Staff/StaffList.php

app/Livewire/Tenant/Staff/InviteStaff.php

app/Livewire/Tenant/Budget/EventBudget.php

app/Livewire/Tenant/Budget/BudgetOverview.php

app/Livewire/Tenant/Vendors/VendorDirectory.php

app/Livewire/Tenant/Vendors/CreateVendor.php

app/Livewire/Tenant/Vendors/VendorDetail.php

app/Livewire/Tenant/Vendors/VendorApplications.php

app/Livewire/Tenant/Guests/GuestList.php        ← per event, custom Alpine dropdowns for filters

app/Livewire/Tenant/Rsvp/RsvpManager.php        ← WithFileUploads, 4 tabs: Setup/Questions/Branding/Responses

app/Livewire/Client/Auth/Login.php

app/Livewire/Client/Dashboard.php

app/Livewire/Client/Onboarding.php

app/Livewire/Vendor/Auth/Login.php

app/Livewire/Vendor/Dashboard.php

app/Livewire/Vendor/Profile.php

app/Livewire/Vendor/Onboarding.php

app/Livewire/Public/RsvpFormPage.php            ← NOT RsvpForm (model name collision)

app/Livewire/Public/RsvpEdit.php

app/Livewire/Public/VendorRegister.php

### Models
app/Models/Central/Tenant.php

app/Models/Central/PlatformUser.php

app/Models/Central/Plan.php

app/Models/Central/Client.php                   ← password_changed bool

app/Models/Central/VendorAccount.php            ← password_changed bool, vendor_id FK

app/Models/Tenant/Event.php                     ← rsvp_enabled bool cast

app/Models/Tenant/Task.php                      ← vendor_account_id nullable FK, assigneeName() helper

app/Models/Tenant/Vendor.php

app/Models/Tenant/VendorApplication.php         ← available_to_travel bool

app/Models/Tenant/VendorEventAssignment.php

app/Models/Tenant/Budget.php

app/Models/Tenant/BudgetItem.php

app/Models/Tenant/ClientPayment.php

app/Models/Tenant/Guest.php                     ← event_id FK, statusBadgeClass() helper

app/Models/Tenant/RsvpForm.php                  ← publicUrl(), questions() HasMany, totalAttendees()

app/Models/Tenant/RsvpQuestion.php              ← fieldTypeLabel(), hasOptions()

app/Models/Tenant/RsvpResponse.php              ← editUrl(), totalAttendees(), generateQrToken()

app/Models/Tenant/RsvpResponseAnswer.php

### Helpers / Services / Traits
app/Helpers/CurrencyHelper.php                  ← symbol(), format(), forTenant(), fromCountry(), countries()

app/Traits/WithToast.php                        ← toastSuccess(), toastError(), toastWarning()

app/Traits/BelongsToTenant.php

app/Services/TenantService.php

app/Services/FeatureGateService.php

### Jobs (all queued)
SendVerificationCodeJob

SendWelcomeEmailJob

SendStaffInviteJob

SendClientInviteJob

SendVendorInviteJob

SendVendorApprovalJob

SendVendorApplicationReceivedJob

SendVendorAssignedJob

SendOutstandingReminderJob

SendRsvpConfirmationJob                         ← sends QR code SVG inline in email

SendRsvpNotificationJob                         ← notifies planner + client on each response

### Email Views
resources/views/emails/verification-code.blade.php

resources/views/emails/welcome.blade.php

resources/views/emails/staff-invite.blade.php

resources/views/emails/client-invite.blade.php

resources/views/emails/vendor-invite.blade.php

resources/views/emails/vendor-approval.blade.php

resources/views/emails/vendor-application-received.blade.php

resources/views/emails/vendor-assigned.blade.php

resources/views/emails/outstanding-reminder.blade.php

resources/views/emails/rsvp-confirmation.blade.php  ← QR SVG inline, download ticket link

resources/views/emails/rsvp-notification.blade.php  ← sent to planner + client

### Layouts
resources/views/layouts/auth.blade.php

resources/views/layouts/platform.blade.php

resources/views/layouts/tenant.blade.php

resources/views/layouts/client.blade.php

resources/views/layouts/vendor.blade.php

resources/views/layouts/rsvp.blade.php          ← bare layout, Fraunces + Spline Sans Google Fonts

### Public Views
resources/views/livewire/public/rsvp-form.blade.php   ← left/right split, Fraunces serif, CSS vars

resources/views/livewire/public/rsvp-edit.blade.php   ← edit via secure token

resources/views/public/rsvp-ticket-pdf.blade.php      ← print/save as PDF page with QR

---

## ROUTES (complete current state)

### Public
/rsvp/{slug}                      → Public\RsvpFormPage

/rsvp/{slug}/edit/{token}         → Public\RsvpEdit

/rsvp/ticket/{token}              → route closure → public.rsvp-ticket-pdf view

/vendors/{slug}/register          → Public\VendorRegister

### Tenant Auth
/login                            → Tenant\Auth\Login

/register                         → Auth\Register

/logout (POST)

### Tenant Authenticated
/dashboard                        → Tenant\Dashboard

/onboarding                       → Tenant\Onboarding

/events                           → Tenant\Events\EventList

/events/create                    → Tenant\Events\CreateEvent

/events/{slug}/edit               → Tenant\Events\CreateEvent

/events/{slug}                    → Tenant\Events\EventDetail

/events/{slug}/budget             → Tenant\Budget\EventBudget

/events/{slug}/guests             → Tenant\Guests\GuestList

/events/{slug}/rsvp               → Tenant\Rsvp\RsvpManager

/tasks                            → Tenant\Tasks\TaskCenter

/tasks/create                     → Tenant\Tasks\CreateTask

/tasks/{id}/edit                  → Tenant\Tasks\CreateTask

/staff                            → Tenant\Staff\StaffList

/staff/invite                     → Tenant\Staff\InviteStaff

/staff/{id}/edit                  → Tenant\Staff\InviteStaff

/budget                           → Tenant\Budget\BudgetOverview

/vendors                          → Tenant\Vendors\VendorDirectory

/vendors/create                   → Tenant\Vendors\CreateVendor

/vendors/{id}/edit                → Tenant\Vendors\CreateVendor

/vendors/{id}                     → Tenant\Vendors\VendorDetail

/vendor-applications              → Tenant\Vendors\VendorApplications

### Client Portal
/client/login                     → Client\Auth\Login

/client/onboarding                → Client\Onboarding

/client/dashboard                 → Client\Dashboard

/client/logout (POST)

### Vendor Portal
/vendor/login                     → Vendor\Auth\Login

/vendor/onboarding                → Vendor\Onboarding

/vendor/dashboard                 → Vendor\Dashboard

/vendor/profile                   → Vendor\Profile

/vendor/logout (POST)

### Platform
/platform/login                   → Platform\Auth\Login

/platform/dashboard               → Platform\Dashboard

/platform/tenants                 → Platform\Tenants\TenantList

/platform/tenants/create          → Platform\Tenants\CreateTenant

/platform/plans                   → Platform\Plans\PlanList

/platform/plans/create            → Platform\Plans\CreatePlan

/platform/plans/{plan}/edit       → Platform\Plans\CreatePlan

/platform/logout (POST)

---

## RSVP ARCHITECTURE (Phase 5 — complete)

### Key Decisions (locked in)
- RSVP is a **premium feature**, feature-gated per event via `rsvp_enabled` on events table
- Planner enables/disables RSVP per event from CreateEvent page
- RSVP has its own dedicated module — NOT merged with Form Engine
- System fields always enforced: Full Name, Email, Will You Attend?, Number of Attendees
- Custom questions via RSVP question builder (10 field types)
- QR codes: SimpleSoftwareIO SVG, token stored as string, regenerated on demand
- Guests can edit responses via secure `edit_token` link (no account needed)
- Both planner and client notified on each response
- Cover image stored via `Storage::disk('public')`, path in `branding->cover_image_path`

### RSVP Manager Tabs
1. **Setup** — title, deadline, guest_limit, active toggle, public link
2. **Questions** — custom question builder (add/edit/delete/reorder)
3. **Branding** — cover image upload, accent_color, bg_color with live preview
4. **Responses** — table + mobile cards, check-in, delete

### Public RSVP Page Design
- Left panel: event details (dark background or cover image with overlay)
- Right panel: form (Spline Sans body, Fraunces headings)
- CSS variables: `--rsvp-accent`, `--rsvp-bg` from branding JSON
- Button flash fix: `@class` Blade directive for server-rendered initial state
- Fonts: Fraunces (serif headings) + Spline Sans (body) from Google Fonts

### RSVP Question Field Types
`text`, `textarea`, `email`, `phone`, `number`, `dropdown`, `checkbox`, `radio`, `yes_no`, `date`

---

## GUEST MANAGEMENT ARCHITECTURE

### Key Decisions (locked in)
- Guests are **event-scoped** (MVP)
- Two modes: simple expected count (on event.max_guests) OR individual records
- Individual guests: name, email, phone, category, notes, rsvp_status, checked_in
- Future-ready for global contacts layer without major rewrite
- RSVP responses are separate from guest records (guests can exist without RSVP)

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

---

## REUSABLE UI COMPONENTS

### Custom Dropdown (`resources/views/components/ui/dropdown.blade.php`)
- Alpine-powered, no native select
- Uses `krdDropdown` Alpine data component registered in `app.js`
- Props: `wire`, `placeholder`, `selected`, `max-width`
- **DO NOT use for country/currency selection** — use plain Alpine pick() pattern

### Toast System
- `window.showToast(message, type)` — global function
- Types: `success`, `error`, `warning`, `info`
- Auto-dismiss: 4 seconds
- Container: `#krd-toast-container` fixed top-right
- `WithToast` trait: `toastSuccess()`, `toastError()`, `toastWarning()`

### Alpine Stores
```javascript
Alpine.store('theme')           // dark mode
Alpine.data('featureToggle')    // plan feature toggles
Alpine.data('krdDropdown')      // universal dropdown
```

---

## INSTANT UI PATTERN

```php
#[Renderless]
public function togglePreferred(int $id): void
{
    Vendor::find($id)->update(['is_preferred' => !$vendor->is_preferred]);
}
```

```html
<button x-data="{ preferred: {{ $vendor->is_preferred ? 'true' : 'false' }} }"
    x-on:click="preferred = !preferred; $wire.togglePreferred({{ $vendor->id }})">
    <span x-text="preferred ? '⭐' : '☆'"></span>
</button>
```

### Toggle Pattern for Booleans (avoids $wire.set re-render loop)
```html
<input type="checkbox" wire:model="rsvp_enabled"
    x-bind:checked="on"
    style="display:none;" id="my_toggle_input" />
<div x-on:click="
    on = !on;
    document.getElementById('my_toggle_input').checked = on;
    document.getElementById('my_toggle_input').dispatchEvent(new Event('change'));
">
```

---

## RESPONSIVENESS PATTERN (NON-NEGOTIABLE)

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
    <div class="krd-card" style="padding:16px;">...</div>
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

## WHAT HAS BEEN BUILT — COMPLETE

### Phase 1 — Foundation ✅
- Laravel 12 + all packages installed
- Tenancy (single DB, session-based)
- All enums, models, migrations
- Two auth guards (platform + web)
- Spatie permissions (all roles + permissions)
- Complete design system (`app.css`)
- Dark mode (Alpine store + CSS + global inline override)
- Toast system
- Custom dropdown component
- Responsive CSS (mobile hamburger, sidebar overlay, grid breakpoints)

### Phase 2 — Auth & Platform ✅
- Tenant login + platform login
- 4-step registration with IP detection + country/currency auto-detect
- Email verification (6-digit, hashed, queued, rate-limited)
- Plan selection
- Onboarding flow
- Platform dashboard (KPI cards, companies table)
- Platform tenant management (create)
- Platform plans management (create/edit)
- Welcome email for manually created tenants

### Phase 3 — Core Event Operations ✅
- Event CRUD (slug-based URLs, status management)
- Task management (4 views, instant status picker)
- Staff management (invite, temp password, activate/deactivate)
- Budget tracking (event budget + overview, client payments, outstanding reminder)
- Vendor management (directory, assignments, preferred toggle, grid/list)
- Dashboard (real KPIs, recent events, pending tasks, budget summary)
- Currency system fully integrated

### Phase 4 — Client + Vendor Portals ✅
- **Client portal:** login, forced onboarding (password change), dashboard (event view, payment history, progress bar), invite from event detail, duplicate invite protection
- **Vendor portal:** login, forced onboarding, dashboard (assigned events + payment status), profile (editable), public registration `/{slug}/vendors/register` with IP detection + travel toggle, application review (approve/reject), auto-create account + send credentials on first assignment, assignment notification if account exists, duplicate invite protection
- **Guest management:** per-event guest list (add/edit/delete), RSVP status dropdown (custom Alpine), check-in toggle, expected count with progress bar, desktop table + mobile cards, custom category filter
- **RSVP toggle on events:** `rsvp_enabled` bool on events, hidden checkbox toggle pattern, persists correctly
- **Vendor tasks:** `vendor_account_id` nullable FK on tasks, `assignedVendor()` relation, `assigneeName()` helper
- All vendor email templates (invite, approval, application received, assigned)

### Phase 5 — RSVP & Guest Experience ✅
- RSVP Manager page (`/events/{slug}/rsvp`) — 4 tabs: Setup, Questions, Branding, Responses
- Custom question builder (10 field types, add/edit/delete/reorder)
- System fields always enforced (Full Name, Email, Will You Attend?, Number of Attendees)
- Public RSVP page (`/rsvp/{slug}`) — Fraunces + Spline Sans, left/right split layout
- QR code generation (SimpleSoftwareIO SVG, stored as token)
- Guest edit response via secure edit_token (`/rsvp/{slug}/edit/{token}`)
- Ticket download page (print/save as PDF with QR)
- RSVP confirmation email (QR SVG inline, download link)
- RSVP notification emails (planner + client on each response)
- Branding tab: cover image upload (Storage::disk('public')), accent color, bg color, live preview
- CSS variables `--rsvp-accent`, `--rsvp-bg` applied from branding JSON
- Button flash fix via `@class` Blade directive
- Dark mode global CSS override for all 81+ inline `color:#1C1917` occurrences

---

## PENDING

### Still Pending from Phase 2
- Platform tenant management — view, edit, suspend, activate (create exists)
- Platform plans management — full CRUD (create/edit exists, delete/toggle pending)

### Still Pending from Phase 4
- Client portal RSVP view (read-only stats for client)
- Vendor dashboard showing tasks + runsheet items (currently shows events + payment only)

### Phase 6 — Runsheet & Live Operations
- Runsheet engine per event
- Minute-by-minute timeline items
- Live event coordination view
- `runsheets` + `runsheet_items` tables already migrated
- `runsheet_items` already has `vendor_id` + `assigned_to` + `depends_on`

### Phase 7 — Form Engine UI
- Booking + consultation forms builder
- External form submission endpoint
- WhatsApp redirect after submission
- Embedded form support
- Shared field engine (reused by RSVP custom questions)

### Phase 8 — Vendor Ecosystem
- Vendor marketplace
- Vendor ratings/reviews
- Vendor availability calendars
- Vendor contracts + invoicing

### Phase 9 — Billing & Subscriptions
- Paystack + Flutterwave integration (Laravel HTTP client, no package)
- Multi-currency display
- Exchange rates via Frankfurter API (cached 24hrs)
- Self-serve plan upgrades
- Trial expiry notifications

### Phase 10 — Public Facing & Infrastructure
- Landing page (SEO optimized)
- Public booking pages: `/book/{slug}`
- Public consultation pages: `/consult/{slug}`
- API v1 (public)
- SEO: meta tags, sitemap, structured data

### Phase 11 — Custom Domains & White Labeling
- Custom domain per tenant (CNAME → app.koordli.com)
- DNS verification + auto SSL via Let's Encrypt
- White label tiers: Starter (koordli subdomain), Pro (custom domain + tenant logo), Enterprise (full white label)
- `tenants.subdomain`, `tenants.domain`, `tenants.white_label_enabled` columns needed
- Logo/branding shown conditionally based on plan

---

## PACKAGES INSTALLED
livewire/livewire

stancl/tenancy

spatie/laravel-permission

spatie/laravel-activitylog

spatie/laravel-medialibrary

spatie/laravel-sitemap

spatie/laravel-sluggable

simplesoftwareio/simple-qrcode    ← QR code generation (SVG format)

stevebauman/location

barryvdh/laravel-debugbar (dev)

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

## INFRASTRUCTURE
- Hostinger VPS
- Docker + Traefik for SSL
- n8n_default bridge network
- Self-hosted n8n at bezalelkoncept.site
- Storage link: `php artisan storage:link` — required for RSVP cover images
- `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local` in `.env`

---

## WHEN CONTINUING IN NEW CHAT

1. Paste this entire context document first
2. Claude reads it fully before responding
3. Ask permission at every major step
4. Always ask for file contents before editing — never assume
5. Always give complete ready-to-paste files

**Key URLs:**
- Registration: `http://127.0.0.1:8000/register`
- Tenant login: `http://127.0.0.1:8000/login`
- Platform login: `http://127.0.0.1:8000/platform/login`
- Dashboard: `http://127.0.0.1:8000/dashboard`
- RSVP (public): `http://127.0.0.1:8000/rsvp/{slug}`
- Client login: `http://127.0.0.1:8000/client/login`
- Vendor login: `http://127.0.0.1:8000/vendor/login`