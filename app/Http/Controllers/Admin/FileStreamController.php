<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\PathTraversalDetected;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileStreamController extends Controller
{
    /**
     * Stream file privat (naskah/surat keaslian) dari disk 'local'
     * (storage/app/private) — hanya bisa diakses lewat middleware 'auth'.
     */
    public function show(string $path): StreamedResponse
    {
        $disk = Storage::disk('local');

        try {
            abort_unless($disk->exists($path), 404);

            return $disk->response($path, null, [
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
            ]);
        } catch (PathTraversalDetected) {
            abort(404);
        }
    }
}
