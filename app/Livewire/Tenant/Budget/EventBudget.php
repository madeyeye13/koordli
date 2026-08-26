<?php

namespace App\Livewire\Tenant\Budget;

use App\Models\Tenant\Budget;
use App\Models\Tenant\BudgetItem;
use App\Models\Tenant\ClientPayment;
use App\Models\Tenant\Event;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.tenant')]
class EventBudget extends Component
{
    use WithToast;

    public Event   $event;
    public ?Budget $budget = null;

    // Active tab
    public string $activeTab = 'breakdown';

    // Add budget item
    public bool   $showAddForm   = false;
    public string $newCategory   = '';
    public string $newEstimated  = '';
    public string $newActual     = '';
    public string $newPaid       = '';
    public string $newNotes      = '';

    // Edit budget item
    public ?int   $editItemId    = null;
    public string $editCategory  = '';
    public string $editEstimated = '';
    public string $editActual    = '';
    public string $editPaid      = '';
    public string $editNotes     = '';

    // Client payment
    public bool   $showPaymentForm    = false;
    public string $paymentAmount      = '';
    public string $paymentDescription = '';
    public string $paymentDate        = '';
    public string $paymentMethod      = 'transfer';

    // Delete item
    public bool $showDeleteModal   = false;
    public ?int $deleteItemId      = null;

    // Delete payment
    public bool $showDeletePayment = false;
    public ?int $deletePaymentId   = null;

