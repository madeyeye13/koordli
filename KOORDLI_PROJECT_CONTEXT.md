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
8. **ALWAYS think about mobile responsiveness** on everything built
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
```
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
```

### Dark Mode Colors
```
Background:  #0C0A09
Surface:     #1C1917
Border:      #292524
Muted:       #A8A29E
Text:        #FAFAF9
```

### Design Rules (absolutely non-negotiable)
- **No box shadows**
- **Minimal border radius** — max 8px
- **No native browser select** — always custom dropdown
- **No alert/confirm** — always toast notifications
- **CSS prefix:** `krd-`
- **Tenant branding via CSS variables:** `--tenant-primary`, `--tenant-accent`
- **Dark mode:** intentionally designed, not inverted
- **Mobile responsive** on everything

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
- Route files: `web.php`, `tenant.php`, `api.php`, `console.php`

### Security Architecture
- Rate limiting on all auth endpoints
- Honeypot on registration form
- CSRF on all forms
- Email verification codes: hashed, 15min expiry, one-time use
- Password requirements: min 8 chars, uppercase + lowercase + number
- UUIDs on all public-facing entities
- Activity log for full audit trail
- Tenant isolation via `BelongsToTenant` trait — zero cross-tenant data leakage

### API Architecture (scaffolded, not yet built)
- `routes/api.php` exists
- `app/Http/Controllers/API/V1/` folder exists
- `app/Http/Resources/` folder exists
- Will be built in a later phase

---

## SEEDED CREDENTIALS

**Platform owner:**
- Email: `admin@koordli.com`
- Password: `Koordli@Admin2026`

---

