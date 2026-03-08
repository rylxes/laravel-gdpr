<?php

namespace Rylxes\Gdpr\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ErasureRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'processed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    /** @var array<string> Valid status values */
    public const STATUSES = ['pending', 'cooling_off', 'processing', 'completed', 'cancelled', 'failed'];

    /** @var array<string> Valid strategy values */
    public const STRATEGIES = ['anonymize', 'delete'];

    public function getTable(): string
    {
        return config('gdpr.table_prefix', 'gdpr_') . 'erasure_requests';
    }

    public function getConnectionName(): ?string
    {
        return config('gdpr.database_connection') ?? parent::getConnectionName();
    }

    /**
     * The user this erasure request belongs to (polymorphic).
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }

    /**
     * Scope: pending requests.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: requests in cooling-off period.
     */
    public function scopeCoolingOff(Builder $query): Builder
    {
        return $query->where('status', 'cooling_off');
    }

    /**
     * Scope: requests ready to process (cooling-off period expired).
     */
    public function scopeReadyToProcess(Builder $query): Builder
    {
        return $query->where('status', 'cooling_off')
            ->where('scheduled_at', '<=', now());
    }

    /**
     * Scope: completed requests.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if the request is in the cooling-off period.
     */
    public function isInCoolingOff(): bool
    {
        return $this->status === 'cooling_off'
            && $this->scheduled_at
            && $this->scheduled_at->isFuture();
    }

    /**
     * Check if the request can be cancelled.
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'cooling_off']);
    }

    /**
     * Mark as processing.
     */
    public function markProcessing(): bool
    {
        return $this->update(['status' => 'processing']);
    }

    /**
     * Mark as completed with audit data.
     */
    public function markCompleted(array $auditData = []): bool
    {
        return $this->update([
            'status' => 'completed',
            'processed_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], ['erased' => $auditData]),
        ]);
    }

    /**
     * Cancel the erasure request.
     */
    public function cancel(?string $reason = null): bool
    {
        $data = [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ];

        if ($reason) {
            $data['metadata'] = array_merge($this->metadata ?? [], ['cancel_reason' => $reason]);
        }

        return $this->update($data);
    }
}
