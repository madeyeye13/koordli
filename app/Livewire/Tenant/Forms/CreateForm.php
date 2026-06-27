<?php

namespace App\Livewire\Tenant\Forms;

use App\Models\Tenant\ConsultationAvailability;
use App\Models\Tenant\Form;
use App\Models\Tenant\FormField;
use App\Models\Tenant\FormRedirect;
use App\Traits\WithToast;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.tenant')]
class CreateForm extends Component
{
    use WithToast, WithFileUploads;

    public ?Form $form = null;
    public ?int  $formId = null;

    // Active tab
    public string $activeTab = 'details';

    // Form details
    public string  $name              = '';
    public string  $type              = 'booking';
    public string  $status            = 'active';
    public string  $description       = '';
    public string  $tenant_email      = '';
    public string  $tenant_phone      = '';
    public string  $tenant_address    = '';
    public string  $consultation_type = 'both';
    public string  $location          = '';
    public int     $duration_minutes  = 60;
    public bool    $whatsapp_enabled  = false;
    public         $hero_image        = null;
    public ?string $hero_image_url    = null;

    // Redirect settings
    public string  $redirect_type      = 'none';
    public string  $redirect_url       = '';
    public string  $whatsapp_number    = '';
    public string  $whatsapp_message   = '';

    // Field builder
    public bool   $showFieldForm   = false;
    public ?int   $editFieldId     = null;
    public string $f_label         = '';
    public string $f_type          = 'text';
    public string $f_placeholder   = '';
    public bool   $f_required      = false;
    public string $f_options_raw   = '';

    // Availability (consultation)
    public array $availability = [
        0 => ['active' => false, 'start' => '09:00', 'end' => '17:00'],
        1 => ['active' => true,  'start' => '09:00', 'end' => '17:00'],
        2 => ['active' => true,  'start' => '09:00', 'end' => '17:00'],
        3 => ['active' => true,  'start' => '09:00', 'end' => '17:00'],
        4 => ['active' => true,  'start' => '09:00', 'end' => '17:00'],
        5 => ['active' => true,  'start' => '09:00', 'end' => '17:00'],
        6 => ['active' => false, 'start' => '09:00', 'end' => '17:00'],
    ];

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->form    = Form::with(['fields', 'redirect', 'availabilities'])->findOrFail($id);
            $this->formId  = $id;

            $this->name             = $this->form->name;
            $this->type             = $this->form->type;
            $this->status           = $this->form->status;
            $this->description      = $this->form->description ?? '';
            $this->tenant_email     = $this->form->tenant_email ?? '';
            $this->tenant_phone     = $this->form->tenant_phone ?? '';
            $this->tenant_address   = $this->form->tenant_address ?? '';
            $this->consultation_type= $this->form->consultation_type ?? 'both';
            $this->location         = $this->form->location ?? '';
            $this->duration_minutes = $this->form->duration_minutes ?? 60;
            $this->whatsapp_enabled = $this->form->whatsapp_enabled;
            $this->hero_image_url   = $this->form->hero_image ?? null;

            if ($this->form->redirect) {
                $this->redirect_type    = $this->form->redirect->redirect_type ?? 'none';
                $this->redirect_url     = $this->form->redirect->redirect_url ?? '';
                $this->whatsapp_number  = $this->form->redirect->whatsapp_number ?? '';
                $this->whatsapp_message = $this->form->redirect->whatsapp_message ?? '';
            }

