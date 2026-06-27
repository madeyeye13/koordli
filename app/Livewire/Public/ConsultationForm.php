<?php

namespace App\Livewire\Public;

use App\Helpers\PublicHolidayHelper;
use App\Jobs\SendFormSubmissionConfirmationJob;
use App\Jobs\SendFormSubmissionNotificationJob;
use App\Models\Central\Tenant;
use App\Models\Tenant\ConsultationAvailability;
use App\Models\Tenant\ConsultationBooking;
use App\Models\Tenant\Form;
use App\Models\Tenant\FormSubmission;
use App\Models\Tenant\FormSubmissionValue;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.rsvp')]
class ConsultationForm extends Component
{
    public Form $form;

    public string $selectedDate          = '';
    public string $selectedTime          = '';
    public string $consultation_type     = 'physical';
    public array  $answers               = [];
    public bool   $submitted             = false;
    public string $error                 = '';
    public ?string $whatsappUrl          = null;
    public array  $availableSlots        = [];
    public string $currentMonth          = '';
    public int    $currentYear           = 0;
    public array  $calendarDays          = [];
    public array  $blockedDates          = [];
    public array  $availableDays         = [];

    public function mount(string $slug): void
    {
        $this->form = Form::with([
            'fields' => fn($q) => $q->orderBy('sort_order'),
            'redirect',
            'availabilities' => fn($q) => $q->where('is_active', true),
        ])
            ->where('slug', $slug)
            ->where('type', 'consultation')
            ->where('status', 'active')
            ->firstOrFail();

        $now = Carbon::now();
        $this->currentMonth = $now->format('F');
        $this->currentYear  = $now->year;

        $this->consultation_type = $this->form->consultation_type === 'virtual' ? 'virtual' : 'physical';

        foreach ($this->form->fields as $field) {
            $this->answers[$field->id] = '';
        }

        $this->availableDays = $this->form->availabilities->pluck('day_of_week')->toArray();
        $this->loadCalendar($now->year, $now->month);
    }

    public function loadCalendar(int $year, int $month): void
    {
        $this->currentYear  = $year;
        $this->currentMonth = Carbon::create($year, $month, 1)->format('F');

        $tenant   = Tenant::find($this->form->tenant_id);
        $country  = $tenant?->country ?? 'NG';
        $holidays = PublicHolidayHelper::getHolidays($country, $year);

        $bookedDates = ConsultationBooking::withoutGlobalScope('tenant')
            ->where('form_id', $this->form->id)
            ->where('status', '!=', 'cancelled')
            ->whereYear('booking_date', $year)
            ->whereMonth('booking_date', $month)
            ->get()
            ->groupBy(fn($b) => $b->booking_date->format('Y-m-d'))
            ->map->count();

        $firstDay   = Carbon::create($year, $month, 1);
        $daysInMonth = $firstDay->daysInMonth;
        $startDow   = $firstDay->dayOfWeek;
        $today      = Carbon::today();

        $days = [];

        // Empty cells before first day
        for ($i = 0; $i < $startDow; $i++) {
            $days[] = null;
        }

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date     = Carbon::create($year, $month, $d);
            $dateStr  = $date->format('Y-m-d');
            $dow      = $date->dayOfWeek;
            $isPast   = $date->lt($today);
            $isHoliday= in_array($dateStr, $holidays);
            $isAvailDay = in_array($dow, $this->availableDays);

            // Check if fully booked
            $avail = $this->form->availabilities->firstWhere('day_of_week', $dow);
            $maxSlots = 0;
            if ($avail) {
                $start    = Carbon::parse($avail->start_time);
                $end      = Carbon::parse($avail->end_time);
                $maxSlots = (int) floor($start->diffInMinutes($end) / $this->form->duration_minutes);
            }
            $bookedCount  = $bookedDates[$dateStr] ?? 0;
            $isFullyBooked = $maxSlots > 0 && $bookedCount >= $maxSlots;

            $days[] = [
                'day'           => $d,
                'date'          => $dateStr,
                'isPast'        => $isPast,
                'isHoliday'     => $isHoliday,
                'isAvailable'   => !$isPast && !$isHoliday && $isAvailDay && !$isFullyBooked,
                'isSelected'    => $dateStr === $this->selectedDate,
                'isToday'       => $date->isToday(),
                'holidayNote'   => $isHoliday ? 'Public holiday' : null,
                'fullyBooked'   => $isFullyBooked,
            ];
        }

