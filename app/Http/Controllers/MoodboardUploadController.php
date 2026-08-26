<?php

namespace App\Http\Controllers;

use App\Enums\DocumentableType;
use App\Models\Tenant\Document;
use App\Models\Tenant\Moodboard;
use App\Services\FeatureGateService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MoodboardUploadController extends Controller
{
    public function upload(Request $request, int $moodboardId)
    {
        $moodboard = Moodboard::find($moodboardId);
        if (!$moodboard) return response()->json(['error' => 'Not found.'], 404);

        if (!app(PermissionService::class)->userCan(auth()->user(), 'moodboards.manage')) {
            return response()->json(['error' => 'You do not have permission to edit this moodboard.'], 403);
        }

        $request->validate(['file' => 'required|file|max:768000']); // 750MB, matching Media Library's cap

        $file = $request->file('file');
        $tenant = auth()->user()->tenant;
        $gate = app(FeatureGateService::class);

        if (!$gate->hasStorageAvailable($tenant, $file->getSize())) {
            return response()->json(['error' => 'Your workspace has run out of storage space. Contact your plan administrator to upgrade.'], 422);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $storagePath = "moodboards/{$moodboard->tenant_id}/{$moodboard->id}/" . Str::uuid() . '.' . $extension;
        Storage::disk('public')->putFileAs('', $file, $storagePath);

        $document = Document::create([
            'tenant_id'         => $moodboard->tenant_id,
            'documentable_type' => DocumentableType::Moodboard->value,
            'documentable_id'   => $moodboard->id,
            'name'              => $file->getClientOriginalName(),
            'type'              => 'file',
            'disk'              => 'public',
            'path'              => $storagePath,
            'mime_type'         => $file->getMimeType(),
            'size'              => $file->getSize(),
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