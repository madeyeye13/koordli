<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormRedirect extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'form_id', 'tenant_id', 'redirect_type',
        'redirect_url', 'whatsapp_number', 'whatsapp_message',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function whatsappUrl(array $data = []): string
    {
        $message = $this->whatsapp_message ?? 'Hello, I just submitted a booking enquiry.';
        foreach ($data as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }
        $phone = preg_replace('/[^0-9]/', '', $this->whatsapp_number);
        return "https://wa.me/{$phone}?text=" . urlencode($message);
    }
}