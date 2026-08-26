<?php

namespace App\Services;

use App\Models\Tenant\Document;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class DocumentMediaProcessingService
{
    private const IMAGE_MAX_DIMENSION = 2560;
    private const IMAGE_RECOMPRESS_THRESHOLD_BYTES = 2 * 1024 * 1024; // 2MB
    private const IMAGE_QUALITY = 82;
    private const THUMBNAIL_MAX_DIMENSION = 400;
    private const VIDEO_MAX_WIDTH = 1280;

    public function ffmpegAvailable(): bool
    {
        $process = new Process(['ffmpeg', '-version']);
        $process->run();
        return $process->isSuccessful();
    }

    public function optimizeImage(Document $document): void
    {
        $disk = Storage::disk($document->disk);
        $absolutePath = $disk->path($document->path);

        if (!file_exists($absolutePath) || !extension_loaded('gd')) {
            $document->update(['compression_status' => 'skipped']);
            return;
        }

        $info = @getimagesize($absolutePath);
        if (!$info) {
            $document->update(['compression_status' => 'failed']);
            return;
        }

        [$width, $height] = $info;
        $mime = $info['mime'];

        $src = $this->loadImage($absolutePath, $mime);
        if (!$src) {
            $document->update(['compression_status' => 'failed']);
            return;
        }

        $originalSize = filesize($absolutePath);
        $needsResize = max($width, $height) > self::IMAGE_MAX_DIMENSION;
        $needsRecompress = !$needsResize && $originalSize > self::IMAGE_RECOMPRESS_THRESHOLD_BYTES;

        $finalWidth = $width;
        $finalHeight = $height;

        if ($needsResize || $needsRecompress) {
            if ($needsResize) {
                $ratio = self::IMAGE_MAX_DIMENSION / max($width, $height);
                $finalWidth = (int) round($width * $ratio);
                $finalHeight = (int) round($height * $ratio);
            }

            $resized = imagecreatetruecolor($finalWidth, $finalHeight);
            $this->preserveTransparency($resized, $mime);
            imagecopyresampled($resized, $src, 0, 0, 0, 0, $finalWidth, $finalHeight, $width, $height);

            $this->saveImage($resized, $absolutePath, $mime);
            imagedestroy($resized);
        }
        // else: file is already reasonably sized and not oversized — left
        // untouched deliberately, since re-encoding an already-optimized
        // small image risks increasing its size rather than reducing it.

        $thumbPath = $this->buildThumbnailPath($document->path);
        $this->generateImageThumbnail($absolutePath, $disk->path($thumbPath), $mime);

        imagedestroy($src);

        $document->update([
            'width'              => $finalWidth,
            'height'             => $finalHeight,
            'size'               => $disk->size($document->path),
            'thumbnail_path'     => $thumbPath,
            'compression_status' => 'completed',
        ]);
    }

    public function compressVideo(Document $document): void
    {
        if (!$this->ffmpegAvailable()) {
            // Graceful degradation, per spec — store as-uploaded rather
            // than failing the whole upload if FFmpeg isn't installed.
            $document->update(['compression_status' => 'skipped']);
            return;
        }

        $disk = Storage::disk($document->disk);
        $sourcePath = $disk->path($document->path);

        if (!file_exists($sourcePath)) {
            $document->update(['compression_status' => 'failed']);
            return;
        }

        $originalSize = filesize($sourcePath);
        $tempOutput = $sourcePath . '.compressed.mp4';

        $process = new Process([
            'ffmpeg', '-y', '-i', $sourcePath,
            '-vf', 'scale=\'min('.self::VIDEO_MAX_WIDTH.',iw)\':-2',
            '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '26',
            '-c:a', 'aac', '-b:a', '128k',
            $tempOutput,
        ]);
        $process->setTimeout(1800); // generous — real phone videos, not instant
        $process->run();

        if (!$process->isSuccessful() || !file_exists($tempOutput)) {
            @unlink($tempOutput);
            $document->update(['compression_status' => 'failed']);
            return;
        }

        $compressedSize = filesize($tempOutput);

        // Only replace the original if compression actually helped — an
        // already-efficiently-encoded source video can end up LARGER after
        // re-encoding at these settings; if so, keep the original untouched.
        $finalPath = $sourcePath;
        $finalSize = $originalSize;
        $replaced = false;

        if ($compressedSize < $originalSize) {
            unlink($sourcePath);
            rename($tempOutput, $sourcePath);
            $finalSize = $compressedSize;
            $replaced = true;
        } else {
            @unlink($tempOutput);
        }

        [$width, $height, $duration] = $this->probeVideo($sourcePath);

        $thumbPath = $this->buildThumbnailPath($document->path, 'jpg');
        $this->generateVideoThumbnail($sourcePath, $disk->path($thumbPath));

        $document->update([
            'width'              => $width,
            'height'             => $height,
            'duration_seconds'   => $duration,
            'size'               => $finalSize,
            'thumbnail_path'     => file_exists($disk->path($thumbPath)) ? $thumbPath : null,
            'compression_status' => 'completed',
        ]);
    }

    private function probeVideo(string $path): array
    {
        $process = new Process([
            'ffprobe', '-v', 'error',
            '-select_streams', 'v:0',
            '-show_entries', 'stream=width,height:format=duration',
            '-of', 'csv=p=0',
            $path,
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            return [null, null, null];
        }

        $lines = array_filter(array_map('trim', explode("\n", $process->getOutput())));
        $first = $lines[0] ?? '';
        $parts = explode(',', $first);

        $width = isset($parts[0]) ? (int) $parts[0] : null;
        $height = isset($parts[1]) ? (int) $parts[1] : null;
        $duration = isset($parts[2]) ? (int) round((float) $parts[2]) : null;

        return [$width, $height, $duration];
    }

    private function generateVideoThumbnail(string $videoPath, string $thumbOutputPath): void
    {
        @mkdir(dirname($thumbOutputPath), 0755, true);

        $process = new Process([
            'ffmpeg', '-y', '-i', $videoPath,
            '-ss', '00:00:01', '-vframes', '1',
            '-vf', 'scale=' . self::THUMBNAIL_MAX_DIMENSION . ':-2',
            $thumbOutputPath,
        ]);
        $process->run();
        // Failure here is non-fatal — the document still saves successfully
        // without a thumbnail; the UI falls back to a generic video icon.
    }

    private function loadImage(string $path, string $mime)
    {
        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/gif'  => @imagecreatefromgif($path),
            'image/webp' => @imagecreatefromwebp($path),
            default      => false,
        };
    }

    private function saveImage($image, string $path, string $mime): void
    {
        match ($mime) {
            'image/jpeg' => imagejpeg($image, $path, self::IMAGE_QUALITY),
            'image/png'  => imagepng($image, $path, 6),
            'image/gif'  => imagegif($image, $path),
            'image/webp' => imagewebp($image, $path, self::IMAGE_QUALITY),
            default      => imagejpeg($image, $path, self::IMAGE_QUALITY),
        };
    }

    private function generateImageThumbnail(string $sourcePath, string $thumbOutputPath, string $mime): void
    {
        @mkdir(dirname($thumbOutputPath), 0755, true);

        $src = $this->loadImage($sourcePath, $mime);
        if (!$src) return;

        $width = imagesx($src);
        $height = imagesy($src);
        $ratio = self::THUMBNAIL_MAX_DIMENSION / max($width, $height);
        $thumbWidth = (int) round($width * min(1, $ratio));
        $thumbHeight = (int) round($height * min(1, $ratio));

        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
        $this->preserveTransparency($thumb, $mime);
        imagecopyresampled($thumb, $src, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);

        imagejpeg($thumb, $thumbOutputPath, self::IMAGE_QUALITY);

        imagedestroy($src);
        imagedestroy($thumb);
    }

    private function preserveTransparency($image, string $mime): void
    {
        if ($mime === 'image/png' || $mime === 'image/webp' || $mime === 'image/gif') {
            imagesavealpha($image, true);
            $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
            imagefill($image, 0, 0, $transparent);
        }
    }

    private function buildThumbnailPath(string $originalPath, string $extension = 'jpg'): string
    {
        $dir = dirname($originalPath);
        $name = pathinfo($originalPath, PATHINFO_FILENAME);
        return "{$dir}/thumbs/{$name}.{$extension}";
    }
}