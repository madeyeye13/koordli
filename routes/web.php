<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PWA Routes — no guard restriction on the route itself; PwaController
| resolves tenant from whichever guard is currently authenticated and
| falls back to generic Koordli branding if none is.
|--------------------------------------------------------------------------
*/
Route::get('/manifest.webmanifest', [\App\Http\Controllers\PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/pwa/icon/{size}.png', [\App\Http\Controllers\PwaController::class, 'icon'])
    ->where('size', '[0-9]+')
    ->name('pwa.icon');
Route::get('/offline', [\App\Http\Controllers\PwaController::class, 'offline'])->name('pwa.offline');
Route::get('/sw.js', [\App\Http\Controllers\PwaController::class, 'serviceWorker'])->name('pwa.sw');
Route::post('/push/subscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'store'])->name('push.subscribe');
Route::post('/push/unsubscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
Route::post('/media/upload/chunk', [\App\Http\Controllers\MediaUploadController::class, 'chunk'])->name('media.upload.chunk');
Route::post('/media/upload/finalize', [\App\Http\Controllers\MediaUploadController::class, 'finalize'])->name('media.upload.finalize');

Route::get('/feedback', \App\Livewire\Public\FeedbackForm::class)->name('feedback');
Route::get('/feedback/thanks', \App\Livewire\Public\FeedbackThanks::class)->name('feedback.thanks');

/*
|--------------------------------------------------------------------------
| Platform Routes
|--------------------------------------------------------------------------
*/
Route::prefix('platform')->name('platform.')->group(function () {

    Route::middleware('guest:platform')->group(function () {
        Route::get('/login', \App\Livewire\Platform\Auth\Login::class)->name('login');
        Route::get('/forgot-password', \App\Livewire\Platform\Auth\ForgotPassword::class)->name('password.request');
        Route::get('/accept-invite/{token}', \App\Livewire\Platform\Auth\AcceptInvite::class)->name('accept-invite');
    });

    Route::middleware('auth.platform')->group(function () {
        Route::get('/dashboard', \App\Livewire\Platform\Dashboard::class)->name('dashboard');
        Route::get('/feedback', \App\Livewire\Platform\FeedbackSubmissionList::class)->name('feedback');
        Route::get('/tenants', \App\Livewire\Platform\Tenants\TenantList::class)->name('tenants');
        Route::get('/tenants/create', \App\Livewire\Platform\Tenants\CreateTenant::class)->name('tenants.create');
        Route::get('/tenants/{tenant}/edit', \App\Livewire\Platform\Tenants\CreateTenant::class)->name('tenants.edit');
        Route::get('/plans', \App\Livewire\Platform\Plans\PlanList::class)->name('plans');
        Route::get('/plans/create', \App\Livewire\Platform\Plans\CreatePlan::class)->name('plans.create');
        Route::get('/plans/{planId}/edit', \App\Livewire\Platform\Plans\CreatePlan::class)->name('plans.edit');
        Route::get('/billing', \App\Livewire\Platform\BillingConfig::class)->name('billing');
        Route::get('/site-settings', \App\Livewire\Platform\SiteSettings::class)->name('site-settings');
        Route::get('/profile', \App\Livewire\Platform\Profile::class)->name('profile');
        Route::get('/support/faqs', \App\Livewire\Platform\Support\FaqList::class)->name('support.faqs');
        Route::get('/support/tickets', \App\Livewire\Platform\Support\TicketInbox::class)->name('support.tickets');
        Route::get('/support/tickets/{uuid}', \App\Livewire\Platform\Support\PlatformTicketDetail::class)->name('support.tickets.show');
        Route::post('/logout', function () {
            Auth::guard('platform')->logout();
            return redirect()->route('platform.login');
        })->name('logout');

        Route::prefix('blog')->name('blog.')->group(function () {
            Route::get('/', \App\Livewire\Platform\Blog\BlogPostList::class)->name('index');
            Route::get('/create', \App\Livewire\Platform\Blog\BlogPostEditor::class)->name('create');
            Route::get('/{id}/edit', \App\Livewire\Platform\Blog\BlogPostEditor::class)->name('edit');
        });

        Route::get('/staff', \App\Livewire\Platform\Staff\PlatformStaffManager::class)->name('staff');
        Route::get('/staff/roles', \App\Livewire\Platform\Staff\PlatformRoleManager::class)->name('staff.roles');

        Route::get('/blog/comments', \App\Livewire\Platform\Blog\BlogCommentModeration::class)->name('blog.comments');

        Route::post('/blog/upload-image', [\App\Http\Controllers\BlogImageUploadController::class, 'upload'])->name('blog.upload-image');
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
            Route::get('/conversations', \App\Livewire\Client\Conversations\ConversationList::class)->name('conversations');
            Route::get('/conversations/{uuid}', \App\Livewire\Client\Conversations\ConversationDetail::class)->name('conversations.show');

            Route::get('/conversations/{uuid}/quick-messages', function (string $uuid) {
                $conversation = \App\Models\Tenant\Conversation::where('uuid', $uuid)->firstOrFail();
                abort_unless($conversation->hasParticipant('client', auth('client')->id()), 403);

                \App\Models\Tenant\ConversationParticipant::where('conversation_id', $conversation->id)
                    ->where('participant_type', 'client')->where('participant_id', auth('client')->id())
                    ->update(['last_read_at' => now()]);

                $messages = \App\Models\Tenant\ConversationMessage::where('conversation_id', $conversation->id)
                    ->with('attachments')->orderByDesc('created_at')->limit(20)->get()->reverse()->values();

                return response()->json($messages->map(fn($msg) => [
                    'id' => $msg->id, 'sender_type' => $msg->sender_type, 'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->senderName(),
                    'body' => $msg->body,
                    'attachments' => $msg->attachments->map(fn($att) => [
                        'url' => \Illuminate\Support\Facades\Storage::url($att->file_path), 'name' => $att->file_name,
                        'mime_type' => str_starts_with($att->file_name, 'voice-note-') ? 'audio/webm' : $att->mime_type,
                        'is_audio' => str_starts_with($att->mime_type ?? '', 'audio/') || str_starts_with($att->file_name, 'voice-note-'),
                    ])->values(),
                ]))->header('Cache-Control', 'no-store');
            })->name('conversations.quick-messages');

            Route::post('/conversations/{uuid}/quick-send', function (string $uuid, \Illuminate\Http\Request $request) {
                $conversation = \App\Models\Tenant\Conversation::where('uuid', $uuid)->firstOrFail();
                abort_unless($conversation->hasParticipant('client', auth('client')->id()), 403);

                $request->validate(['body' => 'nullable|string|max:3000', 'attachment' => 'nullable|file|max:10240']);
                if (empty($request->input('body')) && !$request->hasFile('attachment')) {
                    return response()->json(['error' => 'Empty'], 422);
                }

                $message = \App\Models\Tenant\ConversationMessage::create([
                    'tenant_id' => $conversation->tenant_id, 'conversation_id' => $conversation->id,
                    'sender_type' => 'client', 'sender_id' => auth('client')->id(),
                    'body' => $request->input('body', ''),
                ]);

                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $path = $file->store('conversation-attachments', 'public');
                    \App\Models\Tenant\ConversationMessageAttachment::create([
                        'tenant_id' => $conversation->tenant_id, 'message_id' => $message->id,
                        'file_path' => $path, 'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(), 'mime_type' => $file->getMimeType(),
                    ]);
                }

                $conversation->touch();
                broadcast(new \App\Events\ConversationMessageSent($message, $conversation->uuid))->toOthers();
                \App\Services\Conversations\ConversationNotifier::notifyOthers($conversation, $message);

                \App\Models\Tenant\ConversationParticipant::where('conversation_id', $conversation->id)
                    ->where('participant_type', 'client')->where('participant_id', auth('client')->id())
                    ->update(['last_read_at' => now()]);

                return response()->json(['status' => 'ok']);
            })->name('conversations.quick-send');

            Route::get('/conversations/{uuid}/seen-status', function (string $uuid) {
                $conversation = \App\Models\Tenant\Conversation::where('uuid', $uuid)->firstOrFail();
                abort_unless($conversation->hasParticipant('client', auth('client')->id()), 403);

                $lastMine = \App\Models\Tenant\ConversationMessage::where('conversation_id', $conversation->id)
                    ->where('sender_type', 'client')->where('sender_id', auth('client')->id())
                    ->latest()->first();

                if (!$lastMine) return response()->json(['message_id' => null, 'seen_by' => []]);

                return response()->json([
                    'message_id' => $lastMine->id, 'seen_by' => $lastMine->seenBy()->values(),
                ])->header('Cache-Control', 'no-store');
            })->name('conversations.seen-status');
        });

        Route::get('/notifications/preferences', \App\Livewire\Client\NotificationPreferences::class)->name('notifications.preferences');
        Route::get('/events/{slug}/media', \App\Livewire\Client\Documents\MediaLibraryClient::class)->name('events.media');
        Route::get('/events/{slug}/vendors', \App\Livewire\Client\Vendors\VendorList::class)->name('events.vendors');
        Route::get('/events/{slug}/moodboards', \App\Livewire\Client\Moodboards\MoodboardList::class)->name('moodboards.index');
        Route::get('/events/{slug}/checklist', \App\Livewire\Client\Checklists\ChecklistView::class)->name('checklist.index');
        Route::get('/events/{slug}/budget', \App\Livewire\Client\Budget\ClientBudgetView::class)->name('budget.show');
        Route::get('/events/{slug}/microsite', \App\Livewire\Client\Microsite\ClientMicrosite::class)->name('events.microsite');
        Route::get('/moodboards/{id}', \App\Livewire\Client\Moodboards\MoodboardView::class)->name('moodboards.show');
        Route::get('/profile', \App\Livewire\Client\Profile::class)->name('profile');

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
            Route::get('/conversations', \App\Livewire\Vendor\Conversations\ConversationList::class)->name('conversations');
            Route::get('/conversations/{uuid}', \App\Livewire\Vendor\Conversations\ConversationDetail::class)->name('conversations.show');

            Route::get('/conversations/{uuid}/quick-messages', function (string $uuid) {
                $conversation = \App\Models\Tenant\Conversation::where('uuid', $uuid)->firstOrFail();
                abort_unless($conversation->hasParticipant('vendor_account', auth('vendor')->id()), 403);

                \App\Models\Tenant\ConversationParticipant::where('conversation_id', $conversation->id)
                    ->where('participant_type', 'vendor_account')->where('participant_id', auth('vendor')->id())
                    ->update(['last_read_at' => now()]);

                $messages = \App\Models\Tenant\ConversationMessage::where('conversation_id', $conversation->id)
                    ->with('attachments')->orderByDesc('created_at')->limit(20)->get()->reverse()->values();

                return response()->json($messages->map(fn($msg) => [
                    'id' => $msg->id, 'sender_type' => $msg->sender_type, 'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->senderName(),
                    'body' => $msg->body,
                    'attachments' => $msg->attachments->map(fn($att) => [
                        'url' => \Illuminate\Support\Facades\Storage::url($att->file_path), 'name' => $att->file_name,
                        'mime_type' => str_starts_with($att->file_name, 'voice-note-') ? 'audio/webm' : $att->mime_type,
                        'is_audio' => str_starts_with($att->mime_type ?? '', 'audio/') || str_starts_with($att->file_name, 'voice-note-'),
                    ])->values(),
                ]))->header('Cache-Control', 'no-store');
            })->name('conversations.quick-messages');

            Route::post('/conversations/{uuid}/quick-send', function (string $uuid, \Illuminate\Http\Request $request) {
                $conversation = \App\Models\Tenant\Conversation::where('uuid', $uuid)->firstOrFail();
                abort_unless($conversation->hasParticipant('vendor_account', auth('vendor')->id()), 403);

                $request->validate(['body' => 'nullable|string|max:3000', 'attachment' => 'nullable|file|max:10240']);
                if (empty($request->input('body')) && !$request->hasFile('attachment')) {
                    return response()->json(['error' => 'Empty'], 422);
                }

                $message = \App\Models\Tenant\ConversationMessage::create([
                    'tenant_id' => $conversation->tenant_id, 'conversation_id' => $conversation->id,
                    'sender_type' => 'vendor_account', 'sender_id' => auth('vendor')->id(),
                    'body' => $request->input('body', ''),
                ]);

                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $path = $file->store('conversation-attachments', 'public');
                    \App\Models\Tenant\ConversationMessageAttachment::create([
                        'tenant_id' => $conversation->tenant_id, 'message_id' => $message->id,
                        'file_path' => $path, 'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(), 'mime_type' => $file->getMimeType(),
                    ]);
                }

                $conversation->touch();
                broadcast(new \App\Events\ConversationMessageSent($message, $conversation->uuid))->toOthers();
                \App\Services\Conversations\ConversationNotifier::notifyOthers($conversation, $message);

                \App\Models\Tenant\ConversationParticipant::where('conversation_id', $conversation->id)
                    ->where('participant_type', 'vendor_account')->where('participant_id', auth('vendor')->id())
                    ->update(['last_read_at' => now()]);

                return response()->json(['status' => 'ok']);
            })->name('conversations.quick-send');

            Route::get('/conversations/{uuid}/seen-status', function (string $uuid) {
                $conversation = \App\Models\Tenant\Conversation::where('uuid', $uuid)->firstOrFail();
                abort_unless($conversation->hasParticipant('vendor_account', auth('vendor')->id()), 403);

                $lastMine = \App\Models\Tenant\ConversationMessage::where('conversation_id', $conversation->id)
                    ->where('sender_type', 'vendor_account')->where('sender_id', auth('vendor')->id())
                    ->latest()->first();

                if (!$lastMine) return response()->json(['message_id' => null, 'seen_by' => []]);

                return response()->json([
                    'message_id' => $lastMine->id, 'seen_by' => $lastMine->seenBy()->values(),
                ])->header('Cache-Control', 'no-store');
                        })->name('conversations.seen-status');
        });
        Route::get('/notifications/preferences', \App\Livewire\Vendor\NotificationPreferences::class)->name('notifications.preferences');

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
$publicDomainAppHost = parse_url(config('app.url'), PHP_URL_HOST) ?? 'koordli.site';
$publicDomainHostPattern = '^(?!.*' . preg_quote($publicDomainAppHost, '/') . '$).+$';

Route::domain('{publicDomainHost}')
    ->where(['publicDomainHost' => $publicDomainHostPattern])
    ->middleware('public.rsvp.domain')
    ->group(function () {
        Route::get('/', \App\Livewire\Public\RsvpMicrosite::class)->name('rsvp.public-domain.form');
        Route::get('/edit/{token}', \App\Livewire\Public\RsvpMicrosite::class)->name('rsvp.public-domain.edit');
        Route::get('/ticket/{token}', function (string $token) {
            $form = app('publicRsvpForm');
            $response = \App\Models\Tenant\RsvpResponse::with(['rsvpForm.event'])
                ->where('rsvp_form_id', $form->id)
                ->where('qr_token', $token)
                ->where('status', 'confirmed')
                ->firstOrFail();

            $event = $response->rsvpForm->event;
            $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(200)
                ->generate($token);

            return view('public.rsvp-ticket-pdf', compact('response', 'event', 'qrSvg'));
        })->name('rsvp.public-domain.ticket');
    });

Route::get('/rsvp/{slug}', \App\Livewire\Public\RsvpMicrosite::class)->name('rsvp.form');
Route::get('/rsvp/{slug}/edit/{token}', \App\Livewire\Public\RsvpMicrosite::class)->name('rsvp.edit');
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

// Guest media upload (no login)
Route::get('/media-upload/{token}', \App\Livewire\Public\MediaUploadPage::class)->name('public.media-upload');

// Quick Access — permanent no-login links for staff/vendors
Route::get('/quick-access/{token}', \App\Livewire\Public\QuickAccessPage::class)->name('public.quick-access');
Route::post('/quick-access/{token}/update-task', [\App\Http\Controllers\QuickAccessController::class, 'updateTask'])->name('public.quick-access.update-task');
Route::post('/quick-access/{token}/update-runsheet', [\App\Http\Controllers\QuickAccessController::class, 'updateRunsheet'])->name('public.quick-access.update-runsheet');
Route::post('/quick-access/{token}/update-checklist', [\App\Http\Controllers\QuickAccessController::class, 'updateChecklist'])->name('public.quick-access.update-checklist');

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['tenant.byDomain', 'tenant.resolve'])->group(function () {

    Route::middleware('guest:web')->group(function () {
        Route::get('/login', \App\Livewire\Tenant\Auth\Login::class)->name('tenant.login');
        Route::get('/forgot-password', \App\Livewire\Tenant\Auth\ForgotPassword::class)->name('tenant.password.request');
        Route::get('/reset-password/{token}', \App\Livewire\Tenant\Auth\ResetPassword::class)->name('tenant.password.reset');
    });

    Route::middleware(['auth.tenant', 'tenant.resolve', 'onboarding.check'])->group(function () {
        Route::get('/account-suspended', \App\Livewire\Tenant\SuspendedAccount::class)->name('tenant.suspended');

        Route::middleware('tenant.active')->group(function () {
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
        Route::get('/events/{slug}/media', \App\Livewire\Tenant\Documents\MediaLibrary::class)->name('tenant.events.media');
        Route::get('/events/{slug}/vendor-suggestions', \App\Livewire\Tenant\Vendors\VendorSuggestions::class)->name('tenant.events.vendor-suggestions');
        Route::get('/events/{slug}/moodboards', \App\Livewire\Tenant\Moodboards\MoodboardList::class)->name('tenant.events.moodboards');
        Route::get('/events/{slug}/checklist', \App\Livewire\Tenant\Checklists\ChecklistPage::class)->name('tenant.events.checklist');
        Route::get('/events/{slug}/microsite', \App\Livewire\Tenant\Microsite\EventMicrosite::class)->name('tenant.events.microsite');
        Route::get('/checklists/{id}/export', [\App\Http\Controllers\ChecklistExportController::class, 'export'])->name('tenant.checklists.export');
        Route::get('/moodboards', \App\Livewire\Tenant\Moodboards\MoodboardsHub::class)->name('tenant.moodboards.hub');
        Route::get('/checklists', \App\Livewire\Tenant\Checklists\ChecklistsHub::class)->name('tenant.checklists.hub');
        Route::get('/moodboards/pexels-search', [\App\Http\Controllers\MoodboardPexelsController::class, 'search'])->name('tenant.moodboards.pexels-search');
        Route::get('/moodboards/{id}', \App\Livewire\Tenant\Moodboards\MoodboardEditor::class)->name('tenant.moodboards.edit');
        Route::post('/moodboards/{moodboardId}/upload', [\App\Http\Controllers\MoodboardUploadController::class, 'upload'])->name('tenant.moodboards.upload');
        Route::get('/moodboards/{id}/export', [\App\Http\Controllers\MoodboardExportController::class, 'export'])->name('tenant.moodboards.export');
        Route::post('/moodboards/{moodboardId}/pexels-select', [\App\Http\Controllers\MoodboardPexelsController::class, 'select'])->name('tenant.moodboards.pexels-select');
        Route::get('/media/download/{id}', [\App\Http\Controllers\MediaDownloadController::class, 'single'])->name('media.download');
        Route::get('/media/download-bulk', [\App\Http\Controllers\MediaDownloadController::class, 'bulk'])->name('media.download.bulk');

        Route::get('/tasks', \App\Livewire\Tenant\Tasks\TaskCenter::class)->name('tenant.tasks');
        Route::get('/tasks/create', \App\Livewire\Tenant\Tasks\CreateTask::class)->name('tenant.tasks.create');
        Route::get('/tasks/{id}/edit', \App\Livewire\Tenant\Tasks\CreateTask::class)->name('tenant.tasks.edit');

        Route::get('/staff', \App\Livewire\Tenant\Staff\StaffList::class)->name('tenant.staff');
        Route::get('/staff/invite', \App\Livewire\Tenant\Staff\InviteStaff::class)->name('tenant.staff.invite');
        Route::get('/staff/{id}/edit', \App\Livewire\Tenant\Staff\InviteStaff::class)->name('tenant.staff.edit');
        Route::get('/staff/roles', \App\Livewire\Tenant\Staff\RolePermissions::class)->name('tenant.staff.roles');

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
        Route::get('/client-notifications', \App\Livewire\Tenant\ClientNotificationSettings::class)->name('tenant.client-notifications');
        Route::get('/client-financial-visibility', \App\Livewire\Tenant\ClientFinancialVisibilitySettings::class)->name('tenant.client-financial-visibility');
        Route::get('/quick-access', \App\Livewire\Tenant\QuickAccessAdmin::class)->name('tenant.quick-access');
        Route::get('/clients', \App\Livewire\Tenant\Clients\ClientsList::class)->name('tenant.clients');
        Route::get('/settings', \App\Livewire\Tenant\SettingsHub::class)->name('tenant.settings');
        Route::get('/branding-settings', \App\Livewire\Tenant\BrandingSettings::class)->name('tenant.branding-settings');
        Route::get('/my-quick-access', \App\Livewire\Staff\QuickAccessSettings::class)->name('tenant.my-quick-access');
        Route::get('/my-profile', \App\Livewire\Staff\Profile::class)->name('tenant.my-profile');

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
                'sender_name' => $msg->senderName(),
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

        Route::get('/conversations/{uuid}/seen-status', function (string $uuid) {
            $conversation = \App\Models\Tenant\Conversation::where('uuid', $uuid)->firstOrFail();
            abort_unless($conversation->hasParticipant('tenant_user', auth()->id()), 403);

            $lastMine = \App\Models\Tenant\ConversationMessage::where('conversation_id', $conversation->id)
                ->where('sender_type', 'tenant_user')
                ->where('sender_id', auth()->id())
                ->latest()
                ->first();

            if (!$lastMine) {
                return response()->json(['message_id' => null, 'seen_by' => []]);
            }

            return response()->json([
                'message_id' => $lastMine->id,
                'seen_by'    => $lastMine->seenBy()->values(),
            ])->header('Cache-Control', 'no-store');
        })->name('tenant.conversations.seen-status');
        });
    });

    Route::get('/register', \App\Livewire\Auth\Register::class)->name('register');

    // Outside auth.tenant/tenant.active on purpose: a brand-new registrant paying mid-registration
    // isn't logged in yet when the gateway redirects back. BillingCallback branches internally on
    // auth()->check() to resolve the correct tenant for both the "new registration" and "existing
    // tenant upgrading" cases.
    Route::get('/billing/callback/{gateway}', \App\Livewire\Tenant\Billing\BillingCallback::class)->name('tenant.billing.callback');

});

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/
Route::get('/', \App\Livewire\Public\LandingPage::class)->name('landing');
Route::get('/docs', fn () => view('public.documentation'))->name('docs');
Route::get('/terms', function () {
    return view('public.legal', [
        'title' => 'Terms & Conditions',
        'content' => \App\Models\Central\PlatformSetting::get('terms_content', \App\Models\Central\PlatformSetting::defaultTerms()),
    ]);
})->name('terms');
Route::get('/privacy', function () {
    return view('public.legal', [
        'title' => 'Privacy Policy',
        'content' => \App\Models\Central\PlatformSetting::get('privacy_content', \App\Models\Central\PlatformSetting::defaultPrivacy()),
    ]);
})->name('privacy');

Route::get('/sitemap.xml', function () {
    $blogPosts = \App\Models\Central\BlogPost::where('status', 'published')
        ->where('published_at', '<=', now())
        ->orderByDesc('published_at')
        ->get();

    return response()->view('sitemap', compact('blogPosts'))->header('Content-Type', 'text/xml');
});

// Public — outside any auth group
Route::get('/blog', \App\Livewire\Public\Blog\BlogIndex::class)->name('blog.index');
Route::get('/blog/{slug}', \App\Livewire\Public\Blog\BlogShow::class)->name('blog.show');

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