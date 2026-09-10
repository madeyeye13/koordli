<?php

namespace App\Livewire\Tenant\Microsite;

use App\Models\Tenant\Event;
use App\Models\Tenant\EventGalleryImage;
use App\Models\Tenant\EventGiftInfo;
use App\Models\Tenant\EventMicrositeSettings;
use App\Models\Tenant\EventStoryChapter;
use App\Models\Tenant\EventWish;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.tenant')]
class EventMicrosite extends Component
{
    use WithToast, WithFileUploads;

    public Event $event;
    public EventMicrositeSettings $settings;
    public ?EventGiftInfo $giftInfo = null;

    public string $activeTab = 'settings';

    // Story
    public bool $showChapterForm = false;
    public ?int $editChapterId = null;
    public string $chapterTitle = '';
    public string $chapterContent = '';

    // Gallery
    public $newImages = [];
    public string $newCaption = '';

    // Gifts
    public string $giftNote = '';
    public array $bankAccounts = [];
    public array $registryLinks = [];
    public string $newBankName = '';
    public string $newAccountName = '';
    public string $newAccountNumber = '';
    public string $newAccountSwiftCode = '';
    public string $newRegistryLabel = '';
    public string $newRegistryUrl = '';

    // Dress code
    public array $dressColors = [];
    public string $newGroupName = '';
    public string $selectedColorGroup = '';
    public ?int $editingDressGroupIndex = null;
    public string $editingDressGroupName = '';
    public string $newColorName = '';
    public string $newColorHex = '#7C3AED';
    public string $dressNote = '';

    // Gallery password + Hotels
    public string $galleryPassword = '';
    public string $galleryPasswordWhatsapp = '';
    public string $hotelsNote = '';
    public string $hotelsMapsUrl = '';
    public string $receptionVenue = '';
    public string $receptionAddress = '';
    public string $receptionTime = '';

    public string $newAccountCurrency = 'NGN';

