<?php

namespace App\Http\Controllers;

use App\Services\QuickAccessService;
use Illuminate\Http\Request;

class QuickAccessController extends Controller
{
    public function updateTask(Request $request, string $token)
    {
        $service = app(QuickAccessService::class);
        $link = $service->findValidLink($token);
        if (!$link) return response()->json(['error' => 'Invalid link.'], 404);

        if ($link->pin_enabled && !session()->get('qa_pin_verified_' . $link->id, false)) {
            return response()->json(['error' => 'Please re-verify your PIN.'], 403);
        }

        $data = $request->validate([
            'task_id' => 'required|integer',
            'status'  => 'required|in:todo,in_progress,blocked,done,cancelled',
        ]);

        $result = $service->updateTaskStatus($link, $data['task_id'], $data['status']);

        return $result['ok']
            ? response()->json(['success' => true])
            : response()->json(['error' => $result['error']], 422);
    }

    public function updateRunsheet(Request $request, string $token)
    {
        $service = app(QuickAccessService::class);
        $link = $service->findValidLink($token);
        if (!$link) return response()->json(['error' => 'Invalid link.'], 404);

        if ($link->pin_enabled && !session()->get('qa_pin_verified_' . $link->id, false)) {
            return response()->json(['error' => 'Please re-verify your PIN.'], 403);
        }

        $data = $request->validate([
            'item_id'    => 'required|integer',
            'status'     => 'required|in:pending,in_progress,done,delayed',
            'delay_note' => 'nullable|string|max:300',
        ]);

        $result = $service->updateRunsheetStatus($link, $data['item_id'], $data['status'], $data['delay_note'] ?? null);

        return $result['ok']
            ? response()->json(['success' => true])
            : response()->json(['error' => $result['error']], 422);
    }
}