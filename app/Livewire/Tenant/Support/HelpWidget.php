<?php

namespace App\Livewire\Tenant\Support;

use Livewire\Component;

class HelpWidget extends Component
{
    use \App\Traits\WithToast;

    public bool $showChoiceModal = false;

    public function openChoices(): void
    {
        $this->showChoiceModal = true;
    }

    public function liveSupportComingSoon(): void
    {
        $this->showChoiceModal = false;
        $this->toastInfo('Live chat is launching soon! For now, please open a ticket and we\'ll respond quickly.');
    }

    public function render()
    {
        return view('livewire.tenant.support.help-widget');
    }
}