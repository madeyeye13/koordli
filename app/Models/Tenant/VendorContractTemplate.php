<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorContractTemplate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'category', 'content', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(VendorContract::class, 'template_id');
    }

    public static function availablePlaceholders(): array
    {
        return [
            '{{vendor_name}}'       => "Vendor's business name",
            '{{company_name}}'      => 'Your company name',
            '{{event_name}}'        => 'Event name (if linked)',
            '{{event_date}}'        => 'Event date (if linked)',
            '{{event_location}}'    => 'Event location (if linked)',
            '{{service_category}}'  => "Vendor's category",
            '{{contract_amount}}'   => 'Contract amount',
            '{{payment_schedule}}'  => 'Payment schedule text',
            '{{planner_name}}'      => 'Your name (contract creator)',
            '{{generated_date}}'    => "Today's date",
        ];
    }

    public function render(Vendor $vendor, ?Event $event, ?string $amount, ?string $paymentSchedule, string $plannerName): string
    {
        $tenant = $vendor->tenant ?? \App\Models\Central\Tenant::find($vendor->tenant_id);

        $replacements = [
            '{{vendor_name}}'      => $vendor->name,
            '{{company_name}}'     => $tenant?->name ?? '',
            '{{event_name}}'       => $event?->name ?? '',
            '{{event_date}}'       => $event?->date?->format('F j, Y') ?? '',
            '{{event_location}}'   => $event?->venue ?? $event?->location ?? '',
            '{{service_category}}' => $vendor->category?->name ?? '',
            '{{contract_amount}}'  => $amount ? \App\Helpers\CurrencyHelper::formatForPdf((float)$amount, auth()->user()?->tenant?->billing_currency ?? 'NGN') : '',
            '{{payment_schedule}}' => $paymentSchedule ?? '',
            '{{planner_name}}'     => $plannerName,
            '{{generated_date}}'   => now()->format('F j, Y'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $this->content);
    }
}