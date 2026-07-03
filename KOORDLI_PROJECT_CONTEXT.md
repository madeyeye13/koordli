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
24. **TOGGLE PATTERN** — Hidden checkbox with `wire:model` + Alpine visual div dispatching `change` event — avoids `$wire.set` re-render loop. Exception: when hidden checkbox itself doesn't sync reliably (e.g. required field toggles), use a dedicated `#[Renderless]` Livewire method instead.
25. **BOOLEAN VALIDATION** — Always include `'field' => 'boolean'` in validate() for bool properties or Laravel throws foreach error
26. **RSVP COMPONENT NAME** — Public RSVP Livewire component is `RsvpFormPage` (NOT `RsvpForm`) to avoid collision with `RsvpForm` model
27. **QR CODES** — SimpleSoftwareIO SVG format. Store token string only (`qr_payload`), regenerate on demand. Never store image files.
28. **DARK MODE INLINE STYLES** — Never hardcode `color:#1C1917` in inline styles across tenant blade views — use CSS classes. A global dark mode override exists in `app.css` for legacy inline styles.
29. **ALPINE OWNERSHIP RULE (CRITICAL)** — Any text or state that lives inside an `x-data` block AND needs to change after a Livewire round trip MUST use `x-text` bound to an Alpine variable, NOT `{{ $phpVariable }}`. Livewire's DOM morph is conservative about patching inside `x-data` scopes and can leave server-rendered text stale. Always initialize Alpine's label from server (`label: '{{ $serverValue }}'`) then update via Alpine (`this.label = newVal`) before calling `$wire.*`. Never rely on Blade re-rendering text inside an `x-data` scope after a Wirewire re-render.
30. **SESSION ISOLATION** — Each portal has its own session cookie via `ConfigureSessionByPortal` middleware: `koordli_platform_session`, `koordli_client_session`, `koordli_vendor_session`, `koordli_session` (tenant). Prevents cross-portal session bleeding.
31. **EMAILS ALWAYS QUEUED** — All emails go through Jobs. Never call `Mail::send()` directly in Livewire or controllers. Always dispatch a Job that calls `Mail::send()` inside `handle()`.
32. **PUBLIC HOLIDAY BLOCKING** — Use `App\Helpers\PublicHolidayHelper::getHolidays($countryCode, $year)` — no external package. Country pulled from `tenants.country`.
33. **FORM FIELD REQUIRED TOGGLE** — Hidden checkbox `wire:model` does NOT reliably sync in Livewire 4 for boolean toggles in nested components. Use a dedicated `#[Renderless]` method e.g. `toggleFieldRequired()` that flips the property directly.

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
- **Fraunces** — serif font for RSVP + public form pages (loaded from Google Fonts)
- **Spline Sans** — body font for RSVP + public form pages (loaded from Google Fonts)
- Fraunces replaces Playfair Display / Cormorant Garamond — decision locked in
- Public form pages (booking, consultation) use same Fraunces + Spline Sans as RSVP

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

### Session Isolation
`ConfigureSessionByPortal` middleware (prepended in `bootstrap/app.php`) assigns different session cookie names per route prefix:
- `/platform/*` → `koordli_platform_session`
- `/client/*` → `koordli_client_session`
- `/vendor/*` → `koordli_vendor_session`
- Everything else → `koordli_session`

### Permissions
- Spatie Laravel Permission with `teams: true`
- `team_foreign_key` = `tenant_id`
- Platform roles: `platform_owner`
- Tenant roles: `company_owner`, `coordinator`, `finance`, `operations`, `social_media_manager`, `client`, `vendor`

### Business Logic
- **Never in controllers**
- Services + Actions + DTOs
- UI = 100% Livewire
- API = Controllers only

---

## SIDEBAR NAVIGATION (tenant)
```
Overview:   Dashboard
Operations: Events, Tasks, Vendors, Applications, Budget
Experience: Clients, Guests & RSVP, Runsheet
Business:   Forms & Bookings, Staff, Settings
```

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

