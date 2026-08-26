<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanupChunkedUploadSessions extends Command
{
    protected $signature = 'koordli:cleanup-chunked-upload-sessions';
    protected $description = 'Deletes abandoned chunked-upload session directories older than 24 hours '
        . '(created when someone starts an upload but never finalizes it — e.g. closes the tab mid-upload).';

    public function handle(): int
    {
        $baseDir = storage_path('app/chunked-uploads');

        if (!is_dir($baseDir)) {
            $this->info('No chunked-uploads directory yet — nothing to clean.');
            return self::SUCCESS;
        }

        $cutoff = now()->subHours(24)->getTimestamp();
        $deleted = 0;

        foreach (glob($baseDir . '/*', GLOB_ONLYDIR) as $dir) {
            $lastModified = filemtime($dir);
            if ($lastModified !== false && $lastModified < $cutoff) {
                array_map('unlink', glob($dir . '/*'));
                @rmdir($dir);
                $deleted++;
            }
        }

        $this->info("Deleted {$deleted} abandoned upload session(s).");
        return self::SUCCESS;
    }
}