## MAIL CONFIG

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=info@cenbabusinessaward.com
MAIL_PASSWORD=[password in .env]
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@cenbabusinessaward.com
MAIL_FROM_NAME=Koordli
```

---

## DATABASE

- Engine: MySQL
- Database: `koordli`
- Charset: `utf8mb4_unicode_ci`
- **47+ tables fully migrated**

### Central Tables (no tenant scope)
```
platform_users
tenants
plans
plan_prices
feature_flags
plan_features
tenant_feature_overrides
subscriptions
subscription_invoices
currency_settings
email_verification_codes
```

### Tenant-Scoped Tables (all have tenant_id + BelongsToTenant trait)
```
users
event_types
tenant_event_statuses
tenant_task_categories
tenant_labels
label_assignments
events
event_team
tasks
vendor_categories
vendors
vendor_profiles
vendor_applications
budgets
budget_items
guests
rsvp_forms
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
```

---

## IMPORTANT PRODUCT DECISIONS (locked in)

1. **Tenant self-registration** — visitors register at `/register`
2. **Free trial** — 30 days, configurable per plan by platform owner
3. **Plans fully dynamic** — platform owner creates/edits from dashboard, zero hardcoding
4. **Onboarding flow** — after registration: password change + company branding (lightweight)
5. **Hybrid tenant creation** — both self-serve and manual by platform owner
6. **Platform owner manual plan override** — for support cases, bank transfers, special deals
7. **Event statuses are tenant-defined** — `tenant_event_statuses` table, not hardcoded
8. **Vendor ecosystem** — vendors are first-class users after approval, get their own dashboard
9. **Form engine** — powers booking + consultation forms; RSVP is a separate module that consumes shared field engine
10. **No WhatsApp/SMS in MVP** — email only for notifications
11. **UUIDs on all public-facing entities** — never expose sequential IDs in URLs
12. **Hard deletes only** — activity log covers audit trail
13. **Queued emails always** — never synchronous in production
14. **Nothing hardcoded** — tenants can customize event types, statuses, vendor categories, task categories, labels
15. **Multi-currency** — NGN, GHS, GBP, USD, EUR, KES, ZAR via Paystack + Flutterwave
16. **Exchange rates** — Frankfurter API, cached 24hrs, free
17. **SEO ready from day one** — meta tags, canonical URLs, structured data on public pages

---

## WHAT HAS BEEN BUILT — COMPLETE

### Foundation (Phase 1 — Complete)
- Laravel 12 project
- All packages: Livewire v4, stancl/tenancy, Spatie Permission, Activity Log, Media Library, Sitemap, Sluggable, Stevebauman Location, Debugbar
- Tenancy configured (single DB, session-based resolution)
- Complete folder structure
- All enums: `TaskStatus`, `TaskPriority`, `RSVPStatus`, `RunsheetItemStatus`, `VendorStatus`, `UserType` (Staff/Client/Vendor), `NotificationChannel`, `DocumentableType`
- `BelongsToTenant` trait
- `TenantContext` service (singleton)
- `AppServiceProvider` — registers TenantContext, AuthService, TenantService
- All models created (see database section above)
- All migrations run
- Two auth guards configured (`platform` + `web`)
- Spatie permissions seeded (all roles + permissions)
- Platform user seeded
- Currencies seeded (7 currencies)
- Plans seeded (Free Trial 30d, Starter, Pro, Enterprise)
- Feature flags seeded (13 flags)
- `DefaultTenantSeeder` — seeds event types, statuses, task categories, vendor categories, labels per tenant
- `TenantService` — creates tenant + user + seeds defaults + assigns role in DB transaction

### Design System (Phase 1 — Complete)
- Complete `app.css` with all design tokens
- Dark mode (intentionally designed)
- Responsive CSS (mobile hamburger, sidebar overlay, grid breakpoints, table scroll)
- Logo component with light/dark/auto modes
- `krd-` CSS class system
- Toast CSS ready
- Custom dropdown CSS ready
- Badge system (all variants + dark mode)
- Card system
- Button system
- Table system
- Empty state component
- Typography scale

### Auth Pages (Phase 1 — Complete)
- Tenant login (`/login`) — email, password toggle, remember me
- Platform login (`/platform/login`) — email, password toggle
- Both with split layout (dark left panel, light right panel)
- Mobile: left panel hidden, logo shown at top of form
- Dark mode aware

### Registration Flow (Phase 2 — Complete)
- 4-step registration at `/register`
- Step 1: Account (company name, name, email, password + confirm with toggle, terms, honeypot)
- Step 2: Email verification (6-digit code, individual input boxes with auto-focus, resend)
- Step 3: Plan selection (all active plans shown as cards, cannot skip)
- Step 4: Onboarding questions (heard from, team size, event types — skippable)
- Security: rate limiting, honeypot, code hashing, 15min expiry, 3 attempt lockout
- Email: `SendVerificationCodeJob` (queued), `VerificationCodeMail`, branded HTML template
- Mobile step indicator bar replaces left panel steps

### Platform Dashboard (Phase 2 — Partial)
- Platform layout (`layouts/platform.blade.php`) with Alpine store dark mode
- Platform sidebar with navigation, user info, sign out
- Platform topbar with hamburger (mobile), dark mode toggle, Platform Admin badge
- Mobile sidebar with overlay + close button
- Dashboard view: KPI cards (total companies, active, trial, plans), recent companies table
- `Platform\Dashboard` Livewire component
- `Platform\Tenants\CreateTenant` Livewire component + view
- `Platform\Tenants\TenantList` Livewire component (scaffolded)
- Dark mode: all components dark-mode aware

---

## WHAT IS LEFT TO BUILD

### Phase 2 — In Progress (continue here)

**Immediate next steps (in order):**

1. **Test full registration flow end to end**
   - Register a new company
   - Verify email (check queue worker + Hostinger SMTP)
   - Select plan
   - Complete onboarding
   - Confirm tenant + user created in DB
   - Confirm default data seeded

2. **Tenant onboarding flow** (after first login)
   - Lightweight: change password + upload company logo + set brand colors
   - Cannot skip password change
   - Can skip branding (do later from settings)
   - Route: `/onboarding`

3. **Tenant dashboard scaffold**
   - `layouts/tenant.blade.php` — sidebar + topbar + content
   - Tenant sidebar navigation
   - Tenant topbar (dark mode, user menu, notifications bell)
   - Tenant dashboard Livewire component (basic KPI cards)
   - `resources/views/livewire/tenant/dashboard.blade.php`

4. **Toast notification system**
   - JavaScript toast manager
   - Livewire event listener
   - 4 types: success, error, warning, info
   - Auto-dismiss after 4 seconds
   - Manual dismiss

5. **Custom dropdown Livewire component**
   - No native select anywhere
   - Searchable option
   - Single + multi-select variants
   - `app/Livewire/Shared/Dropdown.php`

6. **Platform tenant management**
   - Tenant list page (search, filter by status, pagination)
   - Tenant detail page (view, edit, suspend, activate)
   - Manual plan assignment
   - Trial extension
   - Send message to tenant

7. **Platform plans management**
   - List all plans
   - Create/edit plan
   - Set features + limits per plan
   - Set pricing per currency
   - Toggle plan active/inactive

8. **Welcome email for manually created tenants**
   - Queued, branded, professional
   - Contains: login URL, credentials, what to expect

### Phase 3 — Core Event Operations
- Event management (create, list, edit, delete)
- Task management + workflows
- Staff management + invitations
- Budget + payment tracking
- Vendor management

### Phase 4 — Client Experience
- Client portal
- Shared files
- Messaging
- Event visibility + timelines

### Phase 5 — RSVP & Guest Experience
- RSVP engine (own module, uses shared Form Field Engine)
- Guest management
- QR tickets + check-in
- Attendance analytics
- Font pairing system for RSVP pages (Spline Sans + serif options)
- Public RSVP pages: `koordli.com/rsvp/{slug}`

### Phase 6 — Runsheet & Live Operations
- Runsheet engine
- Minute-by-minute timelines
- Live event coordination
- Operational status tracking
- Different views: planner/admin, staff, client

### Phase 7 — Form Engine UI
- Booking forms builder
- Consultation forms builder
- External form submission endpoint (Formspree-style)
- `POST https://api.koordli.com/forms/{uuid}`
- WhatsApp redirect after submission
- Embedded form support
- Form analytics

