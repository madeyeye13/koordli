<?php

namespace App\Http\Controllers;

use App\Models\Tenant\Checklist;
use App\Services\PermissionService;
use Barryvdh\DomPDF\Facade\Pdf;

class ChecklistExportController extends Controller
{
    public function export(int $id)
    {
        abort_unless(app(PermissionService::class)->userCan(auth()->user(), 'checklists.view'), 403);

        $checklist = Checklist::with(['event', 'items' => fn($q) => $q->orderBy('phase')->orderBy('sort_order')])->findOrFail($id);

        $itemsByPhase = [];
        foreach (\App\Enums\ChecklistPhase::cases() as $phase) {
            $items = $checklist->items->where('phase', $phase->value);
            if ($items->isNotEmpty()) $itemsByPhase[$phase->label()] = $items;
        }

        $tenant   = auth()->user()->tenant;
        $branding = $tenant->branding ?? [];

        // Base64 data URI, not a storage URL — matches RunsheetManager's
        // call sheet export exactly. DomPDF can't reliably resolve
        // external/relative image URLs the way a browser does, so the
        // logo is embedded directly into the HTML instead.
        $logoUrl = null;
        if (!empty($branding['logo'])) {
            $logoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($branding['logo']);
            if (file_exists($logoPath)) {
                $logoUrl = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $pdf = Pdf::loadView('pdf.checklist-pdf', [
            'checklist'    => $checklist,
            'itemsByPhase' => $itemsByPhase,
            'companyName'  => $tenant->name,
            'primaryColor' => $branding['primary_color'] ?? '#7C3AED',
            'accentColor'  => $branding['accent_color'] ?? '#F59E0B',
            'logoUrl'      => $logoUrl,
        ])->setPaper('a4');

        return $pdf->stream($checklist->event->name . ' - Checklist.pdf');
    }
}