---

## DATABASE

### Central Tables
```
platform_users
tenants                         ← country (ISO2), billing_currency, branding JSON, slug
plans                           ← is_featured bool (only one featured at a time = Recommended)
plan_prices
feature_flags
plan_features
tenant_feature_overrides
subscriptions
subscription_invoices
currency_settings
email_verification_codes
clients                         ← password_changed bool
vendor_accounts                 ← password_changed bool, vendor_id FK, vendor_application_id FK
```

### Tenant-Scoped Tables
```
users
event_types
tenant_event_statuses
tenant_task_categories
tenant_labels
label_assignments
events                          ← slug, client_name, client_phone, client_email, agreed_budget,
                                   start_time, end_date, end_time, location, venue, rsvp_enabled bool
event_team
tasks                           ← event_id nullable, vendor_account_id nullable FK → vendor_accounts
vendor_categories
vendors
vendor_event_assignments
vendor_applications             ← available_to_travel bool, status: pending/approved/rejected
budgets
budget_items
client_payments
guests                          ← event_id FK, category, rsvp_status, checked_in, checked_in_at
rsvp_forms                      ← slug, branding JSON (cover_image, cover_image_path, accent_color, bg_color),
                                   questions JSON, ticket_settings JSON, deadline, guest_limit, is_active
rsvp_questions                  ← rsvp_form_id, label, field_type, is_required, options JSON, sort_order
rsvp_responses                  ← rsvp_form_id, guest_id nullable, respondent_name/email/phone,
                                   status, plus_one_count, qr_token, edit_token, response_data JSON, checked_in_at
rsvp_response_answers           ← rsvp_response_id, rsvp_question_id, answer
runsheets                       ← title, date, status: draft/active/completed
runsheet_items                  ← start_time, end_time, assigned_to (users FK), vendor_id (vendors FK),
                                   status, sort_order, depends_on
forms                           ← uuid, tenant_id, name, slug, type (booking|consultation), status,
                                   description, hero_image, hero_image_path, endpoint_token,
                                   tenant_email, tenant_phone, tenant_address,
                                   consultation_type (physical|virtual|both), location,
                                   duration_minutes, whatsapp_enabled, settings
form_fields                     ← form_id, tenant_id, field_type, label, placeholder,
                                   is_required, options JSON, settings, sort_order
form_submissions                ← uuid, tenant_id, form_id, source, status, assigned_to,
                                   ip_address, user_agent, submitted_at, followed_up_at
form_submission_values          ← submission_id, field_id, value
form_redirects                  ← form_id, tenant_id, redirect_type, redirect_url,
                                   whatsapp_number, whatsapp_message
consultation_availabilities     ← tenant_id, form_id, day_of_week (0=Sun..6=Sat),
                                   start_time, end_time, is_active
consultation_bookings           ← uuid, tenant_id, form_id, submission_id,
                                   booking_date, booking_time, consultation_type,
                                   status (pending|confirmed|cancelled),
                                   guest_name, guest_email, guest_phone, meeting_link, notes
```

---

## KEY FILE LOCATIONS