            // Load availability
            foreach ($this->form->availabilities as $avail) {
                $this->availability[$avail->day_of_week] = [
                    'active' => $avail->is_active,
                    'start'  => $avail->start_time,
                    'end'    => $avail->end_time,
                ];
            }
        } else {
            // Pre-fill tenant contact from tenant settings
            $tenant = auth()->user()->tenant;
            $this->tenant_email = auth()->user()->email ?? '';
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function saveDetails(): void
    {
        $this->validate([
            'name'              => 'required|string|min:2|max:200',
            'type'              => 'required|in:booking,consultation',
            'status'            => 'required|in:active,inactive',
            'description'       => 'nullable|string|max:500',
            'tenant_email'      => 'nullable|email|max:150',
            'tenant_phone'      => 'nullable|string|max:30',
            'tenant_address'    => 'nullable|string|max:300',
            'duration_minutes'  => 'nullable|integer|min:15|max:480',
            'hero_image'        => 'nullable|image|max:3072',
            'whatsapp_enabled'  => 'boolean',
        ]);

        $data = [
            'name'              => $this->name,
            'type'              => $this->type,
            'status'            => $this->status,
            'description'       => $this->description ?: null,
            'tenant_email'      => $this->tenant_email ?: null,
            'tenant_phone'      => $this->tenant_phone ?: null,
            'tenant_address'    => $this->tenant_address ?: null,
            'consultation_type' => $this->consultation_type,
            'location'          => $this->location ?: null,
            'duration_minutes'  => $this->duration_minutes,
            'whatsapp_enabled'  => $this->whatsapp_enabled,
        ];

        if ($this->hero_image) {
            Storage::disk('public')->makeDirectory('form-heroes');

            if ($this->form && $this->form->hero_image_path) {
                Storage::disk('public')->delete($this->form->hero_image_path);
            }

            $path = $this->hero_image->storeAs(
                'form-heroes',
                Str::uuid() . '.' . $this->hero_image->getClientOriginalExtension(),
                'public'
            );

            $data['hero_image_path'] = $path;
            $data['hero_image']      = Storage::disk('public')->url($path);
            $this->hero_image_url    = $data['hero_image'];
            $this->hero_image        = null;
        }

        if ($this->form) {
            $this->form->update($data);
            $this->form->load(['fields', 'redirect', 'availabilities']);
        } else {
            $slug = Str::slug($this->name);
            $count = Form::withoutGlobalScope('tenant')->where('slug', 'like', $slug . '%')->count();
            if ($count > 0) $slug .= '-' . ($count + 1);

            $this->form   = Form::create(array_merge($data, [
                'uuid'      => Str::uuid(),
                'tenant_id' => auth()->user()->tenant_id,
                'slug'      => $slug,
            ]));
            $this->formId = $this->form->id;
            $this->form->load(['fields', 'redirect', 'availabilities']);
        }

        $this->toastSuccess('Form details saved.');
    }

    public function saveRedirect(): void
    {
        if (!$this->form) {
            $this->toastError('Save form details first.');
            return;
        }

        $this->validate([
            'redirect_type'     => 'required|in:none,url,whatsapp',
            'redirect_url'      => 'nullable|url|max:500',
            'whatsapp_number'   => 'nullable|string|max:20',
            'whatsapp_message'  => 'nullable|string|max:500',
        ]);

        FormRedirect::updateOrCreate(
            ['form_id' => $this->form->id],
            [
                'tenant_id'        => auth()->user()->tenant_id,
                'redirect_type'    => $this->redirect_type,
                'redirect_url'     => $this->redirect_url ?: null,
                'whatsapp_number'  => $this->whatsapp_number ?: null,
                'whatsapp_message' => $this->whatsapp_message ?: null,
            ]
        );

        $this->toastSuccess('Redirect settings saved.');
    }

    public function saveAvailability(): void
    {
        if (!$this->form) {
            $this->toastError('Save form details first.');
            return;
        }

        ConsultationAvailability::where('form_id', $this->form->id)->delete();

        foreach ($this->availability as $day => $config) {
            if ($config['active']) {
                ConsultationAvailability::create([
                    'tenant_id'   => auth()->user()->tenant_id,
                    'form_id'     => $this->form->id,
                    'day_of_week' => $day,
                    'start_time'  => $config['start'],
                    'end_time'    => $config['end'],
                    'is_active'   => true,
                ]);
            }
        }

        $this->toastSuccess('Availability saved.');
    }

    public function showAddField(): void
    {
        if (!$this->form) {
            $this->toastError('Save form details first.');
            return;
        }
        $this->reset(['f_label', 'f_type', 'f_placeholder', 'f_required', 'f_options_raw', 'editFieldId']);
        $this->f_type        = 'text';
        $this->showFieldForm = true;
    }

    public function saveField(): void
    {
        $this->validate([
            'f_label' => 'required|string|min:2|max:200',
            'f_type'  => 'required|in:text,textarea,email,phone,number,dropdown,radio,checkbox,date',
        ]);

        $options = null;
        if (in_array($this->f_type, ['dropdown', 'radio', 'checkbox']) && $this->f_options_raw) {
            $options = array_values(array_filter(array_map('trim', explode(',', $this->f_options_raw))));
        }

        if ($this->editFieldId) {
            FormField::find($this->editFieldId)?->update([
                'label'       => $this->f_label,
                'field_type'  => $this->f_type,
                'placeholder' => $this->f_placeholder ?: null,
                'is_required' => $this->f_required,
                'options'     => $options,
            ]);
            $this->toastSuccess('Field updated.');
        } else {
            $sortOrder = FormField::where('form_id', $this->form->id)->max('sort_order') + 1;
            FormField::create([
                'tenant_id'   => auth()->user()->tenant_id,
                'form_id'     => $this->form->id,
                'field_type'  => $this->f_type,
                'label'       => $this->f_label,
                'placeholder' => $this->f_placeholder ?: null,
                'is_required' => $this->f_required,
                'options'     => $options,
                'sort_order'  => $sortOrder,
            ]);
            $this->toastSuccess('Field added.');
        }

        $this->showFieldForm = false;
        $this->reset(['f_label', 'f_type', 'f_placeholder', 'f_required', 'f_options_raw', 'editFieldId']);
        $this->form->load('fields');
    }

    public function editField(int $id): void
    {
        $field = FormField::find($id);
        if (!$field) return;
        $this->editFieldId   = $id;
        $this->f_label       = $field->label;
        $this->f_type        = $field->field_type;
        $this->f_placeholder = $field->placeholder ?? '';
        $this->f_required    = $field->is_required;
        $this->f_options_raw = $field->options ? implode(', ', $field->options) : '';
        $this->showFieldForm = true;
    }

    public function deleteField(int $id): void
    {
        FormField::find($id)?->delete();
        $this->form->load('fields');
        $this->toastSuccess('Field removed.');
    }

    public function moveFieldUp(int $id): void
    {
        $field = FormField::find($id);
        if (!$field) return;
        $prev = FormField::where('form_id', $field->form_id)
            ->where('sort_order', '<', $field->sort_order)
            ->orderByDesc('sort_order')->first();
        if ($prev) {
            [$field->sort_order, $prev->sort_order] = [$prev->sort_order, $field->sort_order];
            $field->save(); $prev->save();
        }
        $this->form->load('fields');
    }

    public function moveFieldDown(int $id): void
    {
        $field = FormField::find($id);
        if (!$field) return;
        $next = FormField::where('form_id', $field->form_id)
            ->where('sort_order', '>', $field->sort_order)
            ->orderBy('sort_order')->first();
        if ($next) {
            [$field->sort_order, $next->sort_order] = [$next->sort_order, $field->sort_order];
            $field->save(); $next->save();
        }
        $this->form->load('fields');
    }

    public function render()
    {
        return view('livewire.tenant.forms.create-form');
    }
}