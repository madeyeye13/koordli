<?php

namespace App\Livewire\Platform;

use App\Models\Central\PlatformSetting;
use App\Traits\WithToast;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.platform')]
class SiteSettings extends Component
{
    use WithToast, WithFileUploads;

    public string $site_name    = '';
    public string $site_tagline = '';
    public string $terms_content = '';
    public string $privacy_content = '';
    public $favicon = null;
    public ?string $favicon_path = null;
    public $tenant_tour_video = null;
    public bool $tenant_tour_enabled = false;
    public ?string $tenant_tour_video_path = null;

    public function mount(): void
    {
        $this->site_name    = PlatformSetting::get('site_name', 'Koordli');
        $this->site_tagline = PlatformSetting::get('site_tagline', 'Event Operations Simplified');
        $this->terms_content = PlatformSetting::get('terms_content', PlatformSetting::defaultTerms());
        $this->privacy_content = PlatformSetting::get('privacy_content', PlatformSetting::defaultPrivacy());
        $this->favicon_path = PlatformSetting::get('site_favicon');
        $this->tenant_tour_video_path = PlatformSetting::get('tenant_quick_tour_video');
        $this->tenant_tour_enabled = (bool) PlatformSetting::get('tenant_quick_tour_enabled', false);
    }

    public function save(): void
    {
        $this->validate([
            'site_name'    => 'required|string|max:100',
            'site_tagline' => 'nullable|string|max:150',
            'terms_content' => 'required|string|max:50000',
            'privacy_content' => 'required|string|max:50000',
            'favicon'      => 'nullable|image|max:512',
            'tenant_tour_video' => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm', 'max:51200'],
            'tenant_tour_enabled' => 'boolean',
        ]);

        PlatformSetting::set('site_name', $this->site_name);
        PlatformSetting::set('site_tagline', $this->site_tagline);
        PlatformSetting::set('terms_content', $this->terms_content);
        PlatformSetting::set('privacy_content', $this->privacy_content);
        PlatformSetting::set('tenant_quick_tour_enabled', $this->tenant_tour_enabled);

        if ($this->favicon) {
            $path = $this->favicon->store('platform', 'public');
            PlatformSetting::set('site_favicon', $path);
            $this->favicon_path = $path;
            $this->favicon = null;
        }

        if ($this->tenant_tour_video) {
            $oldPath = PlatformSetting::get('tenant_quick_tour_video');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $path = $this->tenant_tour_video->store('platform/tenant-tour', 'public');
            PlatformSetting::set('tenant_quick_tour_video', $path);
            $this->tenant_tour_video_path = $path;
            $this->tenant_tour_video = null;
        }

        $this->toastSuccess('Site settings saved.');
    }

    public function removeTourVideo(): void
    {
        $oldPath = PlatformSetting::get('tenant_quick_tour_video');

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        PlatformSetting::set('tenant_quick_tour_video', null);
        $this->tenant_tour_video_path = null;
        $this->toastSuccess('Tenant tour video removed.');
    }

    public function render()
    {
        return view('livewire.platform.site-settings');
    }
}