### Livewire Components
```
app/Livewire/Auth/Register.php
app/Livewire/Platform/Dashboard.php
app/Livewire/Platform/Tenants/TenantList.php
app/Livewire/Platform/Tenants/CreateTenant.php          ← edit mode via ?Tenant $tenant param
app/Livewire/Platform/Plans/PlanList.php                ← toggle active, set featured, delete w/ modal
app/Livewire/Platform/Plans/CreatePlan.php
app/Livewire/Tenant/Dashboard.php
app/Livewire/Tenant/Events/EventList.php
app/Livewire/Tenant/Events/CreateEvent.php              ← rsvp_enabled bool, hidden checkbox toggle
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
app/Livewire/Tenant/Guests/GuestList.php               ← per event, custom Alpine dropdowns for filters
app/Livewire/Tenant/Rsvp/RsvpManager.php               ← WithFileUploads, 4 tabs: Setup/Questions/Branding/Responses
app/Livewire/Tenant/Runsheet/RunsheetManager.php        ← timeline view, item form, status quick-change, reorder
app/Livewire/Tenant/Forms/FormList.php                  ← booking + consultation forms list
app/Livewire/Tenant/Forms/CreateForm.php                ← WithFileUploads, 5 tabs: Details/Fields/AfterSubmission/Availability/Embed
app/Livewire/Tenant/Forms/FormSubmissions.php           ← submissions table + consultation bookings
app/Livewire/Client/Auth/Login.php
app/Livewire/Client/Dashboard.php                       ← RSVP read-only stats + link
app/Livewire/Client/Onboarding.php
app/Livewire/Vendor/Auth/Login.php
app/Livewire/Vendor/Dashboard.php                       ← events + tasks + runsheet summary strip
app/Livewire/Vendor/Profile.php
app/Livewire/Vendor/Onboarding.php
app/Livewire/Vendor/VendorRunsheet.php                  ← dedicated runsheet page, step status updates, delay modal
app/Livewire/Public/RsvpFormPage.php                    ← NOT RsvpForm (model name collision)
app/Livewire/Public/RsvpEdit.php
app/Livewire/Public/VendorRegister.php
app/Livewire/Public/BookingForm.php                     ← public booking form, left/right split
app/Livewire/Public/ConsultationForm.php                ← public consultation form, 2-step (date/time → details)
```

### Models
```
app/Models/Central/Tenant.php
app/Models/Central/PlatformUser.php
app/Models/Central/Plan.php                             ← is_featured bool
app/Models/Central/Client.php                           ← password_changed bool
app/Models/Central/VendorAccount.php                    ← password_changed bool, vendor_id FK
app/Models/Tenant/Event.php                             ← rsvp_enabled bool cast, rsvpForm() HasOne
app/Models/Tenant/Task.php                              ← vendor_account_id nullable FK, assigneeName() helper
app/Models/Tenant/Vendor.php
app/Models/Tenant/VendorApplication.php                 ← available_to_travel bool
app/Models/Tenant/VendorEventAssignment.php
app/Models/Tenant/Budget.php
app/Models/Tenant/BudgetItem.php
app/Models/Tenant/ClientPayment.php
app/Models/Tenant/Guest.php                             ← event_id FK, statusBadgeClass() helper
app/Models/Tenant/RsvpForm.php                          ← publicUrl(), questions() HasMany, totalAttendees()
app/Models/Tenant/RsvpQuestion.php                      ← fieldTypeLabel(), hasOptions()
app/Models/Tenant/RsvpResponse.php                      ← editUrl(), totalAttendees(), generateQrToken()
app/Models/Tenant/RsvpResponseAnswer.php
app/Models/Tenant/Runsheet.php                          ← event FK, items() HasMany
app/Models/Tenant/RunsheetItem.php                      ← RunsheetItemStatus enum cast, statusColor(), statusLabel()
app/Models/Tenant/Form.php                              ← publicUrl(), endpointUrl(), embedCode(), fields/submissions/redirect/availabilities/bookings
app/Models/Tenant/FormField.php                         ← fieldTypeLabel(), hasOptions()
app/Models/Tenant/FormSubmission.php                    ← values() HasMany, getValueFor()
app/Models/Tenant/FormSubmissionValue.php
app/Models/Tenant/FormRedirect.php                      ← whatsappUrl(array $data)
app/Models/Tenant/ConsultationAvailability.php          ← dayName()
app/Models/Tenant/ConsultationBooking.php
```

