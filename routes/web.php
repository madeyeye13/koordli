<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Platform Routes
|--------------------------------------------------------------------------
*/
Route::prefix('platform')->name('platform.')->group(function () {

    Route::middleware('guest:platform')->group(function () {
        Route::get('/login', \App\Livewire\Platform\Auth\Login::class)->name('login');
    });

    Route::middleware('auth.platform')->group(function () {
        Route::get('/dashboard', \App\Livewire\Platform\Dashboard::class)->name('dashboard');
        Route::get('/tenants', \App\Livewire\Platform\Tenants\TenantList::class)->name('tenants');
        Route::get('/tenants/create', \App\Livewire\Platform\Tenants\CreateTenant::class)->name('tenants.create');
        Route::get('/tenants/{tenant}/edit', \App\Livewire\Platform\Tenants\CreateTenant::class)->name('tenants.edit');
        Route::get('/plans', \App\Livewire\Platform\Plans\PlanList::class)->name('plans');
        Route::get('/plans/create', \App\Livewire\Platform\Plans\CreatePlan::class)->name('plans.create');
        Route::get('/plans/{planId}/edit', \App\Livewire\Platform\Plans\CreatePlan::class)->name('plans.edit');
        Route::get('/billing', \App\Livewire\Platform\BillingConfig::class)->name('billing');
        Route::get('/site-settings', \App\Livewire\Platform\SiteSettings::class)->name('site-settings');
        Route::get('/support/faqs', \App\Livewire\Platform\Support\FaqList::class)->name('support.faqs');
        Route::get('/support/tickets', \App\Livewire\Platform\Support\TicketInbox::class)->name('support.tickets');
        Route::get('/support/tickets/{uuid}', \App\Livewire\Platform\Support\PlatformTicketDetail::class)->name('support.tickets.show');
        Route::post('/logout', function () {
            Auth::guard('platform')->logout();
            return redirect()->route('platform.login');
        })->name('logout');
    });

});

/*
|--------------------------------------------------------------------------
| Client Routes
|--------------------------------------------------------------------------
*/
Route::prefix('client')->name('client.')->group(function () {

    Route::middleware(['tenant.byDomain', 'guest:client'])->group(function () {
        Route::get('/login', \App\Livewire\Client\Auth\Login::class)->name('login');
    });

    Route::middleware('auth.client')->group(function () {
        Route::get('/onboarding', \App\Livewire\Client\Onboarding::class)->name('onboarding');

        Route::middleware('client.password.check')->group(function () {
            Route::get('/dashboard', \App\Livewire\Client\Dashboard::class)->name('dashboard');
        });

        Route::post('/logout', function () {
            Auth::guard('client')->logout();
            return redirect()->route('client.login');
        })->name('logout');
    });

});

/*
|--------------------------------------------------------------------------
| Vendor Routes
|--------------------------------------------------------------------------
*/
Route::prefix('vendor')->name('vendor.')->group(function () {

    Route::middleware(['tenant.byDomain', 'guest:vendor'])->group(function () {
        Route::get('/login', \App\Livewire\Vendor\Auth\Login::class)->name('login');
    });

    Route::middleware('auth.vendor')->group(function () {
        Route::get('/onboarding', \App\Livewire\Vendor\Onboarding::class)->name('onboarding');

        Route::middleware('vendor.password.check')->group(function () {
            Route::get('/dashboard', \App\Livewire\Vendor\Dashboard::class)->name('dashboard');
            Route::get('/runsheet', \App\Livewire\Vendor\VendorRunsheet::class)->name('runsheet');
            Route::get('/availability', \App\Livewire\Vendor\Availability::class)->name('availability');
            Route::get('/profile', \App\Livewire\Vendor\Profile::class)->name('profile');
        });

        Route::post('/logout', function () {
            Auth::guard('vendor')->logout();
            return redirect()->route('vendor.login');
        })->name('logout');
    });

});

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/vendors/{slug}/register', \App\Livewire\Public\VendorRegister::class)
    ->name('vendor.public.register');