### Phase 8 — Vendor Ecosystem
- Vendor registration portal: `{company}.koordli.com/vendors/register`
- Vendor application + approval flow
- Vendor dashboard (profile, assigned events, runsheet, payments)
- Vendor marketplace (future)
- Vendor ratings/reviews (future)

### Phase 9 — Billing & Subscriptions
- Paystack integration (NGN, GHS, KES, USD)
- Flutterwave integration (all currencies)
- Multi-currency display
- Exchange rates via Frankfurter API (cached 24hrs)
- Self-serve plan upgrades
- Subscription invoices
- Trial expiry notifications + grace period

### Phase 10 — Public Facing
- Landing page (`koordli.com`) — SEO optimized, conversion focused
- Public booking pages: `koordli.com/book/{slug}`
- Public consultation pages: `koordli.com/consult/{slug}`
- SEO: meta tags, sitemap, structured data
- API v1 (public)

---

## FOLDER STRUCTURE (key locations)

```
app/
├── Actions/
├── DTOs/
├── Enums/              ← all PHP 8.1 enums
├── Events/
├── Http/
│   ├── Controllers/API/V1/    ← API only, never UI
│   ├── Middleware/
│   └── Resources/
├── Jobs/               ← SendVerificationCodeJob etc.
├── Livewire/
│   ├── Auth/           ← Register
│   ├── Platform/       ← Platform owner UI
│   ├── Tenant/         ← Tenant staff UI
│   ├── Client/         ← Client portal UI
│   └── Shared/         ← Reusable components
├── Mail/               ← Mailables
├── Models/
│   ├── Central/        ← Tenant, Plan, PlatformUser etc.
│   └── Tenant/         ← All tenant-scoped models
├── Notifications/
├── Observers/
├── Policies/
├── Providers/
├── Services/           ← All business logic
└── Traits/             ← BelongsToTenant

resources/
├── css/
│   └── app.css         ← Complete design system
├── js/
│   └── app.js          ← Alpine store + bootstrap
└── views/
    ├── components/
    │   ├── layout/     ← sidebar, topbar partials
    │   └── ui/         ← logo, dropdown, toast etc.
    ├── emails/         ← Email templates
    ├── layouts/        ← auth, platform, tenant, app
    └── livewire/       ← all Livewire views

public/
├── fonts/              ← Satoshi self-hosted
└── images/             ← logoonblack.png, logoonwhite.png
```

