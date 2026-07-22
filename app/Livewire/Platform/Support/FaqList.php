<?php

namespace App\Livewire\Platform\Support;

use App\Models\Central\SupportFaq;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.platform')]
class FaqList extends Component
{
    use WithToast;

    public bool   $showForm      = false;
    public ?int   $editId        = null;
    public string $question      = '';
    public string $answer        = '';
    public string $keywords      = ''; // comma-separated in the UI
    public string $category      = '';
    public bool   $is_active     = true;

    public bool $showDeleteModal = false;
    public ?int $deleteId        = null;

    public function showCreate(): void
    {
        $this->reset(['editId', 'question', 'answer', 'keywords', 'category']);
        $this->is_active = true;
        $this->showForm   = true;
    }

    public function showEdit(int $id): void
    {
        $faq = SupportFaq::find($id);
        if (!$faq) return;

        $this->editId    = $id;
        $this->question  = $faq->question;
        $this->answer    = $faq->answer;
        $this->keywords  = implode(', ', $faq->keywords ?? []);
        $this->category  = $faq->category ?? '';
        $this->is_active = $faq->is_active;
        $this->showForm  = true;
    }

    public function save(): void
    {
        $this->validate([
            'question' => 'required|string|max:255',
            'answer'   => 'required|string',
        ]);

        $keywordsArray = array_filter(array_map('trim', explode(',', $this->keywords)));

        $data = [
            'question'  => $this->question,
            'answer'    => $this->answer,
            'keywords'  => array_values($keywordsArray),
            'category'  => $this->category ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editId) {
            SupportFaq::find($this->editId)?->update($data);
            $this->toastSuccess('FAQ updated.');
        } else {
            $data['sort_order'] = SupportFaq::max('sort_order') + 1;
            SupportFaq::create($data);
            $this->toastSuccess('FAQ created.');
        }

        $this->showForm = false;
    }

    public function toggleActive(int $id): void
    {
        $faq = SupportFaq::find($id);
        $faq?->update(['is_active' => !$faq->is_active]);
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId        = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        SupportFaq::find($this->deleteId)?->delete();
        $this->showDeleteModal = false;
        $this->deleteId        = null;
        $this->toastSuccess('FAQ deleted.');
    }

    public function render()
    {
        $faqs = SupportFaq::orderBy('sort_order')->get();
        return view('livewire.platform.support.faq-list', compact('faqs'));
    }
}