<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ImageOptimizationService
{
    /**
     * Resizes (never upscales), compresses, and stores on the CONFIGURED
     * disk — never hardcoded 'public' — so switching to S3 later needs
     * zero changes here.
     */
    public function store($uploadedFile, string $directory): array
    {
        $disk = config('blog.storage_disk');
        $maxWidth = config('blog.image.max_width');
        $quality = config('blog.image.quality');

        $image = Image::read($uploadedFile->getRealPath());

        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        $filename = Str::uuid() . '.webp';
        $encoded = $image->toWebp($quality);

        $path = trim($directory, '/') . '/' . $filename;
        Storage::disk($disk)->put($path, (string) $encoded);

        return [
            'path' => $path,
            'size' => strlen((string) $encoded),
        ];
    }
}