<?php

namespace App\Http\Controllers;

use App\Models\Central\Tenant;
use App\Services\FeatureGateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PwaController extends Controller
{
    public function manifest(Request $request)
    {
        $tenant = $this->resolveTenant();
        $gate = app(FeatureGateService::class);
        $whiteLabel = $tenant && $gate->canAccess($tenant, 'white_label');

        $name = ($whiteLabel && $tenant) ? $tenant->name : 'Koordli';
        $shortName = mb_substr($name, 0, 12);
        $themeColor = ($whiteLabel && $tenant) ? ($tenant->branding['primary_color'] ?? '#7C3AED') : '#7C3AED';

        $manifest = [
            'name'             => $name,
            'short_name'       => $shortName,
            'start_url'        => '/',
            'display'          => 'standalone',
            'background_color' => '#FAFAF9',
            'theme_color'      => $themeColor,
            'icons' => [
                ['src' => route('pwa.icon', ['size' => 192]), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => route('pwa.icon', ['size' => 512]), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => route('pwa.icon', ['size' => 512]), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json',
        ]);
    }

    public function icon(Request $request, int $size)
    {
        $tenant = $this->resolveTenant();
        $gate = app(FeatureGateService::class);
        $whiteLabel = $tenant && $gate->canAccess($tenant, 'white_label');

        $sourcePath = null;

        if ($whiteLabel && $tenant && !empty($tenant->branding['logo'] ?? null)) {
            $candidate = Storage::disk('public')->path($tenant->branding['logo']);
            if (file_exists($candidate)) {
                $sourcePath = $candidate;
            }
        }

        if (!$sourcePath) {
            $sourcePath = public_path('icon/koordli_icon.png');
        }

        // Hash the file's actual CONTENTS, not its path — a re-uploaded logo
        // almost always keeps the same filename/path, so a path-based hash
        // would never change and would silently serve the stale old icon
        // forever. Content-hashing means any change automatically produces
        // a fresh cache key, with no separate invalidation step needed.
        $cacheKey = md5_file($sourcePath) . '-' . $size;
        $cachedPath = storage_path('app/pwa-icons/' . $cacheKey . '.png');

        if (!file_exists($cachedPath)) {
            $this->generateResizedIcon($sourcePath, $cachedPath, $size);
        }

        return response()->file($cachedPath, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function offline(Request $request)
    {
        $tenant = $this->resolveTenant();
        $gate = app(FeatureGateService::class);
        $whiteLabel = $tenant && $gate->canAccess($tenant, 'white_label');

        $appName = ($whiteLabel && $tenant) ? $tenant->name : 'Koordli';
        $themeColor = ($whiteLabel && $tenant) ? ($tenant->branding['primary_color'] ?? '#7C3AED') : '#7C3AED';

        return response()->view('pwa.offline', [
            'appName'    => $appName,
            'themeColor' => $themeColor,
        ]);
    }

    public function serviceWorker(Request $request)
    {
        $version = $this->computeAssetVersion();
        $js = view('pwa.service-worker', ['version' => $version])->render();

        return response($js, 200, [
            'Content-Type'  => 'application/javascript',
            // Critical: the service worker script itself must NEVER be
            // aggressively HTTP-cached, or the browser may never even
            // check whether this file (and the version bump inside it)
            // has changed — a well-known PWA gotcha, and exactly the class
            // of caching bug this project has already been bitten by once.
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    /**
     * Ties the cache version directly to real deploys rather than a
     * manually-incremented number someone has to remember to bump. Checks
     * both possible Vite manifest locations (path differs across
     * laravel-vite-plugin versions) for robustness, with safe fallbacks
     * so this never hard-fails even if neither manifest is found.
     */
    private function computeAssetVersion(): string
    {
        $candidates = [
            public_path('build/.vite/manifest.json'),
            public_path('build/manifest.json'),
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return substr(md5_file($path), 0, 12);
            }
        }

        if (is_dir(public_path('build'))) {
            return substr(md5((string) filemtime(public_path('build'))), 0, 12);
        }

        return 'v1';
    }

    /**
     * Resolves tenant via whichever guard is currently authenticated, rather
     * than domain-based middleware — sidesteps the fact that ResolveTenantByDomain
     * doesn't run on every authenticated route today. Every user model (staff,
     * client, vendor) carries tenant_id, so this works reliably regardless.
     */
    private function resolveTenant(): ?Tenant
    {
        $tenantId = auth('web')->check()
            ? auth('web')->user()->tenant_id
            : (auth('client')->check()
                ? auth('client')->user()->tenant_id
                : (auth('vendor')->check() ? auth('vendor')->user()->tenant_id : null));

        return $tenantId ? Tenant::find($tenantId) : null;
    }

    /**
     * Pads any source image to a square canvas (transparent background)
     * before scaling — protects against a non-square tenant-uploaded logo
     * looking stretched or cropped as a home-screen icon. Uses PHP's GD
     * extension directly; no new composer dependency required.
     */
    private function generateResizedIcon(string $sourcePath, string $destPath, int $size): void
    {
        if (!extension_loaded('gd')) {
            @mkdir(dirname($destPath), 0755, true);
            copy($sourcePath, $destPath);
            return;
        }

        @mkdir(dirname($destPath), 0755, true);

        $info = @getimagesize($sourcePath);
        $mime = $info['mime'] ?? 'image/png';

        $src = match ($mime) {
            'image/png'  => @imagecreatefrompng($sourcePath),
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            default      => @imagecreatefrompng($sourcePath),
        };

        if (!$src) {
            copy($sourcePath, $destPath);
            return;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);
        $squareSize = max($srcW, $srcH);

        $square = imagecreatetruecolor($squareSize, $squareSize);
        imagesavealpha($square, true);
        $transparent = imagecolorallocatealpha($square, 0, 0, 0, 127);
        imagefill($square, 0, 0, $transparent);
        imagecopy($square, $src, (int) (($squareSize - $srcW) / 2), (int) (($squareSize - $srcH) / 2), 0, 0, $srcW, $srcH);

        $resized = imagecreatetruecolor($size, $size);
        imagesavealpha($resized, true);
        imagefill($resized, 0, 0, $transparent);
        imagecopyresampled($resized, $square, 0, 0, 0, 0, $size, $size, $squareSize, $squareSize);

        imagepng($resized, $destPath);

        imagedestroy($src);
        imagedestroy($square);
        imagedestroy($resized);
    }
}