    public function mount(string $slug): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'budget.view'),
            403
        );

        $this->event       = Event::where('slug', $slug)->firstOrFail();
        $this->budget      = Budget::with(['items', 'clientPayments'])
                               ->where('event_id', $this->event->id)
                               ->first();
        $this->paymentDate = now()->format('Y-m-d');
    }

    private function requireManage(): bool
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'budget.manage')) {
            $this->toastError('You do not have permission to manage the budget.');
            return false;
        }
        return true;
    }

    protected function getOrCreateBudget(): Budget
    {
        if (!$this->budget) {
            $this->budget = Budget::create([
                'tenant_id'    => auth()->user()->tenant_id,
                'event_id'     => $this->event->id,
                'total_amount' => 0,
                'client_paid'  => 0,
                'currency'     => auth()->user()->tenant->billing_currency ?? 'NGN',
            ]);
            $this->budget->load(['items', 'clientPayments']);
        }
        return $this->budget;
    }

    protected function reload(): void
    {
        $this->budget->load(['items', 'clientPayments']);
    }

    // ── Budget Items ──────────────────────────────────────────

    public function addItem(): void
    {
        if (!$this->requireManage()) return;

        $this->validate([
            'newCategory'  => 'required|string|max:200',
            'newEstimated' => 'required|numeric|min:0',
            'newActual'    => 'nullable|numeric|min:0',
            'newPaid'      => 'nullable|numeric|min:0',
            'newNotes'     => 'nullable|string|max:500',
        ], [], [
            'newCategory'  => 'category',
            'newEstimated' => 'estimated amount',
        ]);

        $budget = $this->getOrCreateBudget();

        BudgetItem::create([
            'tenant_id' => auth()->user()->tenant_id,
            'budget_id' => $budget->id,
            'category'  => $this->newCategory,
            'estimated' => $this->newEstimated ?: 0,
            'actual'    => $this->newActual ?: 0,
            'paid'      => $this->newPaid ?: 0,
            'notes'     => $this->newNotes ?: null,
        ]);

        $this->reload();
        $this->reset(['newCategory', 'newEstimated', 'newActual', 'newPaid', 'newNotes', 'showAddForm']);
        $this->toastSuccess('Budget item added.');
    }

    public function startEdit(int $itemId): void
    {
        if (!$this->requireManage()) return;

        $item = BudgetItem::find($itemId);
        if (!$item) return;
        $this->editItemId    = $itemId;
        $this->editCategory  = $item->category;
        $this->editEstimated = $item->estimated;
        $this->editActual    = $item->actual;
        $this->editPaid      = $item->paid;
        $this->editNotes     = $item->notes ?? '';
    }

    public function saveEdit(): void
    {
        if (!$this->requireManage()) return;

        $this->validate([
            'editCategory'  => 'required|string|max:200',
            'editEstimated' => 'required|numeric|min:0',
            'editActual'    => 'nullable|numeric|min:0',
            'editPaid'      => 'nullable|numeric|min:0',
        ], [], [
            'editCategory'  => 'category',
            'editEstimated' => 'estimated amount',
        ]);

        $item = BudgetItem::find($this->editItemId);
        if ($item) {
            $item->update([
                'category'  => $this->editCategory,
                'estimated' => $this->editEstimated ?: 0,
                'actual'    => $this->editActual ?: 0,
                'paid'      => $this->editPaid ?: 0,
                'notes'     => $this->editNotes ?: null,
            ]);
        }

        $this->reload();
        $this->reset(['editItemId', 'editCategory', 'editEstimated', 'editActual', 'editPaid', 'editNotes']);
        $this->toastSuccess('Budget item updated.');
    }

    public function cancelEdit(): void
    {
        $this->reset(['editItemId', 'editCategory', 'editEstimated', 'editActual', 'editPaid', 'editNotes']);
    }

    public function confirmDelete(int $itemId): void
    {
        if (!$this->requireManage()) return;

        $this->deleteItemId    = $itemId;
        $this->showDeleteModal = true;
    }

    public function deleteItem(): void
    {
        if (!$this->requireManage()) return;

        BudgetItem::find($this->deleteItemId)?->delete();
        $this->reload();
        $this->showDeleteModal = false;
        $this->deleteItemId    = null;
        $this->toastSuccess('Budget item deleted.');
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->deleteItemId    = null;
    }

    // ── Client Payments ───────────────────────────────────────

    public function addPayment(): void
    {
        if (!$this->requireManage()) return;

        $this->validate([
            'paymentAmount'      => 'required|numeric|min:1',
            'paymentDate'        => 'required|date',
            'paymentMethod'      => 'required|in:transfer,cash,cheque,pos,ussd',
            'paymentDescription' => 'nullable|string|max:300',
        ], [], [
            'paymentAmount' => 'amount',
            'paymentDate'   => 'payment date',
        ]);

        $budget = $this->getOrCreateBudget();

        $payment = ClientPayment::create([
            'tenant_id'      => auth()->user()->tenant_id,
            'budget_id'      => $budget->id,
            'amount'         => $this->paymentAmount,
            'description'    => $this->paymentDescription ?: null,
            'paid_on'        => $this->paymentDate,
            'payment_method' => $this->paymentMethod,
        ]);

        app(\App\Services\Notifications\ClientNotificationService::class)
            ->notifyPaymentRecorded($payment);

        $this->reload();
        $this->reset(['paymentAmount', 'paymentDescription', 'paymentMethod', 'showPaymentForm']);
        $this->paymentDate   = now()->format('Y-m-d');
        $this->paymentMethod = 'transfer';
        $this->toastSuccess('Payment recorded.');
    }

    public function confirmDeletePayment(int $paymentId): void
    {
        if (!$this->requireManage()) return;

        $this->deletePaymentId   = $paymentId;
        $this->showDeletePayment = true;
    }

    public function deletePayment(): void
    {
        if (!$this->requireManage()) return;

        ClientPayment::find($this->deletePaymentId)?->delete();
        $this->reload();
        $this->showDeletePayment = false;
        $this->deletePaymentId   = null;
        $this->toastSuccess('Payment deleted.');
    }

    public function cancelDeletePayment(): void
    {
        $this->showDeletePayment = false;
        $this->deletePaymentId   = null;
    }

    public function sendOutstandingReminder(): void
    {
        if (!$this->requireManage()) return;

        if (!$this->event->client_email) {
            $this->toastError('No client email on this event. Edit the event to add one.');
            return;
        }

        if (!$this->budget || $this->budget->clientOutstanding() <= 0) {
            $this->toastError('No outstanding balance to remind about.');
            return;
        }

        $symbol = $this->getCurrencySymbol();

        \App\Jobs\SendOutstandingReminderJob::dispatch(
            clientEmail:  $this->event->client_email,
            clientName:   $this->event->client_name ?? 'Valued Client',
            eventName:    $this->event->name,
            companyName:  auth()->user()->tenant->name,
            agreedBudget: number_format($this->budget->agreedBudget(), 2),
            amountPaid:   number_format($this->budget->totalClientPaid(), 2),
            outstanding:  number_format($this->budget->clientOutstanding(), 2),
            currency:     $symbol,
            whiteLabel:   app(\App\Services\FeatureGateService::class)->canAccess(auth()->user()->tenant, 'white_label'),
        );
        
        $this->toastSuccess('Reminder sent to ' . $this->event->client_email);
    }

    protected function getCurrencySymbol(): string
    {
        return \App\Helpers\CurrencyHelper::symbol(
            $this->budget?->currency ?? auth()->user()->tenant->billing_currency ?? 'NGN'
        );
    }

    public function render()
    {
        if ($this->budget) $this->reload();
        return view('livewire.tenant.budget.event-budget');
    }
}