        $this->calendarDays = $days;
    }

    public function prevMonth(): void
    {
        $date = Carbon::create($this->currentYear, Carbon::parse("1 {$this->currentMonth} {$this->currentYear}")->month, 1)->subMonth();
        if ($date->gte(Carbon::today()->startOfMonth())) {
            $this->loadCalendar($date->year, $date->month);
            $this->selectedDate = '';
            $this->selectedTime = '';
            $this->availableSlots = [];
        }
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->currentYear, Carbon::parse("1 {$this->currentMonth} {$this->currentYear}")->month, 1)->addMonth();
        $this->loadCalendar($date->year, $date->month);
        $this->selectedDate = '';
        $this->selectedTime = '';
        $this->availableSlots = [];
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->selectedTime = '';
        $this->loadTimeSlots($date);
    }

    private function loadTimeSlots(string $date): void
    {
        $carbon = Carbon::parse($date);
        $dow    = $carbon->dayOfWeek;

        $avail = $this->form->availabilities->firstWhere('day_of_week', $dow);
        if (!$avail) {
            $this->availableSlots = [];
            return;
        }

        $booked = ConsultationBooking::withoutGlobalScope('tenant')
            ->where('form_id', $this->form->id)
            ->where('booking_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('booking_time')
            ->map(fn($t) => Carbon::parse($t)->format('H:i'))
            ->toArray();

        $slots  = [];
        $cursor = Carbon::parse($avail->start_time);
        $end    = Carbon::parse($avail->end_time);

        while ($cursor->copy()->addMinutes($this->form->duration_minutes)->lte($end)) {
            $timeStr = $cursor->format('H:i');
            $slots[] = [
                'time'      => $timeStr,
                'label'     => $cursor->format('g:i A'),
                'available' => !in_array($timeStr, $booked),
            ];
            $cursor->addMinutes($this->form->duration_minutes);
        }

        $this->availableSlots = $slots;
    }

    public function selectTime(string $time): void
    {
        $this->selectedTime = $time;
    }

    public function submit(): void
    {
        if (empty($this->selectedDate)) {
            $this->error = 'Please select a date.';
            return;
        }
        if (empty($this->selectedTime)) {
            $this->error = 'Please select a time slot.';
            return;
        }

        // Check slot still available
        $existing = ConsultationBooking::withoutGlobalScope('tenant')
            ->where('form_id', $this->form->id)
            ->where('booking_date', $this->selectedDate)
            ->where('booking_time', $this->selectedTime)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($existing) {
            $this->error = 'Sorry, this time slot was just booked. Please choose another.';
            $this->loadTimeSlots($this->selectedDate);
            return;
        }

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
        $guestPhone = '';

        foreach ($this->form->fields as $field) {
            $value = $this->answers[$field->id] ?? '';
            FormSubmissionValue::create([
                'submission_id' => $submission->id,
                'field_id'      => $field->id,
                'value'         => is_array($value) ? implode(', ', $value) : $value,
            ]);
            $fields[] = ['label' => $field->label, 'value' => $value];
            if ($field->field_type === 'email' && empty($guestEmail)) $guestEmail = $value;
            if ($field->field_type === 'phone' && empty($guestPhone)) $guestPhone = $value;
            if (in_array(strtolower($field->label), ['name', 'full name', 'your name']) && empty($guestName)) $guestName = $value;
        }

        if (empty($guestName)) $guestName = 'Guest';

        $booking = ConsultationBooking::create([
            'uuid'              => Str::uuid(),
            'tenant_id'         => $this->form->tenant_id,
            'form_id'           => $this->form->id,
            'submission_id'     => $submission->id,
            'booking_date'      => $this->selectedDate,
            'booking_time'      => $this->selectedTime,
            'consultation_type' => $this->consultation_type,
            'status'            => 'pending',
            'guest_name'        => $guestName,
            'guest_email'       => $guestEmail ?: null,
            'guest_phone'       => $guestPhone ?: null,
        ]);

        $tenant = Tenant::find($this->form->tenant_id);
        $plannerUser = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->form->tenant_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'company_owner'))
            ->first();

        $dateLabel = Carbon::parse($this->selectedDate)->format('D, d M Y');
        $timeLabel = Carbon::parse($this->selectedTime)->format('g:i A');

        if ($plannerUser?->email) {
            SendFormSubmissionNotificationJob::dispatch(
                $plannerUser->email,
                $plannerUser->name,
                $this->form->name,
                'consultation',
                $guestName,
                now()->format('D, d M Y g:i A'),
                $fields,
                $dateLabel,
                $timeLabel,
            );
        }

        if ($guestEmail) {
            SendFormSubmissionConfirmationJob::dispatch(
                $guestEmail,
                $guestName,
                $this->form->name,
                'consultation',
                $tenant?->name ?? 'The organiser',
                $this->form->tenant_email ?? $plannerUser?->email ?? '',
                $this->form->tenant_phone,
                $dateLabel,
                $timeLabel,
                $this->consultation_type,
                $this->form->location,
                $booking->meeting_link,
            );
        }

        if ($this->form->redirect?->redirect_type === 'whatsapp') {
            $this->whatsappUrl = $this->form->redirect->whatsappUrl(['name' => $guestName]);
        }

        $this->submitted = true;
        $this->error     = '';
    }

    public function render()
    {
        return view('livewire.public.consultation-form');
    }
}