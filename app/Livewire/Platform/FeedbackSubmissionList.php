<?php

namespace App\Livewire\Platform;

use App\Models\Central\FeedbackSubmission;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.platform')]
class FeedbackSubmissionList extends Component
{
    #[Url]
    public string $search = '';

    public ?int $selectedId = null;

    public function show(int $id): void
    {
        $this->selectedId = $id;
    }

    public function close(): void
    {
        $this->selectedId = null;
    }

    public function deleteFeedback(int $id): void
    {
        FeedbackSubmission::findOrFail($id)->delete();
        $this->selectedId = null;
        $this->dispatch('feedback-deleted');
    }

    public function render()
    {
        $submissions = FeedbackSubmission::query()
            ->when($this->search, fn ($query) => $query->where('name', 'like', '%' . $this->search . '%'))
            ->latest()
            ->get();

        $selected = $this->selectedId
            ? FeedbackSubmission::find($this->selectedId)
            : null;

        return view('livewire.platform.feedback-submission-list', compact('submissions', 'selected'));
    }
}
