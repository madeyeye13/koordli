<?php

namespace App\Services;

use App\Models\Central\SupportTicketAttachment;
use App\Models\Tenant\ConversationMessageAttachment;
use App\Models\Tenant\Document;
use App\Models\Tenant\Form;
use App\Models\Tenant\RsvpForm;
use App\Models\Tenant\VendorContract;
use App\Models\Tenant\VendorInvoice;
use App\Models\Tenant\VendorInvoicePayment;

class DocumentStorageService
{
    /**
     * Sums stored file size across EVERY file-producing table in the app,
     * not just the Media Library — deliberate per the user's own reasoning:
     * a plan's storage allowance is a real commitment, so an undercounted
     * figure now becomes a worse retrofit later. Every source here uses a
     * stored size column (never a live filesystem stat call), matching the
     * same performance reasoning already applied to the storage-limit
     * check itself.
     *
     * withoutGlobalScope('tenant') is used throughout since this method
     * may be called from contexts with no ambient tenant binding (e.g. a
     * queued job validating an upload) — never rely on the global scope
     * here, always filter by the explicitly passed $tenantId.
     */
    public function totalBytesUsed(int $tenantId): int
    {
        $total = 0;

        $total += (int) Document::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)->sum('size');

        $total += (int) ConversationMessageAttachment::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)->sum('file_size');

        // SupportTicketAttachment has NO tenant_id column of its own —
        // confirmed via a real crash during testing. It's only tenant-
        // scoped indirectly, through its parent SupportTicket (which does
        // have tenant_id). Filter by first resolving that tenant's ticket
        // IDs, rather than assuming a relationship method exists on the
        // attachment model itself.
        $ticketIds = \App\Models\Central\SupportTicket::where('tenant_id', $tenantId)->pluck('id');
        $total += (int) SupportTicketAttachment::whereIn('ticket_id', $ticketIds)->sum('file_size');

        $total += (int) VendorInvoice::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)->sum('attachment_size');

        $total += (int) VendorInvoicePayment::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)->sum('receipt_size');

        $total += (int) VendorContract::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)->sum('unsigned_file_size');

        $total += (int) VendorContract::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)->sum('signed_file_size');

        $total += (int) Form::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)->sum('hero_image_size');

        // RSVP cover image size lives inside a JSON column, not a real
        // column — can't SQL-sum it portably, so summed in PHP instead.
        // Row count per tenant here is small (one RsvpForm per event with
        // RSVP enabled), so this is not a performance concern.
        $total += RsvpForm::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->get(['branding'])
            ->sum(fn($form) => (int) ($form->branding['cover_image_size'] ?? 0));

        return $total;
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}