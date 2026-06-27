<?php

namespace App\Livewire\Tenant\Rsvp;

use App\Models\Tenant\Event;
use App\Models\Tenant\RsvpForm;
use App\Models\Tenant\RsvpQuestion;
use App\Models\Tenant\RsvpResponse;
use App\Traits\WithToast;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.tenant')]
class RsvpManager extends Component
{
    use WithToast, WithFileUploads;

    public Event     $event;
    public ?RsvpForm $form = null;

    public string $activeTab = 'setup';

    // Setup
    public string  $title       = '';
    public string  $deadline    = '';
    public ?int    $guest_limit = null;
    public bool    $is_active   = true;

    // Branding
    public string $accent_color = '#7C3AED';
    public string $bg_color     = '#FAFAF9';
    public $cover_image         = null;
    public ?string $cover_image_url = null;

    // Question builder
    public bool   $showQuestionForm = false;
    public string $q_label          = '';
    public string $q_field_type     = 'text';
    public bool   $q_is_required    = false;
    public string $q_options_raw    = '';
    public ?int   $editQuestionId   = null;

    // Delete response
    public bool $showDeleteResponseModal = false;
    public ?int $deleteResponseId        = null;

    public function mount(string $slug): void
    {
        $this->event = Event::where('slug', $slug)
            ->where('rsvp_enabled', true)
            ->firstOrFail();

        $this->form = RsvpForm::with('questions')
            ->where('event_id', $this->event->id)
            ->first();

        if ($this->form) {
            $this->title       = $this->form->title;
            $this->deadline    = $this->form->deadline?->format('Y-m-d\TH:i') ?? '';
            $this->guest_limit = $this->form->guest_limit;
            $this->is_active   = $this->form->is_active;

            $branding = $this->form->branding ?? [];
            $this->accent_color    = $branding['accent_color'] ?? '#7C3AED';
            $this->bg_color        = $branding['bg_color'] ?? '#FAFAF9';
            $this->cover_image_url = $branding['cover_image'] ?? null;
        } else {
            $this->title = $this->event->name . ' — RSVP';
        }

        if ($this->form && $this->form->questions === null) {
            $this->form->setRelation('questions', collect());
        }
    }

    #[Renderless]
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function saveForm(): void
    {
        $this->validate([
            'title'       => 'required|string|min:2|max:200',
            'deadline'    => 'nullable|date',
            'guest_limit' => 'nullable|integer|min:1',
            'is_active'   => 'boolean',
        ]);

        $slug = Str::slug($this->event->name) . '-rsvp';

        if ($this->form) {
            $this->form->update([
                'title'       => $this->title,
                'deadline'    => $this->deadline ?: null,
                'guest_limit' => $this->guest_limit,
                'is_active'   => $this->is_active,
            ]);
        } else {
            $baseSlug = $slug;
            $count    = RsvpForm::withoutGlobalScope('tenant')->where('slug', 'like', $baseSlug . '%')->count();
            if ($count > 0) $slug = $baseSlug . '-' . ($count + 1);

            $this->form = RsvpForm::create([
                'uuid'        => Str::uuid(),
                'tenant_id'   => auth()->user()->tenant_id,
                'event_id'    => $this->event->id,
                'title'       => $this->title,
                'slug'        => $slug,
                'deadline'    => $this->deadline ?: null,
                'guest_limit' => $this->guest_limit,
                'is_active'   => $this->is_active,
                'created_by'  => auth()->id(),
            ]);
        }

        $this->form = RsvpForm::with('questions')->find($this->form->id);
        $this->toastSuccess('RSVP form saved.');
    }

    public function saveBranding(): void
    {
        if (!$this->form) {
            $this->toastError('Save the RSVP form settings first.');
            return;
        }

        $this->validate([
            'accent_color' => 'required|string|max:20',
            'bg_color'     => 'required|string|max:20',
            'cover_image'  => 'nullable|image|max:3072', // 3MB
        ]);

        $branding = $this->form->branding ?? [];
        $branding['accent_color'] = $this->accent_color;
        $branding['bg_color']     = $this->bg_color;

        if ($this->cover_image) {
            try {
                // Delete old image
                if (!empty($branding['cover_image_path'])) {
                    Storage::disk('public')->delete($branding['cover_image_path']);
                }

                // Ensure directory exists
                Storage::disk('public')->makeDirectory('rsvp-covers');

                $path = $this->cover_image->storeAs(
                    'rsvp-covers',
                    Str::uuid() . '.' . $this->cover_image->getClientOriginalExtension(),
                    'public'
                );

                if (!$path) {
                    $this->toastError('Image upload failed. Please try again.');
                    return;
                }

                $branding['cover_image_path'] = $path;
                $branding['cover_image']      = Storage::disk('public')->url($path);
                $this->cover_image_url        = $branding['cover_image'];
                $this->cover_image            = null;

            } catch (\Exception $e) {
                Log::error('RSVP cover image upload failed: ' . $e->getMessage());
                $this->toastError('Image upload failed: ' . $e->getMessage());
                return;
            }
        }

        $this->form->update(['branding' => $branding]);
        $this->toastSuccess('Branding saved.');
    }

