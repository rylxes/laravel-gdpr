<?php

namespace Rylxes\Gdpr;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Rylxes\Gdpr\Contracts\Deletable;
use Rylxes\Gdpr\Contracts\Exportable;
use Rylxes\Gdpr\Events\ConsentRecorded;
use Rylxes\Gdpr\Events\ErasureRequested;
use Rylxes\Gdpr\Jobs\DataErasureJob;
use Rylxes\Gdpr\Jobs\DataExportJob;
use Rylxes\Gdpr\Models\ConsentLog;
use Rylxes\Gdpr\Models\DataExport;
use Rylxes\Gdpr\Models\ErasureRequest;
use Rylxes\Gdpr\Support\DataPackager;
use Rylxes\Gdpr\Support\DependencyResolver;
use Rylxes\Gdpr\Support\DownloadLinkGenerator;

class GdprManager
{
    public function __construct(protected Application $app)
    {
    }

    /**
     * Initiate a data export for a user.
     */
    public function export(Model $user, ?string $format = null): DataExport
    {
        $format = $format ?? config('gdpr.export.default_format', 'json');

        $export = DataExport::create([
            'user_id' => $user->getKey(),
            'user_type' => get_class($user),
            'status' => 'pending',
            'format' => $format,
        ]);

        if (config('gdpr.queue.enabled', true)) {
            DataExportJob::dispatch($export->id);
        } else {
            DataExportJob::dispatchSync($export->id);
        }

        return $export;
    }

    /**
     * Initiate an erasure request for a user.
     */
    public function erase(Model $user, ?string $strategy = null, ?string $reason = null, ?string $requestedBy = null): ErasureRequest
    {
        $strategy = $strategy ?? config('gdpr.erasure.strategy', 'anonymize');
        $coolingOffDays = config('gdpr.erasure.cooling_off_days', 14);
        $scheduledAt = now()->addDays($coolingOffDays);

        $request = ErasureRequest::create([
            'user_id' => $user->getKey(),
            'user_type' => get_class($user),
            'status' => 'cooling_off',
            'strategy' => $strategy,
            'reason' => $reason,
            'requested_by' => $requestedBy ?? 'self',
            'scheduled_at' => $scheduledAt,
        ]);

        event(new ErasureRequested(
            $request->id,
            $user->getKey(),
            $strategy,
            $scheduledAt,
        ));

        if (config('gdpr.queue.enabled', true)) {
            DataErasureJob::dispatch($request->id)->delay($scheduledAt);
        } else {
            DataErasureJob::dispatchSync($request->id);
        }

        return $request;
    }

    /**
     * Record consent for a user.
     */
    public function recordConsent(Model $user, string $type, ?string $ipAddress = null, ?string $version = null, array $metadata = []): ConsentLog
    {
        $consent = ConsentLog::create([
            'user_id' => $user->getKey(),
            'user_type' => get_class($user),
            'consent_type' => $type,
            'consent_version' => $version ?? config('gdpr.consent.version', '1.0'),
            'ip_address' => $ipAddress,
            'given_at' => now(),
            'metadata' => ! empty($metadata) ? $metadata : null,
        ]);

        event(new ConsentRecorded(
            $consent->id,
            $user->getKey(),
            $type,
            $consent->consent_version,
        ));

        return $consent;
    }