    private function requireManage(): bool
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'microsite.manage')) {
            $this->toastError('You do not have permission to manage this.');
            return false;
        }
        return true;
    }

    public function mount(string $slug): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'microsite.view'),
            403
        );

        $this->event = Event::where('slug', $slug)->firstOrFail();

        $this->settings = EventMicrositeSettings::firstOrCreate(
            ['event_id' => $this->event->id],
            ['tenant_id' => auth()->user()->tenant_id]
        );

        $this->giftInfo = EventGiftInfo::where('event_id', $this->event->id)->first();
        if ($this->giftInfo) {
            $this->giftNote = $this->giftInfo->note ?? '';
            $this->bankAccounts = $this->giftInfo->bank_accounts ?? [];
            $this->registryLinks = $this->giftInfo->registry_links ?? [];
        }

        $this->dressColors = $this->normalizeDressGroups($this->settings->dress_code_colors ?? []);
        $this->selectedColorGroup = $this->dressColors[0]['name'] ?? '';
        $this->dressNote = $this->settings->dress_code_note ?? '';
        $this->galleryPassword = $this->settings->gallery_password ?? '';
        $this->galleryPasswordWhatsapp = $this->settings->gallery_password_whatsapp ?? '';
        $this->hotelsNote = $this->settings->hotels_note ?? '';
        $this->hotelsMapsUrl = $this->settings->hotels_maps_url ?? '';
        $this->receptionVenue = $this->settings->reception_venue ?? '';
        $this->receptionAddress = $this->settings->reception_address ?? '';
        $this->receptionTime = $this->settings->reception_time ? substr($this->settings->reception_time, 0, 5) : '';
    }

    private function normalizeDressGroups(array $palette): array
    {
        if ($palette === []) return [];

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

    // ── Settings toggles ─────────────────────────────────────
    /**
     * Idempotent by design: takes the EXACT target value Alpine already
     * computed locally, rather than negating whatever's in the database.
     * This is immune to duplicate/rapid-fire calls — firing the same
     * request twice always lands on the same value, never double-flips.
     */
    public function toggleSection(string $field, bool $value): void
    {
        if (!$this->requireManage()) return;

        $valid = ['story_enabled', 'gallery_enabled', 'wishes_enabled', 'gifts_enabled', 'dress_code_enabled', 'countdown_enabled', 'wishes_require_approval', 'gate_venue_address', 'separate_venues_enabled', 'gallery_password_protected', 'hotels_enabled'];
        if (!in_array($field, $valid)) return;

        $this->settings->update([$field => $value]);
    }

    // ── Story ────────────────────────────────────────────────
    public function showAddChapter(): void
    {
        if (!$this->requireManage()) return;
        $this->reset(['chapterTitle', 'chapterContent', 'editChapterId']);
        $this->showChapterForm = true;
    }

    public function editChapter(int $id): void
    {
        if (!$this->requireManage()) return;
        $chapter = EventStoryChapter::find($id);
        if (!$chapter) return;
        $this->editChapterId = $id;
        $this->chapterTitle = $chapter->title;
        $this->chapterContent = $chapter->content;
        $this->showChapterForm = true;
    }

    public function saveChapter(): void
    {
        if (!$this->requireManage()) return;

        $this->validate([
            'chapterTitle' => 'required|string|max:150',
            'chapterContent' => 'required|string',
        ]);

        if ($this->editChapterId) {
            EventStoryChapter::find($this->editChapterId)?->update([
                'title' => $this->chapterTitle,
                'content' => $this->chapterContent,
                'last_edited_by_type' => 'tenant',
            ]);
        } else {
            $sort = EventStoryChapter::where('event_id', $this->event->id)->max('sort_order') + 1;
            EventStoryChapter::create([
                'event_id' => $this->event->id,
                'tenant_id' => auth()->user()->tenant_id,
                'title' => $this->chapterTitle,
                'content' => $this->chapterContent,
                'sort_order' => $sort,
                'last_edited_by_type' => 'tenant',
            ]);
        }

        $this->showChapterForm = false;
        $this->reset(['chapterTitle', 'chapterContent', 'editChapterId']);
        $this->toastSuccess('Chapter saved.');
    }

    public function deleteChapter(int $id): void
    {
        if (!$this->requireManage()) return;
        EventStoryChapter::find($id)?->delete();
        $this->toastSuccess('Chapter removed.');
    }

    // ── Gallery ──────────────────────────────────────────────
    public function uploadImages(): void
    {
        if (!$this->requireManage()) return;

        $this->validate(['newImages.*' => 'image|max:5120']);

        $disk = config('blog.storage_disk');
        $sort = EventGalleryImage::where('event_id', $this->event->id)->max('sort_order') + 1;

        foreach ($this->newImages as $file) {
            $result = app(\App\Services\ImageOptimizationService::class)->store($file, 'microsite/gallery');
            EventGalleryImage::create([
                'event_id' => $this->event->id,
                'tenant_id' => auth()->user()->tenant_id,
                'image_path' => $result['path'],
                'image_size' => $result['size'],
                'caption' => $this->newCaption ?: null,
                'uploaded_by_type' => 'tenant',
                'sort_order' => $sort++,
            ]);
        }

        $this->reset(['newImages', 'newCaption']);
        $this->toastSuccess('Images uploaded.');
    }

    public function deleteImage(int $id): void
    {
        if (!$this->requireManage()) return;
        $img = EventGalleryImage::find($id);
        if ($img) {
            \Illuminate\Support\Facades\Storage::disk(config('blog.storage_disk'))->delete($img->image_path);
            $img->delete();
        }
        $this->toastSuccess('Image removed.');
    }

    // ── Wishes ───────────────────────────────────────────────
    public function approveWish(int $id): void
    {
        if (!$this->requireManage()) return;
        $wish = EventWish::find($id);
        if (!$wish) return;
        $wish->update(['status' => 'approved', 'approved_by_type' => 'tenant']);
        event(new \App\Events\WishApproved($wish));
        $this->toastSuccess('Wish approved.');
    }

    public function rejectWish(int $id): void
    {
        if (!$this->requireManage()) return;
        EventWish::find($id)?->update(['status' => 'rejected', 'approved_by_type' => 'tenant']);
        $this->toastSuccess('Wish rejected.');
    }

    public function deleteWish(int $id): void
    {
        if (!$this->requireManage()) return;
        EventWish::find($id)?->delete();
        $this->toastSuccess('Wish deleted.');
    }

    // ── Gifts ────────────────────────────────────────────────
    public function addBankAccount(): void
    {
        if (!$this->requireManage()) return;
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
        if (!$this->requireManage()) return;
        unset($this->bankAccounts[$index]);
        $this->bankAccounts = array_values($this->bankAccounts);
        $this->saveGiftInfo();
    }

    public array $newLinkCurrencies = [];

    public function toggleLinkCurrency(string $currency): void
    {
        $this->newLinkCurrencies = in_array($currency, $this->newLinkCurrencies)
            ? array_values(array_diff($this->newLinkCurrencies, [$currency]))
            : [...$this->newLinkCurrencies, $currency];
    }

    public function addRegistryLink(): void
    {
        if (!$this->requireManage()) return;
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
        if (!$this->requireManage()) return;
        unset($this->registryLinks[$index]);
        $this->registryLinks = array_values($this->registryLinks);
        $this->saveGiftInfo();
    }

    public function saveGiftNote(): void
    {
        if (!$this->requireManage()) return;
        $this->saveGiftInfo();
        $this->toastSuccess('Gift note saved.');
    }

    private function saveGiftInfo(): void
    {
        EventGiftInfo::updateOrCreate(
            ['event_id' => $this->event->id],
            [
                'tenant_id' => auth()->user()->tenant_id,
                'note' => $this->giftNote ?: null,
                'bank_accounts' => $this->bankAccounts,
                'registry_links' => $this->registryLinks,
            ]
        );
        $this->giftInfo = EventGiftInfo::where('event_id', $this->event->id)->first();
    }

    // ── Dress code ───────────────────────────────────────────
    public function addDressColor(): void
    {
        if (!$this->requireManage()) return;
        if (!$this->newColorName || !$this->selectedColorGroup) return;

        $groupIndex = collect($this->dressColors)->search(fn ($group) => $group['name'] === $this->selectedColorGroup);
        if ($groupIndex === false) return;

        $this->dressColors[$groupIndex]['colors'][] = ['name' => $this->newColorName, 'hex' => $this->newColorHex];
        $this->newColorName = '';
        $this->settings->update(['dress_code_colors' => $this->dressColors]);
    }

    public function addDressGroup(): void
    {
        if (!$this->requireManage()) return;
        $name = trim($this->newGroupName);
        if (!$name || collect($this->dressColors)->contains('name', $name)) return;

        $this->dressColors[] = ['name' => $name, 'colors' => []];
        $this->newGroupName = '';
        $this->selectedColorGroup = $name;
        $this->settings->update(['dress_code_colors' => $this->dressColors]);
    }

    public function startEditDressGroup(int $groupIndex): void
    {
        if (!$this->requireManage() || !isset($this->dressColors[$groupIndex])) return;

        $this->editingDressGroupIndex = $groupIndex;
        $this->editingDressGroupName = $this->dressColors[$groupIndex]['name'];
    }

    public function saveDressGroupName(): void
    {
        if (!$this->requireManage() || $this->editingDressGroupIndex === null) return;

        $name = trim($this->editingDressGroupName);
        if (!$name || collect($this->dressColors)->except($this->editingDressGroupIndex)->contains('name', $name)) return;

        $oldName = $this->dressColors[$this->editingDressGroupIndex]['name'];
        $this->dressColors[$this->editingDressGroupIndex]['name'] = $name;
        if ($this->selectedColorGroup === $oldName) $this->selectedColorGroup = $name;
        $this->settings->update(['dress_code_colors' => $this->dressColors]);
        $this->editingDressGroupIndex = null;
        $this->editingDressGroupName = '';
    }

    public function removeDressColor(int $groupIndex, int $colorIndex): void
    {
        if (!$this->requireManage()) return;
        unset($this->dressColors[$groupIndex]['colors'][$colorIndex]);
        $this->dressColors[$groupIndex]['colors'] = array_values($this->dressColors[$groupIndex]['colors']);
        $this->settings->update(['dress_code_colors' => $this->dressColors]);
    }

    public function removeDressGroup(int $groupIndex): void
    {
        if (!$this->requireManage()) return;
        unset($this->dressColors[$groupIndex]);
        $this->dressColors = array_values($this->dressColors);
        $this->selectedColorGroup = $this->dressColors[0]['name'] ?? '';
        $this->settings->update(['dress_code_colors' => $this->dressColors]);
    }

    public function saveDressNote(): void
    {
        if (!$this->requireManage()) return;
        $this->settings->update(['dress_code_note' => $this->dressNote]);
        $this->toastSuccess('Saved.');
    }

    public function saveGalleryPassword(): void
    {
        if (!$this->requireManage()) return;

        $this->settings->update([
            'gallery_password' => $this->galleryPassword ?: null,
            'gallery_password_whatsapp' => $this->galleryPasswordWhatsapp ?: null,
        ]);
        $this->toastSuccess('Gallery password settings saved.');
    }

    public function saveHotelsInfo(): void
    {
        if (!$this->requireManage()) return;

        $this->validate([
            'hotelsMapsUrl' => 'nullable|url|max:500',
        ]);

        $this->settings->update([
            'hotels_note' => $this->hotelsNote ?: null,
            'hotels_maps_url' => $this->hotelsMapsUrl ?: null,
        ]);
        $this->toastSuccess('Hotels info saved.');
    }

    public function saveReceptionVenue(): void
    {
        if (!$this->requireManage()) return;

        $this->validate([
            'receptionVenue' => 'nullable|string|max:255',
            'receptionAddress' => 'nullable|string|max:1000',
            'receptionTime' => 'nullable|date_format:H:i',
        ]);

        $this->settings->update([
            'reception_venue' => $this->receptionVenue ?: null,
            'reception_address' => $this->receptionAddress ?: null,
            'reception_time' => $this->receptionTime ?: null,
        ]);
        $this->toastSuccess('Reception venue saved.');
    }

    public function render()
    {
        return view('livewire.tenant.microsite.event-microsite', [
            'chapters' => EventStoryChapter::where('event_id', $this->event->id)->orderBy('sort_order')->get(),
            'images'   => EventGalleryImage::where('event_id', $this->event->id)->orderBy('sort_order')->get(),
            'wishes'   => EventWish::where('event_id', $this->event->id)->orderByDesc('created_at')->get(),
        ]);
    }
}