### Helpers / Services / Traits
```
app/Helpers/CurrencyHelper.php                          ← symbol(), format(), forTenant(), fromCountry(), countries()
app/Helpers/PublicHolidayHelper.php                     ← getHolidays($countryCode, $year), isHoliday($date, $countryCode)
                                                           Supports: NG, GH, KE, ZA, GB, US + Easter calc
                                                           No external package — hardcoded per country
app/Http/Middleware/ConfigureSessionByPortal.php        ← isolates session cookies per portal
app/Traits/WithToast.php                                ← toastSuccess(), toastError(), toastWarning()
app/Traits/BelongsToTenant.php
app/Services/TenantService.php
app/Services/FeatureGateService.php
```

### API Controllers
```
app/Http/Controllers/Api/FormSubmissionController.php   ← POST /api/forms/{token}/submit (booking)
app/Http/Controllers/Api/ConsultationSubmissionController.php ← POST /api/consult/{token}/submit
```

### Jobs (all queued)
```
SendVerificationCodeJob
SendWelcomeEmailJob
SendStaffInviteJob
SendClientInviteJob
SendVendorInviteJob
SendVendorApprovalJob
SendVendorApplicationReceivedJob
SendVendorAssignedJob
SendOutstandingReminderJob
SendRsvpConfirmationJob                                 ← sends QR code SVG inline in email
SendRsvpNotificationJob                                 ← notifies planner + client on each response
SendFormSubmissionNotificationJob                       ← notifies tenant on booking/consultation submission
SendFormSubmissionConfirmationJob                       ← confirms to guest on booking/consultation submission
```

### Email Views
```
resources/views/emails/verification-code.blade.php
resources/views/emails/welcome.blade.php
resources/views/emails/staff-invite.blade.php
resources/views/emails/client-invite.blade.php
resources/views/emails/vendor-invite.blade.php
resources/views/emails/vendor-approval.blade.php
resources/views/emails/vendor-application-received.blade.php
resources/views/emails/vendor-assigned.blade.php
resources/views/emails/outstanding-reminder.blade.php
resources/views/emails/rsvp-confirmation.blade.php     ← QR SVG inline, download ticket link
resources/views/emails/rsvp-notification.blade.php     ← sent to planner + client
resources/views/emails/form-submission-notification.blade.php ← sent to tenant on form submission
resources/views/emails/form-submission-confirmation.blade.php ← sent to guest on form submission
```

### Layouts
```
resources/views/layouts/auth.blade.php
resources/views/layouts/platform.blade.php
resources/views/layouts/tenant.blade.php
resources/views/layouts/client.blade.php
resources/views/layouts/vendor.blade.php
resources/views/layouts/rsvp.blade.php                 ← bare layout, Fraunces + Spline Sans Google Fonts
                                                           Also used by public booking + consultation forms
```

### Public Views
```
resources/views/livewire/public/rsvp-form.blade.php           ← left/right split, Fraunces serif, CSS vars
resources/views/livewire/public/rsvp-edit.blade.php           ← edit via secure token
resources/views/public/rsvp-ticket-pdf.blade.php              ← print/save as PDF page with QR
resources/views/livewire/public/booking-form.blade.php        ← left/right split, Fraunces + Spline Sans
resources/views/livewire/public/consultation-form.blade.php   ← 2-step: date/time → details, custom calendar
```

### Tenant Form Views
```
resources/views/livewire/tenant/forms/form-list.blade.php
resources/views/livewire/tenant/forms/create-form.blade.php   ← 5 tabs (pure @if server-driven, no x-show)
resources/views/livewire/tenant/forms/form-submissions.blade.php
```

---

## ROUTES (complete current state)

### Public
```
/rsvp/{slug}                          → Public\RsvpFormPage
/rsvp/{slug}/edit/{token}             → Public\RsvpEdit
/rsvp/ticket/{token}                  → route closure → public.rsvp-ticket-pdf view
/vendors/{slug}/register              → Public\VendorRegister
/book/{slug}                          → Public\BookingForm
/consult/{slug}                       → Public\ConsultationForm
```

