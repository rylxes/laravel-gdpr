<?php

namespace Rylxes\Gdpr\Support;

use Illuminate\Support\Facades\URL;
use Rylxes\Gdpr\Models\DataExport;

class DownloadLinkGenerator
{
    /**
     * Generate a secure, time-limited download URL for an export.
     */
    public function generate(DataExport $export): string
    {
        $expiryMinutes = config('gdpr.export.download_link_expiry_minutes', 60);

        return URL::temporarySignedRoute(
            'gdpr.download',
            now()->addMinutes($expiryMinutes),
            ['token' => $export->download_token],
        );
    }

    /**
     * Generate a unique download token.
     */
    public function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Verify a download token and return the export if valid.
     */
    public function verify(string $token): ?DataExport
    {
        return DataExport::where('download_token', $token)
            ->where('status', 'completed')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
    }
}
