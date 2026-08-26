<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature = 'koordli:generate-vapid-keys';
    protected $description = 'Generates a new VAPID key pair for Web Push and prints them for you to paste into .env';

    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();

        $this->info('Add these to your .env file:');
        $this->line('');
        $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        $this->line('VAPID_SUBJECT=mailto:support@koordli.com');
        $this->line('');
        $this->warn('Then run: php artisan config:clear');

        return self::SUCCESS;
    }
}