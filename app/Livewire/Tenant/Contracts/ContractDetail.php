<?php

namespace App\Livewire\Tenant\Contracts;

use App\Jobs\SendVendorContractJob;
use App\Models\Tenant\VendorContract;
use App\Services\PermissionService;
use App\Traits\WithToast;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.tenant')]
class ContractDetail extends Component
{
    use WithToast, WithFileUploads;

    public VendorContract $contract;

    public bool   $editing = false;
    public string $title   = '';
    public string $content = '';
    public string $contract_amount  = '';
    public string $payment_schedule = '';
    public string $expires_at       = '';

    public bool $showSendModal    = false;
    public bool $showCancelModal  = false;
    public bool $showSignedUpload = false;
    public bool $showSignModal    = false;

    public $signedFile = null;

    // Planner signature (temporary properties bound to signature pad component)
    public string $planner_signature_data = '';
    public string $planner_signature_data_type = 'draw';
    public string $planner_signature_full_name = '';


    public function mount(string $uuid): void
    {
        abort_unless(
            app(PermissionService::class)->userCan(auth()->user(), 'contracts.view')
                || app(PermissionService::class)->userCan(auth()->user(), 'contracts.manage'),
            403
        );

        $this->contract = VendorContract::where('uuid', $uuid)
            ->with(['vendor', 'event', 'template', 'statusHistory.changedBy', 'createdBy'])
            ->firstOrFail();
    }

    private function requireManage(): bool
    {
        if (!app(PermissionService::class)->userCan(auth()->user(), 'contracts.manage')) {
            $this->toastError('You do not have permission to edit this contract.');
            return false;
        }
        return true;
    }

    public function startEdit(): void
    {
        if (!$this->requireManage()) return;

        $this->title             = $this->contract->title;
        $this->content           = $this->contract->content;
        $this->contract_amount   = (string) ($this->contract->contract_amount ?? '');
        $this->payment_schedule  = $this->contract->payment_schedule ?? '';
        $this->expires_at        = $this->contract->expires_at?->format('Y-m-d') ?? '';
        $this->editing           = true;
    }

    public function saveEdit(): void
    {
        if (!$this->requireManage()) return;

        $this->validate([
            'title'   => 'required|string|min:2|max:200',
            'content' => 'required|string|min:10',
        ]);

        $this->contract->update([
            'title'            => $this->title,
            'content'          => $this->content,
            'contract_amount'  => $this->contract_amount ?: null,
            'payment_schedule' => $this->payment_schedule ?: null,
            'expires_at'       => $this->expires_at ?: null,
        ]);

        $this->editing = false;
        $this->toastSuccess('Contract updated.');
    }

    public function downloadPdf()
    {
        $pdf = Pdf::loadView('pdf.vendor-contract-pdf', $this->pdfData())->setPaper('a4');
        return response()->streamDownload(
            fn() => print($pdf->output()),
            str_replace(' ', '-', $this->contract->title) . '.pdf'
        );
    }

