<?php

namespace App\Livewire\Client\Microsite;

use App\Models\Tenant\ClientEventAccess;
use App\Models\Tenant\Event;
use App\Models\Tenant\EventGalleryImage;
use App\Models\Tenant\EventGiftInfo;
use App\Models\Tenant\EventMicrositeSettings;
use App\Models\Tenant\EventStoryChapter;
use App\Models\Tenant\EventWish;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.client')]
class ClientMicrosite extends Component
{
    use WithToast, WithFileUploads;

    public Event $event;
    public ?EventMicrositeSettings $settings = null;
    public ?EventGiftInfo $giftInfo = null;

    public string $activeTab = 'story';

    public bool $showChapterForm = false;
    public ?int $editChapterId = null;
    public string $chapterTitle = '';
    public string $chapterContent = '';

    public $newImages = [];

    public string $giftNote = '';
    public array $bankAccounts = [];
    public array $registryLinks = [];
    public string $newBankName = '';
    public string $newAccountName = '';
    public string $newAccountNumber = '';
    public string $newRegistryLabel = '';
    public string $newRegistryUrl = '';

    public function mount(string $slug): void
    {
        $client = auth('client')->user();

        $this->event = Event::withoutGlobalScope('tenant')
            ->where('tenant_id', $client->tenant_id)
            ->where('slug', $slug)
            ->firstOrFail();

        $hasAccess = ClientEventAccess::withoutGlobalScope('tenant')
            ->where('client_id', $client->id)
            ->where('event_id', $this->event->id)
            ->exists() || $this->event->client_email === $client->email;

        abort_unless($hasAccess, 403);

        $this->settings = EventMicrositeSettings::withoutGlobalScope('tenant')
            ->where('event_id', $this->event->id)->first();

        $this->giftInfo = EventGiftInfo::withoutGlobalScope('tenant')
            ->where('event_id', $this->event->id)->first();

        if ($this->giftInfo) {
            $this->giftNote = $this->giftInfo->note ?? '';
            $this->bankAccounts = $this->giftInfo->bank_accounts ?? [];
            $this->registryLinks = $this->giftInfo->registry_links ?? [];
        }
    }

    #[Renderless]
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function showAddChapter(): void
    {
        $this->reset(['chapterTitle', 'chapterContent', 'editChapterId']);
        $this->showChapterForm = true;
    }

    public function editChapter(int $id): void
    {
        $chapter = EventStoryChapter::withoutGlobalScope('tenant')
            ->where('event_id', $this->event->id)->find($id);
        if (!$chapter) return;
        $this->editChapterId = $id;
        $this->chapterTitle = $chapter->title;
        $this->chapterContent = $chapter->content;
        $this->showChapterForm = true;
    }

    public function saveChapter(): void
    {
        $this->validate(['chapterTitle' => 'required|string|max:150', 'chapterContent' => 'required|string']);

        if ($this->editChapterId) {
            EventStoryChapter::withoutGlobalScope('tenant')
                ->where('event_id', $this->event->id)->find($this->editChapterId)
                ?->update(['title' => $this->chapterTitle, 'content' => $this->chapterContent, 'last_edited_by_type' => 'client']);
        } else {
            $sort = EventStoryChapter::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->max('sort_order') + 1;
            EventStoryChapter::create([
                'event_id' => $this->event->id,
                'tenant_id' => $this->event->tenant_id,
                'title' => $this->chapterTitle,
                'content' => $this->chapterContent,
                'sort_order' => $sort,
                'last_edited_by_type' => 'client',
            ]);
        }

        $this->showChapterForm = false;
        $this->toastSuccess('Chapter saved.');
    }

    public function deleteChapter(int $id): void
    {
        EventStoryChapter::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->find($id)?->delete();
        $this->toastSuccess('Chapter removed.');
    }

    public function uploadImages(): void
    {
        $this->validate(['newImages.*' => 'image|max:5120']);

        $sort = EventGalleryImage::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->max('sort_order') + 1;

        foreach ($this->newImages as $file) {
            $result = app(\App\Services\ImageOptimizationService::class)->store($file, 'microsite/gallery');
            EventGalleryImage::create([
                'event_id' => $this->event->id,
                'tenant_id' => $this->event->tenant_id,
                'image_path' => $result['path'],
                'image_size' => $result['size'],
                'uploaded_by_type' => 'client',
                'sort_order' => $sort++,
            ]);
        }

        $this->reset(['newImages']);
        $this->toastSuccess('Images uploaded.');
    }

    public function deleteImage(int $id): void
    {
        $img = EventGalleryImage::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->find($id);
        if ($img) {
            \Illuminate\Support\Facades\Storage::disk(config('blog.storage_disk'))->delete($img->image_path);
            $img->delete();
        }
        $this->toastSuccess('Image removed.');
    }

    public function approveWish(int $id): void
    {
        $wish = EventWish::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->find($id);
        if (!$wish) return;
        $wish->update(['status' => 'approved', 'approved_by_type' => 'client']);
        event(new \App\Events\WishApproved($wish));
        $this->toastSuccess('Wish approved.');
    }

    public function rejectWish(int $id): void
    {
        EventWish::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->find($id)
            ?->update(['status' => 'rejected', 'approved_by_type' => 'client']);
        $this->toastSuccess('Wish rejected.');
    }

    public function addBankAccount(): void
    {
        if (!$this->newBankName || !$this->newAccountNumber) return;
        $this->bankAccounts[] = ['bank_name' => $this->newBankName, 'account_name' => $this->newAccountName, 'account_number' => $this->newAccountNumber];
        $this->newBankName = $this->newAccountName = $this->newAccountNumber = '';
        $this->saveGiftInfo();
    }

    public function removeBankAccount(int $index): void
    {
        unset($this->bankAccounts[$index]);
        $this->bankAccounts = array_values($this->bankAccounts);
        $this->saveGiftInfo();
    }

    public function addRegistryLink(): void
    {
        if (!$this->newRegistryLabel || !$this->newRegistryUrl) return;
        $this->registryLinks[] = ['label' => $this->newRegistryLabel, 'url' => $this->newRegistryUrl];
        $this->newRegistryLabel = $this->newRegistryUrl = '';
        $this->saveGiftInfo();
    }

    public function removeRegistryLink(int $index): void
    {
        unset($this->registryLinks[$index]);
        $this->registryLinks = array_values($this->registryLinks);
        $this->saveGiftInfo();
    }

    public function saveGiftNote(): void
    {
        $this->saveGiftInfo();
        $this->toastSuccess('Saved.');
    }

    private function saveGiftInfo(): void
    {
        EventGiftInfo::updateOrCreate(
            ['event_id' => $this->event->id],
            ['tenant_id' => $this->event->tenant_id, 'note' => $this->giftNote ?: null, 'bank_accounts' => $this->bankAccounts, 'registry_links' => $this->registryLinks]
        );
    }

    public function render()
    {
        return view('livewire.client.microsite.client-microsite', [
            'chapters' => EventStoryChapter::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->orderBy('sort_order')->get(),
            'images'   => EventGalleryImage::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->orderBy('sort_order')->get(),
            'wishes'   => EventWish::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->orderByDesc('created_at')->get(),
        ]);
    }
}