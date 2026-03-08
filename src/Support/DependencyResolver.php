<?php

namespace Rylxes\Gdpr\Support;

use Illuminate\Database\Eloquent\Model;
use Rylxes\Gdpr\Contracts\Deletable;

class DependencyResolver
{
    /**
     * Resolve the deletion order for Deletable model groups.
     *
     * Sorts by erasurePriority() ascending (children first, parents last)
     * to respect foreign key constraints during deletion.
     *
     * @param array<array{class: string, strategy: string, records: \Illuminate\Support\Collection}> $deletableGroups
     * @return array<array{class: string, strategy: string, records: \Illuminate\Support\Collection}>
     */
    public function resolve(array $deletableGroups): array
    {
        usort($deletableGroups, function (array $a, array $b) {
            $priorityA = $this->getPriority($a);
            $priorityB = $this->getPriority($b);

            return $priorityA <=> $priorityB;
        });

        return $deletableGroups;
    }

    /**
     * Get the erasure priority for a group.
     */
    protected function getPriority(array $group): int
    {
        $records = $group['records'] ?? collect();
        $first = $records->first();

        if ($first instanceof Deletable) {
            return $first->erasurePriority();
        }

        return 100;
    }

    /**
     * Build a deletable group entry.
     *
     * @param class-string $modelClass
     * @param \Illuminate\Support\Collection $records
     * @param string $strategy
     * @return array{class: string, strategy: string, records: \Illuminate\Support\Collection}
     */
    public function buildGroup(string $modelClass, $records, string $strategy): array
    {
        return [
            'class' => $modelClass,
            'strategy' => $strategy,
            'records' => $records,
        ];
    }

    /**
     * Determine the erasure strategy for a model class.
     *
     * Checks per-model config overrides, then falls back to default strategy.
     */
    public function resolveStrategy(Model $model): string
    {
        if ($model instanceof Deletable) {
            return $model->erasureStrategy();
        }

        $modelClass = get_class($model);
        $overrides = config('gdpr.erasure.model_strategies', []);

        return $overrides[$modelClass] ?? config('gdpr.erasure.strategy', 'anonymize');
    }
}