### API
```
POST /api/forms/{token}/submit        → Api\FormSubmissionController@submit      (booking endpoint)
POST /api/consult/{token}/submit      → Api\ConsultationSubmissionController@submit (consultation endpoint)
```

### Tenant Auth
```
/login                                → Tenant\Auth\Login
/register                             → Auth\Register
/logout (POST)
```

### Tenant Authenticated
```
/dashboard                            → Tenant\Dashboard
/onboarding                           → Tenant\Onboarding
/events                               → Tenant\Events\EventList
/events/create                        → Tenant\Events\CreateEvent
/events/{slug}/edit                   → Tenant\Events\CreateEvent
/events/{slug}                        → Tenant\Events\EventDetail
/events/{slug}/budget                 → Tenant\Budget\EventBudget
/events/{slug}/guests                 → Tenant\Guests\GuestList
/events/{slug}/rsvp                   → Tenant\Rsvp\RsvpManager
/events/{slug}/runsheet               → Tenant\Runsheet\RunsheetManager
/tasks                                → Tenant\Tasks\TaskCenter
/tasks/create                         → Tenant\Tasks\CreateTask
/tasks/{id}/edit                      → Tenant\Tasks\CreateTask
/staff                                → Tenant\Staff\StaffList
/staff/invite                         → Tenant\Staff\InviteStaff
/staff/{id}/edit                      → Tenant\Staff\InviteStaff
/budget                               → Tenant\Budget\BudgetOverview
/vendors                              → Tenant\Vendors\VendorDirectory
/vendors/create                       → Tenant\Vendors\CreateVendor
/vendors/{id}/edit                    → Tenant\Vendors\CreateVendor
/vendors/{id}                         → Tenant\Vendors\VendorDetail
/vendor-applications                  → Tenant\Vendors\VendorApplications
/forms                                → Tenant\Forms\FormList
/forms/create                         → Tenant\Forms\CreateForm
/forms/{id}/edit                      → Tenant\Forms\CreateForm
/forms/{id}/submissions               → Tenant\Forms\FormSubmissions
```

### Client Portal
```
/client/login                         → Client\Auth\Login
/client/onboarding                    → Client\Onboarding
/client/dashboard                     → Client\Dashboard
/client/logout (POST)
```

### Vendor Portal
```
/vendor/login                         → Vendor\Auth\Login
/vendor/onboarding                    → Vendor\Onboarding
/vendor/dashboard                     → Vendor\Dashboard
/vendor/profile                       → Vendor\Profile
/vendor/runsheet                      → Vendor\VendorRunsheet
/vendor/logout (POST)
```

### Platform
```
/platform/login                       → Platform\Auth\Login
/platform/dashboard                   → Platform\Dashboard
/platform/tenants                     → Platform\Tenants\TenantList
/platform/tenants/create              → Platform\Tenants\CreateTenant
/platform/tenants/{tenant}/edit       → Platform\Tenants\CreateTenant
/platform/plans                       → Platform\Plans\PlanList
/platform/plans/create                → Platform\Plans\CreatePlan
/platform/plans/{plan}/edit           → Platform\Plans\CreatePlan
/platform/logout (POST)
```

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

## RUNSHEET ARCHITECTURE (Phase 6 — complete)

### Key Decisions
- One runsheet per event
- Items have: title, description, start_time, end_time, assigned_to (staff), vendor_id, status, notes, sort_order
- Status: `pending` → `in_progress` → `done` / `delayed` (RunsheetItemStatus enum)
- Items reorderable via ↑↓ buttons (sort_order swap)
- Status quick-change inline via Alpine dropdown on each item
- Progress bar on stats strip shows % complete (done/total)
- Vendor runsheet page (`/vendor/runsheet`) — dedicated page for vendors to self-report status
- Vendor status updates visible to planner in real time (auto-refresh `wire:poll.60s`)
- Delay modal: when vendor marks Delayed, they add a reason note
- Planner's RunsheetManager also polls every 60s

