<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    public static function defaultTerms(): string
    {
        return <<<'TEXT'
Terms and Conditions

Last updated: September 5, 2026

1. Acceptance of these terms

By creating an account or using Koordli, you agree to these Terms and Conditions. If you do not agree, do not create an account or use the service.

2. The service

Koordli provides event planning and operations tools, including workspace management, client and vendor collaboration, event forms, documents, schedules, budgets, and related features. We may update, improve, suspend, or discontinue parts of the service from time to time.

3. Accounts and security

You are responsible for providing accurate account information, keeping your login credentials confidential, and all activity carried out through your account. Notify us promptly if you believe your account has been accessed without permission.

4. Acceptable use

You must use Koordli lawfully and respectfully. You must not misuse the service, interfere with its operation, attempt unauthorized access, upload malicious code, infringe another person's rights, or use the service to send unlawful or unwanted communications.

5. Your content

You retain ownership of content you upload or create in Koordli. You grant us the limited permission needed to host, process, display, back up, and transmit that content to provide and improve the service. You are responsible for having the rights and permissions required for your content and for the information you share with clients, vendors, guests, or other users.

6. Third-party services

Koordli may connect with third-party services or links. Their services are governed by their own terms and policies, and we are not responsible for third-party services that we do not control.

7. Plans, trials, and payment

Paid features, trial periods, pricing, renewal, and cancellation terms are shown at the time of purchase or in your workspace. You authorize the applicable charges and are responsible for keeping billing information current. We may restrict paid features for overdue amounts, subject to applicable law.

8. Availability and support

We work to keep Koordli available and reliable, but we do not guarantee uninterrupted or error-free operation. Maintenance, outages, events outside our reasonable control, or third-party failures may temporarily affect availability.

9. Suspension and termination

We may suspend or terminate access where these terms are violated, payment is overdue, or necessary to protect the service, users, or the public. You may stop using the service at any time. Where reasonably possible, we will provide notice and an opportunity to resolve the issue.

10. Disclaimers and limitation of liability

To the fullest extent permitted by law, Koordli is provided on an "as is" and "as available" basis. We are not liable for indirect, incidental, special, consequential, or loss-of-profit damages arising from use of the service. Nothing in these terms limits liability that cannot lawfully be limited.

11. Changes to these terms

We may update these terms when the service or applicable requirements change. We will publish the updated version on this page and update the date above. Continued use of Koordli after an update means you accept the revised terms.

12. Contact

For questions about these terms, contact the Koordli team through the support channels provided in the service.
TEXT;
    }

    public static function defaultPrivacy(): string
    {
        return <<<'TEXT'
Privacy Policy

Last updated: September 5, 2026

1. Information we collect

We collect information you provide when you create an account, configure a workspace, contact support, manage events, invite collaborators, or use Koordli features. This may include names, email addresses, business details, event information, uploaded files, and billing or support information.

2. How we use information

We use information to provide, secure, maintain, and improve Koordli; authenticate users; process billing; provide support; communicate service updates; prevent abuse; and comply with legal obligations.

3. Information shared through your workspace

Workspace owners control the content and access settings for their workspace. Information you add to a workspace may be visible to other people the workspace owner invites, such as staff, clients, vendors, or guests. Do not add information unless you have the necessary rights and permissions.

4. Service providers

We may use trusted service providers for hosting, storage, email, payments, analytics, security, and other operations. They may process information only as needed to provide services to us and under appropriate obligations.

5. Retention and security

We retain information for as long as needed to provide the service, meet legal and business requirements, resolve disputes, and enforce our agreements. We use reasonable administrative, technical, and organizational safeguards, but no online service can guarantee absolute security.

6. Your choices and rights

Depending on your location, you may have rights to access, correct, export, delete, or restrict use of your personal information. You can also update information in your account or contact us through the support channels provided in the service.

7. Cookies and similar technology

Koordli may use cookies and similar technologies to keep you signed in, remember preferences, maintain security, and understand service usage. Your browser settings may allow you to control some cookies, though disabling them can affect functionality.

8. Children

Koordli is intended for business and event operations. It is not directed to children, and we do not knowingly collect personal information from children in violation of applicable law.

9. International processing

Your information may be processed in countries other than where you live. Where required, we use appropriate safeguards for international transfers.

10. Changes to this policy

We may update this policy as Koordli develops or legal requirements change. We will publish the updated version on this page and update the date above.

11. Contact

For privacy questions or requests, contact the Koordli team through the support channels provided in the service.
TEXT;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("platform_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting?->value ?? $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("platform_setting_{$key}");
    }
}