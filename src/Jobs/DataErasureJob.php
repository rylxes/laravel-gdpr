<?php

namespace Rylxes\Gdpr\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Rylxes\Gdpr\Events\DataErased;
use Rylxes\Gdpr\Models\ErasureRequest;
use Rylxes\Gdpr\Support\DependencyResolver;

class DataErasureJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 120;

    public function __construct(
        protected int $erasureRequestId,
    ) {
        $this->onQueue(config('gdpr.queue.queue_name', 'gdpr'));

        if ($connection = config('gdpr.queue.connection')) {
            $this->onConnection($connection);
        }
    }

    public function handle(DependencyResolver $resolver): void
    {
        $request = ErasureRequest::findOrFail($this->erasureRequestId);

        // Skip if already completed, cancelled, or currently processing
        if (in_array($request->status, ['cancelled', 'completed', 'processing'], true)) {
            return;
        }

        // Re-dispatch if still in cooling-off period
        if ($request->isInCoolingOff()) {
            // Only re-dispatch on async queues to prevent infinite loops on sync driver
            if (config('queue.default') === 'sync') {
                return;
            }

            self::dispatch($this->erasureRequestId)
                ->delay($request->scheduled_at);

            return;
        }

        $request->markProcessing();

        try {
            $user = $request->user;

            if (! $user) {
                throw new \Rylxes\Gdpr\Exceptions\ErasureException(
                    "User not found for erasure request #{$this->erasureRequestId}"
                );
            }

            $gdprManager = app('gdpr');

            // 1. Discover all Deletable models for this user
            $deletables = $gdprManager->discoverDeletables($user);

            // 2. Order by dependency priority (FK-safe ordering)
            $ordered = $resolver->resolve($deletables);

            // 3. Execute erasure within a transaction
            $auditData = [];

            DB::transaction(function () use ($ordered, &$auditData) {
                foreach ($ordered as $modelGroup) {
                    $strategy = $modelGroup['strategy'];
                    $count = 0;

                    foreach ($modelGroup['records'] as $record) {
                        if ($strategy === 'delete') {
                            // Use forceDelete if SoftDeletes is available, otherwise delete
                            if (method_exists($record, 'forceDelete')) {
                                $record->forceDelete();
                            } else {
                                $record->delete();
                            }
                        } else {
                            $record->eraseData();
                        }
                        $count++;
                    }

                    $auditData[$modelGroup['class']] = [
                        'strategy' => $strategy,
                        'count' => $count,
                    ];
                }
            });

            // 4. Mark completed with audit data
            $request->markCompleted($auditData);

            // 5. Fire event
            event(new DataErased(
                $request->id,
                $user->getKey(),
                $request->strategy,
                array_map(fn ($d) => $d['count'], $auditData),
            ));
        } catch (\Throwable $e) {
            $request->update([
                'status' => 'failed',
                'metadata' => array_merge($request->metadata ?? [], [
                    'last_error' => $e->getMessage(),
                    'last_error_at' => now()->toIso8601String(),
                ]),
            ]);

            throw $e;
        }
    }
}