---

## PACKAGES INSTALLED

```
livewire/livewire
stancl/tenancy
spatie/laravel-permission
spatie/laravel-activitylog
spatie/laravel-medialibrary
spatie/laravel-sitemap
spatie/laravel-sluggable
stevebauman/location
barryvdh/laravel-debugbar (dev)
```

**NOT installed (to be added when needed):**
- Paystack — will use Laravel HTTP client directly (no package, Laravel 12 compatibility)
- Flutterwave — same approach
- PDF generation
- QR generation

---

## ROUTES STRUCTURE

```
web.php:
  GET  /                     → redirect to /login
  GET  /login                → Tenant\Auth\Login (Livewire)
  GET  /register             → Auth\Register (Livewire)
  GET  /dashboard            → Tenant\Dashboard (auth.tenant)
  GET  /onboarding           → tenant.onboarding (auth.tenant)

  GET  /platform/login       → Platform\Auth\Login (Livewire)
  GET  /platform/dashboard   → Platform\Dashboard (auth.platform)
  GET  /platform/tenants     → Platform\Tenants\TenantList
  GET  /platform/tenants/create → Platform\Tenants\CreateTenant
  POST /platform/logout
  POST /logout (platform)

api.php:
  Scaffolded — empty, to be built in Phase 10
```

---

## WHEN CONTINUING IN NEW CHAT

Start with:
1. Ask to see relevant files before editing anything
2. Continue from: **Testing the full registration flow**
3. After testing, move to: **Tenant onboarding flow**
4. Then: **Tenant dashboard scaffold**
5. Ask permission at every step

**The registration flow exists at:** `http://127.0.0.1:8000/register`
**Platform dashboard at:** `http://127.0.0.1:8000/platform/dashboard`
**Tenant login at:** `http://127.0.0.1:8000/login`

---

### Phase 2 — COMPLETE ✅

✅ Platform dashboard (sidebar, topbar, KPI cards, companies table)
✅ Registration flow (4 steps: Account → Verify → Plan → Setup)
✅ Email verification (6-digit code, queued, branded email, rate limited)
✅ Plan selection during registration
✅ Tenant + user created on registration
✅ Tenant login (resolves tenant from email, no TenantContext dependency)
✅ Tenant sidebar + topbar (responsive, mobile hamburger, dark mode)
✅ Tenant dashboard (real KPI data: events, tasks, vendors, guests)
✅ Company name showing in sidebar and topbar
✅ Toast notification system (bottom right, 4 types, auto-dismiss)
✅ Custom dropdown Livewire component (searchable, multi-select)
✅ Tenant onboarding flow:
   - Self-registered: skips password step, goes straight to branding
   - Manually created: must change password first, then branding
   - Cannot revisit once completed (middleware enforces this)
   - Branding: logo upload, primary color, accent color, live preview
✅ is_self_registered + onboarding_completed columns on users table
✅ EnsureOnboardingComplete middleware
✅ Dark mode fully working (Alpine store, CSS switching, logo auto-switch)
✅ Responsive (mobile sidebar, hamburger, overlay, grid breakpoints)
✅ Wide screen support (krd-content fills full width)

### Phase 3 — Next (Core Event Operations)
⬜ Event management (create, list, edit, delete)
⬜ Task management + workflows  
⬜ Staff management + invitations
⬜ Budget + payment tracking
⬜ Vendor management

### Still pending from Phase 2
⬜ Platform tenant management (list, view, edit, suspend)
⬜ Platform plans management (create/edit plans from dashboard)
⬜ Welcome email for manually created tenants