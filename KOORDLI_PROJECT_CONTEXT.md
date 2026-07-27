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
24. **TOGGLE PATTERN** — Hidden checkbox with `wire:model` + Alpine visual div dispatching `change` event — avoids `$wire.set` re-render loop. Exception: when hidden checkbox itself doesn't sync reliably (e.g. required field toggles, gateway enable toggles), use a dedicated `#[Renderless]` Livewire method instead.
25. **BOOLEAN VALIDATION** — Always include `'field' => 'boolean'` in validate() for bool properties or Laravel throws foreach error
26. **RSVP COMPONENT NAME** — Public RSVP Livewire component is `RsvpFormPage` (NOT `RsvpForm`) to avoid collision with `RsvpForm` model
27. **QR CODES** — SimpleSoftwareIO SVG format. Store token string only (`qr_payload`), regenerate on demand. Never store image files.
28. **DARK MODE INLINE STYLES** — Never hardcode `color:#1C1917` in inline styles across tenant blade views — use CSS classes (e.g. `krd-btn-primary`) so dark mode adapts automatically. A global dark mode override exists in `app.css` for legacy inline styles.
29. **ALPINE OWNERSHIP RULE (CRITICAL)** — Any text or state that lives inside an `x-data` block AND needs to change after a Livewire round trip MUST use `x-text` bound to an Alpine variable, NOT `{{ $phpVariable }}`. Livewire's DOM morph is conservative about patching inside `x-data` scopes and can leave server-rendered text stale. Always initialize Alpine's label from server (`label: '{{ $serverValue }}'`) then update via Alpine (`this.label = newVal`) before calling `$wire.*`. Never rely on Blade re-rendering text inside an `x-data` scope after a Livewire re-render. When multiple related toggles/values live together (e.g. two gateway toggles), put them in ONE shared `x-data` scope, not separate ones — separate scopes can visually interfere with each other during re-renders.
30. **SESSION ISOLATION** — Each portal has its own session cookie via `ConfigureSessionByPortal` middleware: `koordli_platform_session`, `koordli_client_session`, `koordli_vendor_session`, `koordli_session` (tenant). Prevents cross-portal session bleeding.
31. **EMAILS ALWAYS QUEUED** — All emails go through Jobs. Never call `Mail::send()` directly in Livewire or controllers. Always dispatch a Job that calls `Mail::send()` inside `handle()`.
32. **PUBLIC HOLIDAY BLOCKING** — Use `App\Helpers\PublicHolidayHelper::getHolidays($countryCode, $year)` — no external package. Country pulled from `tenants.country`.
33. **FORM FIELD REQUIRED TOGGLE** — Hidden checkbox `wire:model` does NOT reliably sync in Livewire 4 for boolean toggles in nested components. Use a dedicated `#[Renderless]` method e.g. `toggleFieldRequired()` that flips the property directly.
34. **NEW MIGRATIONS — ALWAYS VERIFY AUTO_INCREMENT** — When creating a new table's `id` column, always use `$table->id()`, never `$table->unsignedBigInteger('id')->primary()` manually — the latter creates a primary key WITHOUT auto_increment and every insert fails with "Field 'id' doesn't have a default value". This bug hit `subscriptions`, `subscription_invoices`, and `plan_prices` in Phase 9 and required `ALTER TABLE x MODIFY id BIGINT UNSIGNED AUTO_INCREMENT` migrations to fix. When debugging a mysterious insert failure on a table, always check `DB::select('SHOW COLUMNS FROM {table} WHERE Field = "id"')` for `"Extra": "auto_increment"` first.
35. **LIVEWIRE V4 WRITE-BLOCKING (E.G. FOR BILLING LOCKOUT) MUST USE COMPONENT HOOKS, NOT HTTP MIDDLEWARE** — Livewire v4 sends all component method calls to `/livewire/update` (or similar internal path) which is OUTSIDE named route groups, so normal route middleware (`Route::middleware([...])->group(...)`) never intercepts Livewire POST calls — only the initial page GET. To block specific Livewire actions (e.g. locked-tenant write prevention), register a `Livewire\ComponentHook` via `Livewire::componentHook(HookClass::class)` and override `call($method, $params, $returnEarly, $metadata, $componentContext)`. Call `$returnEarly(null)` to stop the method from executing. **Critical:** register the hook inside `AppServiceProvider::register()`, NOT `boot()` — `ComponentHookRegistry::boot()` runs during `LivewireServiceProvider::boot()`, and if your hook is registered in your own `boot()` it may run too late and be silently ignored, since Laravel calls `register()` on all providers before `boot()` on any.
36. **NEVER PASS `{{ $phpString }}` DIRECTLY INTO A JS FUNCTION CALL INSIDE AN HTML ATTRIBUTE** — e.g. `x-on:click="select('{{ $vendor->name }}', {{ $vendor->id }})"`. Blade's `{{ }}` HTML-escapes the value (turning `&` into `&amp;`), and when that escaped string is later re-displayed via Alpine's `x-text`, it can end up double-escaped and show literal `&amp;` on screen instead of `&`. This bit names like "Chukwuemeka & Adaeze Wedding" across dropdown option click-handlers (Contracts, Invoices, Forms create pages, etc.). **Fix:** use Laravel's `@js()` Blade directive instead, which properly JSON-encodes the value for a JavaScript context: `x-on:click="select(@js($vendor->name), {{ $vendor->id }})"`. Apply this to every dropdown option's JS-string argument that could contain `&`, `'`, `"`, or other special characters (vendor names, event names, contract titles, etc.) — plain `'{{ }}'` interpolation into a JS string literal is never safe for user-entered text.
37. **`x-ui.dropdown` COMPONENT ALREADY RENDERS ITS OWN PLACEHOLDER OPTION** — the shared `resources/views/components/ui/dropdown.blade.php` component auto-renders a `@if($placeholder)` option at the top of its menu that calls `clear()`. Never ALSO manually add your own "None" / "No contract" / "General (no event)" option inside the slot — this creates a duplicate entry in the dropdown. Only pass the desired text via the `placeholder` prop; don't repeat it in the slot content.
38. **ROUTE PARAMETER NAME MUST MATCH `mount()` PARAMETER NAME EXACTLY** — Livewire binds route parameters to `mount()` by NAME, not position. A route defined as `Route::get('/plans/{plan}/edit', ...)` will NEVER populate a `mount(?int $planId = null)` parameter — Laravel silently passes `null` since `plan` ≠ `planId`, with no error thrown. This caused Plan edit to silently behave like Create (bug found and fixed in Phase 9/10 boundary). Whenever a Livewire full-page component's edit route "doesn't load existing data," check this FIRST before assuming the component's own logic is wrong.
39. **ALWAYS VERIFY `$fillable` WHEN ADDING NEW COLUMNS TO AN EXISTING MODEL** — Laravel's mass-assignment silently DROPS any column not listed in `$fillable` during `create()`/`update()` — no error, no exception, just silent data loss. This caused a real bug: `Tenant.php`'s `$fillable` was missing `billing_currency`, `detected_country`, and ALL SIX new domain columns (`subdomain`, `custom_domain`, `domain_verification_token`, `domain_verified_at`, `domain_last_checked_at`, `domain_status`) added in Phase 11 — Domain Settings showed "Success" toasts but nothing actually persisted, and fields went blank on every page refresh. Whenever a migration adds columns to an existing table, ALWAYS check the model's `$fillable` array in the same pass — don't assume it auto-updates.
40. **WHITE-LABEL / TENANT-BRANDING VALUES MUST BE PASSED AS EXPLICIT PARAMETERS THROUGH THE FULL Job → Mailable → Blade CHAIN** — Mailables/Jobs are constructed with plain primitives (strings, bools), not model instances, so there's no way to "look up" white-label status inside a blade template unless the boolean was computed at the ORIGINAL dispatch call site (where `auth()->user()->tenant` or an equivalent tenant model is actually available) and threaded through as a new constructor parameter on both the Job and the Mailable. Pattern used throughout Phase 11: add `public readonly bool $whiteLabel = false` as the LAST constructor parameter (default value keeps it 100% backward-compatible with any other dispatch site calling the same Job), pass it through `handle()` into the Mailable's constructor, then in the blade template swap `Koordli` text/logo for the tenant's own name based on `{{ $whiteLabel ? $companyName : 'Koordli' }}`. Compute the boolean at the dispatch site via `app(\App\Services\FeatureGateService::class)->canAccess($tenant, 'white_label')`.
41. **SPLIT KOORDLI-TO-TENANT EMAILS FROM TENANT-TO-CUSTOMER EMAILS WHEN APPLYING WHITE LABEL** — Not every email should respect the `white_label` flag. Emails Koordli sends TO the tenant about their own account (welcome, subscription activated/expired/reminder, verification code, staff/company internal notifications like form-submission-notification or contract-signed-notification) should ALWAYS show Koordli branding — the tenant IS Koordli's customer here, same as a Shopify merchant always sees "Shopify" in their own admin regardless of their storefront's white-label status. Only emails going to the TENANT'S OWN clients/vendors/guests/applicants (client-invite, staff-invite, vendor-invite, vendor-approval, vendor-application-received, vendor-assigned, vendor-contract, form-submission-confirmation, rsvp-confirmation, outstanding-reminder) should swap to the tenant's branding when `white_label` is enabled.
42. **DOMAIN-RESOLVED TENANT (PRE-LOGIN) vs SESSION-RESOLVED TENANT (POST-LOGIN) ARE TWO DIFFERENT MECHANISMS FOR THE SAME GOAL** — Before a client/vendor logs in, the ONLY way to know which tenant a visitor belongs to is the URL itself (subdomain or custom domain) via the `tenant.byDomain` middleware populating `app('resolvedTenant')`. On the plain shared `koordli.com/client/login` URL with no subdomain, there is no way to know the tenant — branding correctly stays generic Koordli in that case. AFTER login, the tenant is always known directly from the authenticated user's own relationship (`auth('client')->user()->tenant` / `auth('vendor')->user()->tenant`) regardless of which URL was used to log in — this session-based lookup should be used for the post-login dashboard/sidebar, NOT `resolvedTenant`, since a client could have logged in via the generic URL yet still needs their tenant's branding on their own dashboard afterward. The `<x-ui.portal-logo>` component encapsulates this: accepts an explicit `:tenant="..."` prop for the post-login case, and falls back to `app('resolvedTenant')` only when no explicit tenant is passed (the pre-login case).

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
- Payments: Laravel `Http` facade (Guzzle, bundled with Laravel — no SDK packages) for Paystack + Flutterwave API calls

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
- **Use `krd-btn-primary` / `krd-btn-secondary` classes instead of hardcoded inline colors on buttons** — inline `background:#1C1917` etc. breaks dark mode (buttons become invisible against dark surfaces)

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
`auth.platform`, `auth.tenant`, `auth.client`, `auth.vendor`, `tenant.resolve`, `onboarding.check`, `vendor.password.check`, `client.password.check`, `tenant.active`

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
Operations: Events, Tasks, Vendors, Applications, Budget, Contracts, Vendor Invoices
Experience: Clients, Guests & RSVP, Runsheet
Business:   Forms & Bookings, Billing, Domain Settings, Staff, Settings
```

## SIDEBAR NAVIGATION (platform)
```
Overview:   Dashboard
Management: Companies, Plans, Billing Config, Site Settings
System:     Settings
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
tenants                         ← country (ISO2), billing_currency, branding JSON, slug,
                                   subdomain, custom_domain, domain_verification_token,
                                   domain_verified_at, domain_last_checked_at, domain_status
plans                           ← is_featured bool, annual_discount_percent, allowed_cycles JSON
plan_prices                     ← currency, amount, billing_cycle, amount_with_charges, annual_discount_percent
feature_flags                   ← includes custom_subdomain, custom_domain, white_label (Phase 11)
plan_features
tenant_feature_overrides
subscriptions                   ← expires_at, grace_until, billing_cycle, reminder_14_sent, reminder_3_sent
subscription_invoices           ← uuid, amount_ngn, exchange_rate, billing_cycle
currency_settings
gateway_charges                 ← gateway, region, percentage, fixed_fee, cap, absorb, is_active, description
billing_settings                ← key/value config store (grace period, reminder days, API keys, enabled gateways)
platform_settings               ← key/value store (site_name, site_tagline, site_favicon) — landing page branding,
                                   editable at /platform/site-settings, cached via PlatformSetting::get()/set()
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
vendor_unavailable_dates        ← tenant_id, vendor_id, date_from, date_to,
                                   reason (personal|vacation|holiday|other), notes
                                   — MANAGED BY VENDOR (their own portal), planner sees read-only
vendor_reviews                  ← tenant_id, vendor_id, vendor_event_assignment_id, event_id,
                                   reviewer_type (planner|client), reviewer_id nullable,
                                   professionalism/communication/punctuality/quality_of_service/
                                   reliability/overall_experience (1-5 each), comment,
                                   is_public (future marketplace), is_archived, locked_at
                                   — one review per reviewer_type per assignment, editable 7 days
vendor_contract_templates       ← tenant_id, name, category, content (HTML w/ {{placeholders}}),
                                   is_active, created_by
vendor_contracts                ← uuid, signing_token, tenant_id, vendor_id, event_id nullable,
                                   vendor_event_assignment_id nullable, template_id nullable,
                                   title, content, contract_amount, payment_schedule,
                                   status (draft|sent|signed|expired|cancelled),
                                   unsigned_file_path, signed_file_path,
                                   expires_at, sent_at, signed_at, cancelled_at, created_by,
                                   planner_signature_type/data/name, planner_signed_at, planner_signed_ip,
                                   vendor_signature_type/data/name, vendor_signed_at, vendor_signed_ip
