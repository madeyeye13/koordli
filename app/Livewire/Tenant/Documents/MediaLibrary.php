<?php

namespace App\Livewire\Tenant\Documents;

use App\Enums\DocumentableType;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentFolder;
use App\Models\Tenant\Event;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class MediaLibrary extends Component
{
    use WithToast;

    public Event $event;
    public ?int $currentFolderId = null;

    public bool $showFolderForm = false;
    public string $newFolderName = '';

    public bool $showLinkForm = false;
    public string $newLinkName = '';
    public string $newLinkUrl = '';

    public bool $showDeleteModal = false;
    public ?int $deleteDocumentId = null;

    public bool $showDeleteFolderModal = false;
    public ?int $deleteFolderId = null;

    public bool $showShareLinkModal = false;
    public int $shareLinkExpiryDays = 30;

    public bool $selectMode = false;
    public array $selectedDocumentIds = [];

    public bool $showBulkDeleteModal = false;

    public function mount(string $slug): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'documents.view'),
            403
        );

        $this->event = Event::where('slug', $slug)->firstOrFail();
    }

    public function openFolder(?int $folderId): void
    {
        $this->currentFolderId = $folderId;
    }

    public function showCreateFolder(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'documents.upload')) {
            $this->toastError('You do not have permission to create folders.');
            return;
        }
        $this->newFolderName = '';
        $this->showFolderForm = true;
    }

    public function createFolder(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'documents.upload')) {
            $this->toastError('You do not have permission to create folders.');
            return;
        }

        $this->validate(['newFolderName' => 'required|string|min:1|max:150']);

        DocumentFolder::create([
            'tenant_id'         => auth()->user()->tenant_id,
            'documentable_type' => DocumentableType::Event->value,
            'documentable_id'   => $this->event->id,
            'name'              => $this->newFolderName,
            'created_by_type'   => \App\Models\Tenant\User::class,
            'created_by_id'     => auth()->id(),
        ]);

        $this->showFolderForm = false;
        $this->toastSuccess('Folder created.');
    }

    public function confirmDeleteFolder(int $folderId): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'documents.delete')) {
            $this->toastError('You do not have permission to delete folders.');
            return;
        }
        $this->deleteFolderId = $folderId;
        $this->showDeleteFolderModal = true;
    }

    public function deleteFolder(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'documents.delete')) {
            $this->toastError('You do not have permission to delete folders.');
            $this->showDeleteFolderModal = false;
            return;
        }

        $folder = DocumentFolder::find($this->deleteFolderId);

        if ($folder) {
            $hasFiles = Document::withoutGlobalScope('tenant')->where('folder_id', $folder->id)->exists();
            if ($hasFiles) {
                $this->toastError('This folder still has files in it. Move or delete them first.');
                $this->showDeleteFolderModal = false;
                return;
            }
            $folder->delete();
        }

        $this->showDeleteFolderModal = false;
        $this->toastSuccess('Folder deleted.');
    }

    public function showAddLink(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'documents.upload')) {
            $this->toastError('You do not have permission to add links.');
            return;
        }
        $this->newLinkName = '';
        $this->newLinkUrl = '';
        $this->showLinkForm = true;
    }

    public function addLink(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'documents.upload')) {
            $this->toastError('You do not have permission to add links.');
            return;
        }

        $this->validate([
            'newLinkName' => 'required|string|min:1|max:200',
            'newLinkUrl'  => 'required|url|max:2000',
        ]);

        Document::create([
            'tenant_id'         => auth()->user()->tenant_id,
            'documentable_type' => DocumentableType::Event->value,
            'documentable_id'   => $this->event->id,
            'folder_id'         => $this->currentFolderId,
            'name'              => $this->newLinkName,
            'type'              => 'link',
            'external_url'      => $this->newLinkUrl,
            'compression_status'=> 'not_applicable',
            'uploaded_by_type'  => \App\Models\Tenant\User::class,
            'uploaded_by_id'    => auth()->id(),
        ]);

        $this->showLinkForm = false;
        $this->toastSuccess('Link saved.');
    }

    public function confirmDelete(int $documentId): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'documents.delete')) {
            $this->toastError('You do not have permission to delete files.');
            return;
        }
        $this->deleteDocumentId = $documentId;
        $this->showDeleteModal = true;
    }

    public function deleteDocument(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'documents.delete')) {
            $this->toastError('You do not have permission to delete files.');
            $this->showDeleteModal = false;
            return;
        }

        $document = Document::find($this->deleteDocumentId);
        if ($document) {
            if ($document->type !== 'link' && $document->path) {
                \Illuminate\Support\Facades\Storage::disk($document->disk)->delete($document->path);
            }
            $document->delete();
        }

        $this->showDeleteModal = false;
        $this->toastSuccess('Deleted.');
    }

    public function toggleSelectMode(): void
    {
        $this->selectMode = !$this->selectMode;
        $this->selectedDocumentIds = [];
    }

    public function toggleDocumentSelected(int $documentId): void
    {
        if (in_array($documentId, $this->selectedDocumentIds)) {
            $this->selectedDocumentIds = array_values(array_diff($this->selectedDocumentIds, [$documentId]));
        } else {
            $this->selectedDocumentIds[] = $documentId;
        }
    }

    public function confirmBulkDelete(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'documents.delete')) {
            $this->toastError('You do not have permission to delete files.');
            return;
        }

        if (empty($this->selectedDocumentIds)) {
            $this->toastError('Select at least one file first.');
            return;
        }

        $this->showBulkDeleteModal = true;
    }

    public function downloadSelected(): void
    {
        if (empty($this->selectedDocumentIds)) {
            $this->toastError('Select at least one file first.');
            return;
        }

        $this->redirect(route('media.download.bulk', ['ids' => implode(',', $this->selectedDocumentIds)]));
    }

    public function bulkDelete(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'documents.delete')) {
            $this->toastError('You do not have permission to delete files.');
            $this->showBulkDeleteModal = false;
            return;
        }

        $documents = Document::withoutGlobalScope('tenant')
            ->whereIn('id', $this->selectedDocumentIds)
            ->where('tenant_id', auth()->user()->tenant_id)
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
        $this->selectMode = false;
        $this->showBulkDeleteModal = false;
        $this->toastSuccess("{$count} file(s) deleted.");
    }

    public function removeLogo(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'documents.delete')) {
            $this->toastError('You do not have permission to remove the event logo.');
            return;
        }

        $logo = Document::withoutGlobalScope('tenant')
            ->where('documentable_type', DocumentableType::Event->value)
            ->where('documentable_id', $this->event->id)
            ->where('type', 'logo')
            ->first();

        if ($logo) {
            \Illuminate\Support\Facades\Storage::disk($logo->disk)->delete($logo->path);
            $logo->delete();
            $this->toastSuccess('Event logo removed.');
        }
    }

    public function openShareLinkManager(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'documents.upload')) {
            $this->toastError('You do not have permission to manage upload links.');
            return;
        }
        $this->showShareLinkModal = true;
    }

    public function generateShareLink(): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'documents.upload')) {
            $this->toastError('You do not have permission to manage upload links.');
            return;
        }

        $this->validate(['shareLinkExpiryDays' => 'required|integer|in:30,60,90']);

        $existing = \App\Models\Tenant\DocumentShareLink::where('event_id', $this->event->id)
            ->where('is_active', true)
            ->first();

        if ($existing && $existing->isUsable()) {
            $this->toastError('An active link already exists. Disable it first to generate a new one.');
            return;
        }

        \App\Models\Tenant\DocumentShareLink::create([
            'tenant_id'  => auth()->user()->tenant_id,
            'event_id'   => $this->event->id,
            'expires_at' => now()->addDays($this->shareLinkExpiryDays),
            'is_active'  => true,
            'created_by' => auth()->id(),
        ]);

        $this->toastSuccess('Upload link generated.');
    }

    public function disableShareLink(int $linkId): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'documents.upload')) {
            $this->toastError('You do not have permission to manage upload links.');
            return;
        }

        \App\Models\Tenant\DocumentShareLink::where('id', $linkId)
            ->where('event_id', $this->event->id)
            ->update(['is_active' => false]);

        $this->toastSuccess('Link disabled.');
    }

    public function render()
    {
        $canUpload = app(PermissionService::class)->userCan(auth()->user(), 'documents.upload');
        $canDelete = app(PermissionService::class)->userCan(auth()->user(), 'documents.delete');

        $activeShareLink = \App\Models\Tenant\DocumentShareLink::where('event_id', $this->event->id)
            ->where('is_active', true)
            ->latest()
            ->first();

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

        $logo = Document::withoutGlobalScope('tenant')
            ->where('documentable_type', DocumentableType::Event->value)
            ->where('documentable_id', $this->event->id)
            ->where('type', 'logo')
            ->first();

        $currentFolder = $this->currentFolderId
            ? $folders->firstWhere('id', $this->currentFolderId)
            : null;

        // Only images/videos are lightbox-navigable — built in the SAME
        // filter/order as the file grid below, so indices line up exactly
        // between this JSON payload and the onclick handlers in the view.
        $mediaItems = $documents->filter(fn($d) => $d->isImage() || $d->isVideo())
            ->values()
            ->map(fn($d) => [
                'url'         => \Illuminate\Support\Facades\Storage::disk($d->disk)->url($d->path),
                'kind'        => $d->isImage() ? 'image' : 'video',
                'name'        => $d->name,
                'downloadUrl' => route('media.download', $d->id),
            ]);

        return view('livewire.tenant.documents.media-library', [
            'folders'         => $this->currentFolderId ? collect() : $folders,
            'documents'       => $documents,
            'logo'            => $logo,
            'currentFolder'   => $currentFolder,
            'canUpload'       => $canUpload,
            'canDelete'       => $canDelete,
            'activeShareLink' => $activeShareLink,
            'mediaItemsJson'  => $mediaItems->toJson(),
        ]);
    }
}