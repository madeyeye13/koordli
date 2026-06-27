<?php

namespace App\Livewire\Public;

use App\Jobs\SendFormSubmissionConfirmationJob;
use App\Jobs\SendFormSubmissionNotificationJob;
use App\Models\Central\Tenant;
use App\Models\Tenant\Form;
use App\Models\Tenant\FormSubmission;
use App\Models\Tenant\FormSubmissionValue;
use App\Models\Tenant\User;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.rsvp')]
class BookingForm extends Component
{
    public Form $form;

    public array  $answers   = [];
    public bool   $submitted = false;
    public string $error     = '';
    public ?string $whatsappUrl = null;

    public function mount(string $slug): void
    {
        $this->form = Form::with(['fields' => fn($q) => $q->orderBy('sort_order'), 'redirect'])
            ->where('slug', $slug)
            ->where('type', 'booking')
            ->where('status', 'active')
            ->firstOrFail();

        foreach ($this->form->fields as $field) {
            $this->answers[$field->id] = '';
        }
    }

    public function submit(): void
    {
        $rules = [];
        foreach ($this->form->fields as $field) {
            $rules["answers.{$field->id}"] = $field->is_required ? 'required' : 'nullable';
            if ($field->field_type === 'email') {
                $rules["answers.{$field->id}"] .= '|email';
            }
        }

        $this->validate($rules);

        $submission = FormSubmission::create([
            'tenant_id'    => $this->form->tenant_id,
            'form_id'      => $this->form->id,
            'source'       => 'web',
            'status'       => 'new',
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
            'submitted_at' => now(),
        ]);

        $fields     = [];
        $guestName  = '';
        $guestEmail = '';

        foreach ($this->form->fields as $field) {
            $value = $this->answers[$field->id] ?? '';

            FormSubmissionValue::create([
                'submission_id' => $submission->id,
                'field_id'      => $field->id,
                'value'         => is_array($value) ? implode(', ', $value) : $value,
            ]);

            $fields[] = ['label' => $field->label, 'value' => $value];

            if ($field->field_type === 'email' && empty($guestEmail)) {
                $guestEmail = $value;
            }
            if (in_array(strtolower($field->label), ['name', 'full name', 'your name']) && empty($guestName)) {
                $guestName = $value;
            }
        }

        if (empty($guestName)) $guestName = 'Guest';

        $tenant = Tenant::find($this->form->tenant_id);
        $plannerUser = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->form->tenant_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'company_owner'))
            ->first();

        if ($plannerUser?->email) {
            SendFormSubmissionNotificationJob::dispatch(
                $plannerUser->email,
                $plannerUser->name,
                $this->form->name,
                'booking',
                $guestName,
                now()->format('D, d M Y g:i A'),
                $fields,
            );
        }

        if ($guestEmail) {
            SendFormSubmissionConfirmationJob::dispatch(
                $guestEmail,
                $guestName,
                $this->form->name,
                'booking',
                $tenant?->name ?? 'The organiser',
                $this->form->tenant_email ?? $plannerUser?->email ?? '',
                $this->form->tenant_phone,
            );
        }

        // WhatsApp redirect
        if ($this->form->redirect?->redirect_type === 'whatsapp') {
            $this->whatsappUrl = $this->form->redirect->whatsappUrl(['name' => $guestName]);
        }

        $this->submitted = true;
        $this->error     = '';
    }

    public function render()
    {
        return view('livewire.public.booking-form');
    }
}