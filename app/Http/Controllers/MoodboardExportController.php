<?php

namespace App\Http\Controllers;

use App\Models\Tenant\Moodboard;
use App\Services\PermissionService;

class MoodboardExportController extends Controller
{
    public function export(int $id)
    {
        abort_unless(app(PermissionService::class)->userCan(auth()->user(), 'moodboards.view'), 403);

        $moodboard = Moodboard::with(['items' => fn($q) => $q->orderBy('sort_order'), 'items.document'])->findOrFail($id);

        return view('moodboards.export-print', compact('moodboard'));
    }
}