<?php

namespace App\Livewire\Tenant;

use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class ClientFinancialVisibilitySettings extends Component
{
    use WithToast;

    public array $fields = [
        'balance'   => ['label' => 'Their Balance', 'desc' => 'Agreed budget, amount paid, and outstanding balance.'],
        'breakdown' => ['label' => 'Cost Breakdown', 'desc' => 'Itemized list of what the budget is being spent on.'],
        'vendors'   => ['label' => 'Vendor Payment Detail', 'desc' => 'Which vendors are paid, and how much.'],
        'fee'       => ['label' => 'Your Professional Fee', 'desc' => 'Your fee amount and structure.'],
    ];

    public array $settings = [];

    public function mount(): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'budget.client-visibility.manage'),
            403
        );

        $stored = auth()->user()->tenant->client_financial_visibility ?? [];
        foreach (array_keys($this->fields) as $key) {
            $this->settings[$key] = $stored[$key] ?? ($key === 'balance');
        }
    }

    public function setVisibility(string $key, bool $enabled): void
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'budget.client-visibility.manage')) {
            $this->toastError('You do not have permission to manage this setting.');
            return;
        }

        if (!array_key_exists($key, $this->fields)) {
            return;
        }

        $this->settings[$key] = $enabled;

        auth()->user()->tenant->update([
            'client_financial_visibility' => $this->settings,
        ]);
    }

    public function render()
    {
        return view('livewire.tenant.client-financial-visibility-settings');
    }
}