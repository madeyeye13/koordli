<?php

namespace App\Livewire\Client\Microsite;

use App\Models\Tenant\ClientEventAccess;
use App\Models\Tenant\Event;
use App\Models\Tenant\EventGalleryImage;
use App\Models\Tenant\EventGiftInfo;
use App\Models\Tenant\EventMicrositeSettings;
use App\Models\Tenant\EventStoryChapter;
use App\Models\Tenant\EventWish;
use App\Models\Tenant\RsvpForm;
use App\Models\Tenant\RsvpResponse;
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
    public array $dressColors = [];
    public string $dressNote = '';
    public string $receptionVenue = '';
    public string $receptionAddress = '';
    public string $receptionTime = '';
    public string $hotelsNote = '';
    public string $hotelsMapsUrl = '';
    public string $galleryPassword = '';
    public string $galleryPasswordWhatsapp = '';

    public bool $showChapterForm = false;
    public bool $showDeleteModal = false;
    public ?int $editChapterId = null;
    public ?int $deleteTargetId = null;
    public string $chapterTitle = '';
    public string $chapterContent = '';
    public string $deleteTargetType = '';

    public $newImages = [];

    public string $giftNote = '';
    public array $bankAccounts = [];
    public array $registryLinks = [];
    public string $newBankName = '';
    public string $newAccountName = '';
    public string $newAccountNumber = '';
    public string $newAccountSwiftCode = '';
    public string $newRegistryLabel = '';
    public string $newRegistryUrl = '';
    public string $newAccountCurrency = 'NGN';
    public array $newLinkCurrencies = [];

    public string $newGroupName = '';
    public string $selectedColorGroup = '';
    public ?int $editingDressGroupIndex = null;
    public string $editingDressGroupName = '';
    public string $newColorName = '';
    public string $newColorHex = '#7C3AED';

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

        $this->dressColors = $this->normalizeDressGroups($this->settings?->dress_code_colors ?? []);
        $this->selectedColorGroup = $this->dressColors[0]['name'] ?? '';
        $this->dressNote = $this->settings?->dress_code_note ?? '';
        $this->receptionVenue = $this->settings?->reception_venue ?? '';
        $this->receptionAddress = $this->settings?->reception_address ?? '';
        $this->receptionTime = $this->settings?->reception_time ? substr($this->settings->reception_time, 0, 5) : '';
        $this->hotelsNote = $this->settings?->hotels_note ?? '';
        $this->hotelsMapsUrl = $this->settings?->hotels_maps_url ?? '';
        $this->galleryPassword = $this->settings?->gallery_password ?? '';
        $this->galleryPasswordWhatsapp = $this->settings?->gallery_password_whatsapp ?? '';
    }

    private function normalizeDressGroups(array $palette): array
    {
        if ($palette === []) {
            return [];
        }

        if (isset($palette[0]['colors'])) {
            return array_values(array_map(fn ($group) => [
                'name' => $group['name'] ?? 'Colour Palette',
                'colors' => array_values($group['colors'] ?? []),
            ], $palette));
        }

        return [[
            'name' => 'Colour Palette',
            'colors' => array_values($palette),
        ]];
    }

    #[Renderless]
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function showAddChapter(): void
    {
        abort_unless($this->settings?->story_enabled, 403);
        $this->reset(['chapterTitle', 'chapterContent', 'editChapterId']);
        $this->showChapterForm = true;
    }

    public function closeChapterModal(): void
    {
        $this->showChapterForm = false;
        $this->reset(['chapterTitle', 'chapterContent', 'editChapterId']);
    }

    public function setAccountCurrency(string $currency): void
    {
        $this->newAccountCurrency = $currency;
    }

    public function editChapter(int $id): void
    {
        abort_unless($this->settings?->story_enabled, 403);
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
        abort_unless($this->settings?->story_enabled, 403);
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
        $this->reset(['chapterTitle', 'chapterContent', 'editChapterId']);
        $this->toastSuccess('Chapter saved.');
    }

    public function confirmDelete(string $type, int $id): void
    {
        $this->deleteTargetType = $type;
        $this->deleteTargetId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteTargetType = '';
        $this->deleteTargetId = null;
    }

    public function executeDelete(): void
    {
        if (!$this->deleteTargetType || $this->deleteTargetId === null) {
            return;
        }

        switch ($this->deleteTargetType) {
            case 'chapter':
                $this->deleteChapter($this->deleteTargetId);
                break;
            case 'image':
                $this->deleteImage($this->deleteTargetId);
                break;
            case 'wish':
                $this->deleteWish($this->deleteTargetId);
                break;
            case 'bank_account':
                $this->removeBankAccount($this->deleteTargetId);
                break;
            case 'registry_link':
                $this->removeRegistryLink($this->deleteTargetId);
                break;
            case 'response':
                $this->deleteResponse($this->deleteTargetId);
                break;
        }

        $this->cancelDelete();
    }

    public function deleteChapter(int $id): void
    {
        abort_unless($this->settings?->story_enabled, 403);
        EventStoryChapter::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->find($id)?->delete();
        $this->toastSuccess('Chapter removed.');
    }

    public function uploadImages(): void
    {
        abort_unless($this->settings?->gallery_enabled, 403);
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
        abort_unless($this->settings?->gallery_enabled, 403);
        $img = EventGalleryImage::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->find($id);
        if ($img) {
            \Illuminate\Support\Facades\Storage::disk(config('blog.storage_disk'))->delete($img->image_path);
            $img->delete();
        }
        $this->toastSuccess('Image removed.');
    }

    public function approveWish(int $id): void
    {
        abort_unless($this->settings?->wishes_enabled, 403);
        $wish = EventWish::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->find($id);
        if (!$wish) return;
        $wish->update(['status' => 'approved', 'approved_by_type' => 'client']);
        event(new \App\Events\WishApproved($wish));
        $this->toastSuccess('Wish approved.');
    }

    public function rejectWish(int $id): void
    {
        abort_unless($this->settings?->wishes_enabled, 403);
        EventWish::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->find($id)
            ?->update(['status' => 'rejected', 'approved_by_type' => 'client']);
        $this->toastSuccess('Wish rejected.');
    }

    public function deleteWish(int $id): void
    {
        abort_unless($this->settings?->wishes_enabled, 403);
        EventWish::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->find($id)?->delete();
        $this->toastSuccess('Wish deleted.');
    }

    public function addBankAccount(): void
    {
        if (!$this->newBankName || !$this->newAccountNumber) return;
        $this->bankAccounts[] = [
            'bank_name' => $this->newBankName,
            'account_name' => $this->newAccountName,
            'account_number' => $this->newAccountNumber,
            'swift_code' => $this->newAccountSwiftCode,
            'currency' => $this->newAccountCurrency,
        ];
        $this->newBankName = $this->newAccountName = $this->newAccountNumber = $this->newAccountSwiftCode = '';
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
        $this->registryLinks[] = [
            'label' => $this->newRegistryLabel,
            'url' => $this->newRegistryUrl,
            'currencies' => $this->newLinkCurrencies,
        ];
        $this->newRegistryLabel = $this->newRegistryUrl = '';
        $this->newLinkCurrencies = [];
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
        $this->toastSuccess('Gift details saved.');
    }

    public function addDressGroup(): void
    {
        abort_unless($this->settings?->dress_code_enabled, 403);
        $name = trim($this->newGroupName);
        if (!$name || collect($this->dressColors)->contains('name', $name)) return;

        $this->dressColors[] = ['name' => $name, 'colors' => []];
        $this->newGroupName = '';
        $this->selectedColorGroup = $name;
        $this->settings->update(['dress_code_colors' => $this->dressColors]);
    }

    public function addDressColor(): void
    {
        abort_unless($this->settings?->dress_code_enabled, 403);
        if (!$this->newColorName || !$this->selectedColorGroup) return;

        $groupIndex = collect($this->dressColors)->search(fn ($group) => $group['name'] === $this->selectedColorGroup);
        if ($groupIndex === false) return;

        $this->dressColors[$groupIndex]['colors'][] = ['name' => $this->newColorName, 'hex' => $this->newColorHex];
        $this->newColorName = '';
        $this->settings->update(['dress_code_colors' => $this->dressColors]);
    }

    public function startEditDressGroup(int $groupIndex): void
    {
        abort_unless($this->settings?->dress_code_enabled, 403);
        if (!isset($this->dressColors[$groupIndex])) return;

        $this->editingDressGroupIndex = $groupIndex;
        $this->editingDressGroupName = $this->dressColors[$groupIndex]['name'];
    }

    public function saveDressGroupName(): void
    {
        abort_unless($this->settings?->dress_code_enabled, 403);
        if ($this->editingDressGroupIndex === null) return;

        $name = trim($this->editingDressGroupName);
        if (!$name || collect($this->dressColors)->except($this->editingDressGroupIndex)->contains('name', $name)) return;

        $oldName = $this->dressColors[$this->editingDressGroupIndex]['name'];
        $this->dressColors[$this->editingDressGroupIndex]['name'] = $name;
        if ($this->selectedColorGroup === $oldName) {
            $this->selectedColorGroup = $name;
        }
        $this->settings->update(['dress_code_colors' => $this->dressColors]);
        $this->editingDressGroupIndex = null;
        $this->editingDressGroupName = '';
    }

    public function removeDressColor(int $groupIndex, int $colorIndex): void
    {
        abort_unless($this->settings?->dress_code_enabled, 403);
        unset($this->dressColors[$groupIndex]['colors'][$colorIndex]);
        $this->dressColors[$groupIndex]['colors'] = array_values($this->dressColors[$groupIndex]['colors']);
        $this->settings->update(['dress_code_colors' => $this->dressColors]);
    }

    public function removeDressGroup(int $groupIndex): void
    {
        abort_unless($this->settings?->dress_code_enabled, 403);
        unset($this->dressColors[$groupIndex]);
        $this->dressColors = array_values($this->dressColors);
        $this->selectedColorGroup = $this->dressColors[0]['name'] ?? '';
        $this->settings->update(['dress_code_colors' => $this->dressColors]);
    }

    public function saveDressNote(): void
    {
        abort_unless($this->settings?->dress_code_enabled, 403);
        $this->settings->update(['dress_code_note' => $this->dressNote]);
        $this->toastSuccess('Dress note saved.');
    }

    public function saveMicrositeSettings(): void
    {
        if (!$this->settings) return;

        $this->validate([
            'receptionVenue' => 'nullable|string|max:255',
            'receptionAddress' => 'nullable|string|max:1000',
            'receptionTime' => 'nullable|date_format:H:i',
            'hotelsMapsUrl' => 'nullable|url|max:500',
            'galleryPasswordWhatsapp' => 'nullable|url|max:500',
        ]);

        $this->settings->update([
            'reception_venue' => $this->receptionVenue ?: null,
            'reception_address' => $this->receptionAddress ?: null,
            'reception_time' => $this->receptionTime ?: null,
            'hotels_note' => $this->hotelsNote ?: null,
            'hotels_maps_url' => $this->hotelsMapsUrl ?: null,
            'gallery_password' => $this->galleryPassword ?: null,
            'gallery_password_whatsapp' => $this->galleryPasswordWhatsapp ?: null,
            'dress_code_note' => $this->dressNote ?: null,
            'dress_code_colors' => $this->dressColors,
        ]);
        $this->toastSuccess('Microsite settings saved.');
    }

    public function saveGalleryPassword(): void
    {
        if (!$this->settings) return;

        $this->settings->update([
            'gallery_password' => $this->galleryPassword ?: null,
            'gallery_password_whatsapp' => $this->galleryPasswordWhatsapp ?: null,
        ]);
        $this->toastSuccess('Gallery password updated.');
    }

    public function deleteResponse(int $id): void
    {
        RsvpResponse::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->find($id)?->delete();
        $this->toastSuccess('RSVP response removed.');
    }

    public function checkInResponse(int $id): void
    {
        $response = RsvpResponse::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->find($id);
        if (!$response) return;

        $response->update([
            'checked_in_at' => $response->checked_in_at ? null : now(),
        ]);
        $this->toastSuccess($response->checked_in_at ? 'Checked in.' : 'Check-in removed.');
    }

    private function saveGiftInfo(): void
    {
        EventGiftInfo::withoutGlobalScope('tenant')->updateOrCreate(
            ['event_id' => $this->event->id],
            [
                'tenant_id' => $this->event->tenant_id,
                'note' => $this->giftNote ?: null,
                'bank_accounts' => $this->bankAccounts,
                'registry_links' => $this->registryLinks,
            ]
        );
    }

    public function render()
    {
        $responses = ($this->event->rsvp_enabled && RsvpForm::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->exists())
            ? RsvpResponse::withoutGlobalScope('tenant')
            ->whereHas('rsvpForm', fn ($query) => $query->where('event_id', $this->event->id))
            ->with('companions')->latest()->get()
            : collect();
        return view('livewire.client.microsite.client-microsite', [
            'chapters' => EventStoryChapter::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->orderBy('sort_order')->get(),
            'images'   => EventGalleryImage::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->orderBy('sort_order')->get(),
            'wishes'   => EventWish::withoutGlobalScope('tenant')->where('event_id', $this->event->id)->orderByDesc('created_at')->get(),
            'responses' => $responses,
        ]);
    }
}