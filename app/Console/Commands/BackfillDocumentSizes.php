<?php

namespace App\Console\Commands;

use App\Models\Tenant\Form;
use App\Models\Tenant\RsvpForm;
use App\Models\Tenant\VendorContract;
use App\Models\Tenant\VendorInvoice;
use App\Models\Tenant\VendorInvoicePayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackfillDocumentSizes extends Command
{
    protected $signature = 'koordli:backfill-document-sizes';

    protected $description = 'One-time, purely additive: fills in NULL file-size columns for '
        . 'existing invoice attachments, payment receipts, contract PDFs, RSVP cover images, '
        . 'and form hero images. Never overwrites an existing value. Gracefully skips and '
        . 'reports any file no longer found on disk rather than failing the whole run.';

    private array $missing = [];
    private int $filled = 0;

    public function handle(): int
    {
        $this->backfillColumn(VendorInvoice::withoutGlobalScope('tenant'), 'attachment_path', 'attachment_size', 'VendorInvoice');
        $this->backfillColumn(VendorInvoicePayment::withoutGlobalScope('tenant'), 'receipt_path', 'receipt_size', 'VendorInvoicePayment');
        $this->backfillColumn(VendorContract::withoutGlobalScope('tenant'), 'unsigned_file_path', 'unsigned_file_size', 'VendorContract (unsigned)');
        $this->backfillColumn(VendorContract::withoutGlobalScope('tenant'), 'signed_file_path', 'signed_file_size', 'VendorContract (signed)');
        $this->backfillColumn(Form::withoutGlobalScope('tenant'), 'hero_image_path', 'hero_image_size', 'Form');
        $this->backfillRsvpCoverImages();

        $this->info("Done. Filled {$this->filled} size value(s).");

        if (!empty($this->missing)) {
            $this->warn(count($this->missing) . ' file(s) referenced in the database were not found on disk and were skipped:');
            foreach ($this->missing as $m) {
                $this->line("  - {$m}");
            }
        }

        return self::SUCCESS;
    }

    private function backfillColumn($query, string $pathColumn, string $sizeColumn, string $label): void
    {
        $rows = (clone $query)
            ->whereNotNull($pathColumn)
            ->whereNull($sizeColumn)
            ->get();

        foreach ($rows as $row) {
            $path = $row->{$pathColumn};

            if (!Storage::disk('public')->exists($path)) {
                $this->missing[] = "{$label} #{$row->id}: {$path}";
                continue;
            }

            $row->{$sizeColumn} = Storage::disk('public')->size($path);
            $row->save();
            $this->filled++;
        }
    }

    private function backfillRsvpCoverImages(): void
    {
        $forms = RsvpForm::withoutGlobalScope('tenant')
            ->whereNotNull('branding')
            ->get();

        foreach ($forms as $form) {
            $branding = $form->branding ?? [];
            $path = $branding['cover_image_path'] ?? null;

            if (!$path || isset($branding['cover_image_size'])) {
                continue; // no cover image, or size already recorded — never overwrite
            }

            if (!Storage::disk('public')->exists($path)) {
                $this->missing[] = "RsvpForm #{$form->id}: {$path}";
                continue;
            }

            $branding['cover_image_size'] = Storage::disk('public')->size($path);
            $form->branding = $branding;
            $form->save();
            $this->filled++;
        }
    }
}