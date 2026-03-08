<?php

namespace Rylxes\Gdpr\Console\Commands;

use Illuminate\Console\Command;
use Rylxes\Gdpr\Jobs\DataErasureJob;
use Rylxes\Gdpr\Models\ErasureRequest;
use Rylxes\Gdpr\Support\DependencyResolver;

class EraseCommand extends Command
{
    protected $signature = 'gdpr:erase
        {user : The user ID to erase data for}
        {--force : Skip the cooling-off period and erase immediately}
        {--strategy= : Override the erasure strategy (anonymize or delete)}
        {--reason= : Reason for the erasure request}';

    protected $description = 'Initiate right to erasure for a user (GDPR Article 17)';

    public function handle(): int
    {
        $userId = $this->argument('user');
        $userModel = config('gdpr.user_model', 'App\\Models\\User');

        $user = $userModel::find($userId);

        if (! $user) {
            $this->error("User #{$userId} not found.");

            return self::FAILURE;
        }

        $strategy = $this->option('strategy') ?? config('gdpr.erasure.strategy', 'anonymize');

        if (! in_array($strategy, ErasureRequest::STRATEGIES)) {
            $this->error("Invalid strategy: {$strategy}. Supported: " . implode(', ', ErasureRequest::STRATEGIES));

            return self::FAILURE;
        }

        // Confirmation
        if (! $this->option('force')) {
            $coolingOffDays = config('gdpr.erasure.cooling_off_days', 14);
            $this->warn("This will initiate data erasure for user #{$userId}.");
            $this->line("  Strategy: {$strategy}");
            $this->line("  Cooling-off period: {$coolingOffDays} days");

            if (! $this->confirm('Do you want to continue?')) {
                $this->info('Erasure request cancelled.');

                return self::SUCCESS;
            }
        }

        $this->info("Initiating erasure for user #{$userId}...");

        /** @var \Rylxes\Gdpr\GdprManager $gdpr */
        $gdpr = app('gdpr');

        $request = $gdpr->erase(
            $user,
            $strategy,
            $this->option('reason'),
            'artisan',
        );

        // Skip cooling-off if --force
        if ($this->option('force')) {
            $request->update([
                'scheduled_at' => now(),
            ]);

            // Execute synchronously
            (new DataErasureJob($request->id))->handle(
                app(DependencyResolver::class),
            );

            $request->refresh();
            $this->info("Erasure completed immediately.");

            if ($request->metadata && isset($request->metadata['erased'])) {
                $this->newLine();
                $this->line('Erased data:');
                foreach ($request->metadata['erased'] as $model => $details) {
                    $this->line("  {$model}: {$details['count']} records ({$details['strategy']})");
                }
            }
        } else {
            $this->info("Erasure request created. Request ID: {$request->id}");
            $this->line("Scheduled for: {$request->scheduled_at->format('Y-m-d H:i:s')}");
            $this->line("The request can be cancelled before the cooling-off period ends.");
        }

        return self::SUCCESS;
    }
}
