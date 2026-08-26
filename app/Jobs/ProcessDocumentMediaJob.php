<?php

namespace App\Jobs;

use App\Models\Tenant\Document;
use App\Services\DocumentMediaProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDocumentMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1; // media processing failures shouldn't silently retry against a possibly-corrupt intermediate file

    public function __construct(public int $documentId) {}

    public function handle(DocumentMediaProcessingService $service): void
    {
        // withoutGlobalScope('tenant'): runs in a queue worker with no
        // request-bound tenant context — same reasoning already applied
        // to SendWebPushNotificationJob and every other queued job in
        // this app that touches a BelongsToTenant model.
        $document = Document::withoutGlobalScope('tenant')->find($this->documentId);

        if (!$document || $document->compression_status !== 'pending') {
            return; // already processed, or the document/file was deleted before the job ran
        }

        $document->update(['compression_status' => 'processing']);

        try {
            if ($document->isImage()) {
                $service->optimizeImage($document);
            } elseif ($document->isVideo()) {
                $service->compressVideo($document);
            } else {
                $document->update(['compression_status' => 'not_applicable']);
            }
        } catch (\Throwable $e) {
            $document->update(['compression_status' => 'failed']);
            report($e);
        }
    }
}