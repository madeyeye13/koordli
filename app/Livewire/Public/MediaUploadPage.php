<?php

namespace App\Livewire\Public;

use App\Models\Central\Tenant;
use App\Models\Tenant\DocumentShareLink;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.rsvp')]
class MediaUploadPage extends Component
{
    public DocumentShareLink $link;
    public string $companyName = 'the organizer';
    public bool $isUsable = false;

    public function mount(string $token): void
    {
        $this->link = DocumentShareLink::withoutGlobalScope('tenant')
            ->where('token', $token)
            ->with('event')
            ->firstOrFail();

        $tenant = Tenant::find($this->link->tenant_id);
        $this->companyName = $tenant->name ?? 'the organizer';
        $this->isUsable = $this->link->isUsable();
    }

    public function render()
    {
        return view('livewire.public.media-upload-page')
            ->layout('layouts.rsvp', ['title' => 'Upload Files — ' . ($this->link->event->name ?? 'Media Library')]);
    }
}