// RSVP
Route::get('/rsvp/{slug}', \App\Livewire\Public\RsvpFormPage::class)->name('rsvp.form');
Route::get('/rsvp/{slug}/edit/{token}', \App\Livewire\Public\RsvpEdit::class)->name('rsvp.edit');
Route::get('/rsvp/ticket/{token}', function (string $token) {
    $response = \App\Models\Tenant\RsvpResponse::with(['rsvpForm.event'])
        ->where('qr_token', $token)
        ->where('status', 'confirmed')
        ->firstOrFail();

    $event = $response->rsvpForm->event;

    $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
        ->size(200)
        ->generate($token);

    return view('public.rsvp-ticket-pdf', compact('response', 'event', 'qrSvg'));
})->name('rsvp.ticket');

// Booking Forms
Route::get('/book/{slug}', \App\Livewire\Public\BookingForm::class)->name('public.booking');
Route::get('/consult/{slug}', \App\Livewire\Public\ConsultationForm::class)->name('public.consultation');

// Vendor Contract e-signature
Route::get('/contracts/sign/{token}', \App\Livewire\Public\VendorContractSign::class)->name('public.contract.sign');

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['tenant.byDomain', 'tenant.resolve'])->group(function () {

    Route::middleware('guest:web')->group(function () {
        Route::get('/login', \App\Livewire\Tenant\Auth\Login::class)->name('tenant.login');
    });

    Route::middleware(['auth.tenant', 'tenant.resolve', 'onboarding.check', 'tenant.active'])->group(function () {
        Route::get('/dashboard', \App\Livewire\Tenant\Dashboard::class)->name('tenant.dashboard');
        Route::get('/onboarding', \App\Livewire\Tenant\Onboarding::class)->name('tenant.onboarding');

        Route::post('/logout', function () {
            Auth::guard('web')->logout();
            return redirect()->route('tenant.login');
        })->name('tenant.logout');

        Route::get('/events', \App\Livewire\Tenant\Events\EventList::class)->name('tenant.events');
        Route::get('/events/create', \App\Livewire\Tenant\Events\CreateEvent::class)->name('tenant.events.create');
        Route::get('/events/{slug}/edit', \App\Livewire\Tenant\Events\CreateEvent::class)->name('tenant.events.edit');
        Route::get('/events/{slug}', \App\Livewire\Tenant\Events\EventDetail::class)->name('tenant.events.show');
        Route::get('/events/{slug}/budget', \App\Livewire\Tenant\Budget\EventBudget::class)->name('tenant.events.budget');

        Route::get('/tasks', \App\Livewire\Tenant\Tasks\TaskCenter::class)->name('tenant.tasks');
        Route::get('/tasks/create', \App\Livewire\Tenant\Tasks\CreateTask::class)->name('tenant.tasks.create');
        Route::get('/tasks/{id}/edit', \App\Livewire\Tenant\Tasks\CreateTask::class)->name('tenant.tasks.edit');

        Route::get('/staff', \App\Livewire\Tenant\Staff\StaffList::class)->name('tenant.staff');
        Route::get('/staff/invite', \App\Livewire\Tenant\Staff\InviteStaff::class)->name('tenant.staff.invite');
        Route::get('/staff/{id}/edit', \App\Livewire\Tenant\Staff\InviteStaff::class)->name('tenant.staff.edit');

        Route::get('/budget', \App\Livewire\Tenant\Budget\BudgetOverview::class)->name('tenant.budget');

        Route::get('/vendors', \App\Livewire\Tenant\Vendors\VendorDirectory::class)->name('tenant.vendors');
        Route::get('/vendors/create', \App\Livewire\Tenant\Vendors\CreateVendor::class)->name('tenant.vendors.create');
        Route::get('/vendors/{id}/edit', \App\Livewire\Tenant\Vendors\CreateVendor::class)->name('tenant.vendors.edit');
        Route::get('/vendors/{id}', \App\Livewire\Tenant\Vendors\VendorDetail::class)->name('tenant.vendors.show');
        Route::get('/vendor-applications', \App\Livewire\Tenant\Vendors\VendorApplications::class)->name('tenant.vendor.applications');

        // Guests
        Route::get('/events/{slug}/guests', \App\Livewire\Tenant\Guests\GuestList::class)->name('tenant.events.guests');

        // Runsheet
        Route::get('/events/{slug}/runsheet', \App\Livewire\Tenant\Runsheet\RunsheetManager::class)->name('tenant.events.runsheet');

        // Forms & Bookings
        Route::get('/forms', \App\Livewire\Tenant\Forms\FormList::class)->name('tenant.forms');
        Route::get('/forms/create', \App\Livewire\Tenant\Forms\CreateForm::class)->name('tenant.forms.create');
        Route::get('/forms/{id}/edit', \App\Livewire\Tenant\Forms\CreateForm::class)->name('tenant.forms.edit');
        Route::get('/forms/{id}/submissions', \App\Livewire\Tenant\Forms\FormSubmissions::class)->name('tenant.forms.submissions');

        Route::get('/billing', \App\Livewire\Tenant\Billing\BillingDashboard::class)->name('tenant.billing');
        Route::get('/billing/upgrade', \App\Livewire\Tenant\Billing\UpgradePage::class)->name('tenant.billing.upgrade');
        Route::get('/billing/callback/{gateway}', \App\Livewire\Tenant\Billing\BillingCallback::class)->name('tenant.billing.callback');

        // RSVP Management
        Route::get('/events/{slug}/rsvp', \App\Livewire\Tenant\Rsvp\RsvpManager::class)->name('tenant.events.rsvp');

        // Vendor Contracts
        Route::get('/contract-templates', \App\Livewire\Tenant\Contracts\ContractTemplates::class)->name('tenant.contract-templates');
        Route::get('/contracts', \App\Livewire\Tenant\Contracts\ContractList::class)->name('tenant.contracts');
        Route::get('/contracts/create', \App\Livewire\Tenant\Contracts\CreateContract::class)->name('tenant.contracts.create');
        Route::get('/contracts/{uuid}', \App\Livewire\Tenant\Contracts\ContractDetail::class)->name('tenant.contracts.show');

        // Vendor Invoices
        Route::get('/invoices', \App\Livewire\Tenant\Invoices\InvoiceList::class)->name('tenant.invoices');
        Route::get('/invoices/create', \App\Livewire\Tenant\Invoices\CreateInvoice::class)->name('tenant.invoices.create');
        Route::get('/invoices/{uuid}', \App\Livewire\Tenant\Invoices\InvoiceDetail::class)->name('tenant.invoices.show');

        Route::get('/domain-settings', \App\Livewire\Tenant\DomainSettings::class)->name('tenant.domain-settings');

        // Support
        Route::get('/support/tickets', \App\Livewire\Tenant\Support\TicketList::class)->name('tenant.support.tickets');
        Route::get('/support/tickets/create', \App\Livewire\Tenant\Support\CreateTicket::class)->name('tenant.support.tickets.create');
        Route::get('/support/tickets/{uuid}', \App\Livewire\Tenant\Support\TicketDetail::class)->name('tenant.support.tickets.show');

        Route::get('/support/chat', \App\Livewire\Tenant\Support\ChatBot::class)->name('tenant.support.chat');


        Route::get('/assets', \App\Livewire\Tenant\Assets\AssetList::class)->name('tenant.assets');
        Route::get('/assets/create', \App\Livewire\Tenant\Assets\CreateAsset::class)->name('tenant.assets.create');
        Route::get('/assets/{id}/edit', \App\Livewire\Tenant\Assets\CreateAsset::class)->name('tenant.assets.edit');
        Route::get('/assets/{id}', \App\Livewire\Tenant\Assets\AssetDetail::class)->name('tenant.assets.show');

        Route::get('/notifications/preferences', \App\Livewire\Tenant\Notifications\NotificationPreferences::class)->name('tenant.notifications.preferences');

        Route::get('/conversations/{uuid}', \App\Livewire\Tenant\Conversations\ConversationDetail::class)->name('tenant.conversations.show');

        Route::get('/conversations/{uuid}/quick-messages', function (string $uuid) {
            $conversation = \App\Models\Tenant\Conversation::where('uuid', $uuid)->firstOrFail();

            $isParticipant = $conversation->hasParticipant('tenant_user', auth()->id());
            abort_unless($isParticipant, 403);

            \App\Models\Tenant\ConversationParticipant::where('conversation_id', $conversation->id)
                ->where('participant_type', 'tenant_user')
                ->where('participant_id', auth()->id())
                ->update(['last_read_at' => now()]);

            $messages = \App\Models\Tenant\ConversationMessage::where('conversation_id', $conversation->id)
                ->with('attachments')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
                ->reverse()
                ->values();

            return response()->json($messages->map(fn($msg) => [
                'id'          => $msg->id,
                'sender_type' => $msg->sender_type,
                'sender_id'   => $msg->sender_id,
                'body'        => $msg->body,
                'attachments' => $msg->attachments->map(fn($att) => [
                    'url'       => \Illuminate\Support\Facades\Storage::url($att->file_path),
                    'name'      => $att->file_name,
                    'mime_type' => str_starts_with($att->file_name, 'voice-note-') ? 'audio/webm' : $att->mime_type,
                    'is_audio'  => str_starts_with($att->mime_type ?? '', 'audio/') || str_starts_with($att->file_name, 'voice-note-'),
                ])->values(),
            ]))->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        })->name('tenant.conversations.quick-messages');

        Route::post('/conversations/{uuid}/quick-send', function (string $uuid, \Illuminate\Http\Request $request) {
            $conversation = \App\Models\Tenant\Conversation::where('uuid', $uuid)->firstOrFail();

            $isParticipant = $conversation->hasParticipant('tenant_user', auth()->id());
            abort_unless($isParticipant, 403);

            $request->validate([
                'body'       => 'nullable|string|max:3000',
                'attachment' => 'nullable|file|max:10240',
            ]);

            if (empty($request->input('body')) && !$request->hasFile('attachment')) {
                return response()->json(['error' => 'Empty message'], 422);
            }

            $message = \App\Models\Tenant\ConversationMessage::create([
                'tenant_id'       => auth()->user()->tenant_id,
                'conversation_id' => $conversation->id,
                'sender_type'     => 'tenant_user',
                'sender_id'       => auth()->id(),
                'body'            => $request->input('body', ''),
            ]);

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->store('conversation-attachments', 'public');

                \App\Models\Tenant\ConversationMessageAttachment::create([
                    'tenant_id'  => auth()->user()->tenant_id,
                    'message_id' => $message->id,
                    'file_path'  => $path,
                    'file_name'  => $file->getClientOriginalName(),
                    'file_size'  => $file->getSize(),
                    'mime_type'  => $file->getMimeType(),
                ]);
            }

            $conversation->touch();

            broadcast(new \App\Events\ConversationMessageSent($message, $conversation->uuid))->toOthers();

            \App\Models\Tenant\ConversationParticipant::where('conversation_id', $conversation->id)
                ->where('participant_type', 'tenant_user')
                ->where('participant_id', auth()->id())
                ->update(['last_read_at' => now()]);

            return response()->json(['status' => 'ok']);
        })->name('tenant.conversations.quick-send');
    });

    Route::get('/register', \App\Livewire\Auth\Register::class)->name('register');

});

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/
Route::get('/', \App\Livewire\Public\LandingPage::class)->name('landing');

Route::get('/sitemap.xml', function () {
    return response()->view('sitemap')->header('Content-Type', 'text/xml');
});

Route::post('/broadcasting/multi-auth', function (\Illuminate\Http\Request $request) {
    if (auth('platform')->check()) {
        return \Illuminate\Support\Facades\Broadcast::auth($request->setUserResolver(fn() => auth('platform')->user()));
    }
    if (auth('web')->check()) {
        return \Illuminate\Support\Facades\Broadcast::auth($request->setUserResolver(fn() => auth('web')->user()));
    }
    if (auth('client')->check()) {
        return \Illuminate\Support\Facades\Broadcast::auth($request->setUserResolver(fn() => auth('client')->user()));
    }
    if (auth('vendor')->check()) {
        return \Illuminate\Support\Facades\Broadcast::auth($request->setUserResolver(fn() => auth('vendor')->user()));
    }
    abort(403);
})->middleware('web');