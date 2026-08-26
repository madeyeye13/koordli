<?php

namespace App\Livewire\Client\Documents;

use App\Enums\DocumentableType;
use App\Models\Tenant\ClientEventAccess;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentFolder;
use App\Models\Tenant\Event;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.client')]
class MediaLibraryClient extends Component
{
    use \App\Traits\WithToast;

    public Event $event;
    public ?int $currentFolderId = null;

    public bool $showDeleteModal = false;
    public ?int $deleteDocumentId = null;

    public array $selectedDocumentIds = [];
    public bool $showBulkDeleteModal = false;

    public function mount(string $slug): void
    {
        $this->event = Event::withoutGlobalScope('tenant')->where('slug', $slug)->firstOrFail();
        abort_unless($this->hasAccess(), 403);
    }

    private function hasAccess(): bool
    {
        return ClientEventAccess::withoutGlobalScope('tenant')
            ->where('client_id', auth('client')->id())
            ->where('event_id', $this->event->id)
            ->exists();
    }

    public function openFolder(?int $folderId): void
    {
        $this->currentFolderId = $folderId;
    }

    public function confirmDelete(int $documentId): void
    {
        $this->deleteDocumentId = $documentId;
        $this->showDeleteModal = true;
    }

    public function deleteDocument(): void
    {
        $document = Document::withoutGlobalScope('tenant')
            ->where('id', $this->deleteDocumentId)
            ->where('tenant_id', $this->event->tenant_id)
            ->first();

        if ($document) {
            if ($document->type !== 'link' && $document->path) {
                \Illuminate\Support\Facades\Storage::disk($document->disk)->delete($document->path);
            }
            if ($document->thumbnail_path) {
                \Illuminate\Support\Facades\Storage::disk($document->disk)->delete($document->thumbnail_path);
            }
            $document->delete();
        }

        $this->showDeleteModal = false;
        $this->toastSuccess('Deleted.');
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selectedDocumentIds)) {
            $this->toastError('Select at least one file first.');
            return;
        }
        $this->showBulkDeleteModal = true;
    }

    public function bulkDelete(): void
    {
        $documents = Document::withoutGlobalScope('tenant')
            ->whereIn('id', $this->selectedDocumentIds)
            ->where('tenant_id', $this->event->tenant_id)
            ->get();

        foreach ($documents as $document) {
            if ($document->type !== 'link' && $document->path) {
                \Illuminate\Support\Facades\Storage::disk($document->disk)->delete($document->path);
            }
            if ($document->thumbnail_path) {
                \Illuminate\Support\Facades\Storage::disk($document->disk)->delete($document->thumbnail_path);
            }
            $document->delete();
        }

        $count = $documents->count();
        $this->selectedDocumentIds = [];
        $this->showBulkDeleteModal = false;
        $this->toastSuccess("{$count} file(s) deleted.");
    }

    public function downloadSelected(): void
    {
        if (empty($this->selectedDocumentIds)) {
            $this->toastError('Select at least one file first.');
            return;
        }
        $this->redirect(route('media.download.bulk', ['ids' => implode(',', $this->selectedDocumentIds)]));
    }

    public function render()
    {
        $folders = DocumentFolder::withoutGlobalScope('tenant')
            ->where('documentable_type', DocumentableType::Event->value)
            ->where('documentable_id', $this->event->id)
            ->orderBy('name')
            ->get();

        $documents = Document::withoutGlobalScope('tenant')
            ->where('documentable_type', DocumentableType::Event->value)
            ->where('documentable_id', $this->event->id)
            ->where('type', '!=', 'logo')
            ->where('folder_id', $this->currentFolderId)
            ->orderByDesc('created_at')
            ->get();

        $currentFolder = $this->currentFolderId
            ? $folders->firstWhere('id', $this->currentFolderId)
            : null;

        $mediaItems = $documents->filter(fn($d) => $d->isImage() || $d->isVideo())
            ->values()
            ->map(fn($d) => [
                'url'         => \Illuminate\Support\Facades\Storage::disk($d->disk)->url($d->path),
                'kind'        => $d->isImage() ? 'image' : 'video',
                'name'        => $d->name,
                'downloadUrl' => route('media.download', $d->id),
            ]);

        return view('livewire.client.documents.media-library-client', [
            'folders'        => $this->currentFolderId ? collect() : $folders,
            'documents'      => $documents,
            'currentFolder'  => $currentFolder,
            'mediaItemsJson' => $mediaItems->toJson(),
        ]);
    }
}