### Runsheet Enums
- `App\Enums\RunsheetItemStatus` — `Pending`, `InProgress`, `Done`, `Delayed`
- Has `label()` and `color()` methods (color returns string like 'green', not hex)
- In blade: use `match($item->status)` with full enum class for hex colors

---

## FORM ENGINE ARCHITECTURE (Phase 7 — complete)

### Two Form Types
**Booking Form** (`type = 'booking'`)
- Custom field builder (9 field types: text, textarea, email, phone, number, dropdown, radio, checkbox, date)
- Public URL: `/book/{slug}`
- External endpoint: `POST /api/forms/{token}/submit` (Formspree-style)
- Embed code: `<iframe>` snippet
- After submission: optional WhatsApp redirect, URL redirect, or thank you message
- Emails: tenant notification + guest confirmation (both queued)

**Consultation Form** (`type = 'consultation'`)
- All booking form features PLUS:
- Weekly availability calendar (tenant sets available days + time range)
- Duration setting (e.g. 60 minutes per slot)
- Public page: 2-step flow (Step 1: type + date + time → Step 2: your details)
- Custom calendar: past dates blocked, public holidays blocked, fully booked slots blocked
- Physical / Virtual / Both consultation type setting
- External endpoint: `POST /api/consult/{token}/submit`
- Embed iframe supported
- Consultation bookings tracked in `consultation_bookings` table
- Planner can confirm/cancel bookings from FormSubmissions page

### Public Form Page Design (both types)
- Left panel: form name (Fraunces), description, tenant contact info (email, phone, address), hero image
- Right panel: form fields (Spline Sans)
- Same layout pattern as RSVP public pages
- Uses `layouts/rsvp.blade.php` layout (Fraunces + Spline Sans loaded)

### Consultation Calendar Rules
- Past dates: not clickable
- Public holidays: blocked (red, uses `PublicHolidayHelper`)
- Unavailable days (not in availability): grey, not clickable
- Fully booked dates: amber, not clickable
- Selected date: dark background `#1C1917` — applied via inline style directly, NOT Alpine class binding (avoids morph issue)
- Selected time slot: same — inline style, not Alpine

### After Submission Options
- `none` — show thank you message
- `url` — redirect to custom URL
- `whatsapp` — redirect to WhatsApp with pre-filled message (`{name}` placeholder supported)

### External Endpoint (Formspree-style)
- Each form has a unique `endpoint_token`
- HTML forms POST to `/api/forms/{token}/submit` (booking) or `/api/consult/{token}/submit` (consultation)
- Field values matched by label name or `field_{id}` key
- Returns JSON `{ success, message, whatsapp_url? }`
- CSRF not required on API routes

### Form Builder Tabs
1. **Details** — name, type, status, description, hero image, contact info, consultation settings
2. **Fields** — custom field builder (add/edit/delete/reorder)
3. **After Submission** — redirect type + settings
4. **Availability** — (consultation only) weekly day/time availability
5. **Embed & Share** — public link, external endpoint, embed code

### Form Builder Critical Patterns
- Tabs use pure `@if($activeTab === 'tab')` Blade — NO `x-show` — because `setType()` causes re-render and `x-show` with Alpine tab state would reset
- Tab buttons call `wire:click="setTab(...)"` — server tracks `$activeTab`
- `setTab()` is `#[Renderless]` for instant response — no re-render
- Type dropdown uses Alpine ownership: `label: '{{ $type === 'consultation' ? 'Consultation' : 'Booking' }}'` init + `x-text="label"` + `this.label = val` in `pick()` before `$wire.setType(val)`
- Required field toggle uses `$wire.toggleFieldRequired()` method (NOT hidden checkbox) — hidden checkbox `wire:model` doesn't reliably sync in Livewire 4

---

## PLATFORM MANAGEMENT (complete)

