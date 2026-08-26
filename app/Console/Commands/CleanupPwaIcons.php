<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupPwaIcons extends Command
{
    protected $signature = 'koordli:cleanup-pwa-icons';
    protected $description = 'Deletes cached PWA icon files older than 30 days. Safe to run anytime — anything still in use regenerates automatically on next request.';

    public function handle(): int
    {
        $directory = storage_path('app/pwa-icons');

        if (!is_dir($directory)) {
            $this->info('No pwa-icons directory yet — nothing to clean.');
            return self::SUCCESS;
        }

        $cutoff = now()->subDays(30)->getTimestamp();
        $deleted = 0;

        foreach (glob($directory . '/*.png') as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }

        $this->info("Deleted {$deleted} stale cached icon(s).");
        return self::SUCCESS;
    }
}