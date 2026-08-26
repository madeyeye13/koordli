<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupVideoCompressionTempFiles extends Command
{
    protected $signature = 'koordli:cleanup-video-temp-files';
    protected $description = 'Deletes leftover .compressed.mp4 temp files from FFmpeg jobs that crashed '
        . 'or were interrupted mid-compression (older than 24 hours — anything newer might still be actively processing).';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $cutoff = now()->subHours(24)->getTimestamp();
        $deleted = 0;

        foreach ($disk->allFiles('documents') as $path) {
            if (str_ends_with($path, '.compressed.mp4')) {
                $lastModified = $disk->lastModified($path);
                if ($lastModified < $cutoff) {
                    $disk->delete($path);
                    $deleted++;
                }
            }
        }

        $this->info("Deleted {$deleted} stale video compression temp file(s).");
        return self::SUCCESS;
    }
}