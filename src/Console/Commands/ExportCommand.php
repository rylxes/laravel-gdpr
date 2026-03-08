<?php

namespace Rylxes\Gdpr\Console\Commands;

use Illuminate\Console\Command;
use Rylxes\Gdpr\Jobs\DataExportJob;
use Rylxes\Gdpr\Models\DataExport;
use Rylxes\Gdpr\Support\DataPackager;
use Rylxes\Gdpr\Support\DownloadLinkGenerator;

class ExportCommand extends Command
{
    protected $signature = 'gdpr:export
        {user : The user ID to export data for}
        {--format=json : Export format (json, csv, xml)}
        {--sync : Run the export synchronously instead of queuing}';

    protected $description = 'Export all personal data for a user (GDPR data portability)';

    public function handle(): int
    {
        $userId = $this->argument('user');
        $format = $this->option('format');
        $userModel = config('gdpr.user_model', 'App\\Models\\User');

        if (! in_array($format, DataExport::FORMATS)) {
            $this->error("Invalid format: {$format}. Supported: " . implode(', ', DataExport::FORMATS));

            return self::FAILURE;
        }

        $user = $userModel::find($userId);

        if (! $user) {
            $this->error("User #{$userId} not found.");

            return self::FAILURE;
        }

        $this->info("Exporting data for user #{$userId} ({$format} format)...");

        /** @var \Rylxes\Gdpr\GdprManager $gdpr */
        $gdpr = app('gdpr');

        if ($this->option('sync')) {
            // Create export record manually for sync execution
            $export = DataExport::create([
                'user_id' => $user->getKey(),
                'user_type' => get_class($user),
                'status' => 'pending',
                'format' => $format,
            ]);

            try {
                (new DataExportJob($export->id))->handle(
                    app(DataPackager::class),
                    app(DownloadLinkGenerator::class),
                );

                $export->refresh();
                $this->info("Export completed successfully.");
                $this->line("  File: {$export->file_path}");
                $this->line("  Size: {$export->fileSizeForHumans()}");
                $this->line("  Download URL: {$export->downloadUrl()}");
            } catch (\Throwable $e) {
                $this->error("Export failed: {$e->getMessage()}");

                return self::FAILURE;
            }
        } else {
            $export = $gdpr->export($user, $format);
            $this->info("Export job dispatched. Export ID: {$export->id}");
            $this->line("The user will be notified by email when the export is ready.");
        }

        return self::SUCCESS;
    }
}
