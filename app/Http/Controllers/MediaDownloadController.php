<?php

namespace App\Http\Controllers;

use App\Enums\DocumentableType;
use App\Models\Tenant\ClientEventAccess;
use App\Models\Tenant\Document;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaDownloadController extends Controller
{
    public function single(int $id)
    {
        $document = Document::withoutGlobalScope('tenant')->find($id);

        if (!$document || $document->type === 'link' || !$this->isAuthorized($document)) {
            abort(404);
        }

        if (!Storage::disk($document->disk)->exists($document->path)) {
            abort(404);
        }

        return Storage::disk($document->disk)->download($document->path, $document->name);
    }

    public function bulk(Request $request)
    {
        $ids = array_filter(array_map('intval', explode(',', $request->query('ids', ''))));
        if (empty($ids)) abort(404);

        $documents = Document::withoutGlobalScope('tenant')
            ->whereIn('id', $ids)
            ->where('type', '!=', 'link')
            ->get()
            ->filter(fn($doc) => $this->isAuthorized($doc));

        if ($documents->isEmpty()) abort(404);

        $zipName = 'media-' . now()->format('Ymd-His') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipName);
        @mkdir(dirname($zipPath), 0755, true);

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $usedNames = [];
        foreach ($documents as $doc) {
            $disk = Storage::disk($doc->disk);
            if (!$disk->exists($doc->path)) continue;

            $name = $doc->name;
            $i = 1;
            while (in_array($name, $usedNames)) {
                $ext = pathinfo($doc->name, PATHINFO_EXTENSION);
                $base = pathinfo($doc->name, PATHINFO_FILENAME);
                $name = $base . '-' . $i . ($ext ? '.' . $ext : '');
                $i++;
            }
            $usedNames[] = $name;

            $zip->addFromString($name, $disk->get($doc->path));
        }
        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    private function isAuthorized(Document $document): bool
    {
        if (auth('web')->check()) {
            $user = auth('web')->user();
            return $user->tenant_id === $document->tenant_id
                && app(PermissionService::class)->userCan($user, 'documents.view');
        }

        if (auth('client')->check()) {
            $client = auth('client')->user();
            if ($client->tenant_id !== $document->tenant_id) return false;

            if ($document->documentable_type === DocumentableType::Event->value) {
                return ClientEventAccess::withoutGlobalScope('tenant')
                    ->where('client_id', $client->id)
                    ->where('event_id', $document->documentable_id)
                    ->exists();
            }
            return false;
        }

        return false;
    }
}