### Tenant Management
- List with search + status filter (custom Alpine dropdown)
- View panel (slide-in) showing owner name, email, country, plan, status, created date
- Edit: `/platform/tenants/{tenant}/edit` — can edit name, owner name, currency, country, plan, status
- Suspend / Activate with confirmation modal
- Desktop table + mobile cards

### Plans Management
- Grid view of all plans
- `is_featured` bool — mark one plan as "Recommended" (auto-unmarks others)
- Featured plan shown with ⭐ ribbon + violet border in grid
- Featured plan sorts first + shown with badge during registration
- If only one active plan exists → auto-selected on registration step 3
- Delete with modal — blocked if tenants are on the plan
- Toggle active/inactive
- Edit via existing `CreatePlan` component

---

## VENDOR RUNSHEET (complete)

### Vendor Runsheet Page (`/vendor/runsheet`)
- Dedicated page — NOT just dashboard section
- Groups items by event
- Shows: title, description, time range, status badge
- Status action buttons (large, tap-friendly for tablet/desktop/mobile):
  - ▶ In Progress
  - ✓ Mark Done
  - ⚠ Delayed (triggers delay note modal)
- Delay modal: optional reason note saved to `runsheet_items.notes`
- Done items show "Undo" link to revert to pending
- Auto-refresh: `wire:poll.60s` on page
- Dashboard shows summary strip (4 stat cards + progress bar) + "Open Runsheet" link

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
```
NGN → ₦  (Nigeria)
GHS → ₵  (Ghana)
GBP → £  (United Kingdom)
USD → $  (US, CA, AU, AE, SA, IN, SG)
EUR → €  (DE, FR, IT, ES, NL, BE, PT, AT, FI, IE)
KES → KSh (Kenya)
ZAR → R  (South Africa)
```

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
- On public pages (no Livewire layout): use `KrdToast.success('msg')` directly

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

**Exception:** For simple boolean toggles where hidden checkbox doesn't sync (e.g. field required toggle in form builder), use a dedicated renderless method:
```php
#[\Livewire\Attributes\Renderless]
public function toggleFieldRequired(): void
{
    $this->f_required = !$this->f_required;
}
```
```html
<div x-on:click="on = !on; $wire.toggleFieldRequired()">
```

---

## ALPINE OWNERSHIP PATTERN (CRITICAL — READ BEFORE EVERY DROPDOWN)

When any display value inside `x-data` needs to update after a Livewire call:

```js
// CORRECT — Alpine owns the label
x-data="{
    open: false,
    label: '{{ $serverValue }}',        // initialize from server once
    pick(val) {
        this.label = 'New Label';        // Alpine updates immediately
        this.open  = false;
        $wire.someMethod(val);           // Livewire syncs in background
    }
}"
```
```blade
<span x-text="label"></span>  {{-- Alpine owns this, NOT Blade --}}
```

```js
// WRONG — Blade text inside x-data goes stale after morph
x-data="{ open: false }"
```
```blade
<span>{{ $serverValue }}</span>  {{-- WILL GO STALE --}}
```

**Apply this to:** dropdown trigger labels, status badges, any display text inside `x-data` that changes after `$wire.*` calls.

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
- Plan selection (respects `is_featured` — featured plan shows first + Recommended badge)
- Auto-select if only one active plan exists
- Onboarding flow
- Platform dashboard (KPI cards, companies table)
- Platform tenant management — full CRUD (create, view panel, edit, suspend, activate)
- Platform plans management — full CRUD (create, edit, delete w/ modal, toggle active, mark featured)
- Welcome email for manually created tenants
- Session isolation per portal via `ConfigureSessionByPortal` middleware

### Phase 3 — Core Event Operations ✅
- Event CRUD (slug-based URLs, status management)
- Task management (4 views, instant status picker)
- Staff management (invite, temp password, activate/deactivate)
- Budget tracking (event budget + overview, client payments, outstanding reminder)
- Vendor management (directory, assignments, preferred toggle, grid/list)
- Dashboard (real KPIs, recent events, pending tasks, budget summary)
- Currency system fully integrated

