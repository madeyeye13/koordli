<?php

namespace App\Livewire\Public;

use App\Models\Tenant\VendorContract;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.rsvp')]
class VendorContractSign extends Component
{
    public VendorContract $contract;
    public bool $expired  = false;
    public bool $notFound = false;
    public bool $signed   = false;

    public string $vendor_signature_data      = '';
    public string $vendor_signature_data_type = 'draw';
    public string $vendor_signature_full_name = '';

    public string $error = '';

    public function mount(string $token): void
    {
        $contract = VendorContract::withoutGlobalScope('tenant')
            ->where('signing_token', $token)
            ->with(['vendor', 'event'])
            ->first();

        if (!$contract) {
            $this->notFound = true;
            return;
        }

        if ($contract->isSigningLinkExpired()) {
            $this->expired = true;
        }

        if ($contract->status === 'cancelled') {
            $this->error = 'This contract has been cancelled and can no longer be signed.';
        }

        $this->contract = $contract;
        $this->vendor_signature_full_name = $contract->vendor->contact_name ?? $contract->vendor->name;

        if ($contract->vendor_signed_at) {
            $this->signed = true;
        }
    }

    public function submitSignature(): void
    {
        if ($this->contract->isSigningLinkExpired()) {
            $this->error = 'This signing link has expired.';
            return;
        }

        $this->validate([
            'vendor_signature_data'      => 'required|string',
            'vendor_signature_full_name' => 'required|string|min:2|max:100',
        ], [
            'vendor_signature_data.required' => 'Please draw or type your signature first.',
        ]);

        $this->contract->update([
            'vendor_signature_type' => $this->vendor_signature_data_type,
            'vendor_signature_data' => $this->vendor_signature_data,
            'vendor_signature_name' => $this->vendor_signature_full_name,
            'vendor_signed_at'      => now(),
            'vendor_signed_ip'      => request()->ip(),
        ]);

        $this->contract->checkAndUpdateSignedStatus();
        $this->contract->refresh();

        // Notify the planner
        $plannerUser = \App\Models\Tenant\User::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->contract->tenant_id)
            ->where('id', $this->contract->created_by)
            ->first();

        if ($plannerUser?->email) {
            \App\Jobs\SendContractSignedNotificationJob::dispatch(
                $plannerUser->email,
                $plannerUser->name,
                $this->contract->vendor->name,
                $this->contract->title,
                now()->format('D, d M Y g:i A'),
                route('tenant.contracts.show', $this->contract->uuid),
                $this->contract->isFullySigned(),
            );
        }

        $this->signed = true;
    }

    public function downloadSignedCopy()
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.vendor-contract-pdf', $this->buildPdfData())->setPaper('a4');
        return response()->streamDownload(
            fn() => print($pdf->output()),
            str_replace(' ', '-', $this->contract->title) . '-signed.pdf'
        );
    }

    private function buildPdfData(): array
    {
        $tenant   = \App\Models\Central\Tenant::find($this->contract->tenant_id);
        $branding = $tenant->branding ?? [];

        $logoUrl = null;
        if (!empty($branding['logo'])) {
            $logoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($branding['logo']);
            if (file_exists($logoPath)) {
                $logoUrl = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        return [
            'contract'     => $this->contract,
            'companyName'  => $tenant->name,
            'primaryColor' => $branding['primary_color'] ?? '#7C3AED',
            'accentColor'  => $branding['accent_color'] ?? '#F59E0B',
            'logoUrl'      => $logoUrl,
            'currencyCode' => $tenant->billing_currency ?? 'NGN',
        ];
    }

    public function render()
    {
        return view('livewire.public.vendor-contract-sign');
    }
}