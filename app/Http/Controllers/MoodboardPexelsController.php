<?php

namespace App\Http\Controllers;

use App\Enums\DocumentableType;
use App\Models\Tenant\Document;
use App\Models\Tenant\Moodboard;
use App\Services\FeatureGateService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MoodboardPexelsController extends Controller
{
    public function search(Request $request)
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'moodboards.manage')) {
            return response()->json(['error' => 'You do not have permission to search stock photos.'], 403);
        }

        $query = $request->query('q', '');
        if (!$query) return response()->json(['results' => []]);

        $response = Http::withHeaders(['Authorization' => config('services.pexels.key')])
            ->get('https://api.pexels.com/v1/search', [
                'query' => $query,
                'per_page' => 20,
            ]);

        if (!$response->successful()) {
            return response()->json(['error' => 'Search failed. Please try again.'], 502);
        }

        $photos = collect($response->json('photos', []))->map(fn($p) => [
            'id'        => $p['id'],
            'thumbnail' => $p['src']['medium'],
            'full_url'  => $p['src']['large2x'],
            'alt'       => $p['alt'] ?? 'Photo',
            'photographer' => $p['photographer'] ?? null,
        ]);

        return response()->json(['results' => $photos]);
    }

    public function select(Request $request, int $moodboardId)
    {
        $moodboard = Moodboard::find($moodboardId);
        if (!$moodboard) return response()->json(['error' => 'Not found.'], 404);

        if (!app(PermissionService::class)->userCan(auth()->user(), 'moodboards.manage')) {
            return response()->json(['error' => 'You do not have permission to edit this moodboard.'], 403);
        }

        $data = $request->validate([
            'full_url' => 'required|url',
            'alt'      => 'nullable|string|max:200',
        ]);

        $imageResponse = Http::get($data['full_url']);
        if (!$imageResponse->successful()) {
            return response()->json(['error' => 'Could not download the selected image.'], 502);
        }

        $imageBytes = $imageResponse->body();
        $sizeBytes = strlen($imageBytes);

        $tenant = auth()->user()->tenant;
        if (!app(FeatureGateService::class)->hasStorageAvailable($tenant, $sizeBytes)) {
            return response()->json(['error' => 'Your workspace has run out of storage space.'], 422);
        }

        $storagePath = "moodboards/{$moodboard->tenant_id}/{$moodboard->id}/" . Str::uuid() . '.jpg';
        Storage::disk('public')->put($storagePath, $imageBytes);

        $document = Document::create([
            'tenant_id'         => $moodboard->tenant_id,
            'documentable_type' => DocumentableType::Moodboard->value,
            'documentable_id'   => $moodboard->id,
            'name'              => $data['alt'] ?? 'Stock photo',
            'type'              => 'file',
            'disk'              => 'public',
            'path'              => $storagePath,
            'mime_type'         => 'image/jpeg',
            'size'              => $sizeBytes,
            'uploaded_by_type'  => \App\Models\Tenant\User::class,
            'uploaded_by_id'    => auth()->id(),
        ]);

        return response()->json([
            'document_id' => $document->id,
            'url'         => Storage::disk('public')->url($document->path),
            'name'        => $document->name,
        ]);
    }
}