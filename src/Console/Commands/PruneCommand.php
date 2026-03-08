<?php

namespace Rylxes\Gdpr\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Rylxes\Gdpr\Models\ConsentLog;
use Rylxes\Gdpr\Models\DataExport;
use Rylxes\Gdpr\Models\ErasureRequest;

class PruneCommand extends Command
{
    protected $signature = 'gdpr:prune {--force : Skip confirmation prompt}';

    protected $description = 'Prune expired exports and old GDPR audit logs based on retention settings';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will delete expired GDPR data based on retention settings. Continue?')) {
            return self::SUCCESS;
        }

        $this->info('Pruning GDPR data...');
        $this->newLine();

        // 1. Clean up expired export files and records
        $this->pruneExports();

        // 2. Prune old consent logs
        $this->pruneConsentLogs();

        // 3. Prune old erasure request records
        $this->pruneErasureRequests();

        // 4. Prune old export log records
        $this->pruneExportLogs();

        $this->newLine();
        $this->info('Pruning complete!');

        return self::SUCCESS;
    }

    protected function pruneExports(): void
    {
        $cleanupDays = config('gdpr.export.cleanup_after_days', 7);
        $disk = config('gdpr.export.storage_disk', 'local');

        $exports = DataExport::readyForCleanup()->get();
        $fileCount = 0;
        $recordCount = 0;

        foreach ($exports as $export) {
            if ($export->file_path && Storage::disk($disk)->exists($export->file_path)) {
                Storage::disk($disk)->delete($export->file_path);
                $fileCount++;
            }

            // Also delete the DB record for the cleaned-up export
            $export->delete();
            $recordCount++;
        }

        $this->line("  Deleted {$fileCount} export files and {$recordCount} export records (>{$cleanupDays} days old)");
    }

    protected function pruneConsentLogs(): void
    {
        $retentionDays = config('gdpr.audit.consent_logs_retention_days', 2555);
        $deleted = ConsentLog::where('created_at', '<', now()->subDays($retentionDays))->delete();
        $this->line("  Deleted {$deleted} consent log records (>{$retentionDays} days old)");
    }

    protected function pruneErasureRequests(): void
    {
        $retentionDays = config('gdpr.audit.erasure_logs_retention_days', 2555);
        $deleted = ErasureRequest::where('created_at', '<', now()->subDays($retentionDays))->delete();
        $this->line("  Deleted {$deleted} erasure request records (>{$retentionDays} days old)");
    }

    protected function pruneExportLogs(): void
    {
        $retentionDays = config('gdpr.audit.export_logs_retention_days', 365);
        $deleted = DataExport::where('created_at', '<', now()->subDays($retentionDays))->delete();
        $this->line("  Deleted {$deleted} export log records (>{$retentionDays} days old)");
    }
}