    private function pdfData(): array
    {
        $tenant   = auth()->user()->tenant;
        $branding = $tenant->branding ?? [];
        $currencyCode = $tenant->billing_currency ?? 'NGN';

        $logoUrl = null;
        if (!empty($branding['logo'])) {
            $logoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($branding['logo']);
            if (file_exists($logoPath)) {
                $logoUrl = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        return [
            'contract'      => $this->contract,
            'companyName'   => $tenant->name,
            'primaryColor'  => $branding['primary_color'] ?? '#7C3AED',
            'accentColor'   => $branding['accent_color'] ?? '#F59E0B',
            'logoUrl'       => $logoUrl,
            'currencyCode'  => $currencyCode,
        ];
    }

    public function downloadSignedPdf()
    {
        $pdf = Pdf::loadView('pdf.vendor-contract-pdf', $this->pdfData())->setPaper('a4');
        return response()->streamDownload(
            fn() => print($pdf->output()),
            str_replace(' ', '-', $this->contract->title) . '-signed.pdf'
        );
    }


    private function buildPdfHtml(): string
    {
        return '<html><head><style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1C1917; line-height: 1.6; }
            h2 { font-size: 18px; } h3 { font-size: 14px; margin-top: 16px; }
        </style></head><body>' . $this->contract->content . '</body></html>';
    }

    public function confirmSend(): void
    {
        if (!$this->requireManage()) return;

        if (!$this->contract->vendor->email) {
            $this->toastError('This vendor has no email address on file.');
            return;
        }
        $this->showSendModal = true;
    }

    public function sendContract(): void
    {
        if (!$this->requireManage()) return;

        $pdf = Pdf::loadView('pdf.vendor-contract-pdf', $this->pdfData())->setPaper('a4');
        $fileName = 'contracts/' . $this->contract->uuid . '.pdf';
        $pdfOutput = $pdf->output();
        Storage::disk('public')->put($fileName, $pdfOutput);

        $this->contract->update([
            'unsigned_file_path' => $fileName,
            'unsigned_file_size' => strlen($pdfOutput),
        ]);

        $signingUrl = route('public.contract.sign', $this->contract->signing_token);

        SendVendorContractJob::dispatch(
            $this->contract->vendor->email,
            $this->contract->vendor->contact_name ?? $this->contract->vendor->name,
            $this->contract->title,
            auth()->user()->tenant->name,
            $fileName,
            $signingUrl,
            app(\App\Services\FeatureGateService::class)->canAccess(auth()->user()->tenant, 'white_label'),
        );

        $this->contract->changeStatus('sent', 'Contract emailed to vendor with e-signature link.');
        $this->contract->refresh();
        event(new \App\Events\ContractSent($this->contract));

        app(\App\Services\Notifications\VendorNotificationService::class)
            ->notifyContractSent($this->contract);

        $this->showSendModal = false;
        $this->toastSuccess('Contract sent to vendor.');
    }

    public function markSentManually(): void
    {
        if (!$this->requireManage()) return;

        $this->contract->changeStatus('sent', 'Marked as sent manually (delivered outside Koordli).');
        $this->contract->refresh();
        $this->showSendModal = false;
        $this->toastSuccess('Marked as sent.');
    }

    public function showUploadSigned(): void
    {
        if (!$this->requireManage()) return;

        $this->showSignedUpload = true;
    }

    public function uploadSigned(): void
    {
        if (!$this->requireManage()) return;

        $this->validate([
            'signedFile' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $signedFileSize = $this->signedFile->getSize();
        $path = $this->signedFile->store('contracts/signed', 'public');

        $this->contract->update([
            'signed_file_path' => $path,
            'signed_file_size' => $signedFileSize,
        ]);
        $this->contract->changeStatus('signed', 'Signed copy uploaded.');
        $this->contract->refresh();

        $this->showSignedUpload = false;
        $this->signedFile       = null;
        $this->toastSuccess('Signed contract uploaded.');
    }

    public function confirmCancel(): void
    {
        if (!$this->requireManage()) return;

        $this->showCancelModal = true;
    }

    public function cancelContract(): void
    {
        if (!$this->requireManage()) return;

        $this->contract->changeStatus('cancelled', 'Contract cancelled by planner.');
        $this->contract->refresh();
        $this->showCancelModal = false;
        $this->toastSuccess('Contract cancelled.');
    }

    public function showSignPanel(): void
    {
        if (!$this->requireManage()) return;

        $this->planner_signature_full_name = auth()->user()->name;
        $this->showSignModal = true;
    }

    public function savePlannerSignature(): void
    {
        if (!$this->requireManage()) return;

        $this->validate([
            'planner_signature_data'      => 'required|string',
            'planner_signature_full_name' => 'required|string|min:2|max:100',
        ], [
            'planner_signature_data.required' => 'Please draw or type your signature first.',
        ]);

        $this->contract->update([
            'planner_signature_type' => $this->planner_signature_data_type,
            'planner_signature_data' => $this->planner_signature_data,
            'planner_signature_name' => $this->planner_signature_full_name,
            'planner_signed_at'      => now(),
            'planner_signed_ip'      => request()->ip(),
        ]);

        $this->contract->checkAndUpdateSignedStatus();
        $this->contract->refresh();

        $this->showSignModal = false;
        $this->toastSuccess('Your signature has been recorded.');
    }

    public function removePlannerSignature(): void
    {
        if (!$this->requireManage()) return;

        $this->contract->update([
            'planner_signature_type' => null,
            'planner_signature_data' => null,
            'planner_signature_name' => null,
            'planner_signed_at'      => null,
            'planner_signed_ip'      => null,
        ]);
        $this->contract->refresh();
        $this->toastSuccess('Signature removed.');
    }

    public function render()
    {
        return view('livewire.tenant.contracts.contract-detail');
    }
}