vendor_contract_status_history  ← tenant_id, vendor_contract_id, from_status, to_status,
                                   changed_by, note (table name is singular "history" — model
                                   MUST set protected $table manually, Eloquent's auto-pluralizer
                                   guesses "histories" which doesn't exist)
vendor_invoices                 ← uuid, tenant_id, vendor_id, event_id nullable,
                                   vendor_contract_id nullable, vendor_event_assignment_id nullable,
                                   invoice_number (auto: INV-YYYYMM-0001), title,
                                   issue_date, due_date, amount, tax_amount, discount_amount,
                                   total_amount, status (draft|sent|partially_paid|paid|overdue|cancelled),
                                   notes, attachment_path, created_by
vendor_invoice_payments         ← tenant_id, vendor_invoice_id, amount, paid_on,
                                   payment_method (cash|bank_transfer|card|other),
                                   reference, notes, receipt_path, recorded_by
```

### Marketplace-Ready Fields (Phase 8.5 — dormant, inert until Global Marketplace is built)
```
vendors.is_public               ← bool, default false
vendors.slug                    ← nullable, unique
vendors.city / vendors.country  ← nullable (country = ISO2)
vendors.portfolio_images        ← JSON array, nullable
vendors.public_description      ← separate from internal 'description', nullable
vendors.marketplace_views       ← unsigned int, default 0
vendors.marketplace_inquiries   ← unsigned int, default 0
```
`Vendor::isMarketplaceReady()` and `Vendor::generateMarketplaceSlug()` helper methods exist on the model but are not called anywhere yet — pure architecture prep so a future Global Vendor Marketplace phase needs zero schema changes.

### Known Migration Bugs Fixed (Phase 9)
`subscriptions`, `subscription_invoices`, and `plan_prices` were originally created with `id` columns missing `auto_increment` (likely from a copy-pasted migration using `unsignedBigInteger('id')->primary()` instead of `$table->id()`). Fixed via follow-up migrations:
```php
DB::statement('ALTER TABLE {table} MODIFY id BIGINT UNSIGNED AUTO_INCREMENT');
```
Applied to all three tables. `gateway_charges` and `billing_settings` were unaffected (created correctly with `$table->id()`).

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
app/Livewire/Platform/BillingConfig.php                 ← 3 tabs: Settings/Gateway Charges/API Keys
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
app/Livewire/Tenant/Billing/UpgradePage.php             ← plan grid, cycle toggle, gateway selector, checkout()
app/Livewire/Tenant/Billing/BillingCallback.php         ← verifies Paystack/Flutterwave payment, activates subscription
app/Livewire/Tenant/Billing/BillingDashboard.php        ← current plan, invoice history
app/Livewire/Client/Auth/Login.php
app/Livewire/Client/Dashboard.php                       ← RSVP read-only stats + link
app/Livewire/Client/Onboarding.php
app/Livewire/Vendor/Auth/Login.php
app/Livewire/Vendor/Dashboard.php                       ← events + tasks + runsheet summary strip
app/Livewire/Vendor/Profile.php
app/Livewire/Vendor/Onboarding.php
app/Livewire/Vendor/VendorRunsheet.php                  ← dedicated runsheet page, step status updates, delay modal
app/Livewire/Vendor/Availability.php                    ← vendor manages own unavailable dates (personal/vacation/holiday/other)
app/Livewire/Tenant/Contracts/ContractTemplates.php     ← reusable templates w/ placeholders, contenteditable rich text + preview toggle
app/Livewire/Tenant/Contracts/ContractList.php          ← stats + filters
app/Livewire/Tenant/Contracts/CreateContract.php        ← template selection auto-fills placeholders, editable before save
app/Livewire/Tenant/Contracts/ContractDetail.php        ← send (emails branded PDF + signing link), upload signed copy,
                                                            planner e-signature capture, download signed/unsigned PDF, status history
app/Livewire/Tenant/Invoices/InvoiceList.php             ← stats (total invoiced/paid/outstanding/overdue), filters
app/Livewire/Tenant/Invoices/CreateInvoice.php           ← vendor/event/contract linking, amount+tax+discount breakdown
app/Livewire/Tenant/Invoices/InvoiceDetail.php           ← record payments (multiple, partial), cancel, attachment/receipt uploads
app/Livewire/Tenant/DomainSettings.php                   ← subdomain, custom domain, DNS instructions, verify now,
                                                            feature-gated per capability (custom_subdomain/custom_domain/white_label)
app/Livewire/Platform/SiteSettings.php                   ← site_name, site_tagline, favicon upload (landing page branding)
app/Livewire/Public/LandingPage.php                      ← hero, feature mockups, live pricing from plans table,
                                                            dark mode + scroll animations, fully responsive
app/Livewire/Public/RsvpFormPage.php                    ← NOT RsvpForm (model name collision)
app/Livewire/Public/RsvpEdit.php
app/Livewire/Public/VendorRegister.php
app/Livewire/Public/BookingForm.php                     ← public booking form, left/right split
app/Livewire/Public/ConsultationForm.php                ← public consultation form, 2-step (date/time → details)
app/Livewire/Public/VendorContractSign.php              ← e-signature public page, no login required, token-based, expires with contract
```

### Models
```
app/Models/Central/Tenant.php
app/Models/Central/PlatformUser.php
app/Models/Central/Plan.php                             ← is_featured bool, allowsMonthly()/allowsAnnual(), getPriceFor()
app/Models/Central/PlanPrice.php                         ← displayAmount(), annualSavings()
app/Models/Central/Subscription.php                     ← isActive(), isTrialing(), isExpired(), isInGracePeriod(), isLocked(), trialDaysRemaining(), daysUntilExpiry()
app/Models/Central/SubscriptionInvoice.php
app/Models/Central/GatewayCharge.php                     ← calculateAbsorbed(), calculateFee()
app/Models/Central/BillingSetting.php                    ← static get()/set(), cached
app/Models/Central/Client.php                           ← password_changed bool
app/Models/Central/VendorAccount.php                    ← password_changed bool, vendor_id FK
app/Models/Tenant/Event.php                             ← rsvp_enabled bool cast, rsvpForm() HasOne
app/Models/Tenant/Task.php                              ← vendor_account_id nullable FK, assigneeName() helper
app/Models/Tenant/Vendor.php                            ← recalculateRating(), categoryAverages(), isUnavailableOn(),
                                                            conflictingAssignments(), isMarketplaceReady() (dormant),
                                                            generateMarketplaceSlug() (dormant)
app/Models/Tenant/VendorApplication.php                 ← available_to_travel bool
app/Models/Tenant/VendorEventAssignment.php             ← invoices() HasMany (added Phase 8.4)
app/Models/Tenant/VendorUnavailableDate.php             ← reasonLabel(), reasonColor()
app/Models/Tenant/VendorReview.php                      ← averageScore() (6-category avg), isEditable() (7-day window)
app/Models/Tenant/VendorContractTemplate.php            ← availablePlaceholders(), render() — resolves {{placeholders}}
app/Models/Tenant/VendorContract.php                    ← changeStatus() (logs history), isFullySigned(), isSigningLinkExpired(),
                                                            checkAndUpdateSignedStatus(), statusLabel(), statusColor()
app/Models/Tenant/VendorContractStatusHistory.php       ← protected $table = 'vendor_contract_status_history' REQUIRED
                                                            (Eloquent's auto-pluralizer guesses wrong table name otherwise)
app/Models/Tenant/VendorInvoice.php                     ← generateInvoiceNumber(), totalPaid(), balance(), isPaid(), isOverdue(),
                                                            recalculateStatus(), syncBudgetItem() (auto budget_item),
                                                            syncAssignmentAmount() (keeps VendorEventAssignment in sync)
app/Models/Tenant/VendorInvoicePayment.php              ← methodLabel(), triggers invoice recalculation on save/delete
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
app/Http/Middleware/EnsureTenantActive.php              ← shares $tenantLocked/$tenantSubscription to views,
                                                           blocks non-GET requests at the HTTP layer (page-level,
                                                           NOT Livewire actions — see SubscriptionLockHook below)
app/Livewire/Hooks/SubscriptionLockHook.php             ← ComponentHook that blocks write-action Livewire methods
                                                           for locked tenants; registered in AppServiceProvider::register()
app/Traits/WithToast.php                                ← toastSuccess(), toastError(), toastWarning()
app/Traits/BelongsToTenant.php
app/Services/TenantService.php
app/Services/FeatureGateService.php                     ← isOnTrial(), trialDaysLeft(), isHighestPlan(), canAccess()
app/Services/BillingService.php                         ← getExchangeRate(), convertAmount(), getPriceForTenant(),
                                                           getPreferredGateway(), initializePaystackPayment(),
                                                           initializeFlutterwavePayment(), activateSubscription(),
                                                           verifyPaystackPayment(), verifyFlutterwavePayment(),
                                                           processExpiredSubscriptions()
app/Services/DomainVerificationService.php              ← generateVerificationToken(), verify() — checks CNAME
                                                           (via dns_get_record DNS_CNAME) + TXT ownership record
                                                           (_koordli-verify.{domain}), sets tenants.domain_status
app/Http/Middleware/ResolveTenantByDomain.php           ← aliased 'tenant.byDomain'. Resolves tenant from incoming
                                                           Host header (subdomain of app host, or verified custom
                                                           domain) and populates app('resolvedTenant') +
                                                           TenantContext::set(). No-ops entirely on the main app
                                                           domain — existing session-based tenant.resolve middleware
                                                           is untouched and still runs alongside it.
```

### API Controllers
```
app/Http/Controllers/Api/FormSubmissionController.php   ← POST /api/forms/{token}/submit (booking)
app/Http/Controllers/Api/ConsultationSubmissionController.php ← POST /api/consult/{token}/submit
```

### Console Commands
```
app/Console/Commands/ProcessSubscriptions.php           ← koordli:process-subscriptions — daily 6am cron.
                                                           Processes expired subs, sends 14-day + 3-day reminders,
                                                           sends expiry notifications
app/Console/Commands/BackfillTenantSubscriptions.php    ← koordli:backfill-subscriptions — ONE-TIME command,
                                                           creates expired subscription records for tenants that
                                                           registered before Phase 9 billing was built and have
                                                           no subscription row at all
app/Console/Commands/BackfillVendorInvoicesFromAssignments.php ← koordli:backfill-vendor-invoices — ONE-TIME command,
                                                           converts existing VendorEventAssignment.amount_agreed/
                                                           amount_paid into proper VendorInvoice + VendorInvoicePayment
                                                           records (Phase 8.4 migration). Re-runnable, skips already-
                                                           converted assignments via whereDoesntHave('invoices')
app/Console/Commands/RecheckTenantDomains.php           ← koordli:recheck-domains — daily 7am cron. Re-verifies
                                                           every tenant with a custom_domain set, catches DNS
                                                           breakage/misconfiguration after initial verification
```

### PDF Generation (Phase 8.3)
```
barryvdh/laravel-dompdf                                 ← installed via composer, used ONLY for contract PDFs
                                                           (config published to config/dompdf.php)
resources/views/pdf/vendor-contract-pdf.blade.php       ← branded PDF template: tenant logo/colors from
                                                           tenants.branding JSON, custom @font-face (Satoshi body +
                                                           Spline Sans headings, both loaded as .ttf from storage/fonts/),
                                                           two-column signature block at bottom (planner left, vendor right)
storage/fonts/                                          ← Satoshi-Regular/Bold/Italic/BoldItalic.ttf,
                                                           SplineSans-Regular/Medium/Bold.ttf — DomPDF's font_dir AND
                                                           font_cache both point here (config/dompdf.php).
                                                           MUST be actual .ttf files — DomPDF cannot load .woff2.
                                                           default_font in config/dompdf.php set to 'Satoshi'
                                                           (was 'serif' by default)
```

### Jobs (all queued)
```
SendVerificationCodeJob
SendWelcomeEmailJob
SendStaffInviteJob                                       ← white-label aware (whiteLabel bool, last param)
SendClientInviteJob                                      ← white-label aware
SendVendorInviteJob                                      ← white-label aware
SendVendorApprovalJob                                    ← white-label aware
SendVendorApplicationReceivedJob                         ← white-label aware
SendVendorAssignedJob                                    ← white-label aware. Was DEAD CODE (Job+Mailable+blade
                                                            existed, zero dispatch sites anywhere) until Phase 11 —
                                                            now dispatched from VendorDetail::assignToEvent(), which
                                                            also auto-creates a VendorAccount portal login on first
                                                            assignment (mirrors the logic in VendorDetail::inviteVendor())
SendOutstandingReminderJob                               ← white-label aware
SendRsvpConfirmationJob                                 ← sends QR code SVG inline in email; white-label aware,
                                                           gained new `companyName` (default 'Koordli') param
SendRsvpNotificationJob                                 ← notifies planner + client on each response (NOT white-label gated — internal notification)
SendFormSubmissionNotificationJob                       ← notifies tenant on booking/consultation submission (NOT white-label gated)
SendFormSubmissionConfirmationJob                       ← confirms to guest on booking/consultation submission — white-label aware
SendSubscriptionReminderJob                             ← 14-day and 3-day renewal reminder email (NOT white-label gated — Koordli-to-tenant)
SendSubscriptionExpiredJob                              ← sent when subscription/trial fully expires (NOT white-label gated)
SendSubscriptionActivatedJob                            ← sent to TENANT on successful payment (NOT white-label gated — Koordli-to-tenant)
SendPlatformPaymentNotificationJob                      ← sent to PLATFORM OWNER (NOT white-label gated)
SendVendorContractJob                                   ← emails branded contract PDF + e-signature link to vendor — white-label aware
SendContractSignedNotificationJob                       ← notifies planner when vendor signs (NOT white-label gated — Koordli-to-tenant)
```

**White-label gating rule (Rule 41):** only emails going to the TENANT'S OWN clients/vendors/guests/applicants are white-label aware. Emails Koordli sends to the tenant about their own account always show Koordli branding.

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
resources/views/emails/subscription-reminder.blade.php ← 14-day / 3-day renewal reminder (color changes if urgent)
resources/views/emails/subscription-expired.blade.php  ← sent when account is locked
resources/views/emails/subscription-activated.blade.php ← receipt-style email to tenant on successful payment
resources/views/emails/platform-payment-notification.blade.php ← revenue notification to platform owner
resources/views/emails/vendor-contract.blade.php       ← sent to vendor, PDF attached, includes signing link
resources/views/emails/contract-signed-notification.blade.php ← sent to planner when vendor signs (or fully executed)
```

### Layouts
```
resources/views/layouts/auth.blade.php
resources/views/layouts/platform.blade.php
resources/views/layouts/tenant.blade.php               ← includes trial banner + subscription-locked JS listener
resources/views/layouts/client.blade.php
resources/views/layouts/vendor.blade.php
resources/views/layouts/rsvp.blade.php                 ← bare layout, Fraunces + Spline Sans Google Fonts
                                                           Also used by public booking + consultation forms
```

### Sidebar / UI Components
```
resources/views/components/layout/tenant-sidebar.blade.php  ← includes Billing nav link
resources/views/layouts/platform-sidebar.blade.php           ← includes Billing Config nav link
resources/views/components/ui/trial-banner.blade.php         ← ACTUAL location (not layouts/ or components/layout/)
                                                                 Shows trial/grace/locked/expiring states,
                                                                 links to tenant.billing.upgrade
```

### Public Views
```
resources/views/livewire/public/rsvp-form.blade.php           ← left/right split, Fraunces serif, CSS vars
resources/views/livewire/public/rsvp-edit.blade.php           ← edit via secure token
resources/views/public/rsvp-ticket-pdf.blade.php              ← print/save as PDF page with QR
resources/views/livewire/public/booking-form.blade.php        ← left/right split, Fraunces + Spline Sans
resources/views/livewire/public/consultation-form.blade.php   ← 2-step: date/time → details, custom calendar
resources/views/livewire/public/vendor-contract-sign.blade.php ← e-signature page, no auth, token-based,
                                                                    signature pad (draw/type), download-after-sign
```

### Tenant Form Views
```
resources/views/livewire/tenant/forms/form-list.blade.php
resources/views/livewire/tenant/forms/create-form.blade.php   ← 5 tabs (pure @if server-driven, no x-show)
resources/views/livewire/tenant/forms/form-submissions.blade.php
```

### Billing Views
```
resources/views/livewire/tenant/billing/upgrade-page.blade.php   ← plan grid, cycle toggle (Alpine-owned), gateway selector
resources/views/livewire/tenant/billing/billing-callback.blade.php
resources/views/livewire/tenant/billing/billing-dashboard.blade.php
resources/views/livewire/platform/billing-config.blade.php      ← 3 tabs, fee absorption calculator (pure Alpine, no server round trip)
```

### Vendor Contract Views (Phase 8.3)
```
resources/views/livewire/tenant/contracts/contract-templates.blade.php ← contenteditable rich text editor + Edit/Preview toggle
resources/views/livewire/tenant/contracts/contract-list.blade.php      ← stats strip, status filter
resources/views/livewire/tenant/contracts/create-contract.blade.php    ← template picker, Edit/Preview toggle for generated content
resources/views/livewire/tenant/contracts/contract-detail.blade.php    ← signature status card, send/cancel modals,
                                                                            signed-copy upload, status history timeline
resources/views/components/ui/signature-pad.blade.php                 ← shared canvas signature component (draw/type toggle),
                                                                            used by BOTH planner (contract-detail) and
                                                                            vendor (vendor-contract-sign) — props: wireModel, label
```

### Vendor Invoice Views (Phase 8.4)
```
resources/views/livewire/tenant/invoices/invoice-list.blade.php   ← stats (invoiced/paid/outstanding/overdue)
resources/views/livewire/tenant/invoices/create-invoice.blade.php ← two-column layout w/ explanatory sidebar
                                                                       (Base Amount/Tax/Discount breakdown, status guide)
resources/views/livewire/tenant/invoices/invoice-detail.blade.php ← payment history, record-payment form, receipt uploads
```

### Vendor Availability Views (Phase 8.1)
```
resources/views/livewire/vendor/availability.blade.php ← vendor-facing, upcoming/past sections, block-dates form
```

---

## ROUTES (complete current state)

### Root / Public Marketing
```
/                                     → Public\LandingPage  (hero, live pricing from plans table, dark mode,
                                                              scroll animations, mockups of actual app screens)
/sitemap.xml                          → route closure, plain PHP-built XML string (NOT a .blade.php view —
                                                              raw <?xml tag inside a .blade.php file causes
                                                              IDE/compiler conflicts)
/robots.txt                           → static file in public/, disallows /dashboard /platform /vendor /client /billing
```

### Public
```
/rsvp/{slug}                          → Public\RsvpFormPage
/rsvp/{slug}/edit/{token}             → Public\RsvpEdit
/rsvp/ticket/{token}                  → route closure → public.rsvp-ticket-pdf view
/vendors/{slug}/register              → Public\VendorRegister
/book/{slug}                          → Public\BookingForm
/consult/{slug}                       → Public\ConsultationForm
/contracts/sign/{token}               → Public\VendorContractSign  (e-signature, no login, expires with contract)
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

### Tenant Authenticated (middleware: auth.tenant, tenant.resolve, onboarding.check, tenant.active)
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
/billing                              → Tenant\Billing\BillingDashboard
/billing/upgrade                      → Tenant\Billing\UpgradePage
/billing/callback/{gateway}           → Tenant\Billing\BillingCallback
/contract-templates                   → Tenant\Contracts\ContractTemplates
/contracts                            → Tenant\Contracts\ContractList
/contracts/create                     → Tenant\Contracts\CreateContract
/contracts/{uuid}                     → Tenant\Contracts\ContractDetail
/invoices                             → Tenant\Invoices\InvoiceList
/invoices/create                      → Tenant\Invoices\CreateInvoice
/invoices/{uuid}                      → Tenant\Invoices\InvoiceDetail
/domain-settings                      → Tenant\DomainSettings
```

### Client Portal (login route wrapped in `tenant.byDomain` middleware — Phase 11)
```
/client/login                         → Client\Auth\Login       (shows tenant logo/name if resolved via
                                                                    subdomain/custom domain AND white_label
                                                                    enabled; else Koordli branding)
/client/onboarding                    → Client\Onboarding
/client/dashboard                     → Client\Dashboard        (post-login, uses auth('client')->user()->tenant
                                                                    directly — NOT resolvedTenant — works regardless
                                                                    of which URL was used to log in)
/client/logout (POST)
```

### Vendor Portal (login route wrapped in `tenant.byDomain` middleware — Phase 11)
```
/vendor/login                         → Vendor\Auth\Login       (same resolvedTenant branding logic as client login)
/vendor/onboarding                    → Vendor\Onboarding
/vendor/dashboard                     → Vendor\Dashboard        (post-login, uses auth('vendor')->user()->tenant directly)
/vendor/profile                       → Vendor\Profile
/vendor/runsheet                      → Vendor\VendorRunsheet
/vendor/availability                  → Vendor\Availability
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
/platform/plans/{planId}/edit         → Platform\Plans\CreatePlan   ← param MUST be named {planId}, NOT {plan} —
                                                                       see Rule 38 (route param must match mount()
                                                                       param name exactly, or edit silently behaves
                                                                       like create with no error thrown)
/platform/billing                     → Platform\BillingConfig
/platform/site-settings               → Platform\SiteSettings
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
- `allowed_cycles` JSON (`["monthly","annual"]`) controls which billing cycles tenants can pick for that plan
- `annual_discount_percent` — e.g. 20 means annual price = monthly × 12 × 0.8

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

## VENDOR ECOSYSTEM ARCHITECTURE (Phase 8 — complete: 8.1 through 8.5)

### Product Direction (locked in)
Koordli stays an **Event Operations Platform first, not a vendor marketplace**. Vendor Directory (private, per-tenant) is the MVP; a Global Vendor Marketplace is architected for but NOT built (Phase 8.5 is schema-prep only). Priority chain: Vendor Discovery (future) → Onboarding → Approval → Assignment → Communication → Tasks → Runsheet → Contracts → Payments → Performance History.

---

### 8.1 — Vendor Availability Calendar (complete)
- **Vendor manages their own unavailable dates** — NOT the planner. This was corrected mid-build: initially built as planner-managed, then moved to `/vendor/availability` (dedicated vendor portal page) once it was realized the planner has no way to actually know a vendor's real availability.
- Reasons: personal, vacation, holiday, other. Date range (from/to), optional notes.
- Planner sees these read-only on the vendor's detail page (no add/delete controls on tenant side).
- **Conflict warning (not a hard block)** on the "Assign to Event" form: when an event date is picked, `Vendor::isUnavailableOn()` and `Vendor::conflictingAssignments()` run, showing a yellow warning banner if the vendor is either personally blocked OR already assigned to another event same day. Planner can still proceed — this is visibility, not enforcement, since vendor availability can change after the fact via verbal agreement.
- Livewire quirk hit: cannot call an `updated{Property}()` lifecycle hook directly from a dropdown's `x-on:click` — Livewire blocks "Unable to call lifecycle method directly on component". Fixed by using the generic `updated($property)` hook and checking `if ($property === 'assign_event_id')` inside it, rather than a dedicated `updatedAssignEventId()` method.

### 8.2 — Ratings & Reviews (complete, awaiting first real-world test once an event date passes)
- Reviews are tied to a specific `vendor_event_assignment_id` — never generic. One vendor can accumulate multiple reviews across multiple events.
- **Planner review**: only allowed after the event has ended (`assignment->eventHasEnded()`). One planner review per assignment. Submitted from the vendor detail page ("★ Rate Vendor" button appears once event has passed).
- **Client review**: submitted from the Client Portal dashboard, under a "Rate Your Vendors" section that appears once the event date has passed and vendor assignments exist for that event. One client review per vendor per assignment. Client can only review vendors actually assigned to their own event (ownership check via `client_email` match).
- **6 scoring categories**, 1-5 stars each: Professionalism, Communication, Punctuality, Quality of Service, Reliability, Overall Experience. Comment field supported.
- **Weighting: Planner 80% / Client 20%.** If only one type of review exists for a vendor, that type's average is used alone (no artificial dilution). `Vendor::recalculateRating()` runs automatically via the `VendorReview::saved` model event and updates `planner_rating_avg`, `client_rating_avg`, `weighted_rating`, `rating` (rounded int for star display), `reviews_count`.
- **Editable for 7 days** (`locked_at` set on creation to `now()->addDays(7)`), then permanently locked (`isEditable()` check). Reviews are never hard-deleted — `is_archived` bool exists for hiding without destroying history.
- **Private to tenant for MVP** — `is_public` bool exists on the table (default false) purely for future Global Marketplace use; no UI currently exposes it.
- Vendor detail page shows a "Performance History" card: overall weighted score + stars, Planner Avg vs Client Avg side by side, and a per-category breakdown (`Vendor::categoryAverages()`).

### 8.3 — Vendor Contracts (complete, went beyond original MVP scope to include full e-signature)
**Templates:**
- Reusable HTML templates with `{{placeholder}}` tokens: `{{vendor_name}}`, `{{company_name}}`, `{{event_name}}`, `{{event_date}}`, `{{event_location}}`, `{{service_category}}`, `{{contract_amount}}`, `{{payment_schedule}}`, `{{planner_name}}`, `{{generated_date}}`.
- `VendorContractTemplate::render($vendor, $event, $amount, $paymentSchedule, $plannerName)` does the token replacement — this happens ONCE at contract-generation time and the resolved text is saved as plain HTML into `vendor_contracts.content`. Editing the template later does NOT retroactively change already-generated contracts.
- Rich text editing uses a plain `contenteditable="true"` div + a manual toolbar (Bold/Italic/Underline/H2/H3/lists via `document.execCommand()`) — **no TipTap/Quill/CKEditor package installed**, deliberately kept lightweight. Content synced to Livewire via `x-on:input="$wire.set('content', $el.innerHTML, false)"` (the `false` third arg avoids a re-render loop that would blow away the contenteditable cursor position).
- Edit/Preview toggle (pure Alpine `x-data="{ mode: 'edit' }"`) lets the planner see exactly how the rendered content will look before saving — added on user request, present on both the Template editor and the Create Contract page.

**Contract lifecycle:**
- A contract may be linked to a specific event assignment OR exist as a general vendor agreement (event_id nullable).
- Status: `draft → sent → signed / expired / cancelled`. Every status change is logged to `vendor_contract_status_history` via `VendorContract::changeStatus($newStatus, $note)`.
- **"Send" triggers real delivery**: generates a branded PDF (see PDF section below), emails it to the vendor as an attachment via `SendVendorContractJob`, AND includes a unique e-signature link. Planner can also "Mark as Sent Manually" if delivered outside Koordli.
- Signed copy upload (PDF/JPG/PNG) always supported as the no-e-signature fallback, independent of the e-signature flow below.

**E-Signature (built after initial MVP, per user request — "let vendor sign in-browser"):**
- Both planner and vendor can sign: **draw (canvas) or type (script-font text)** — user's choice via a toggle, both stored the same way (`signature_type`: draw|type, `signature_data`: base64 PNG or plain text name).
- Shared `<x-ui.signature-pad>` Blade component (canvas + draw/type toggle) used identically by the planner-side modal (`ContractDetail`) and the public vendor-side page (`VendorContractSign`).
- Planner signs from the contract detail page ("✍️ Add Your Signature" button/modal) — can happen before or after sending.
- Vendor signs via a public, no-login-required link: `/contracts/sign/{signing_token}`. Token is a random 40-char string generated on contract creation (`Str::random(40)`), stored in `vendor_contracts.signing_token`.
- **Signing link expires exactly when the contract's `expires_at` date passes** (`VendorContract::isSigningLinkExpired()`) — shows a clear "Signing Link Expired" page instead of the form.
- **Audit trail captured**: IP address + timestamp for both planner and vendor signatures (`planner_signed_ip`/`planner_signed_at`, `vendor_signed_ip`/`vendor_signed_at`). No "Signed via Koordli" watermark on the PDF — deliberately omitted per user request so white-label tenants (future paid tier) aren't stuck with Koordli branding.
- `VendorContract::isFullySigned()` = both `planner_signed_at` AND `vendor_signed_at` are set → status auto-flips to `signed` via `checkAndUpdateSignedStatus()`.
- After vendor signs on the public page: "Signed Successfully" confirmation + a "Download Your Copy" button (re-generates the same branded PDF with both signatures baked in).
- **Planner is notified by email the moment the vendor signs** (`SendContractSignedNotificationJob`) — subject/wording differs depending on whether it's just the vendor's signature or the contract is now fully executed by both parties.

**Branded PDF (`resources/views/pdf/vendor-contract-pdf.blade.php`):**
- Uses `barryvdh/laravel-dompdf` (installed this phase — no other PDF package existed before).
- Pulls tenant's actual logo + brand colors from `tenants.branding` JSON (`primary_color`, `accent_color`, `logo`) — same source used elsewhere in the app (RSVP, Forms).
- Custom fonts: **Satoshi** (body) + **Spline Sans** (headings) — DomPDF requires actual `.ttf` files (NOT `.woff2`, which the web app uses) registered via `@font-face` inside the PDF blade, with the files physically present in `storage/fonts/` (DomPDF's configured `font_dir`/`font_cache`). `config/dompdf.php`'s `default_font` changed from `'serif'` to `'Satoshi'`.
- **Currency symbol bug**: Satoshi (like most fonts) does NOT include the ₦ (Naira, U+20A6) glyph — DomPDF renders it as `?` or an empty box. **Fix: use currency CODES (NGN, GHS, USD) instead of symbols anywhere a PDF-rendered amount is generated by the system** — `CurrencyHelper::formatForPdf($amount, $currency)` returns `"NGN 1,500,000.00"` instead of `"₦1,500,000.00"`. This is also arguably more correct for a legal document (avoids ambiguity between currencies that share symbols, e.g. `$`). **Caveat:** any amount a planner manually TYPES into the free-form contract content (e.g. typing "₦1,500,000" directly in the rich text editor) will still show the symbol since that's raw saved HTML — only the system-generated `{{contract_amount}}` placeholder and the meta-info box use the code-based formatter. Contracts created before this fix have the broken symbol baked into their saved `content` and must be manually edited once to correct it.
- Signature block: two-column table at the bottom of the PDF, company/planner on the left, vendor on the right, each showing their signature image (if drawn) or script-styled typed name, full name, and signed timestamp.

### 8.4 — Vendor Invoicing & Payment Tracking (complete)
**Core model: Invoices are separate from Payments.** An invoice represents what is owed; payments record money actually received. This allows partial payments, full payment history, and accurate outstanding balances without turning Koordli into an accounting package.

- **Multiple invoices per vendor per event** supported (Deposit, Progress, Final Balance, Additional Service, Change Request, etc.) — each invoice is its own record with its own number (`INV-YYYYMM-0001` auto-generated), issue/due dates, amount, tax, discount, computed total, status, notes, and optional attachment.
- **Multiple payments per invoice** — `vendor_invoice_payments` table, each with amount, date, method (cash/bank_transfer/card/other), reference, optional receipt upload. `VendorInvoice::totalPaid()` sums all payments; `balance()` = total_amount − totalPaid().
- Status: `draft → sent → partially_paid → paid`, plus `overdue` (auto-detected via due_date past + unpaid) and `cancelled`. `recalculateStatus()` runs automatically whenever a payment is saved/deleted.
- **"Invoice" does NOT require the vendor to have sent a formal document.** It's fundamentally the planner's internal record of "what's owed to this vendor" — the attachment field is optional precisely because many vendor agreements are verbal/WhatsApp-based with no paperwork. This was clarified mid-build when the user questioned the module's purpose.
- **This is planner-initiated bookkeeping, NOT a vendor-facing feature** — vendors never see or interact with the Invoices module. It mirrors how Client Payments already work (planner records what the client paid, not the client self-reporting).
- **Budget auto-sync**: every invoice with an `event_id` automatically creates/updates a matching `budget_item` (`VendorInvoice::syncBudgetItem()`, triggered on `saved`). Budget items are tagged `source = 'vendor_invoice'` and linked via `budget_items.vendor_invoice_id` so they're distinguishable from manually-added budget lines and get cleaned up (`deleted`) if the invoice is cancelled/removed.
- **Unified with the older Vendor Event Assignment payment fields**: `vendor_event_assignments.amount_agreed`/`amount_paid` predate the Invoices module. Rather than deprecating them, **Option A was chosen**: assigning a vendor with an amount now auto-creates a matching `VendorInvoice` (+ payment record if `amount_paid` was also entered) behind the scenes, transparently, so planners who just want a quick assignment never have to think about "invoices" explicitly. `VendorInvoice::syncAssignmentAmount()` then keeps the two views in sync bidirectionally — editing the invoice's amount or recording a payment there updates the assignment's `amount_agreed`/`amount_paid` fields too (via `updateQuietly()` to avoid event loops). A "View Full Invoice →" link appears on the assignment card once one exists.
- **One-time backfill**: `koordli:backfill-vendor-invoices` command converts pre-existing assignments (created before this phase) with `amount_agreed > 0` into proper invoice + payment records. Safe to re-run (skips assignments that already have an invoice via `whereDoesntHave('invoices')`).
- Invoice creation page uses a two-column layout with an explanatory right sidebar (What Base Amount/Tax/Discount mean, how the module works, status guide) — added after user feedback that a blank right column "looked empty on desktop" and some terms needed explaining for less technical users.

### 8.5 — Marketplace-Ready Schema (complete — dormant, zero UI)
Pure architecture prep so a future Global Vendor Marketplace phase requires no schema migrations on existing tables. Added to `vendors`: `is_public` (bool, default false), `slug` (nullable unique), `city`, `country` (ISO2), `portfolio_images` (JSON array), `public_description` (separate from internal `description`), `marketplace_views`, `marketplace_inquiries` (both unsigned int counters). Helper methods `Vendor::isMarketplaceReady()` and `Vendor::generateMarketplaceSlug()` exist but are not called anywhere yet.

---

## BILLING & SUBSCRIPTIONS ARCHITECTURE (Phase 9 — complete)

### Key Decisions (locked in)
- **Gateways**: Both Paystack and Flutterwave supported, tenant chooses at checkout (whichever are enabled by platform)
- **Payment implementation**: Laravel `Http` facade only — NO Paystack/Flutterwave SDK packages installed. All API calls are plain HTTP POST/GET via Guzzle (bundled with Laravel).
- **Billing model**: Self-serve (tenant pays via `/billing/upgrade`) + platform can still manually assign/edit plans from `/platform/tenants/{id}/edit`
- **Billing cycles**: Monthly and/or Annual, configurable per-plan via `allowed_cycles`. Annual gets a discount via `annual_discount_percent` (like Hostinger-style yearly savings)
- **Renewal**: Manual only (no card-on-file auto-charge). Reminder emails sent 14 days and 3 days before expiry (configurable via platform billing settings)
- **Trial expiry behavior**: Read-only lockout. Tenant can view ALL their data (GET requests always allowed) but cannot create/edit/delete anything. Grace period (default 7 days, configurable) exists between expiry and full lockout.
- **Currency**: Platform sets prices in NGN (base currency). Frankfurter API (cached, configurable hours) converts to tenant's billing currency at checkout time.
- **Gateway fee absorption**: Platform never eats payment processor fees. When platform sets a plan price (e.g. ₦5,000), the system calculates what to actually charge the tenant so that after Paystack/Flutterwave deduct their cut, the platform receives exactly ₦5,000. Formula: `charge = (desired_amount + fixed_fee) / (1 - percentage_fee)`, capped if a fee cap exists. Gateway rates (percentage, fixed fee, cap) are fully configurable from `/platform/billing` — Gateway Charges tab — no code changes needed when Paystack/Flutterwave update their pricing.

### Gateway Charges (seeded defaults, editable in platform admin)
```
Paystack Nigeria:        1.5% + ₦100, capped at ₦2,000
Paystack International:  3.8% + ₦100, no cap
Flutterwave Nigeria:     1.4%, capped at ₦2,800
Flutterwave International: 3.8%, no cap
```

### Billing Settings (key/value store, `BillingSetting::get()/set()`, cached)
```
base_currency            → NGN
grace_period_days        → 7
reminder_days            → 14
reminder_days_urgent     → 3
paystack_secret_key / paystack_public_key
flutterwave_secret_key / flutterwave_public_key
enabled_gateways          → ["paystack","flutterwave"]
frankfurter_cache_hours   → 24
```

### Read-Only Lockout — How It Actually Works (IMPORTANT — took multiple iterations to get right)
Two layers are needed because Livewire v4 requests bypass normal route middleware:

1. **`EnsureTenantActive` middleware** (HTTP layer) — handles the initial page GET request. Shares `$tenantLocked` and `$tenantSubscription` to all views for the trial banner. Allows all GET requests through. Blocks non-GET requests that somehow do hit named routes directly (rare, since Livewire doesn't use named routes for updates).

2. **`SubscriptionLockHook`** (Livewire component hook, THE REAL ENFORCEMENT LAYER) — registered via `Livewire::componentHook(SubscriptionLockHook::class)` inside `AppServiceProvider::register()` (must be `register()`, not `boot()`, due to Livewire's own boot-order — see Rule 35). Overrides `call($method, $params, $returnEarly, ...)`. Only blocks methods whose name matches a write-action pattern (`save`, `create`, `delete`, `confirm`, `update`, `store`, `submit`, `approve`, `reject`, `activate`, `deactivate`, `toggle`, `send`, `invite`, `cancel`, `suspend`, `assign`) — everything else (dropdowns, `$set`, tab switches, date/time pickers) is whitelisted and works freely so the UI doesn't feel broken while filling out a form. When a blocked method is called: `$returnEarly(null)` stops execution, then `$this->component->dispatch('subscription-locked')` fires a browser event. Billing components (`App\Livewire\Tenant\Billing\*`) are always exempt so the tenant can still renew.

3. **Browser listener** in `tenant.blade.php` catches `subscription-locked` event → shows toast "This action requires an active plan. Redirecting to billing..." → redirects to `/billing/upgrade` after 1.5s.

### Trial Banner (`resources/views/components/ui/trial-banner.blade.php`)
Color-coded by state, always links to `route('tenant.billing.upgrade')`:
- 🟣 Violet gradient — on trial, X days left
- 🟠 Amber — in grace period (expired but not yet locked)
- 🔴 Red — fully locked
- Falls back to querying `Subscription` directly if `$tenantSubscription` isn't shared yet (defensive)

### Upgrade Page (`/billing/upgrade`)
- Billing cycle toggle (Monthly/Annual) — pure Alpine, instant, no server round trip until checkout
- Gateway selector (Paystack/Flutterwave) if multiple enabled — same pure-Alpine pattern
- Plan cards show price in tenant's currency with gateway fees already included ("gateway fees included" label), plus NGN equivalent + exchange rate shown for transparency
- Featured plan gets ⭐ ribbon + violet border
- Buttons use `krd-btn-primary` class (NOT hardcoded `background:#1C1917`) so they remain visible in dark mode

### Payment Flow
1. Tenant picks plan + cycle + gateway → `checkout()` called
2. `BillingService::getPriceForTenant()` computes converted + fee-absorbed amount
3. `BillingService::initializePaystackPayment()` or `initializeFlutterwavePayment()` — Http::withToken()->post() to gateway API
4. Tenant redirected to gateway's hosted checkout page
5. On success, gateway redirects to `/billing/callback/{gateway}`
6. `BillingCallback` component verifies payment server-side (`verifyPaystackPayment()`/`verifyFlutterwavePayment()`) then calls `BillingService::activateSubscription()` which creates the `Subscription` + `SubscriptionInvoice` records and unlocks the tenant

### Platform Billing Config (`/platform/billing`) — 3 tabs
1. **Settings** — grace period, reminder days, exchange rate cache, enabled gateways (single shared `x-data` for both toggles to avoid cross-interference — see Rule 24/29), Billing Overview (active/trial/grace/expired counts + revenue this/last month + recent payments) placed ABOVE the settings form on the left column, Fee Absorption Calculator (live, pure Alpine/JS, no Livewire round trip) + Current Gateway Rates reference on the right column (sticky)
2. **Gateway Charges** — editable percentage/fixed fee/cap per gateway+region, absorb toggle, formula explainer + worked example on the right
3. **API Keys** — Paystack/Flutterwave secret + public keys, where-to-find-keys guide + webhook URL reference on the right

### Console Commands
- `koordli:process-subscriptions` — scheduled `dailyAt('06:00')` in `routes/console.php`. Expires trials/subscriptions past `trial_ends_at`/`expires_at`, sends 14-day and 3-day reminder emails (tracked via `reminder_14_sent`/`reminder_3_sent` to avoid duplicates), sends expiry notification emails.
- `koordli:backfill-subscriptions` — **one-time manual command**, NOT scheduled. Needed because tenants who registered before Phase 9 was built have zero rows in `subscriptions`. Finds tenants with no subscription, creates one with `status: expired`, `trial_ends_at` set to yesterday, `grace_until` = today + grace period days. Safe to re-run — skips tenants that already have a subscription.

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

### Billing Currency Conversion (Phase 9)
- `BillingService::getExchangeRate($from, $to)` — Frankfurter API (`https://api.frankfurter.app/latest`), cached per configurable hours (default 24) via `BillingSetting`
- All plan prices set by platform in NGN; converted live to tenant's `billing_currency` at checkout
- Gateway selection also currency-aware: `getPreferredGateway()` checks which enabled gateway supports the tenant's currency (Paystack: NGN/GHS/USD/ZAR/KES/GBP; Flutterwave: those + EUR/XOF/XAF)

### PDF-Safe Currency Formatting (Phase 8.3 — CRITICAL)
```php
CurrencyHelper::code('NGN')                    // → 'NGN' (just the ISO code, uppercased)
CurrencyHelper::codeForTenant()                // → code for current auth user's tenant
CurrencyHelper::formatForPdf(1500000, 'NGN')   // → 'NGN 1,500,000.00'
```
**Use `formatForPdf()`, never `symbol()`/`format()`, for any amount rendered inside a DomPDF-generated PDF.** Currency symbols like ₦ are missing from most fonts' glyph sets (confirmed with Satoshi) and render as `?` or an empty box in PDF output. Using the plain ISO code instead is both a reliable fix AND arguably more correct for a legal document (avoids symbol ambiguity, e.g. `$` meaning USD/CAD/AUD). This distinction does NOT apply to the web UI (Blade views rendered in-browser) — `symbol()`/`format()` remain correct there since browsers render currency glyphs fine.

---

## REUSABLE UI COMPONENTS

### Custom Dropdown (`resources/views/components/ui/dropdown.blade.php`)
- Alpine-powered, no native select
- Uses `krdDropdown` Alpine data component registered in `app.js`
- Props: `wire`, `placeholder`, `selected`, `max-width`
- **DO NOT use for country/currency selection** — use plain Alpine pick() pattern
- **Auto-renders its own placeholder option** — the component's `@if($placeholder)` block already adds a "None"/clear option at the top of the menu that calls `clear()`. Never manually duplicate this inside the slot (e.g. don't also add `<div class="krd-dropdown-option" x-on:click="select('No contract', null)">` — this creates a visible duplicate entry). Just pass the desired text via the `placeholder` prop.
- **Always use `@js($value)` instead of `'{{ $value }}'`** when passing a PHP string into a `select()` JS call inside an option's `x-on:click` — see Rule 36. Applies to any option label that could contain `&`, `'`, or `"` (vendor names, event names, contract titles).

### Toast System
- `window.showToast(message, type)` — global function
- Types: `success`, `error`, `warning`, `info`
- Auto-dismiss: 4 seconds
- Container: `#krd-toast-container` fixed top-right
- `WithToast` trait: `toastSuccess()`, `toastError()`, `toastWarning()`
- On public pages (no Livewire layout): use `KrdToast.success('msg')` directly

### Portal Logo (`resources/views/components/ui/portal-logo.blade.php`) — Phase 11
- Props: `:tenant` (optional, explicit model), `color` (light|dark|auto, same as `<x-ui.logo>`)
- Renders in priority order: (1) if no explicit `:tenant` passed, falls back to `app('resolvedTenant')` if bound (domain-resolved, pre-login case); (2) if the resolved tenant has `white_label` enabled AND has uploaded a logo (`tenants.branding.logo`) → shows that image; (3) if `white_label` enabled but NO logo uploaded → shows tenant name as styled text; (4) otherwise (no tenant resolved, or `white_label` off) → falls back to `<x-ui.logo :color="$color" />` (the Koordli logo)
- Used in: Client Portal sidebar (`:tenant="auth('client')->user()?->tenant"`), Vendor Portal sidebar (`:tenant="auth('vendor')->user()?->tenant"`), Client/Vendor login pages (no `:tenant` prop — relies on `resolvedTenant` from `tenant.byDomain` middleware)
- Tenant's OWN dashboard (tenant-sidebar.blade.php) intentionally still uses plain `<x-ui.logo>` — NEVER swapped to portal-logo — since the tenant is Koordli's own customer and always sees Koordli branding in their own workspace (see Rule 41)

### Signature Pad (`resources/views/components/ui/signature-pad.blade.php`) — Phase 8.3
- Props: `wireModel` (Livewire property name to bind to), `label`
- Draw (canvas, mouse/touch) or Type (styled script-font text input) toggle
- Shared identically between planner-side contract signing modal and public vendor e-signature page

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

**Exception:** For simple boolean toggles where hidden checkbox doesn't sync (e.g. field required toggle in form builder, gateway enable/disable toggles), use a dedicated renderless method:
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

**When multiple related toggles live near each other** (e.g. two gateway enable/disable switches), put them in ONE shared `x-data` scope with an array, not separate scopes — separate scopes can visually interfere with each other:
```html
<div x-data="{
    gateways: {{ json_encode($enabled_gateways) }},
    toggle(gw) {
        this.gateways = this.gateways.includes(gw)
            ? this.gateways.filter(g => g !== gw)
            : [...this.gateways, gw];
        $wire.toggleGateway(gw);
    },
    isOn(gw) { return this.gateways.includes(gw); }
}">
    <!-- both toggles reference the same gateways array -->
</div>
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

**Apply this to:** dropdown trigger labels, status badges, billing cycle/gateway toggles, any display text inside `x-data` that changes after `$wire.*` calls.

**Live calculators / previews that never need to hit the server at all** (e.g. the platform's Fee Absorption Calculator) should be built as PURE Alpine/JS with a `get result()` computed property — zero `$wire` calls, instant feedback, no Livewire round trip needed since it's just doing arithmetic on values already known client-side.

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

Two-column admin layouts (settings + sidebar-tips pattern) also need a mobile breakpoint:
```css
@media (max-width: 768px) {
    #two-column-grid   { grid-template-columns: 1fr !important; }
    #sticky-right-col  { position: static !important; }
}
```

---

## LANDING PAGE & PUBLIC MARKETING (Phase 10 — complete)

### Key Decisions
- Root `/` route changed from an auto-redirect to `tenant.login` → now serves the actual landing page. Login remains reachable at `/login`.
- **Documentation site and Public API v1 explicitly deferred** — user chose to wait until the platform is fully feature-complete/stable before writing docs (easier to write accurately once, rather than repeatedly updating as things change). Only the pre-existing Formspree-style form/consultation endpoints exist; no general public API.
- Pricing section pulls LIVE data from `plans`/`plan_prices` tables (same pattern as `/billing/upgrade`) — never hardcoded, respects `is_featured`, `allowed_cycles`, `annual_discount_percent`, and converts to visitor's likely currency via `CurrencyHelper::fromCountry()` + `BillingService::convertAmount()`.
- **Feature mockups are NOT generic icons/stock illustrations** — each of the 8 core feature sections (Event Management, Client Portal, Bookings & Consultations, Vendor Management, Task Management, Runsheets, RSVP & QR Check-In, Budgets & Payments) has a hand-built mini mockup styled identically to the real app UI (same `krd-` colors/fonts/patterns), showing a realistic-looking screenshot-style preview rather than marketing iconography. User's explicit reasoning: "event planners buy software they can picture themselves using."
- Dashboard mockup in the hero section: mini sidebar + stat cards + upcoming events list, all using fixed light-mode colors (`#fff`, `#1C1917`, etc.) regardless of the landing page's own dark/light toggle — since it represents the ACTUAL always-light-themed app UI, not themed marketing chrome. This was a bug initially (stat card numbers were invisible in landing-page dark mode because they inherited CSS custom properties) — fixed by hardcoding those specific mockup colors instead of using `var(--lp-text)` etc.

### Design System (landing page has its OWN scoped CSS, prefixed `lp-`, separate from `krd-`)
- CSS custom properties (`--lp-bg`, `--lp-text`, `--lp-accent`, etc.) swap via a `.dark` class for landing-page-specific dark/light toggle — persisted to `localStorage` under the SAME `krd-dark` key the rest of the app uses, so the preference carries over if the visitor later logs in
- Billing cycle toggle (Monthly/Annual) is 100% Alpine (`x-data="{ cycle: 'monthly' }"` on the outer page wrapper) — instant, zero server round trip; the Livewire component preloads BOTH monthly and annual pricing data upfront so both are available client-side immediately
- Scroll animations: single `IntersectionObserver` in the root `x-data.init()`, adds `.lp-visible` class to any `.lp-animate`/`.lp-animate-left`/`.lp-animate-right` element once it enters viewport — fires once per element (no infinite/looping re-triggering)
- Final CTA section is full-bleed (`.lp-final-cta-outer` spans 100% viewport width outside the `.lp-container` max-width wrapper) with inner content centered and max-width constrained — this was a specific fix requested (initially the black background was only as wide as the container, not the full page)
- "How It Works" flow steps wrap onto multiple lines on narrow screens (`flex-wrap: wrap`) rather than horizontally scrolling — an earlier version used `overflow-x: auto` which produced an unwanted visible scrollbar, fixed on request
- Fully responsive from header to footer: nav links hide on mobile (hamburger not built, just hidden — acceptable per user), hero CTAs stack full-width, all 8 feature blocks collapse to single column under 900px, dashboard mockup sidebar hides on narrow screens (main content only), pricing/problem grids collapse to single column under 420px

### Site Settings (Platform-controlled, `/platform/site-settings`)
- `PlatformSetting` model (key/value store, cached) — `site_name`, `site_tagline`, `site_favicon`
- Favicon uploadable, falls back to `public/images/logoonwhite.png` if none set
- `resources/views/partials/favicon.blade.php` — shared include used in `layouts/auth.blade.php`, `layouts/tenant.blade.php`, `layouts/landing.blade.php`

### SEO (basic — no dedicated documentation/structured-data phase yet)
- Meta tags (title, description, OG, Twitter card) on `layouts/landing.blade.php`
- `robots.txt` — static file, disallows dashboard/platform/vendor/client/billing paths
- `sitemap.xml` — built as a raw PHP string directly inside the route closure (`routes/web.php`), NOT a `.blade.php` view file — a literal `<?xml` tag inside a `.blade.php` file causes IDE/Blade-compiler red-squiggle conflicts; building the XML string in plain PHP inside the closure avoids the file-type conflict entirely

---

## DOMAIN MAPPING & WHITE LABEL ARCHITECTURE (Phase 11 — complete)

### Product Direction (locked in)
- **Feature gating integrates with the EXISTING feature flag system** — deliberately did NOT create dedicated `plans` table columns for domain/white-label capability. Three new feature flag keys added: `custom_subdomain` (new), `custom_domain` and `white_label` (both already existed in `feature_flags` from earlier seeding). All three managed through the pre-existing `feature_flags` / `plan_features` / `tenant_feature_overrides` / `FeatureGateService::canAccess()` — zero parallel/duplicate feature-management system.
- **The three capabilities are fully independent** (Option A, not tiered/cumulative) — platform owner can enable ANY combination per plan (e.g. Plan A: subdomain only; Plan B: subdomain + custom domain, no white label; Plan C: all three). Nothing hardcodes which plan gets which capability.
- **Every tenant gets an auto-assigned subdomain matching their slug at registration** (`TenantService::create()` sets `subdomain = $slug` and `domain_status = 'verified'` immediately — no DNS setup needed for subdomains since they're all under the app's own domain).
- **Domain verification is BOTH manual + scheduled**: tenant clicks "Verify Now" for immediate feedback (`DomainVerificationService::verify()` checks a CNAME record AND a TXT ownership-verification record via `dns_get_record()`), AND `koordli:recheck-domains` runs daily to catch DNS breakage/misconfiguration after the fact.
- **Tenant resolution architecture**: every incoming request determines the active tenant from EITHER the platform subdomain OR a verified custom domain — the rest of the app is unaware of HOW the tenant was identified. `ResolveTenantByDomain` middleware (aliased `tenant.byDomain`) populates `app('resolvedTenant')` + `TenantContext::set()`; no-ops entirely when the request is on the main app domain (existing session-based `tenant.resolve` middleware is completely untouched and still handles that case).

### White Label — Scope Boundary (important nuance, decided mid-build)
**The tenant ALWAYS sees Koordli branding in their own dashboard/sidebar/login — this was intentional and confirmed, not a limitation.** Reasoning (mirrors Shopify/Notion/Squarespace-style B2B SaaS): the tenant IS Koordli's customer. Just like a Shopify merchant always sees "Shopify" in their own admin panel even with a fully white-labeled storefront, a Koordli tenant seeing "Koordli" in their own workspace doesn't undermine anything and keeps Koordli's brand visible to the actual decision-maker.

**White label ONLY applies to surfaces the tenant's OWN clients/vendors see:**
1. Client Portal (post-login dashboard/sidebar)
2. Vendor Portal (post-login dashboard/sidebar)
3. Client/Vendor LOGIN pages — but ONLY when the visitor arrived via a resolved subdomain/custom domain (see below)
4. Public pages: RSVP, booking form, consultation form, RSVP edit, RSVP ticket PDF
5. 10 customer-facing emails (see Jobs section above for the full list + Rule 41's Group A/B split)

**Pre-login vs post-login tenant resolution (Rule 42) — this is the key architectural insight of this phase:**
- On the generic shared URL (`koordli.com/client/login`, no subdomain) → genuinely no way to know the tenant before authentication → Koordli branding shows, correctly, since there's no signal at all
- On a domain-resolved URL (`haywhy.koordli.com/client/login` or `app.haywhyevents.com/client/login`) → `tenant.byDomain` middleware (now also wired onto the client/vendor login route groups, not just the main tenant routes) resolves the tenant BEFORE login even happens → tenant's own logo/name shows if `white_label` is enabled on their plan
- AFTER login (regardless of which URL was used to arrive) → tenant is known directly from the authenticated user's own relationship (`auth('client')->user()->tenant` / `auth('vendor')->user()->tenant`) — this is DIFFERENT from `resolvedTenant` and is what the post-login dashboard/sidebar uses
- This ties Phase 11's two halves together meaningfully: setting up a subdomain/custom domain isn't just cosmetic, it's what actually UNLOCKS white-labeled client/vendor login screens — giving tenants a concrete functional reason to configure their domain

**Fallback chain when white_label is on but no logo has been uploaded:** tenant's own NAME shown as styled text (not blank, not broken layout) — e.g. "Haywhy Events" in place of a logo image. `<x-ui.portal-logo>` component encapsulates the entire priority chain (see Reusable UI Components section above).

**Logos deliberately NOT added to transactional emails** — user explicitly decided this was unnecessary complexity/risk (email client image-blocking, inline CID attachment complexity) for the value gained; text-based branding swap (`{{ $whiteLabel ? $companyName : 'Koordli' }}`) was judged sufficient for emails.

### Domain Settings Page (`/domain-settings`, tenant-facing)
Three cards, each independently feature-gated:
1. **Platform Subdomain** — always-on for tenants whose plan includes `custom_subdomain`; instant, no DNS/verification needed
2. **Custom Domain** — input + Add/Update/Remove/Verify Now buttons; shows DNS Records Required box (CNAME + TXT ownership record) once a domain is added; shows `domain_status` badge (Verified/Pending/Failed), last-checked timestamp, verified-since date
3. **White Label Branding** — read-only status card explaining what gets branded, "Upgrade to unlock" CTA if not on tenant's plan
Two-column layout (form left, "How domains work" + Status Guide + conditional DNS-propagation-wait tip on the right) — matches the established `krd-` two-column admin pattern.

### Known Bugs Fixed During This Phase (see Rules 38, 39)
- **Plan edit route parameter mismatch** — `Route::get('/plans/{plan}/edit', ...)` vs `mount(?int $planId)` — Livewire silently passed `null`, causing Edit to behave exactly like Create with zero errors thrown. Fixed by renaming the route parameter to `{planId}`.
- **`Tenant` model's incomplete `$fillable`** — was missing `billing_currency`, `detected_country`, AND all six new domain columns. Every `update()` silently dropped those fields — Domain Settings showed "Success" toasts but nothing persisted, fields went blank on refresh. This is a DIFFERENT and easy-to-miss failure mode from the route-param bug above (no error either way) — always double check `$fillable` immediately after any migration adds columns to an existing table.
- **`SendVendorAssignedJob` was dead code** — Job, Mailable, and blade template all existed and were fully built, but had ZERO dispatch call sites anywhere in the codebase (confirmed via full-app grep). Wired up during this phase inside `VendorDetail::assignToEvent()`, which now also auto-creates a `VendorAccount` portal login on first assignment (mirroring the existing logic in `VendorDetail::inviteVendor()`), and correctly threads the `whiteLabel` boolean through.

### Production Infrastructure (documented separately, not yet actioned — no server purchased yet)
Full standalone guide delivered as `KOORDLI_PRODUCTION_TRAEFIK_SETUP.md` covering: Cloudflare DNS-01 wildcard SSL setup for `*.koordli.com`, a second HTTP-01 certificate resolver specifically for tenant custom domains (since Cloudflare's API only manages Koordli's own zone, not tenant-owned domains), complete `docker-compose.yml` with both Traefik routers (priority-ordered: exact `koordli.com`/`*.koordli.com` match first via wildcard cert, catch-all fallback for anything else via on-demand HTTP-01 cert), and a reminder that `koordli:process-subscriptions` AND `koordli:recheck-domains` both require a real production cron entry (`* * * * * php artisan schedule:run`) — neither runs automatically, including on the current Windows local dev machine.

### Local Development Testing (optional, not required)
Windows hosts file (`C:\Windows\System32\drivers\etc\hosts`) can simulate subdomains/custom domains for local testing: add fake `.test` domain entries pointing to `127.0.0.1`, set `APP_URL` accordingly, run `php artisan serve --host=koordli.test`. Custom domain DNS verification can't work against fake local domains (no real DNS), so `domain_status` can be force-set to `verified` via tinker for local testing purposes only. User opted to skip this and verify for real once a server is purchased instead.

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

### Phase 9 — Billing & Subscriptions ✅
- Full Paystack + Flutterwave integration via Laravel `Http` facade (no SDK packages) — tested end-to-end with a real Paystack test payment
- Gateway fee absorption — platform sets NGN price, tenant is charged the calculated amount so platform receives the exact price after gateway fees; formula + rates fully configurable at `/platform/billing`
- Multi-currency checkout via Frankfurter API (cached, configurable hours)
- Monthly + Annual billing cycles, per-plan configurable (`allowed_cycles`), annual discount %
- Featured-plan-first + auto-select-if-only-one-plan on registration and upgrade page
- Upgrade page (`/billing/upgrade`) — plan grid, instant Alpine-owned cycle/gateway toggles, dark-mode-safe buttons (`krd-btn-primary`)
- Billing callback — verifies payment server-side, activates subscription, creates invoice record
- Billing dashboard (`/billing`) — current plan, status, invoice history
- **Read-only lockout** — two-layer enforcement (`EnsureTenantActive` middleware for HTTP/GET, `SubscriptionLockHook` Livewire ComponentHook for write-action blocking) since Livewire v4 bypasses normal route middleware; toast + 1.5s redirect to upgrade page on any blocked write attempt
- Trial banner — color-coded (violet trial / amber grace / red locked), always links to upgrade
- Daily scheduled command `koordli:process-subscriptions` — expires subs, sends 14-day + 3-day reminders, sends expiry emails
- One-time `koordli:backfill-subscriptions` command for pre-Phase-9 tenants with no subscription row
- Platform Billing Config (`/platform/billing`, 3 tabs): Settings (with Billing Overview stats/revenue + live Fee Absorption Calculator), Gateway Charges (editable rates + formula explainer), API Keys (with where-to-find guide + webhook URLs)
- Jobs/Emails `SubscriptionActivatedMail + PlatformPaymentNotificationMail` — SubscriptionActivatedMail sent to TENANT as
  a payment receipt, PlatformPaymentNotificationMail sent to PLATFORM OWNER for revenue tracking, both dispatched from
  `BillingService::activateSubscription()` right after successful payment verification
- Fixed critical migration bug: `subscriptions`, `subscription_invoices`, `plan_prices` all had `id` columns missing `auto_increment` — fixed via `ALTER TABLE ... MODIFY id BIGINT UNSIGNED AUTO_INCREMENT` migrations

### Phase 8 — Vendor Ecosystem ✅ (8.1 through 8.5, all complete)
- **8.1 Availability**: vendor self-manages unavailable dates from `/vendor/availability`; planner sees read-only + gets
  a non-blocking conflict warning banner when assigning to a conflicting event date
- **8.2 Reviews**: planner (80% weight) + client (20% weight) reviews, tied to specific event assignments, 6 scored
  categories, 7-day edit window then locked, weighted `Vendor::recalculateRating()` auto-runs on save, private to
  tenant for MVP (marketplace-ready `is_public` flag exists but unused)
- **8.3 Contracts**: reusable placeholder templates, contenteditable rich text + Preview toggle, branded PDF export
  (tenant logo/colors, custom Satoshi+Spline Sans fonts), full e-signature flow (draw or type, both planner and vendor,
  IP+timestamp audit trail, unique expiring signing link, auto status update when both parties sign, planner notified
  by email when vendor signs), signed-copy upload fallback, full status history log
- **8.4 Invoicing**: multi-invoice-per-vendor-per-event, multi-payment-per-invoice, auto budget sync, auto-created
  transparently when a vendor is assigned with an amount (keeps old Assignment fields in bidirectional sync), one-time
  backfill command for pre-existing assignment data
- **8.5 Marketplace-ready schema**: dormant `is_public`/`slug`/`city`/`country`/`portfolio_images` fields on `vendors`,
  zero UI, zero behavior change — pure future-proofing

### Phase 10 — Public Facing & Landing Page ✅
- Root `/` serves a full landing page (was previously an auto-redirect to tenant login)
- Live pricing pulled from `plans`/`plan_prices`, same conversion logic as `/billing/upgrade`
- 8 feature sections each with a hand-built mockup styled to match the real app UI (not generic icons/stock art)
- Dark mode toggle (own `lp-` prefixed CSS vars, separate from `krd-`), persists to same `localStorage` key as rest of app
- Instant Alpine-only billing cycle toggle, scroll-triggered fade/slide animations (fire once, no looping), full mobile responsiveness header-to-footer
- Platform-controlled Site Settings (`/platform/site-settings`): site name, tagline, favicon — used across landing page + shared favicon partial
- Basic SEO: meta tags, `robots.txt`, `sitemap.xml` (built as raw PHP string in the route closure, not a Blade view)
- **Explicitly deferred**: full documentation site, public API v1 — user chose to wait until the platform is fully feature-complete before writing docs

### Phase 11 — Domain Mapping & White Labeling ✅
- Three new/existing feature flags (`custom_subdomain` new, `custom_domain`/`white_label` pre-existing) wired through the EXISTING `feature_flags`/`plan_features`/`tenant_feature_overrides`/`FeatureGateService` system — zero duplicate feature-management logic
- Fully independent per-plan toggles — platform owner can enable any combination for any plan
- Every tenant auto-assigned a subdomain matching their slug at registration, instantly verified (no DNS needed)
- Tenant-facing Domain Settings page (`/domain-settings`): subdomain, custom domain + DNS instructions (CNAME + TXT), manual "Verify Now" + daily scheduled re-verification (`koordli:recheck-domains`), white label status card
- `ResolveTenantByDomain` middleware (`tenant.byDomain`) — resolves tenant from Host header (subdomain or verified custom domain), populates `app('resolvedTenant')`, no-ops on the main app domain, completely independent of existing session-based tenant resolution
- White label scope: tenant ALWAYS sees Koordli branding in their own dashboard (deliberate, matches Shopify/Notion-style SaaS convention); only client/vendor-facing surfaces respect `white_label` — Client/Vendor portals (post-login, via `auth()->user()->tenant`), Client/Vendor login pages (pre-login, via domain-resolved `resolvedTenant` — ONLY works when arriving via subdomain/custom domain, generic shared URL stays Koordli-branded), 5 public pages (RSVP, booking, consultation, RSVP edit, ticket PDF), and 10 customer-facing emails
- `<x-ui.portal-logo>` reusable component encapsulates the full fallback chain: explicit tenant → resolvedTenant → tenant's uploaded logo → tenant's name as text → Koordli logo
- Logos deliberately NOT added to emails (text-only branding swap) — explicit user decision to avoid email client image-blocking complexity for the value gained
- Production Traefik/SSL setup fully documented in a separate reference file (`KOORDLI_PRODUCTION_TRAEFIK_SETUP.md`) — Cloudflare wildcard DNS-01 + on-demand HTTP-01 for tenant custom domains, complete docker-compose config, troubleshooting table — not yet actioned since no server purchased
- 3 real bugs found and fixed during this phase: Plan edit route parameter mismatch (Rule 38), `Tenant` model's incomplete `$fillable` silently dropping several columns (Rule 39), and a previously-dead `SendVendorAssignedJob` with zero dispatch sites, now wired into `VendorDetail::assignToEvent()`

---

### NEW CRITICAL RULES TO NOTE
- 43 NEVER USE BACKSLASH-ESCAPED QUOTES (\") INSIDE AN x-data="..." HTML ATTRIBUTE, EVEN INSIDE A JS TEMPLATE LITERAL. Browsers parse HTML attribute boundaries before JS syntax — the moment a \" appears inside a double-quoted x-data="..." attribute, the browser treats the attribute as closed right there, and everything after it renders as literal visible page text instead of executing as JS. This is not an Alpine or Livewire bug — it's fundamental HTML attribute parsing. Fix pattern: any time real-time/dynamic HTML needs to be built client-side (e.g. appending new chat messages via Echo), do NOT build it inline inside an x-data="{...}" attribute string. Instead: (a) use Alpine.data('componentName', (...) => ({...})) registered inside a real <script> block via document.addEventListener('alpine:init', ...), and reference it with x-data="componentName(@js($phpValue))", or (b) build DOM nodes programmatically with document.createElement() + .textContent/.style.cssText (plain string concatenation with +, never template literals with embedded HTML attributes) rather than injecting raw HTML strings with quotes.
- 44 wire:ignore.self DOES NOT PROTECT AN ELEMENT'S CHILDREN FROM LIVEWIRE'S MORPH ENGINE — only wire:ignore (without .self) fully excludes an element AND its descendants from Livewire's DOM morphing. Using wire:ignore.self on a container that has x-data state referenced by descendants (e.g. a typing boolean shown via x-show on a child span) can cause Livewire to re-morph those children on every re-render, silently corrupting Alpine's reactive scope for that subtree — symptoms include "Alpine Expression Error: X is not defined" console errors and previously-working reactive bindings suddenly breaking after a Livewire round-trip. Rule of thumb for real-time chat UIs: once a page enters a "live"/real-time state, that entire message container should be wire:ignore (full, not .self) and ALL further updates to it must happen via direct DOM manipulation in JS (Echo listeners), never via $wire.call() + Livewire re-render — mixing the two in the same subtree is what caused this bug.
- 45 LARAVEL ECHO'S .join() METHOD AUTO-PREPENDS THE presence- PREFIX — never include presence- yourself in the channel name string passed to .join(). Passing 'presence-support-ticket.{uuid}' results in Echo/Reverb actually subscribing to presence-presence-support-ticket.{uuid} (double-prefixed), which won't match your routes/channels.php registration and silently fails all presence detection (no errors thrown — it just never triggers .here()/.joining() callbacks). Always pass the bare logical channel name (e.g. 'support-ticket.{uuid}') to both .join() (presence) and .private() (private) — Echo appends the correct prefix automatically depending on which method you call. One Broadcast::channel('support-ticket.{uuid}', ...) registration in routes/channels.php correctly authorizes BOTH the private and presence channel of that same logical name — no need for two separate route registrations.
- (RULe 46---)APPS WITH MULTIPLE AUTH GUARDS (e.g. web/platform/client/vendor) NEED A CUSTOM BROADCASTING AUTH ENDPOINT — Laravel's auto-registered /broadcasting/auth route only checks the app's single default guard (config('auth.defaults.guard')), so any guard other than the default (e.g. platform) will always fail private/presence channel authorization with a silent 403, even if routes/channels.php is written correctly. Fix: register a custom POST route (e.g. /broadcasting/multi-auth) that checks each relevant guard in priority order and calls Broadcast::auth($request->setUserResolver(fn() => auth($guard)->user())) for whichever guard is actually authenticated, then point Laravel Echo's client config at this custom endpoint via the authEndpoint option instead of the default.
- 47 BROADCAST EVENT PAYLOADS SHOULD PRE-COMPUTE ANY DISPLAY-CONTEXT-DEPENDENT VALUES SERVER-SIDE — e.g. link color inside a rendered chat message differs depending on whether it's shown on a light or dark/colored bubble background. Rather than trying to re-derive that context client-side from the raw broadcast payload, pass a boolean/context flag into the model method that renders the value (renderedMessage(bool $onDarkBubble = false)) and call it correctly both in the initial page-load Blade loop AND inside the broadcastWith() method of the corresponding ShouldBroadcast event — both code paths must agree, or the initial page load and live-appended messages will visually differ.

### DATABASE — NEW CENTRAL TABLES (Support System)

support_faqs                       ← question, answer (plain text, linkified at render), keywords (JSON array,
                                      used for simple keyword-match scoring), category, is_active, sort_order.
                                      Searched by SupportFaq::searchByMessage() — scores by keyword intersection
                                      count against the tenant's typed message, no external AI/API call (rule-based,
                                      Option A was chosen over an AI-powered bot for cost/complexity reasons)
support_agents                     ← platform_user_id (unique, one agent record per platform user), is_available
                                      (bool toggle agent controls themselves), status (online|away|offline),
                                      max_concurrent_chats, active_chat_count, last_seen_at.
                                      SupportAgent::nextAvailable() picks the least-busy available+online agent
                                      (load-balances via orderBy('active_chat_count'))
support_tickets                    ← uuid, tenant_id, created_by_user_id (tenant User id, not FK — cross-schema
                                      pattern used elsewhere in the app), subject, description, priority
                                      (low|medium|high|urgent), category, status (open|in_progress|resolved|closed),
                                      source (ticket|chat|email — email exists as a status value but no email-to-
                                      ticket ingestion was built, it's just the enum slot for a future feature),
                                      assigned_agent_id, rating (1-5 nullable), rating_comment, rated_at,
                                      resolved_at, tenant_last_read_at, agent_last_read_at (added in Stage 5 for
                                      the unread-badge feature)
support_ticket_messages            ← ticket_id, sender_type (tenant|agent|bot|system — 'system' added in Stage 5
                                      polish for auto-close warnings), sender_id (nullable — null for bot/system),
                                      message (ALWAYS plain text, never HTML — see renderedMessage() below)
support_ticket_attachments         ← ticket_id, message_id (nullable), file_path, file_name, file_size, mime_type,
                                      uploaded_by_type, uploaded_by_id
support_ticket_assignment_history  ← ticket_id, from_agent_id (nullable), to_agent_id, reason, changed_by
                                      (platform_user_id) — full audit trail for every claim/handoff
support_chat_sessions              ← ticket_id (unique — one session per ticket), status (bot|waiting|active|ended),
                                      bot_engaged_at, escalated_at, agent_joined_at, ended_at, ended_by
                                      (tenant|agent|system)


- Note: support_ticket_assignment_history's table name is genuinely plural-irregular for Eloquent's auto-pluralizer — the model explicitly sets protected $table = 'support_ticket_assignment_history' to avoid the same auto-pluralization bug documented earlier for VendorContractStatusHistory (Eloquent would otherwise guess support_ticket_assignment_histories).

### KEY FILE LOCATIONS — SUPPORT SYSTEM

SupportFaq.php                      ← searchByMessage() static keyword-scoring search
SupportAgent.php                    ← canAcceptMoreChats(), nextAvailable() (load-balanced pick)
SupportTicket.php                   ← priorityColor(), statusColor(), statusLabel(), assignTo() (writes
                                        assignment history + increments/decrements agent chat counts),
                                        unreadForTenant(), markReadByTenant() (Stage 5)
SupportTicketMessage.php            ← senderName(), renderedMessage(bool $onDarkBubble = false) — the ONLY
                                        place plain-text messages become linkified HTML; escapes first via e(),
                                        then regex-replaces http(s):// URLs into <a> tags with context-aware link color
SupportTicketAttachment.php         ← isImage(), humanSize()
SupportTicketAssignmentHistory.php  ← protected $table set explicitly (see rule above)
SupportChatSession.php              ← isLive() (status in [waiting, active])

### Livewire Components

app/Livewire/Platform/Support/AgentStatus.php        ← topbar availability toggle, auto-creates SupportAgent
                                                          row on first toggle-on
app/Livewire/Platform/Support/FaqList.php             ← FAQ CRUD, keyword comma-string ↔ array conversion
app/Livewire/Platform/Support/TicketInbox.php         ← 3-way view filter (mine/unassigned/all) + status filter,
                                                          refreshList() no-op method exists purely so JS Echo
                                                          listeners can force a re-render via $wire.call()
app/Livewire/Platform/Support/PlatformTicketDetail.php ← claim/handoff/reply/status/archive/delete, dispatches
                                                          SendSupportTicketReplyJob for async tickets, broadcasts
                                                          SupportChatMessageSent directly for live chat tickets
                                                          (no email in that case — tenant is watching live)
app/Livewire/Tenant/Support/HelpWidget.php            ← topbar "Help"/"Live Chat" button, unread badge, jumps
                                                          straight into an active chat instead of showing the
                                                          3-way modal if one already exists (Stage 5)
app/Livewire/Tenant/Support/CreateTicket.php          ← async ticket creation form, first message = description
app/Livewire/Tenant/Support/TicketList.php            ← "My Tickets", instant status filter (wire:ignore + Alpine
                                                          local state pattern, see Rule 44's sibling issue below)
app/Livewire/Tenant/Support/TicketDetail.php          ← async ticket conversation view, reopens ticket to
                                                          in_progress if tenant replies after "resolved", rating flow
app/Livewire/Tenant/Support/ChatBot.php                ← the whole bot conversation state machine (see Bot Logic
                                                          section below) — resumes an existing open chat session
                                                          on mount() instead of always creating a new ticket
                                                          (critical fix — was creating duplicate tickets on every
                                                          page refresh before this was added)

### Events (all App\Events, all ShouldBroadcast)

SupportChatMessageSent   ← broadcasts on private-channel 'support-ticket.{uuid}', broadcastAs 'message.sent',
                            broadcastWith() includes pre-rendered HTML (see Rule 47)
SupportChatWaiting       ← broadcasts on private-channel 'support-queue' when bot escalates to human,
                            broadcastAs 'chat.waiting' — platform TicketInbox listens for this to show a
                            live toast + refresh without polling
SupportChatAccepted      ← broadcasts on BOTH 'support-ticket.{uuid}' and 'support-queue' when an agent claims
                            a chat — the queue broadcast lets OTHER agents' inbox views remove it live too


### Routes 
routes/channels.php  — see below, this is the most important file for the whole real-time layer

// ONE registration serves both private-support-ticket.{uuid} AND presence-support-ticket.{uuid}
Broadcast::channel('support-ticket.{uuid}', function ($user, string $uuid) {
    $ticket = SupportTicket::where('uuid', $uuid)->first();
    if (!$ticket) return false;
    if (auth('web')->check() && auth('web')->id() === $ticket->created_by_user_id) {
        return ['id' => 'tenant-' . auth('web')->id(), 'name' => auth('web')->user()->name, 'type' => 'tenant'];
    }
    if (auth('platform')->check()) {
        $agent = SupportAgent::where('platform_user_id', auth('platform')->id())->first();
        if ($agent && $ticket->assigned_agent_id === $agent->id) {
            return ['id' => 'agent-' . $agent->id, 'name' => auth('platform')->user()->name, 'type' => 'agent'];
        }
    }
    return false; // this is the "no mix-up" guarantee — an unassigned/wrong agent is rejected here, not just hidden in UI
});

Broadcast::channel('support-queue', function ($user) {
    if (!auth('platform')->check()) return false;
    $agent = SupportAgent::where('platform_user_id', auth('platform')->id())->first();
    return $agent ? ['id' => $agent->id, 'name' => auth('platform')->user()->name] : false;
});

Route::post('/broadcasting/multi-auth', ...) ← custom multi-guard broadcasting auth (see Rule 46), registered
                                                 OUTSIDE any guard-specific group, checks platform → web → client
                                                 → vendor in that priority order

Tenant (authenticated group):
/support/tickets                  → Tenant\Support\TicketList
/support/tickets/create           → Tenant\Support\CreateTicket
/support/tickets/{uuid}           → Tenant\Support\TicketDetail
/support/chat                     → Tenant\Support\ChatBot

Platform (auth.platform group):
/platform/support/tickets         → Platform\Support\TicketInbox
/platform/support/tickets/{uuid}  → Platform\Support\PlatformTicketDetail
/platform/support/faqs            → Platform\Support\FaqList

### Console Commands (new one added)

app/Console/Commands/CloseInactiveChatSessions.php   ← koordli:close-inactive-chats, scheduled everyFiveMinutes().
                                                          Two-stage: warns via a 'system' sender_type message after
                                                          5 min of tenant inactivity on an active chat, then closes
                                                          (sets chat_session status=ended, ticket status=resolved)
                                                          after 10 min total if still no reply. Both the warning and
                                                          the close message broadcast live via SupportChatMessageSent
                                                          so the tenant sees them in real time even if the page is
                                                          still open.

Schedule::command('koordli:close-inactive-chats')->everyFiveMinutes();

### SUPPORT SYSTEM — FULL ARCHITECTURE (Stages 1–5, complete)
- Scope & Access Model (decided up front)
Agents = platform staff only, but WHICH platform staff can act as agents is itself controlled by the platform (each platform_user opts in via their own SupportAgent row + is_available toggle) — not every platform user is automatically an agent.
Every live chat auto-creates a lightweight support_ticket behind the scenes (source='chat') the moment the tenant opens the chat widget, even before any human is involved — so there's always a permanent record, never a chat that "just disappears."
Strict no-mix-up guarantee: an agent can NEVER see or subscribe to a chat/ticket they aren't assigned to — enforced at the routes/channels.php authorization layer itself (returns false, hard rejection), not just hidden in the UI. Multi-agent handoff is fully supported via SupportTicket::assignTo() + support_ticket_assignment_history audit trail.
Post-conversation rating: 1-5 stars + optional comment, shown to the tenant once a ticket/chat reaches resolved or closed status, visible to the platform agent in the ticket detail sidebar.

### Three-Way Tenant Entry Point

Tenant clicks the "Help" button (topbar, not a floating bubble — deliberately moved off floating-bubble-in-corner per user preference) → sees three choices:

📋 Open a Ticket — async, full form (subject/priority/category/description/attachments)
💬 Live Support — goes to the bot first (see below)
✉️ Email Koordli — plain mailto: link, no ticket created

### Bot Conversation Flow (rule-based, NOT AI/API-powered — Option A was explicitly chosen)
- Bot greets tenant, offers 4 quick-reply buttons: Billing/Plan Question, How do I...?, Something's broken, Talk to a human
- Billing/Plan Question — bot pulls LIVE data directly from the tenant's own Subscription/Plan/FeatureGateService state (not from the FAQ table): plan name, trial/active/grace status, days remaining, next renewal date, and which features are enabled (custom_subdomain, custom_domain, white_label, rsvp, vendor_portal, client_portal, api_access, etc.) — genuinely answers "am I on a plan that supports X?" without any human involvement
- How do I...? / Something's broken — free-text input, matched against support_faqs.keywords via simple word-intersection scoring (SupportFaq::searchByMessage()), shows best match(es); if nothing matches confidently, bot proactively offers to escalate
- Talk to a human — calls SupportAgent::nextAvailable() (least-busy online+available agent). If one exists: session status → waiting, ticket becomes visible in the platform queue, tenant sees a live JS countdown (starts at 3:00, purely client-side setInterval, NOT tied to any real "average wait time" calculation — it's a UX/psychological device, not a guarantee). If NO agent available: friendly fallback message ("Our agents are currently busy... leave a message and we'll follow up by email"), tenant's next message becomes the ticket description, ticket stays as a normal async ticket for later reply.
- Typewriter effect on the most recent bot message only (older messages render instantly) — pure Alpine setInterval slicing the string, no backend involvement.
- Chat resume on refresh: ChatBot::mount() checks for an existing ticket+session in bot|waiting|active status for that tenant user before creating a new one — critical fix, without this every page refresh created a brand new duplicate ticket.

### Real-Time Layer (Laravel Reverb)
- Presence channel (support-ticket.{uuid}, joined via .join()) — used for (a) detecting when the assigned agent has actually entered the room (.here()/.joining() callbacks check user.type === 'agent', then call $wire.call('agentJoined') which flips the tenant's chat stage from waiting → active), and (b) typing indicators via .whisper() — whisper event names are direction-specific (tenant-typing vs agent-typing) to avoid ambiguity about who's typing.
- Private channel (same logical name, subscribed via .private()) — carries the actual message.sent broadcast for every new chat message, consumed by BOTH the tenant's ChatBot view and the platform's PlatformTicketDetail view.
- Private queue channel (support-queue) — every available agent subscribes; SupportChatWaiting broadcasts here when the bot escalates, so the Ticket Inbox shows a live toast + updates without polling; SupportChatAccepted also broadcasts here so OTHER agents' inbox views remove the now-claimed chat live too.
- Everything real-time is rendered via plain JS DOM manipulation inside Alpine.data() components declared in <script> tags — NOT inside x-data="..." HTML attributes, and NOT mixed with Livewire's morph engine on the same subtree (wire:ignore, full not .self, on any container once it enters "live" mode). See Rules 43 and 44 for why this matters — earlier attempts using template literals inside HTML attributes and wire:ignore.self both produced real, hard-to-diagnose bugs (literal JS text rendering as page content; "Alpine Expression Error: X is not defined" from morphing-corrupted reactive scope).
- Minimizable Chat + Unread Notification (Stage 5)
- HelpWidget (topbar, present on every tenant page) checks on every load whether an active/waiting chat session exists for the current user; if so, the button relabels to "Live Chat" and shows a red unread-count badge
- A hidden wire:ignore Alpine component silently joins that ticket's private channel in the background from ANY page (not just the chat page itself) — new agent messages trigger a toast notification + badge increment even while the tenant is elsewhere in the app
- Clicking the Help button while a chat is active navigates straight to /support/chat (skipping the 3-way choice modal) and resumes exactly where the conversation left off
- SupportTicket::markReadByTenant() / unreadForTenant() track read state via tenant_last_read_at timestamp compared against message created_at — called both on chat page mount and whenever a live message arrives while the chat page itself is already open (so the badge never falsely shows unread for a conversation currently being viewed)
- Auto-Close on Inactivity (Stage 5 polish)

Two-stage scheduled check (koordli:close-inactive-chats, every 5 min): warns via a system-sender-type message at 5 minutes of tenant inactivity ("this chat will close in 5 minutes..."), then actually closes (session→ended, ticket→resolved) at 10 minutes total if still no reply — both messages broadcast live so an open tenant tab sees them appear in real time, not just on next page load.

### Platform Ticket Management
Claim — any available agent can claim an unassigned ticket from the inbox; writes to support_ticket_assignment_history, increments active_chat_count
Hand off — reassign to a different agent with an optional reason note; full audit trail preserved, decrements old agent's count / increments new agent's
Archive — sets status = 'closed' (no separate archived flag/column — reuses the existing status enum, consistent with the app's "hard delete only, no soft deletes" convention elsewhere, except tickets specifically also support permanent deletion as a distinct, separate, confirmed-via-modal action)
Delete — genuinely permanent, cascades to messages/attachments/assignment history/chat session via FK cascadeOnDelete()

### Known Product Decisions Worth Remembering
Reverb chosen over Pusher/Ably specifically because it was already installed by the user before this feature was scoped — confirmed working end-to-end with a custom multi-guard auth endpoint (Rule 46)
FAQ bot uses simple keyword-intersection scoring, deliberately NOT an AI API call — chosen for zero marginal cost per conversation and simplicity, at the cost of being less flexible than a true LLM-backed assistant; revisit if conversation volume/quality demands it later
The 3-minute countdown shown to waiting tenants is a fixed UX device, not a real computed estimate — there is no dynamic "average wait time" calculation anywhere in the system yet

##### NEW EXPANSION

# ADDENDUM — INDUSTRY EXPANSION (Production Management Support), Complete

*Append this section to KOORDLI_PROJECT_CONTEXT.md. Insert the new numbered rules into the CRITICAL RULES list (continuing the existing numbering), and the rest as a new top-level section, e.g. right after the Support System addendum.*

---

## STRATEGIC CONTEXT (why this exists)

Koordli was originally built for event planners. This phase deliberately extended the SAME platform to also serve Production Companies (film, TV, concert, church, theatre, conference, entertainment, exhibition) **without forking the codebase, redesigning the architecture, or turning Koordli into a generic project-management/ERP tool.**

Core principle agreed with the user: **this is a terminology + feature-flag + seed-data exercise, not an architectural rebuild.** Every single thing built in this phase is additive — new tables, nullable columns on existing tables, or new optional JSON keys — nothing existing was modified in a breaking way. Event planners remain the primary target audience; production support is a configuration layer on top of the same foundation.

---

## NEW CRITICAL RULES TO ADD

48. **TENANT PROVISIONING MUST GO THROUGH A SINGLE CENTRALIZED SERVICE, NEVER CALLED DIRECTLY FROM MULTIPLE ENTRY POINTS.** A tenant can be created via self-registration, platform-owner manual creation, or (future) API/import — all three MUST produce byte-for-byte identical provisioning behavior. `App\Services\TenantProvisioningService::provision(array $data)` is the ONLY correct way to create a tenant going forward; it wraps `TenantService::create()` (unchanged, still does account/user creation) plus applies the selected `IndustryProfile`'s seed data, recommended feature flags, and default roles, all inside one DB transaction. Any new tenant-creation entry point (future API, import tool, etc.) MUST call this service, never call `TenantService::create()` directly and duplicate the seeding logic inline.
49. **INDUSTRY PROFILES ARE TEMPLATES READ ONCE AT PROVISIONING TIME, THEN FULLY FORKED INTO THE TENANT'S OWN DATA — NEVER A LIVE DEPENDENCY (with one deliberate exception: terminology).** Once `DefaultTenantSeeder::run($tenantId, $profile)` seeds a tenant's `event_types`/`vendor_categories`/`task_categories`/`asset_categories`, that data belongs entirely to the tenant from that point forward — editing, deactivating, or even deleting the source `industry_profiles` row has zero effect on any already-provisioned tenant. **Terminology is the sole exception**: `term()`/`term_title()` resolve the industry profile's terminology JSON live, every time, specifically because the user wants terminology to always be adjustable/inheritable, unlike the one-time seed data. Don't conflate these two different "profile data" behaviors when extending this system later.
50. **NEVER PASS RAW EMOJI CHARACTERS INTO DOMPDF-RENDERED CONTENT.** Even with a properly registered custom font (Satoshi, per Rule about Phase 8.3), emoji glyphs (🏢, 👤, 📍, etc.) are frequently NOT present in that font's glyph table and render as a visible broken-glyph/placeholder box (can look like a "pause" or "mute" icon) rather than failing loudly. This is the same class of bug as the ₦ Naira symbol issue from Phase 8.3 — DomPDF font rendering has much narrower Unicode coverage than a browser. **Fix pattern:** never use emoji in any `.blade.php` file under `resources/views/pdf/` — use plain text labels, or if a visual icon is truly needed, use small inline SVG icons or CSS-drawn shapes instead. This applies to ALL current and future PDF templates (contracts, call sheets, tickets, etc.), not just the one that broke.
51. **TERMINOLOGY OVERRIDES LIVE INSIDE THE EXISTING `tenants.branding` JSON COLUMN (key: `terminology_overrides`), NOT A NEW COLUMN.** This mirrors the existing pattern where `branding` already holds `primary_color`/`accent_color`/`logo` — terminology is just another optional key in that same JSON blob, resolved by `TerminologyHelper::term()` in this priority order: (1) tenant's own override in `branding.terminology_overrides`, (2) the tenant's `industry_profile.terminology` JSON, (3) the hardcoded English default the calling code passes in. Every `term()`/`term_title()` call site MUST always pass a sensible hardcoded English default as the second argument — never assume a profile or override exists.
52. **NEW OPTIONAL MODULES (Assets, Multi-Location, etc.) MUST DEGRADE INVISIBLY TO ZERO UI FOOTPRINT WHEN UNUSED.** The Runsheet's Location dropdown only renders at all when `$locations->isNotEmpty()` for that specific event — a tenant who never adds a location sees literally no change to their Runsheet experience, not even an empty/disabled dropdown. This is the deliberate pattern for every "lightweight optional feature" added in this phase: check for the presence of related data, not a feature flag alone, before showing any related UI, so single-location/no-asset tenants (the majority) see zero added complexity.
53. **BACKWARD COMPATIBILITY FOR NEW FEATURES ON EXISTING TENANTS REQUIRES AN EXPLICIT ONE-TIME BACKFILL COMMAND** — new seed data (like Asset Categories) introduced via `DefaultTenantSeeder` only runs automatically for tenants created AFTER the feature ships; tenants created before it exist get nothing retroactively unless a dedicated backfill command is run once (e.g. `koordli:backfill-asset-categories`, which checks `if already has data, skip` per tenant so it's safely re-runnable). This is the same category of "new feature needs manual backfill for existing rows" pattern already established with `koordli:backfill-vendor-invoices` in Phase 8.4 — whenever seed data changes, always ask whether existing tenants need a backfill command too.

---

## DATABASE — NEW CENTRAL & TENANT TABLES

### Central
```
industry_profiles     ← key (wedding_events|corporate|production|church|conference|entertainment|exhibition|other),
                         name, icon, description, terminology (JSON: event→Production, client→Producer,
                         guest→Audience, vendor→Supplier + _plural variants), default_event_types (JSON array of
                         {name,icon,color}), default_vendor_categories (JSON array), default_task_categories
                         (JSON array), default_roles (JSON array of role name strings), recommended_feature_flags
                         (JSON array of feature_flags.key values), is_active, sort_order.
                         Seeded once via IndustryProfileSeeder — 8 profiles pre-populated with realistic defaults
                         for each named industry.
```

### On existing `tenants` table (new nullable column)
```
tenants.industry_profile_id  ← FK to industry_profiles, nullOnDelete(), fully nullable — every tenant created
                                before this phase has NULL here and behaves exactly as before
```

### Tenant-scoped (Assets module)
```
asset_categories        ← tenant_id, name, icon, sort_order (same shape as vendor_categories)
assets                  ← tenant_id, asset_category_id (nullable FK), name, status (available|reserved|maintenance),
                           notes
asset_event_assignments ← tenant_id, asset_id, event_id, date_from (nullable), date_to (nullable), notes.
                           unique(['asset_id','event_id']) — same pivot pattern as vendor_event_assignments.
                           Asset status auto-flips to 'reserved' on assignment, back to 'available' when its
                           last assignment is removed (handled in AssetDetail::assignToEvent()/deleteAssign())
```

### Tenant-scoped (Multi-Location support)
```
event_locations   ← tenant_id, event_id, name (e.g. "Studio A"), address (nullable), date (nullable — which day
                     this location applies to for multi-day events), notes, sort_order
```

### On existing `runsheet_items` table (new nullable column)
```
runsheet_items.event_location_id  ← FK to event_locations, nullOnDelete(), fully nullable. Every existing
                                     runsheet item has NULL here; the Location dropdown in the item form only
                                     appears at all if the parent event has ≥1 row in event_locations (see Rule 52)
```

**Note:** `events.venue`/`events.location` fields were deliberately left completely untouched — they remain the single default location for the ~95% of events that only ever have one. `event_locations` is purely additive for the multi-day/multi-site minority case.

---

## KEY FILE LOCATIONS — INDUSTRY EXPANSION

### Services
```
app/Services/TenantProvisioningService.php  ← THE single centralized entry point for all tenant creation
                                                (see Rule 48). provision(array $data): Tenant — wraps
                                                TenantService::create() in a DB transaction, then applies
                                                IndustryProfile seed data (event types/vendor categories/
                                                task categories/asset categories via DefaultTenantSeeder),
                                                enables recommended_feature_flags via TenantFeatureOverride,
                                                and seeds default_roles via Spatie Role::firstOrCreate()
                                                scoped by tenant_id (team_foreign_key)
```

### Helpers
```
app/Helpers/TerminologyHelper.php  ← term($key, $default), termTitle($key, $default) — the 3-tier resolution
                                      chain (tenant override → industry profile → hardcoded default)
app/helpers.php                    ← global term() / term_title() functions, registered via composer.json's
                                      autoload.files array (required `composer dump-autoload` after adding)
```

### Models (new)
```
App\Models\Central\IndustryProfile         ← term($key, $default) instance helper, tenants() HasMany
App\Models\Tenant\AssetCategory            ← assets() HasMany
App\Models\Tenant\Asset                    ← category(), eventAssignments(), events() BelongsToMany,
                                              statusLabel(), statusColor()
App\Models\Tenant\AssetEventAssignment     ← asset(), event()
App\Models\Tenant\EventLocation            ← event(), runsheetItems()
```

### Models (modified — relations added, nothing removed)
```
App\Models\Central\Tenant       ← industryProfile() BelongsTo (new)
App\Models\Tenant\Event         ← assetAssignments() HasMany, locations() HasMany (orderBy sort_order),
                                   hasMultipleLocations() bool helper (all new)
App\Models\Tenant\RunsheetItem  ← event_location_id added to $fillable, location() BelongsTo (new)
```

### Livewire Components (Assets module)
```
app/Livewire/Tenant/Assets/AssetList.php     ← search + status + category filters, delete confirmation
app/Livewire/Tenant/Assets/CreateAsset.php   ← create/edit form (shared component via ?Asset $asset param,
                                                same pattern as CreateEvent/CreateVendor elsewhere in the app)
app/Livewire/Tenant/Assets/AssetDetail.php   ← assign/unassign to events, status quick-change buttons
```

### Console Commands
```
app/Console/Commands/BackfillAssetCategories.php  ← koordli:backfill-asset-categories — ONE-TIME command,
                                                      seeds default asset categories for tenants created before
                                                      the Assets module existed. Skips any tenant that already
                                                      has asset_categories rows — safe to re-run.
```

### Routes (tenant, authenticated group)
```
/assets              → Tenant\Assets\AssetList
/assets/create        → Tenant\Assets\CreateAsset
/assets/{id}/edit    → Tenant\Assets\CreateAsset
/assets/{id}         → Tenant\Assets\AssetDetail
```

### PDF Templates
```
resources/views/pdf/call-sheet-pdf.blade.php  ← branded PDF export of a Runsheet, styled as an industry-standard
                                                  call sheet — SAME underlying Runsheet/RunsheetItem data as the
                                                  in-app Timeline view, just a different presentation (per Rule
                                                  "no separate module" — this is a view/export, not new data).
                                                  Shows locations grid (if any), schedule table with optional
                                                  location column, crew/suppliers lists, runsheet notes.
                                                  Uses the SAME Satoshi/Spline Sans font registration + tenant
                                                  branding pattern established for vendor-contract-pdf.blade.php
                                                  in Phase 8.3. NO EMOJI anywhere in this file (see Rule 50) —
                                                  an earlier version used 🏢/👤/📍 which rendered as broken glyph
                                                  boxes in DomPDF output; fixed by using plain text labels only.
```
Download triggered via `RunsheetManager::downloadCallSheet()` — reuses the exact branded-PDF-building pattern
(logo as base64 data URI, tenant's primary_color/accent_color from `branding` JSON) already established for
vendor contracts.

---

## SEEDED INDUSTRY PROFILES (via `IndustryProfileSeeder`)

8 profiles seeded: **Wedding & Events** (the original/default terminology, zero overrides — matches pre-existing
hardcoded behavior exactly), **Corporate** (guest→Attendee), **Production** (event→Production, client→Producer,
guest→Audience, vendor→Supplier — the flagship profile for this phase), **Church** (guest→Congregation Member,
client→Ministry Lead), **Conference** (guest→Delegate), **Entertainment** (guest→Audience, vendor→Supplier),
**Exhibition** (guest→Visitor, vendor→Exhibitor), **Other** (a blank-slate fallback, minimal single generic
event type, for anyone who doesn't fit the other 7).

Each profile also carries industry-appropriate default event types (e.g. Production profile seeds "Film Shoot,"
"TV Production," "Concert," "Commercial Shoot," "Theatre Production" instead of "Wedding," "Engagement,"
"Birthday"), vendor categories (e.g. "Equipment Rental," "Crew" instead of "Photography," "Beauty"), task
categories (e.g. "Pre-Production," "Shoot Day," "Post-Production" instead of "Pre-Event," "Logistics,"
"On The Day"), and suggested default role names (e.g. "Line Producer," "1st AD," "Unit Manager" instead of
"Coordinator," "Finance," "Operations").

**Re-running `IndustryProfileSeeder` is always safe** — it uses `updateOrCreate` keyed on the profile's unique
`key` column, so re-seeding (e.g. after adding new terminology keys) only refreshes the 8 existing rows, never
duplicates them, and has zero effect on any tenant's already-provisioned data (per Rule 49).

---

## TERMINOLOGY ROLLOUT — WHAT'S DONE, WHAT'S DEFERRED (Phase A only, by design)

Deliberately limited to the highest-traffic, highest-visibility spots rather than an exhaustive sweep across
~150+ Blade views (judged too risky/low-value to attempt in one pass — see original strategy discussion).

**Applied (`term()`/`term_title()` wired in):**
- `tenant-sidebar.blade.php` — Events/Vendors/Vendor Invoices/Clients/Guests & RSVP nav labels
- `dashboard.blade.php` — Total Events/Active Vendors/Guests KPI card labels, Recent Events heading, +New Event
  button, empty-state text
- `event-list.blade.php` — page heading, +New Event button, search placeholder, all 3 empty-state occurrences
- `vendor-directory.blade.php` — page heading ("Vendor Directory" → "{Vendor} Directory"), vendor count text,
  +Add Vendor button, all 3 empty-state occurrences
- New Assets module views — built terminology-aware from day one (`term_title('asset_plural', 'Assets')` etc.)

**Explicitly deferred** (not yet touched — revisit only if a real production-industry customer surfaces specific
wording friction in practice): Task Center, Budget page, Client Portal headings, Vendor Portal headings, Forms &
Bookings, all remaining page titles/empty-states throughout the app. These all currently show plain English
("Events," "Vendors," "Clients," "Guests") regardless of the tenant's industry profile — this is intentional,
not an oversight, per the deliberately narrow Phase A scope agreed with the user.

---

## STRATEGIC ANALYSIS SUMMARY (for future reference — the "why" behind this whole phase)

When asked to evaluate extending Koordli to serve Production Companies without losing its identity as an event
platform, the conclusion reached (and executed) was:

- **Already worked perfectly, zero changes needed:** Task Management, Vendor/Contract/Invoice lifecycle (arguably
  Koordli's strongest asset for production companies — equipment rental, freelance crew, e-signature contracts
  map directly), Staff Management (Spatie roles already support arbitrary tenant-defined role names), Budget
  tracking, Client Portal, multi-tenancy/domain/white-label/billing architecture, Support System.
- **Needed only relabeling, not redesign:** Runsheet (already IS a call sheet, just needed a specialized PDF
  presentation — built), Vendor→Supplier (pure label), Guest→Audience/Attendee/Delegate/etc. (pure label,
  RSVP already optional via existing feature flag).
- **Only two genuine new capabilities added, deliberately staying disciplined against scope creep:** lightweight
  Assets (explicitly NOT a full inventory/warehouse system), Multi-Location support (explicitly NOT a redesign
  of the Event model — purely additive, existing single-venue fields untouched).
- **Explicitly avoided** (flagged as genuine ERP/PM scope creep risk, not built): asset barcode/depreciation
  tracking, script/rundown document management, complex crew scheduling engines, ticketing system, seating
  plans, event website builder — all reserved as *future* feature-flag names if ever needed, but zero
  implementation work done on any of them in this phase.
- **Net effort classification confirmed correct in practice:** this entire phase was genuinely a terminology +
  feature-flag + seed-data + two small additive-schema exercise, exactly as predicted before building anything —
  no existing table, model relationship, or working Blade view was modified in a breaking way anywhere in this
  phase.


## PENDING

### Phase 9 — Remaining polish (optional)
- Test Flutterwave checkout flow end-to-end (only Paystack tested so far)
- Confirm daily `koordli:process-subscriptions` cron is actually registered on production server (Windows dev environment doesn't run cron automatically)

### Future — Documentation & Public API (deferred by choice)
- Full documentation site (Tailwind/Laravel-docs-style: sidebar nav, search, "on this page" TOC) — deliberately deferred until the platform is fully feature-complete, to avoid writing docs that immediately go stale
- Public API v1 — no work started; only the pre-existing Formspree-style form/consultation submission endpoints exist today

### Production Deployment (infrastructure, not code — see `KOORDLI_PRODUCTION_TRAEFIK_SETUP.md`)
- Purchase VPS + `koordli.com` domain
- Cloudflare DNS setup for wildcard SSL (DNS-01 challenge)
- Deploy Docker + Traefik per the documented config
- Set up production cron for `php artisan schedule:run` (required for BOTH `koordli:process-subscriptions` and `koordli:recheck-domains` — neither runs without it)
- Update `.env` `APP_URL` to the real production domain

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
barryvdh/laravel-dompdf           ← installed Phase 8.3, used ONLY for branded vendor contract PDFs
```

Note: `yasumi/yasumi` was NOT installed (unavailable). Public holiday logic is handled by `App\Helpers\PublicHolidayHelper` — no package needed.

Note: NO Paystack or Flutterwave SDK packages installed. All payment gateway calls use Laravel's built-in `Http` facade (Guzzle, bundled with Laravel core — nothing to `composer require`).

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
- Hostinger VPS (not yet purchased — still in local development)
- Docker + Traefik for SSL — full production config documented separately in `KOORDLI_PRODUCTION_TRAEFIK_SETUP.md`
  (Cloudflare DNS-01 wildcard cert for `*.koordli.com` + on-demand HTTP-01 cert resolver for tenant custom domains)
- n8n_default bridge network
- Self-hosted n8n at bezalelkoncept.site
- Storage link: `php artisan storage:link` — required for RSVP cover images, form hero images, contract signed uploads/attachments, invoice attachments/receipts, platform favicon
- `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local` in `.env`
- `SESSION_COOKIE=koordli_session` in `.env`
- **Production TODO**: ensure the Laravel scheduler is actually running via a real cron entry (`* * * * * php artisan schedule:run`) — required for BOTH `koordli:process-subscriptions` AND `koordli:recheck-domains`; neither runs automatically, including on the current Windows dev machine
- **Local domain testing (optional, not required)**: Windows hosts file entries + `php artisan serve --host=koordli.test` can simulate subdomains; custom domain DNS verification can't work against fake local domains, so `domain_status` can be force-set via tinker for local-only testing. User opted to skip this and verify for real once a server is purchased.

---

## WHEN CONTINUING IN NEW CHAT

1. Paste this entire context document first
2. Claude reads it fully before responding
3. Ask permission at every major step
4. Always ask for file contents before editing — never assume
5. Always give complete ready-to-paste files

**Key URLs:**
- Landing page: `http://127.0.0.1:8000/`
- Registration: `http://127.0.0.1:8000/register`
- Tenant login: `http://127.0.0.1:8000/login`
- Platform login: `http://127.0.0.1:8000/platform/login`
- Dashboard: `http://127.0.0.1:8000/dashboard`
- RSVP (public): `http://127.0.0.1:8000/rsvp/{slug}`
- Booking form (public): `http://127.0.0.1:8000/book/{slug}`
- Consultation form (public): `http://127.0.0.1:8000/consult/{slug}`
- Contract e-signature (public): `http://127.0.0.1:8000/contracts/sign/{token}`
- Client login: `http://127.0.0.1:8000/client/login`
- Vendor login: `http://127.0.0.1:8000/vendor/login`
- Vendor runsheet: `http://127.0.0.1:8000/vendor/runsheet`
- Vendor availability: `http://127.0.0.1:8000/vendor/availability`
- Tenant billing: `http://127.0.0.1:8000/billing`
- Tenant upgrade: `http://127.0.0.1:8000/billing/upgrade`
- Tenant contracts: `http://127.0.0.1:8000/contracts`
- Tenant invoices: `http://127.0.0.1:8000/invoices`
- Tenant domain settings: `http://127.0.0.1:8000/domain-settings`
- Platform billing config: `http://127.0.0.1:8000/platform/billing`
- Platform site settings: `http://127.0.0.1:8000/platform/site-settings`

**Separate reference document:** `KOORDLI_PRODUCTION_TRAEFIK_SETUP.md` — full production domain/SSL deployment guide, save for when a VPS is purchased.