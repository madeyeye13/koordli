<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendFormSubmissionConfirmationJob;
use App\Jobs\SendFormSubmissionNotificationJob;
use App\Models\Central\Tenant;
use App\Models\Tenant\Form;
use App\Models\Tenant\FormSubmission;
use App\Models\Tenant\FormSubmissionValue;
use App\Models\Tenant\User;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function submit(Request $request, string $token)
    {
        $form = Form::withoutGlobalScope('tenant')
            ->where('endpoint_token', $token)
            ->where('type', 'booking')
            ->where('status', 'active')
            ->with('fields', 'redirect')
            ->first();

        if (!$form) {
            return response()->json(['error' => 'Form not found or inactive.'], 404);
        }

        $submission = FormSubmission::create([
            'tenant_id'    => $form->tenant_id,
            'form_id'      => $form->id,
            'source'       => $request->header('Referer') ? 'external' : 'embed',
            'status'       => 'new',
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
            'submitted_at' => now(),
        ]);

        $fields = [];
        $guestName  = '';
        $guestEmail = '';

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
            if (in_array(strtolower($field->label), ['name', 'full name', 'your name']) && empty($guestName)) {
                $guestName = $value;
            }
        }

        if (empty($guestName)) $guestName = 'Guest';

        // Notify tenant
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
                'booking',
                $guestName,
                now()->format('D, d M Y g:i A'),
                $fields,
            );
        }

        // Confirm to guest
        if ($guestEmail) {
            SendFormSubmissionConfirmationJob::dispatch(
                $guestEmail,
                $guestName,
                $form->name,
                'booking',
                $tenant?->name ?? 'The organiser',
                $form->tenant_email ?? $plannerUser?->email ?? '',
                $form->tenant_phone,
            );
        }

        return response()->json([
            'success'      => true,
            'message'      => 'Submission received.',
            'whatsapp_url' => $form->redirect?->whatsapp_number
                ? $form->redirect->whatsappUrl(['name' => $guestName])
                : null,
        ]);
    }
}