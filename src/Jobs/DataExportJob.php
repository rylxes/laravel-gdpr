<?php

namespace Rylxes\Gdpr\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Rylxes\Gdpr\Events\DataExported;
use Rylxes\Gdpr\Models\DataExport;
use Rylxes\Gdpr\Notifications\DataExportReady;
use Rylxes\Gdpr\Support\DataPackager;
use Rylxes\Gdpr\Support\DownloadLinkGenerator;

class DataExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        protected int $dataExportId,
    ) {
        $this->onQueue(config('gdpr.queue.queue_name', 'gdpr'));

        if ($connection = config('gdpr.queue.connection')) {
            $this->onConnection($connection);
        }
    }

    public function handle(DataPackager $packager, DownloadLinkGenerator $linkGenerator): void
    {
        $export = DataExport::findOrFail($this->dataExportId);
        $export->update(['status' => 'processing']);

        try {
            $user = $export->user;

            if (! $user) {
                throw new \Rylxes\Gdpr\Exceptions\ExportException(
                    "User not found for data export #{$this->dataExportId}"
                );
            }

            // 1. Collect exportable data
            $gdprManager = app('gdpr');
            $allData = $gdprManager->discoverExportables($user);

            // 2. Package into requested format
            $result = $packager->package($allData, $export->format);

            // 3. Store the file
            $filePath = $packager->store($result, $export);

            // 4. Generate download token
            $token = $linkGenerator->generateToken();

            // 5. Update the export record
            $export->markCompleted($filePath, $result['size_bytes']);
            $export->update([
                'download_token' => $token,
                'expires_at' => now()->addMinutes(
                    config('gdpr.export.download_link_expiry_minutes', 60)
                ),
            ]);

            // 6. Fire event
            event(new DataExported(
                $export->id,
                $user->getKey(),
                $export->format,
                $filePath,
            ));

            // 7. Notify the user
            if (config('gdpr.notifications.export_ready.mail_enabled', true)) {
                $user->notify(new DataExportReady($export));
            }
        } catch (\Throwable $e) {
            $export->markFailed($e->getMessage());

            throw $e;
        }
    }
}
