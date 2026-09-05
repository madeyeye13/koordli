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

    public function mount(): void
    {
        $this->site_name    = PlatformSetting::get('site_name', 'Koordli');
        $this->site_tagline = PlatformSetting::get('site_tagline', 'Event Operations Simplified');
        $this->terms_content = PlatformSetting::get('terms_content', PlatformSetting::defaultTerms());
        $this->privacy_content = PlatformSetting::get('privacy_content', PlatformSetting::defaultPrivacy());
        $this->favicon_path = PlatformSetting::get('site_favicon');
    }

    public function save(): void
    {
        $this->validate([
            'site_name'    => 'required|string|max:100',
            'site_tagline' => 'nullable|string|max:150',
            'terms_content' => 'required|string|max:50000',
            'privacy_content' => 'required|string|max:50000',
            'favicon'      => 'nullable|image|max:512',
        ]);

        PlatformSetting::set('site_name', $this->site_name);
        PlatformSetting::set('site_tagline', $this->site_tagline);
        PlatformSetting::set('terms_content', $this->terms_content);
        PlatformSetting::set('privacy_content', $this->privacy_content);

        if ($this->favicon) {
            $path = $this->favicon->store('platform', 'public');
            PlatformSetting::set('site_favicon', $path);
            $this->favicon_path = $path;
            $this->favicon = null;
        }

        $this->toastSuccess('Site settings saved.');
    }

    public function render()
    {
        return view('livewire.platform.site-settings');
    }
}