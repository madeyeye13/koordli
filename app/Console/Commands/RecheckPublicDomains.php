<?php

namespace App\Console\Commands;

use App\Jobs\SendPublicDomainNeedsUpdateJob;
use App\Models\Central\PublicDomain;
use App\Models\Tenant\User;
use App\Services\PublicDomainVerificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecheckPublicDomains extends Command
{
    protected $signature = 'koordli:recheck-public-domains';
    protected $description = 'Recheck event RSVP domains and notify planners about DNS problems';

    public function handle(PublicDomainVerificationService $verificationService): int
    {
        PublicDomain::with(['rsvpForm.event', 'tenant'])
            ->whereIn('status', ['pending', 'verified', 'failed'])
            ->each(function (PublicDomain $publicDomain) use ($verificationService): void {
                $wasVerified = $publicDomain->status === 'verified';
                $result = $verificationService->verify($publicDomain);

                if ($wasVerified && !$result['success']) {
                    $fingerprint = hash('sha256', implode('|', [
                        $publicDomain->failure_code,
                        $publicDomain->expected_dns_value,
                        $publicDomain->observed_dns_value,
                    ]));

                    if ($fingerprint === $publicDomain->notification_fingerprint) {
                        $this->line("{$publicDomain->domain}: failure already notified");
                        return;
                    }

                    $plannerId = DB::table('model_has_roles')
                        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                        ->join('users', 'users.id', '=', 'model_has_roles.model_id')
                        ->where('model_has_roles.model_type', User::class)
                        ->where('model_has_roles.tenant_id', $publicDomain->tenant_id)
                        ->where('roles.tenant_id', $publicDomain->tenant_id)
                        ->where('roles.is_system', true)
                        ->whereNotNull('users.email')
                        ->orderBy('users.id')
                        ->value('users.id');

                    $planner = $plannerId
                        ? User::withoutGlobalScope('tenant')->find($plannerId)
                        : null;

                    if (!$planner || !$publicDomain->rsvpForm?->event) {
                        return;
                    }

                    $publicDomain->notification_fingerprint = $fingerprint;
                    $publicDomain->last_notified_at = now();
                    $publicDomain->save();

                    SendPublicDomainNeedsUpdateJob::dispatch(
                        $planner->email,
                        $planner->name,
                        $publicDomain->rsvpForm->event->name,
                        $publicDomain->domain,
                        $publicDomain->domain_type,
                        $publicDomain->failure_message ?? 'Your DNS record needs to be updated.',
                        (string) $publicDomain->expected_dns_value,
                        $publicDomain->observed_dns_value,
                        route('tenant.events.rsvp', $publicDomain->rsvpForm->event->slug),
                    );

                    $this->warn("{$publicDomain->domain}: planner notified");
                } else {
                    $this->line("{$publicDomain->domain}: " . ($result['success'] ? 'verified' : $publicDomain->status));
                }
            });

        return self::SUCCESS;
    }
}