### Phase 4 — Client + Vendor Portals ✅
- **Client portal:** login, forced onboarding, dashboard (event view, payment history, progress bar, RSVP read-only stats), invite from event detail, duplicate invite protection
- **Vendor portal:** login, forced onboarding, dashboard (events + tasks + runsheet summary), profile, public registration, application review, auto-create account, assignment notifications
- **Guest management:** per-event (add/edit/delete, RSVP status, check-in, expected count, progress bar)
- **RSVP toggle on events:** `rsvp_enabled` bool, hidden checkbox toggle pattern
- **Vendor tasks:** `vendor_account_id` nullable FK on tasks
- All vendor email templates

### Phase 5 — RSVP & Guest Experience ✅
- RSVP Manager (4 tabs: Setup, Questions, Branding, Responses)
- Custom question builder (10 field types)
- Public RSVP page — Fraunces + Spline Sans, left/right split
- QR code generation (SimpleSoftwareIO SVG)
- Guest edit via secure edit_token
- Ticket download (print/PDF)
- Confirmation + notification emails (queued)
- Branding tab: cover image, accent color, bg color
- Dark mode global CSS override

### Phase 6 — Runsheet & Live Operations ✅
- Runsheet manager per event (`/events/{slug}/runsheet`)
- Settings tab + Timeline tab
- Timeline view with time column, colored dots, connecting line, progress bar
- Add/edit/delete/reorder items
- Assign to staff or vendor
- Instant status quick-change (inline dropdown)
- Auto-refresh `wire:poll.60s` on planner side
- **Vendor Runsheet page** (`/vendor/runsheet`):
  - Grouped by event
  - Large tap-friendly status buttons (In Progress / Mark Done / Delayed)
  - Delay note modal
  - Undo done
  - Auto-refresh `wire:poll.60s`
  - Dashboard summary strip with progress bar

### Phase 7 — Form Engine ✅
- **Booking forms:** custom field builder, public page (`/book/{slug}`), external endpoint, embed code, WhatsApp/URL/none redirect, email notifications
- **Consultation forms:** all booking features + weekly availability calendar, duration, 2-step public page, custom calendar (holiday/booked/unavailable blocking), consultation type (physical/virtual/both), booking management
- **Public pages:** left/right split, Fraunces + Spline Sans, hero image, tenant contact info
- **External endpoint** (Formspree-style): `POST /api/forms/{token}/submit` and `POST /api/consult/{token}/submit`
- **Embed iframe** for both form types
- **Form Builder** (5 tabs): Details, Fields, After Submission, Availability (consultation only), Embed & Share
- **Submissions dashboard** with booking status management (confirm/cancel)
- All emails queued via Jobs (tenant notification + guest confirmation)
- `PublicHolidayHelper` — no external package, hardcoded per country, Easter calculation

---

## PENDING

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
```
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
```

Note: `yasumi/yasumi` was NOT installed (unavailable). Public holiday logic is handled by `App\Helpers\PublicHolidayHelper` — no package needed.

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
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@cenbabusinessaward.com
MAIL_FROM_NAME=Koordli
```

---

## INFRASTRUCTURE
- Hostinger VPS
- Docker + Traefik for SSL
- n8n_default bridge network
- Self-hosted n8n at bezalelkoncept.site
- Storage link: `php artisan storage:link` — required for RSVP cover images + form hero images
- `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local` in `.env`
- `SESSION_COOKIE=koordli_session` in `.env`

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
- Booking form (public): `http://127.0.0.1:8000/book/{slug}`
- Consultation form (public): `http://127.0.0.1:8000/consult/{slug}`
- Client login: `http://127.0.0.1:8000/client/login`
- Vendor login: `http://127.0.0.1:8000/vendor/login`
- Vendor runsheet: `http://127.0.0.1:8000/vendor/runsheet`