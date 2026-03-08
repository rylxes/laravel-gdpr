<?php

namespace Rylxes\Gdpr\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ConsentLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'given_at' => 'datetime',
        'revoked_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function getTable(): string
    {
        return config('gdpr.table_prefix', 'gdpr_') . 'consent_logs';
    }

    public function getConnectionName(): ?string
    {
        return config('gdpr.database_connection') ?? parent::getConnectionName();
    }

    /**
     * The user this consent belongs to (polymorphic).
     */
    public function user(): MorphTo
    {
        return $this->morphTo('user', 'user_type', 'user_id');
    }

    /**
     * Scope: only active (non-revoked) consent records.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * Scope: filter by consent type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('consent_type', $type);
    }

    /**
     * Scope: filter by user.
     */
    public function scopeForUser(Builder $query, int|string $userId, ?string $userType = null): Builder
    {
        $query->where('user_id', $userId);

        if ($userType) {
            $query->where('user_type', $userType);
        }

        return $query;
    }

    /**
     * Scope: filter by consent version.
     */
    public function scopeOfVersion(Builder $query, string $version): Builder
    {
        return $query->where('consent_version', $version);
    }

    /**
     * Check if this consent is currently active.
     */
    public function isActive(): bool
    {
        return is_null($this->revoked_at);
    }

    /**
     * Revoke this consent.
     */
    public function revoke(): bool
    {
        return $this->update(['revoked_at' => now()]);
    }
}
