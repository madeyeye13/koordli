<?php

namespace Tests\Feature;

use App\Jobs\SendPublicDomainNeedsUpdateJob;
use App\Mail\PublicDomainNeedsUpdateMail;
use App\Models\Central\PublicDomain;
use App\Models\Central\Tenant;
use App\Models\Tenant\Event;
use App\Models\Tenant\RsvpForm;
use App\Models\Tenant\User;
use App\Services\PublicDomainService;
use App\Services\PublicDomainVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_event_domain_falls_back_to_existing_rsvp_url(): void
    {
        [$tenant, $form] = $this->createRsvpForm();
        $service = app(PublicDomainService::class);

        $service->createForForm($form, 'rsvp.example.com', 'subdomain');

        $this->assertSame(
            rtrim(config('app.url'), '/') . '/rsvp/' . $form->slug,
            $form->fresh()->publicUrl(),
        );

        PublicDomain::where('rsvp_form_id', $form->id)->update(['status' => 'verified']);

        $this->assertSame('https://rsvp.example.com/', $form->fresh()->publicUrl());
    }

    public function test_public_domain_cannot_claim_an_existing_tenant_custom_domain(): void
    {
        [$tenant, $form] = $this->createRsvpForm();
        $tenant->update(['custom_domain' => 'planner.example.com']);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(PublicDomainService::class)->createForForm(
            $form,
            'planner.example.com',
            'apex',
        );
    }

    public function test_recheck_notifies_planner_for_verified_domain_dns_failure(): void
    {
        Mail::fake();
        [$tenant, $form] = $this->createRsvpForm();
        $planner = User::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Planner User',
            'email' => 'planner@example.com',
            'password' => 'secret',
            'type' => 'staff',
        ]);

        $roleId = DB::table('roles')->insertGetId([
            'tenant_id' => $tenant->id,
            'is_system' => true,
            'name' => 'company_owner',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('model_has_roles')->insert([
            'role_id' => $roleId,
            'tenant_id' => $tenant->id,
            'team_id' => $tenant->id,
            'model_type' => User::class,
            'model_id' => $planner->id,
        ]);

        $domain = PublicDomain::create([
            'tenant_id' => $tenant->id,
            'rsvp_form_id' => $form->id,
            'domain' => 'missing.example.com',
            'domain_type' => 'subdomain',
            'status' => 'verified',
            'verification_token' => 'koordli-verify-test',
            'verified_at' => now(),
        ]);

        $verification = $this->mock(PublicDomainVerificationService::class);
        $verification->shouldReceive('verify')->once()->withArgs(function (PublicDomain $candidate) use ($domain) {
            return $candidate->is($domain);
        })->andReturnUsing(function (PublicDomain $candidate): array {
            $candidate->update([
                'status' => 'failed',
                'failure_code' => 'cname_mismatch',
                'failure_message' => 'The domain CNAME does not point to the configured Koordli host.',
                'expected_dns_value' => 'koordli.site',
                'observed_dns_value' => null,
                'last_checked_at' => now(),
            ]);

            return [
                'success' => false,
                'message' => $candidate->failure_message,
                'failure_code' => $candidate->failure_code,
                'expected' => $candidate->expected_dns_value,
                'observed' => $candidate->observed_dns_value,
            ];
        });

        Artisan::call('koordli:recheck-public-domains');

        $domain->refresh();
        $this->assertNotNull($domain->last_notified_at);
        $this->assertNotNull($domain->notification_fingerprint);
        Mail::assertSent(PublicDomainNeedsUpdateMail::class, function (PublicDomainNeedsUpdateMail $mail) use ($planner) {
            return $mail->recipientEmail === $planner->email
                && $mail->domain === 'missing.example.com';
        });
    }

    private function createRsvpForm(): array
    {
        $tenant = Tenant::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Test Planner',
            'slug' => 'test-planner-' . Str::lower(Str::random(6)),
            'status' => 'active',
        ]);

        $event = Event::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Test Event',
            'slug' => 'test-event-' . Str::lower(Str::random(6)),
            'rsvp_enabled' => true,
        ]);

        $form = RsvpForm::create([
            'uuid' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'event_id' => $event->id,
            'title' => 'Test Event RSVP',
            'slug' => 'test-event-rsvp-' . Str::lower(Str::random(6)),
            'is_active' => true,
        ]);

        return [$tenant, $form];
    }
}
