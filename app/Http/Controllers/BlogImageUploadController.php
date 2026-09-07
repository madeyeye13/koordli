<?php

namespace App\Http\Controllers;

use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        abort_unless(auth('platform')->check(), 403);

        $request->validate(['image' => 'required|image|max:8192']);

        $result = app(ImageOptimizationService::class)->store($request->file('image'), 'blog/content');

        return response()->json([
            'url' => Storage::disk(config('blog.storage_disk'))->url($result['path']),
        ]);
    }
}