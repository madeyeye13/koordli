<?php

namespace Database\Seeders;

use App\Models\Central\SupportFaq;
use Illuminate\Database\Seeder;

class SupportFaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'How do I add a custom domain?',
                'answer'   => 'Go to Domain Settings in your dashboard sidebar. Add your domain, then follow the DNS instructions (a CNAME and TXT record) shown on that page. Click "Verify Now" once you\'ve added the records.',
                'keywords' => ['domain', 'custom domain', 'dns', 'cname', 'subdomain'],
                'category' => 'domains',
            ],
            [
                'question' => 'How does RSVP work?',
                'answer'   => 'Enable RSVP for any event from the event\'s edit page. You can then customize your RSVP form under Guests & RSVP, including branding, custom questions, and QR check-in.',
                'keywords' => ['rsvp', 'guest', 'qr code', 'check-in', 'checkin'],
                'category' => 'rsvp',
            ],
            [
                'question' => 'How do I upgrade my plan?',
                'answer'   => 'Go to Billing in your sidebar, then click Upgrade. You can switch between monthly and annual billing there too.',
                'keywords' => ['upgrade', 'plan', 'billing', 'subscription', 'pricing'],
                'category' => 'billing',
            ],
            [
                'question' => 'Can vendors sign contracts electronically?',
                'answer'   => 'Yes. When you send a contract from the Contracts section, your vendor receives a signing link. They can draw or type their signature directly, no printing required.',
                'keywords' => ['contract', 'sign', 'signature', 'e-signature', 'vendor contract'],
                'category' => 'contracts',
            ],
            [
                'question' => 'How do I invite my staff?',
                'answer'   => 'Go to Staff in your sidebar and click Invite. They\'ll receive an email with login credentials.',
                'keywords' => ['staff', 'invite', 'team', 'add user'],
                'category' => 'staff',
            ],
            [
                'question' => 'What happens if my trial expires?',
                'answer'   => 'Your account moves to read-only mode — you can still view all your data, but can\'t create or edit anything until you upgrade. There\'s a grace period before this happens.',
                'keywords' => ['trial', 'expire', 'expired', 'locked', 'grace period'],
                'category' => 'billing',
            ],
        ];

        foreach ($faqs as $i => $faq) {
            SupportFaq::updateOrCreate(
                ['question' => $faq['question']],
                array_merge($faq, ['sort_order' => $i, 'is_active' => true])
            );
        }
    }
}