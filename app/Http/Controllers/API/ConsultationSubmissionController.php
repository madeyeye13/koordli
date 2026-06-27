<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendFormSubmissionConfirmationJob;
use App\Jobs\SendFormSubmissionNotificationJob;
use App\Models\Central\Tenant;
use App\Models\Tenant\ConsultationBooking;
use App\Models\Tenant\Form;
use App\Models\Tenant\FormSubmission;
use App\Models\Tenant\FormSubmissionValue;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ConsultationSubmissionController extends Controller
{
    public function submit(Request $request, string $token)
    {
        $form = Form::withoutGlobalScope('tenant')
            ->where('endpoint_token', $token)
            ->where('type', 'consultation')
            ->where('status', 'active')
            ->with('fields', 'redirect')
            ->first();

        if (!$form) {
            return response()->json(['error' => 'Form not found or inactive.'], 404);
        }

        $bookingDate = $request->input('booking_date');
        $bookingTime = $request->input('booking_time');
        $consultationType = $request->input('consultation_type', 'physical');

        // Check slot availability
        $existing = ConsultationBooking::withoutGlobalScope('tenant')
            ->where('form_id', $form->id)
            ->where('booking_date', $bookingDate)
            ->where('booking_time', $bookingTime)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($existing) {
            return response()->json(['error' => 'This time slot is no longer available.'], 409);
        }

        $submission = FormSubmission::create([
            'tenant_id'    => $form->tenant_id,
            'form_id'      => $form->id,
            'source'       => 'web',
            'status'       => 'new',
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
            'submitted_at' => now(),
        ]);

        $fields     = [];
        $guestName  = '';
        $guestEmail = '';
        $guestPhone = '';

        foreach ($form->fields as $field) {
            $value = $request->input($field->label) ?? $request->input('field_' . $field->id) ?? '';

            FormSubmissionValue::create([
                'submission_id' => $submission->id,
                'field_id'      => $field->id,
                'value'         => is_array($value) ? implode(', ', $value) : $value,
            ]);

            $fields[] = ['label' => $field->label, 'value' => $value];

            if (strtolower($field->field_type) === 'email' && empty($guestEmail)) {
                $guestEmail = $value;
            }
            if (strtolower($field->field_type) === 'phone' && empty($guestPhone)) {
                $guestPhone = $value;
            }
            if (in_array(strtolower($field->label), ['name', 'full name', 'your name']) && empty($guestName)) {
                $guestName = $value;
            }
        }

        if (empty($guestName)) $guestName = 'Guest';

        $booking = ConsultationBooking::create([
            'uuid'              => Str::uuid(),
            'tenant_id'         => $form->tenant_id,
            'form_id'           => $form->id,
            'submission_id'     => $submission->id,
            'booking_date'      => $bookingDate,
            'booking_time'      => $bookingTime,
            'consultation_type' => $consultationType,
            'status'            => 'pending',
            'guest_name'        => $guestName,
            'guest_email'       => $guestEmail ?: null,
            'guest_phone'       => $guestPhone ?: null,
        ]);

        $tenant = Tenant::find($form->tenant_id);
        $plannerUser = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $form->tenant_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'company_owner'))
            ->first();

        if ($plannerUser?->email) {
            SendFormSubmissionNotificationJob::dispatch(
                $plannerUser->email,
                $plannerUser->name,
                $form->name,
                'consultation',
                $guestName,
                now()->format('D, d M Y g:i A'),
                $fields,
                \Carbon\Carbon::parse($bookingDate)->format('D, d M Y'),
                \Carbon\Carbon::parse($bookingTime)->format('g:i A'),
            );
        }

        if ($guestEmail) {
            SendFormSubmissionConfirmationJob::dispatch(
                $guestEmail,
                $guestName,
                $form->name,
                'consultation',
                $tenant?->name ?? 'The organiser',
                $form->tenant_email ?? $plannerUser?->email ?? '',
                $form->tenant_phone,
                \Carbon\Carbon::parse($bookingDate)->format('D, d M Y'),
                \Carbon\Carbon::parse($bookingTime)->format('g:i A'),
                $consultationType,
                $form->location,
                $booking->meeting_link,
            );
        }

        return response()->json([
            'success'      => true,
            'message'      => 'Consultation booked successfully.',
            'booking_date' => $bookingDate,
            'booking_time' => $bookingTime,
            'whatsapp_url' => $form->redirect?->whatsapp_number
                ? $form->redirect->whatsappUrl(['name' => $guestName])
                : null,
        ]);
    }
}