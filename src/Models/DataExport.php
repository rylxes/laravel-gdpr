<?php

namespace Rylxes\Gdpr\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DataExport extends Model
{
    protected $guarded = [];

    protected $casts = [
        'file_size_bytes' => 'integer',
        'downloaded_at' => 'datetime',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /** @var array<string> Valid status values */
    public const STATUSES = ['pending', 'processing', 'completed', 'failed'];

    /** @var array<string> Valid format values */
    public const FORMATS = ['json', 'csv', 'xml'];

    public function getTable(): string
    {
        return config('gdpr.table_prefix', 'gdpr_') . 'data_exports';
    }

    public function getConnectionName(): ?string
    {
        return config('gdpr.database_connection') ?? parent::getConnectionName();
    }

    /**
     * The user this export belongs to (polymorphic).
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }

    /**
     * Scope: expired exports.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope: completed exports.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: failed exports.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: exports ready for cleanup.
     */
    public function scopeReadyForCleanup(Builder $query): Builder
    {
        $days = config('gdpr.export.cleanup_after_days', 7);

        return $query->where('created_at', '<', now()->subDays($days));
    }

    /**
     * Check if the export download link has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the export has been downloaded.
     */
    public function isDownloaded(): bool
    {
        return ! is_null($this->downloaded_at);
    }

    /**
     * Mark as completed with file details.
     */
    public function markCompleted(string $filePath, int $fileSize): bool
    {
        return $this->update([
            'status' => 'completed',
            'file_path' => $filePath,
            'file_size_bytes' => $fileSize,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as failed with error message.
     */
    public function markFailed(string $errorMessage): bool
    {
        return $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Get the download URL for this export.
     */
    public function downloadUrl(): ?string
    {
        if ($this->status !== 'completed' || ! $this->download_token) {
            return null;
        }

        return app(\Rylxes\Gdpr\Support\DownloadLinkGenerator::class)->generate($this);
    }

    /**
     * Get human-readable file size.
     */
    public function fileSizeForHumans(): ?string
    {
        if (! $this->file_size_bytes) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size_bytes;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }
}