    /**
     * Revoke consent for a user.
     */
    public function revokeConsent(Model $user, string $type): int
    {
        return ConsentLog::where('user_id', $user->getKey())
            ->where('user_type', get_class($user))
            ->where('consent_type', $type)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    /**
     * Check if a user has active consent of a given type.
     */
    public function hasConsent(Model $user, string $type): bool
    {
        return ConsentLog::where('user_id', $user->getKey())
            ->where('user_type', get_class($user))
            ->where('consent_type', $type)
            ->whereNull('revoked_at')
            ->exists();
    }

    /**
     * Discover and collect exportable data for a user.
     *
     * Finds all models implementing Exportable that belong to the user
     * and collects their export data.
     *
     * @return array<string, array> Keyed by export label
     */
    public function discoverExportables(Model $user): array
    {
        $data = [];
        $models = $this->findModelsImplementing(Exportable::class);

        foreach ($models as $modelClass) {
            // The user model itself
            if ($user instanceof Exportable && $modelClass === get_class($user)) {
                $data[$user->exportLabel()] = $user->exportData();
                continue;
            }

            // Related models: look for records belonging to the user
            $records = $this->findUserRecords($modelClass, $user);

            if ($records->isNotEmpty()) {
                $label = $records->first()->exportLabel();
                $data[$label] = $records->map(fn ($record) => $record->exportData())->toArray();
            }
        }

        return $data;
    }

    /**
     * Discover all Deletable model records for a user.
     *
     * @return array<array{class: string, strategy: string, records: \Illuminate\Support\Collection}>
     */
    public function discoverDeletables(Model $user): array
    {
        $groups = [];
        $models = $this->findModelsImplementing(Deletable::class);
        $resolver = $this->dependencyResolver();

        foreach ($models as $modelClass) {
            // The user model itself
            if ($user instanceof Deletable && $modelClass === get_class($user)) {
                $groups[] = $resolver->buildGroup(
                    $modelClass,
                    collect([$user]),
                    $user->erasureStrategy(),
                );
                continue;
            }

            $records = $this->findUserRecords($modelClass, $user);

            if ($records->isNotEmpty()) {
                $strategy = $resolver->resolveStrategy($records->first());
                $groups[] = $resolver->buildGroup($modelClass, $records, $strategy);
            }
        }

        return $groups;
    }

    /**
     * Get the DependencyResolver instance.
     */
    public function dependencyResolver(): DependencyResolver
    {
        return $this->app->make(DependencyResolver::class);
    }

    /**
     * Get the DataPackager instance.
     */
    public function packager(): DataPackager
    {
        return $this->app->make(DataPackager::class);
    }

    /**
     * Get the DownloadLinkGenerator instance.
     */
    public function downloadLinks(): DownloadLinkGenerator
    {
        return $this->app->make(DownloadLinkGenerator::class);
    }

    /**
     * Find all Eloquent model classes implementing a given interface.
     *
     * Scans the configured model directories for classes implementing the interface.
     * Falls back to App\Models if no model_directories config is set.
     *
     * @param class-string $interface
     * @return array<class-string>
     */
    protected function findModelsImplementing(string $interface): array
    {
        $models = [];
        $scanPaths = config('gdpr.model_directories', []);

        // Default: scan app/Models with App\Models namespace
        if (empty($scanPaths)) {
            $scanPaths = [
                [
                    'path' => app_path('Models'),
                    'namespace' => 'App\\Models',
                ],
            ];
        }

        foreach ($scanPaths as $scanConfig) {
            $path = is_array($scanConfig) ? $scanConfig['path'] : $scanConfig;
            $namespace = is_array($scanConfig) ? $scanConfig['namespace'] : 'App\\Models';

            if (! is_dir($path)) {
                continue;
            }

            $files = File::allFiles($path);

            foreach ($files as $file) {
                $className = $namespace . '\\' . str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    $file->getRelativePathname(),
                );

                if (! class_exists($className)) {
                    continue;
                }

                $reflection = new \ReflectionClass($className);

                if ($reflection->isAbstract() || $reflection->isInterface()) {
                    continue;
                }

                if ($reflection->implementsInterface($interface)) {
                    $models[] = $className;
                }
            }
        }

        return $models;
    }

    /**
     * Find records belonging to a user for a given model class.
     *
     * Tries common foreign key conventions: user_id, owner_id, author_id, etc.
     * Collects records from all matching FK columns to avoid missing data
     * when a model has multiple user-referencing columns (e.g., author_id AND reviewer_id).
     */
    protected function findUserRecords(string $modelClass, Model $user): \Illuminate\Support\Collection
    {
        $instance = new $modelClass;
        $userKey = $user->getKey();

        // Try common foreign key columns
        $foreignKeys = ['user_id', 'owner_id', 'author_id', 'created_by', 'customer_id'];
        $matchingColumns = [];

        foreach ($foreignKeys as $fk) {
            if ($this->hasColumn($instance, $fk)) {
                $matchingColumns[] = $fk;
            }
        }

        if (empty($matchingColumns)) {
            return collect();
        }

        // Build a query that matches ANY of the FK columns
        return $modelClass::where(function ($query) use ($matchingColumns, $userKey) {
            foreach ($matchingColumns as $fk) {
                $query->orWhere($fk, $userKey);
            }
        })->get();
    }

    /**
     * Check if a model's table has a given column.
     */
    protected function hasColumn(Model $model, string $column): bool
    {
        try {
            return $model->getConnection()
                ->getSchemaBuilder()
                ->hasColumn($model->getTable(), $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
