<?php

namespace Rylxes\Gdpr\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rylxes\Gdpr\Events\ConsentRecorded;
use Rylxes\Gdpr\Models\ConsentLog;

trait HandlesGdpr
{
    /**
     * Default export label: pluralized class short name.
     */
    public function exportLabel(): string
    {
        return str(class_basename(static::class))->plural()->headline()->toString();
    }

    /**
     * Default erasure strategy from config, with per-model override support.
     */
    public function erasureStrategy(): string
    {
        $modelClass = static::class;
        $overrides = config('gdpr.erasure.model_strategies', []);

        return $overrides[$modelClass] ?? config('gdpr.erasure.strategy', 'anonymize');
    }

    /**
     * Default erasure priority. Lower = erased first.
     */
    public function erasurePriority(): int
    {
        return 100;
    }

    /**
     * Anonymize specific columns by setting them to null.
     *
     * @param array<string> $columns
     */
    public function anonymise(array $columns = []): void
    {
        if (empty($columns)) {
            return;
        }

        $data = [];
        foreach ($columns as $column) {
            $data[$column] = null;
        }

        $this->forceFill($data)->saveQuietly();
    }

    /**
     * Record a consent event for this user.
     */
    public function recordConsent(
        string $type,
        ?string $version = null,
        ?string $ipAddress = null,
        array $metadata = [],
    ): ConsentLog {
        $consent = ConsentLog::create([
            'user_id' => $this->getKey(),
            'user_type' => get_class($this),
            'consent_type' => $type,
            'consent_version' => $version ?? config('gdpr.consent.version', '1.0'),
            'ip_address' => $ipAddress,
            'user_agent' => config('gdpr.consent.log_user_agent', false) ? (request()->userAgent() ?? null) : null,
            'given_at' => now(),
            'metadata' => ! empty($metadata) ? $metadata : null,
        ]);

        event(new ConsentRecorded(
            $consent->id,
            $this->getKey(),
            $type,
            $consent->consent_version,
        ));

        return $consent;
    }

    /**
     * Check if the user has active consent of a specific type.
     */
    public function hasConsent(string $type): bool
    {
        return ConsentLog::where('user_id', $this->getKey())
            ->where('user_type', get_class($this))
            ->where('consent_type', $type)
            ->whereNull('revoked_at')
            ->exists();
    }

    /**
     * Revoke consent of a specific type.
     */
    public function revokeConsent(string $type): int
    {
        return ConsentLog::where('user_id', $this->getKey())
            ->where('user_type', get_class($this))
            ->where('consent_type', $type)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    /**
     * Get all consent logs for this user.
     */
    public function consentLogs(): MorphMany
    {
        return $this->morphMany(ConsentLog::class, 'user', 'user_type', 'user_id');
    }

    /**
     * Get all active consent types for this user.
     *
     * @return array<string>
     */
    public function activeConsentTypes(): array
    {
        return ConsentLog::where('user_id', $this->getKey())
            ->where('user_type', get_class($this))
            ->whereNull('revoked_at')
            ->pluck('consent_type')
            ->unique()
            ->values()
            ->toArray();
    }
}
