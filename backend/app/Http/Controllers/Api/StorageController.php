<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
   public function show(string $path)
{
    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    $fullPath = storage_path('app/public/' . $path);
    $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

    return response()->file($fullPath, [
        'Content-Type' => $mime,
        'Access-Control-Allow-Origin' => '*',
        'Cache-Control' => 'public, max-age=86400',
    ]);
} 



    public function serve(string $path)
    {
        // 1. Sanitise — strip leading slash, block directory traversal
        $path = ltrim($path, '/');
 
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            abort(403, 'Access denied.');
        }
 
        // 2. Check existence on the public disk
        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'File not found.');
        }
 
        // 3. Build the real filesystem path and detect mime type
        //    Uses PHP's built-in mime_content_type() — no facade method chain,
        //    no Intelephense warnings.
        $fullPath = storage_path('app/public/' . $path);
        $mime     = mime_content_type($fullPath) ?: 'application/octet-stream';
 
        // 4. Stream via response()->file() — handles Range, ETag, 304 automatically
        return response()->file($fullPath, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }



}