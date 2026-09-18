<?php

namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.landing')]
class FeedbackThanks extends Component
{
    public function render()
    {
        return view('livewire.public.feedback-thanks');
    }
}
