<?php

namespace App\Livewire\Tenant;

use App\Models\Central\PlatformSetting;
use Livewire\Component;

class VideoTourModal extends Component
{
    public bool $isOpen = false;
    public bool $showPrompt = false;
    public bool $hasVideo = false;
    public bool $showButton = false;
    public ?string $videoUrl = null;

    public function mount(): void
    {
        $this->refreshState();
    }

    public function refreshState(): void
    {
        $this->videoUrl = PlatformSetting::get('tenant_quick_tour_video');
        $this->hasVideo = !empty($this->videoUrl) && (bool) PlatformSetting::get('tenant_quick_tour_enabled', false);
        $this->showButton = $this->hasVideo;

        $user = auth()->user();

        if (!$this->hasVideo) {
            $this->showPrompt = false;
            $this->isOpen = false;
            return;
        }

        if ($user && !$user->quick_tour_seen_at && !$this->isOpen) {
            $this->showPrompt = true;
            return;
        }

        $this->showPrompt = false;
    }

    public function open(): void
    {
        if (!$this->hasVideo) {
            return;
        }

        $this->showPrompt = false;
        $this->isOpen = true;
    }

    public function skipPrompt(): void
    {
        $this->markSeen();
        $this->showPrompt = false;
        $this->isOpen = false;
    }

    public function close(): void
    {
        $this->markSeen();
        $this->isOpen = false;
        $this->showPrompt = false;
    }

    public function skip(): void
    {
        $this->markSeen();
        $this->isOpen = false;
        $this->showPrompt = false;
    }

    public function markSeen(): void
    {
        $user = auth()->user();

        if (!$user || $user->quick_tour_seen_at) {
            return;
        }

        $user->update([
            'quick_tour_seen_at' => now(),
        ]);
    }

    public function render()
    {
        $this->refreshState();

        return view('livewire.tenant.video-tour-modal', [
            'hasVideo' => $this->hasVideo,
            'showButton' => $this->showButton,
            'showPrompt' => $this->showPrompt,
            'videoUrl' => $this->videoUrl,
        ]);
    }
}