    public function removeCoverImage(): void
    {
        if (!$this->form) return;
        $branding = $this->form->branding ?? [];

        if (!empty($branding['cover_image_path'])) {
            Storage::disk('public')->delete($branding['cover_image_path']);
        }

        unset($branding['cover_image'], $branding['cover_image_path']);
        $this->form->update(['branding' => $branding]);
        $this->cover_image_url = null;
        $this->toastSuccess('Cover image removed.');
    }

    public function showAddQuestion(): void
    {
        $this->reset(['q_label', 'q_field_type', 'q_is_required', 'q_options_raw', 'editQuestionId']);
        $this->q_field_type     = 'text';
        $this->showQuestionForm = true;
    }

    public function saveQuestion(): void
    {
        if (!$this->form) {
            $this->toastError('Save the RSVP form settings first.');
            return;
        }

        $this->validate([
            'q_label'      => 'required|string|min:2|max:200',
            'q_field_type' => 'required|in:text,textarea,email,phone,number,dropdown,checkbox,radio,yes_no,date',
        ]);

        $options = null;
        if (in_array($this->q_field_type, ['dropdown', 'radio', 'checkbox']) && $this->q_options_raw) {
            $options = array_values(array_filter(array_map('trim', explode(',', $this->q_options_raw))));
        }

        if ($this->editQuestionId) {
            RsvpQuestion::find($this->editQuestionId)?->update([
                'label'       => $this->q_label,
                'field_type'  => $this->q_field_type,
                'is_required' => $this->q_is_required,
                'options'     => $options,
            ]);
            $this->toastSuccess('Question updated.');
        } else {
            $sortOrder = RsvpQuestion::where('rsvp_form_id', $this->form->id)->max('sort_order') + 1;
            RsvpQuestion::create([
                'tenant_id'    => auth()->user()->tenant_id,
                'rsvp_form_id' => $this->form->id,
                'label'        => $this->q_label,
                'field_type'   => $this->q_field_type,
                'is_required'  => $this->q_is_required,
                'options'      => $options,
                'sort_order'   => $sortOrder,
            ]);
            $this->toastSuccess('Question added.');
        }

        $this->showQuestionForm = false;
        $this->reset(['q_label', 'q_field_type', 'q_is_required', 'q_options_raw', 'editQuestionId']);
        $this->form->load('questions');
    }

    public function editQuestion(int $id): void
    {
        $q = RsvpQuestion::find($id);
        if (!$q) return;
        $this->editQuestionId   = $id;
        $this->q_label          = $q->label;
        $this->q_field_type     = $q->field_type;
        $this->q_is_required    = $q->is_required;
        $this->q_options_raw    = $q->options ? implode(', ', $q->options) : '';
        $this->showQuestionForm = true;
    }

    public function deleteQuestion(int $id): void
    {
        RsvpQuestion::find($id)?->delete();
        $this->form->load('questions');
        $this->toastSuccess('Question removed.');
    }

    public function moveUp(int $id): void
    {
        $q = RsvpQuestion::find($id);
        if (!$q || $q->sort_order === 0) return;
        $prev = RsvpQuestion::where('rsvp_form_id', $q->rsvp_form_id)
            ->where('sort_order', '<', $q->sort_order)
            ->orderByDesc('sort_order')->first();
        if ($prev) {
            [$q->sort_order, $prev->sort_order] = [$prev->sort_order, $q->sort_order];
            $q->save(); $prev->save();
        }
        $this->form->load('questions');
    }

    public function moveDown(int $id): void
    {
        $q = RsvpQuestion::find($id);
        if (!$q) return;
        $next = RsvpQuestion::where('rsvp_form_id', $q->rsvp_form_id)
            ->where('sort_order', '>', $q->sort_order)
            ->orderBy('sort_order')->first();
        if ($next) {
            [$q->sort_order, $next->sort_order] = [$next->sort_order, $q->sort_order];
            $q->save(); $next->save();
        }
        $this->form->load('questions');
    }

    public function confirmDeleteResponse(int $id): void
    {
        $this->deleteResponseId        = $id;
        $this->showDeleteResponseModal = true;
    }

    public function deleteResponse(): void
    {
        RsvpResponse::find($this->deleteResponseId)?->delete();
        $this->showDeleteResponseModal = false;
        $this->deleteResponseId        = null;
        $this->toastSuccess('Response removed.');
    }

    public function checkInResponse(int $id): void
    {
        $response = RsvpResponse::find($id);
        if (!$response) return;
        $response->update([
            'checked_in_at' => $response->checked_in_at ? null : now(),
        ]);
        $this->toastSuccess($response->checked_in_at ? 'Checked in.' : 'Check-in removed.');
    }

    public function render()
    {
        $responses = $this->form
            ? RsvpResponse::where('rsvp_form_id', $this->form->id)
                ->with('answers.question')
                ->orderByDesc('created_at')
                ->get()
            : collect();

        $stats = [
            'total'      => $responses->count(),
            'confirmed'  => $responses->where('status', 'confirmed')->count(),
            'declined'   => $responses->where('status', 'declined')->count(),
            'pending'    => $responses->where('status', 'pending')->count(),
            'checked_in' => $responses->whereNotNull('checked_in_at')->count(),
            'attendees'  => $responses->where('status', 'confirmed')->sum(fn($r) => 1 + $r->plus_one_count),
        ];

        return view('livewire.tenant.rsvp.rsvp-manager', compact('responses', 'stats'));
    }
}