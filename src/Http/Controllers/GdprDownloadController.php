<?php

namespace Rylxes\Gdpr\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Rylxes\Gdpr\Support\DownloadLinkGenerator;

class GdprDownloadController extends Controller
{
    /**
     * Handle the data export download request.
     */
    public function __invoke(Request $request, string $token, DownloadLinkGenerator $linkGenerator)
    {
        $export = $linkGenerator->verify($token);

        if (! $export) {
            abort(404, 'Export not found or download link has expired.');
        }

        // Mark as downloaded
        if (! $export->isDownloaded()) {
            $export->update(['downloaded_at' => now()]);
        }

        $disk = config('gdpr.export.storage_disk', 'local');

        if (! Storage::disk($disk)->exists($export->file_path)) {
            abort(404, 'Export file not found. It may have been cleaned up.');
        }

        return Storage::disk($disk)->download(
            $export->file_path,
            'data-export-' . $export->id . '.' . $export->format,
        );
    }
}
