<?php

namespace Tests\Feature;

use App\Models\Central\Client;
use App\Models\Central\NotificationTemplate;
use App\Models\Central\Tenant;
use App\Models\Tenant\ClientEventAccess;
use App\Models\Tenant\Event;
use App\Models\Tenant\EventWish;
use App\Models\Tenant\RsvpForm;
use App\Models\Tenant\RsvpResponse;
use App\Notifications\KoordliNotification;
use App\Services\Notifications\ClientNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClientRsvpAndWishNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        NotificationTemplate::create([
            'key' => 'client_rsvp_submitted',
            'category' => 'rsvp',
            'subject' => 'RSVP update for {{event_name}}',
            'body' => 'Hi {{user_name}}, {{guest_name}} has submitted a response for {{event_name}} ({{status}}).',
            'is_active' => true,
        ]);

        NotificationTemplate::create([
            'key' => 'client_wish_submitted',
            'category' => 'rsvp',
            'subject' => 'New wish for {{event_name}}',
            'body' => 'Hi {{user_name}}, {{guest_name}} shared a new wish for {{event_name}}.',
            'is_active' => true,
        ]);
    }

    public function test_client_receives_in_app_notification_when_rsvp_is_submitted(): void
    {
        Notification::fake();

        $tenant = Tenant::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'status' => 'active',
        ]);

        $client = Client::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Jane Client',
            'email' => 'jane@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $event = Event::create([
            'tenant_id' => $tenant->id,
            'name' => 'Summer Party',
            'slug' => 'summer-party',
            'status_id' => 1,
            'client_email' => $client->email,
        ]);

        ClientEventAccess::create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'event_id' => $event->id,
        ]);

        $form = RsvpForm::create([
            'tenant_id' => $tenant->id,
            'event_id' => $event->id,
            'title' => 'Summer Party RSVP',
            'slug' => 'summer-party-rsvp',
            'is_active' => true,
        ]);

        $response = RsvpResponse::create([
            'tenant_id' => $tenant->id,
            'event_id' => $event->id,
            'rsvp_form_id' => $form->id,
            'respondent_name' => 'Sam Guest',
            'respondent_email' => 'sam@example.com',
            'status' => 'pending',
            'plus_one_count' => 0,
        ]);

        app(ClientNotificationService::class)->notifyRsvpSubmitted($response);

        Notification::assertSentTo($client, KoordliNotification::class, function (KoordliNotification $notification) {
            return $notification->category === 'rsvp'
                && $notification->notificationType === 'rsvp_submitted';
        });
    }

    public function test_client_receives_in_app_notification_when_a_guest_wish_is_submitted(): void
    {
        Notification::fake();

        $tenant = Tenant::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Wish Tenant',
            'slug' => 'wish-tenant',
            'status' => 'active',
        ]);

        $client = Client::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Wish Client',
            'email' => 'wishclient@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $event = Event::create([
            'tenant_id' => $tenant->id,
            'name' => 'Wedding Weekend',
            'slug' => 'wedding-weekend',
            'status_id' => 1,
            'client_email' => $client->email,
        ]);

        ClientEventAccess::create([
            'tenant_id' => $tenant->id,
            'client_id' => $client->id,
            'event_id' => $event->id,
        ]);

        $wish = EventWish::create([
            'tenant_id' => $tenant->id,
            'event_id' => $event->id,
            'guest_name' => 'Mia Guest',
            'guest_email' => 'mia@example.com',
            'message' => 'We cannot wait!',
            'status' => 'pending',
        ]);

        app(ClientNotificationService::class)->notifyWishSubmitted($wish);

        Notification::assertSentTo($client, KoordliNotification::class, function (KoordliNotification $notification) {
            return $notification->category === 'rsvp'
                && $notification->notificationType === 'wish_submitted';
        });
    }
}
