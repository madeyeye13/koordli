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
    public string $newRegistryLabel = '';
    public string $newRegistryUrl = '';

    // Dress code
    public array $dressColors = [];
    public string $newColorName = '';
    public string $newColorHex = '#7C3AED';
    public string $dressNote = '';

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

        $this->dressColors = $this->settings->dress_code_colors ?? [];
        $this->dressNote = $this->settings->dress_code_note ?? '';
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

        $valid = ['story_enabled', 'gallery_enabled', 'wishes_enabled', 'gifts_enabled', 'dress_code_enabled', 'countdown_enabled', 'wishes_require_approval', 'gate_venue_address'];
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
    public string $newAccountCurrency = 'NGN';

    public function addBankAccount(): void
    {
        if (!$this->requireManage()) return;
        if (!$this->newBankName || !$this->newAccountNumber) return;

        $this->bankAccounts[] = [
            'bank_name' => $this->newBankName,
            'account_name' => $this->newAccountName,
            'account_number' => $this->newAccountNumber,
            'currency' => $this->newAccountCurrency,
        ];
        $this->newBankName = $this->newAccountName = $this->newAccountNumber = '';
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
        if (!$this->newColorName) return;

        $this->dressColors[] = ['name' => $this->newColorName, 'hex' => $this->newColorHex];
        $this->newColorName = '';
        $this->settings->update(['dress_code_colors' => $this->dressColors]);
    }

    public function removeDressColor(int $index): void
    {
        if (!$this->requireManage()) return;
        unset($this->dressColors[$index]);
        $this->dressColors = array_values($this->dressColors);
        $this->settings->update(['dress_code_colors' => $this->dressColors]);
    }

    public function saveDressNote(): void
    {
        if (!$this->requireManage()) return;
        $this->settings->update(['dress_code_note' => $this->dressNote]);
        $this->toastSuccess('Saved.');
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