<?php

namespace App\Http\Controllers;

use App\Enums\DocumentableType;
use App\Models\Central\Tenant;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentShareLink;
use App\Models\Tenant\Event;
use App\Models\Tenant\User;
use App\Services\FeatureGateService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaUploadController extends Controller
{
    private const MAX_FILE_BYTES = 750 * 1024 * 1024;
    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'mp4', 'mov', 'avi', 'webm',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv',
    ];

    public function chunk(Request $request)
    {
        $data = $request->validate([
            'upload_session' => 'required|uuid',
            'event_id'       => 'nullable|integer',
            'share_token'    => 'nullable|string',
            'chunk_index'    => 'required|integer|min:0',
            'total_chunks'   => 'required|integer|min:1',
            'chunk'          => 'required|file',
        ]);

        $resolved = $this->resolveContext($request, $data);
        if (!$resolved['ok']) {
            return response()->json(['error' => $resolved['error']], $resolved['status']);
        }

        $sessionDir = $this->sessionDir($data['upload_session']);
        if (!is_dir($sessionDir)) {
            mkdir($sessionDir, 0755, true);
        }

        $chunkPath = $sessionDir . '/' . $data['chunk_index'] . '.part';

        if (!file_exists($chunkPath)) {
            $request->file('chunk')->move($sessionDir, $data['chunk_index'] . '.part');
        }

        $receivedCount = count(glob($sessionDir . '/*.part'));

        return response()->json([
            'received' => $receivedCount,
            'total'    => $data['total_chunks'],
        ]);
    }

    public function finalize(Request $request)
    {
        $data = $request->validate([
            'upload_session' => 'required|uuid',
            'event_id'       => 'nullable|integer',
            'share_token'    => 'nullable|string',
            'folder_id'      => 'nullable|integer',
            'file_name'      => 'required|string|max:255',
            'total_chunks'   => 'required|integer|min:1',
            'upload_type'    => 'nullable|in:file,logo',
        ]);

        $resolved = $this->resolveContext($request, $data);
        if (!$resolved['ok']) {
            return response()->json(['error' => $resolved['error']], $resolved['status']);
        }

        ['event' => $event, 'tenantId' => $tenantId, 'uploaderType' => $uploaderType,
         'uploaderId' => $uploaderId, 'shareLinkId' => $shareLinkId] = $resolved;

        $extension = strtolower(pathinfo($data['file_name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return response()->json(['error' => "Files of type .{$extension} are not allowed."], 422);
        }

        $sessionDir = $this->sessionDir($data['upload_session']);

        for ($i = 0; $i < $data['total_chunks']; $i++) {
            if (!file_exists($sessionDir . '/' . $i . '.part')) {
                return response()->json(['error' => "Chunk {$i} is missing. Please retry.", 'missing_chunk' => $i], 400);
            }
        }

        $assembledPath = $sessionDir . '/assembled';
        $out = fopen($assembledPath, 'wb');
        for ($i = 0; $i < $data['total_chunks']; $i++) {
            $in = fopen($sessionDir . '/' . $i . '.part', 'rb');
            stream_copy_to_stream($in, $out);
            fclose($in);
        }
        fclose($out);

        $actualSize = filesize($assembledPath);

        if ($actualSize > self::MAX_FILE_BYTES) {
            $this->cleanupSession($sessionDir);
            $sizeMb = round($actualSize / 1024 / 1024);
            return response()->json(['error' => "This file is {$sizeMb}MB, which exceeds the 750MB limit."], 422);
        }

        $tenant = Tenant::find($tenantId);
        $gate = app(FeatureGateService::class);

        if (!$gate->hasStorageAvailable($tenant, $actualSize)) {
            $this->cleanupSession($sessionDir);
            return response()->json(['error' => 'Your workspace has run out of storage space. Contact your plan administrator to upgrade.'], 422);
        }

        $mimeType = mime_content_type($assembledPath) ?: 'application/octet-stream';
        $isLogo = ($data['upload_type'] ?? 'file') === 'logo';

        $finalName = \Illuminate\Support\Str::uuid() . '.' . $extension;
        $storagePath = "documents/{$tenantId}/{$event->id}/{$finalName}";

        $stream = fopen($assembledPath, 'rb');
        Storage::disk('public')->put($storagePath, $stream);
        if (is_resource($stream)) fclose($stream);

        $width = null;
        $height = null;
        if (str_starts_with($mimeType, 'image/')) {
            $dimensions = @getimagesize($assembledPath);
            if ($dimensions) {
                [$width, $height] = $dimensions;
            }
        }

        $needsCompression = str_starts_with($mimeType, 'image/') || str_starts_with($mimeType, 'video/');

        if ($isLogo) {
            $oldLogo = Document::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenantId)
                ->where('documentable_type', DocumentableType::Event->value)
                ->where('documentable_id', $event->id)
                ->where('type', 'logo')
                ->first();

            if ($oldLogo) {
                Storage::disk($oldLogo->disk)->delete($oldLogo->path);
                $oldLogo->delete();
            }
        }

        $document = Document::create([
            'tenant_id'                  => $tenantId,
            'documentable_type'          => DocumentableType::Event->value,
            'documentable_id'            => $event->id,
            'folder_id'                  => $isLogo ? null : ($data['folder_id'] ?? null),
            'name'                       => $data['file_name'],
            'type'                       => $isLogo ? 'logo' : 'file',
            'disk'                       => 'public',
            'path'                       => $storagePath,
            'mime_type'                  => $mimeType,
            'size'                       => $actualSize,
            'width'                      => $width,
            'height'                     => $height,
            'compression_status'         => $needsCompression ? 'pending' : 'not_applicable',
            'uploaded_by_type'           => $uploaderType,
            'uploaded_by_id'             => $uploaderId,
            'uploaded_via_share_link_id' => $shareLinkId,
        ]);

        if ($needsCompression) {
            \App\Jobs\ProcessDocumentMediaJob::dispatch($document->id);
        }

        if ($shareLinkId) {
            $this->notifyOwnersOfGuestUpload($tenant, $event, $document->name);
        }

        $this->cleanupSession($sessionDir);

        return response()->json([
            'success' => true,
            'document' => [
                'id'   => $document->id,
                'name' => $document->name,
                'type' => $document->type,
                'url'  => Storage::disk('public')->url($document->path),
                'mime_type' => $document->mime_type,
                'size' => $document->size,
            ],
        ]);
    }

    /**
     * Resolves EITHER an authenticated uploader (staff/client, existing
     * flow) OR an anonymous guest via a valid share_token — the two paths
     * converge here so chunk()/finalize() don't need separate duplicated
     * logic for each.
     */
    private function resolveContext(Request $request, array $data): array
    {
        if (!empty($data['share_token'])) {
            $link = DocumentShareLink::withoutGlobalScope('tenant')
                ->where('token', $data['share_token'])
                ->first();

            if (!$link) {
                return ['ok' => false, 'error' => 'This link is not valid.', 'status' => 404];
            }

            if (!$link->isUsable()) {
                $tenant = Tenant::find($link->tenant_id);
                $companyName = $tenant->name ?? 'the organizer';
                return ['ok' => false, 'error' => "This link is no longer available. Please reach out to {$companyName} if you believe this is an error.", 'status' => 403];
            }

            $event = Event::withoutGlobalScope('tenant')->find($link->event_id);
            if (!$event) {
                return ['ok' => false, 'error' => 'Event not found.', 'status' => 404];
            }

            return [
                'ok' => true,
                'event' => $event,
                'tenantId' => $link->tenant_id,
                'uploaderType' => null,
                'uploaderId' => null,
                'shareLinkId' => $link->id,
            ];
        }

        // Authenticated path — existing staff/client flow
        [$uploaderType, $uploaderId, $tenantId] = $this->resolveUploader();
        if (!$uploaderId) {
            return ['ok' => false, 'error' => 'Not authenticated.', 'status' => 401];
        }

        if (empty($data['event_id'])) {
            return ['ok' => false, 'error' => 'Event is required.', 'status' => 422];
        }

        $event = Event::withoutGlobalScope('tenant')
            ->where('id', $data['event_id'])
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$event) {
            return ['ok' => false, 'error' => 'Event not found.', 'status' => 404];
        }

        if (!$this->canUpload($uploaderType, $uploaderId, $tenantId, $event)) {
            return ['ok' => false, 'error' => 'You do not have permission to upload files here.', 'status' => 403];
        }

        return [
            'ok' => true,
            'event' => $event,
            'tenantId' => $tenantId,
            'uploaderType' => $uploaderType,
            'uploaderId' => $uploaderId,
            'shareLinkId' => null,
        ];
    }

    private function resolveUploader(): array
    {
        if (auth('web')->check()) {
            $u = auth('web')->user();
            return [get_class($u), $u->id, $u->tenant_id];
        }
        if (auth('client')->check()) {
            $u = auth('client')->user();
            return [get_class($u), $u->id, $u->tenant_id];
        }
        return [null, null, null];
    }

    private function canUpload(string $uploaderType, int $uploaderId, int $tenantId, Event $event): bool
    {
        if ($uploaderType === User::class) {
            $user = User::withoutGlobalScope('tenant')->find($uploaderId);
            return $user && app(PermissionService::class)->userCan($user, 'documents.upload');
        }

        if ($uploaderType === \App\Models\Central\Client::class) {
            return \App\Models\Tenant\ClientEventAccess::withoutGlobalScope('tenant')
                ->where('client_id', $uploaderId)
                ->where('event_id', $event->id)
                ->exists();
        }

        return false;
    }

    /**
     * Notifies whoever holds real owner-level access (is_system flag) —
     * NOT a hardcoded role-name lookup like hasRole('company_owner').
     * The whole point of the roles system is that role NAMES are tenant-
     * customizable, so a name-based check here would silently break for
     * any tenant who renames their owner role.
     *
     * NOTE: depends on a NotificationTemplate row keyed
     * 'document_shared_via_link' existing in the database — notify()
     * silently no-ops if it doesn't. Needs to be seeded; flagging this
     * as an open dependency rather than guessing at how templates are
     * created in this app.
     */
    private function notifyOwnersOfGuestUpload(Tenant $tenant, Event $event, string $fileName): void
    {
        // Explicit team-context set required here — this route has zero
        // middleware (it's hit by an anonymous guest via a share link),
        // so nothing upstream sets Spatie's permissions team ID the way
        // authenticated tenant routes do. Without this, the roles query
        // below silently finds nobody, exactly as reproduced in Tinker
        // before the context was set manually.
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

        $owners = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->whereHas('roles', fn($q) => $q->where('is_system', true))
            ->get();

        foreach ($owners as $owner) {
            app(\App\Services\Notifications\NotificationDispatchService::class)->notify(
                notifiable: $owner,
                category: 'documents',
                notificationType: 'document_shared_via_link',
                templateKey: 'document_shared_via_link',
                placeholders: [
                    'user_name'  => $owner->name,
                    'file_name'  => $fileName,
                    'event_name' => $event->name,
                ],
                priority: 'normal',
                actionUrl: route('tenant.events.media', $event->slug),
                actionLabel: 'View Media Library',
                tenantId: $tenant->id,
            );
        }
    }

    private function sessionDir(string $session): string
    {
        return storage_path('app/chunked-uploads/' . $session);
    }

    private function cleanupSession(string $dir): void
    {
        array_map('unlink', glob($dir . '/*'));
        @rmdir($dir);
    }
}