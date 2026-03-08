<?php

namespace Rylxes\Gdpr\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'gdpr:install {--force : Overwrite existing configuration files}';

    protected $description = 'Install the Laravel GDPR Compliance Package';

    public function handle(): int
    {
        $this->info('Installing Laravel GDPR Package...');
        $this->newLine();

        // Publish configuration
        $this->call('vendor:publish', [
            '--tag' => 'gdpr-config',
            '--force' => $this->option('force'),
        ]);
        $this->info('Configuration file published.');

        // Run migrations
        if ($this->confirm('Run database migrations now?', true)) {
            $this->call('migrate');
            $this->info('Migrations executed successfully.');
        } else {
            $this->warn('Skipped migrations. Run "php artisan migrate" when ready.');
        }

        // Display .env guidance
        $this->newLine();
        $this->info('Add these variables to your .env file as needed:');
        $this->newLine();
        $this->line('GDPR_ENABLED=true');
        $this->line('GDPR_QUEUE_ENABLED=true');
        $this->line('GDPR_QUEUE_NAME=gdpr');
        $this->line('GDPR_ERASURE_STRATEGY=anonymize');
        $this->line('GDPR_COOLING_OFF_DAYS=14');
        $this->line('GDPR_EXPORT_FORMAT=json');
        $this->line('GDPR_DOWNLOAD_EXPIRY=60');
        $this->line('GDPR_CONSENT_VERSION=1.0');

        // Display usage instructions
        $this->newLine();
        $this->info('Next steps:');
        $this->newLine();
        $this->line('1. Add the Exportable and Deletable interfaces to your User model:');
        $this->newLine();
        $this->line('   use Rylxes\Gdpr\Contracts\Exportable;');
        $this->line('   use Rylxes\Gdpr\Contracts\Deletable;');
        $this->line('   use Rylxes\Gdpr\Concerns\HandlesGdpr;');
        $this->newLine();
        $this->line('   class User extends Model implements Exportable, Deletable');
        $this->line('   {');
        $this->line('       use HandlesGdpr;');
        $this->newLine();
        $this->line('       public function exportData(): array');
        $this->line('       {');
        $this->line('           return $this->only([\'name\', \'email\', \'created_at\']);');
        $this->line('       }');
        $this->newLine();
        $this->line('       public function eraseData(): void');
        $this->line('       {');
        $this->line('           $this->anonymise([\'name\', \'email\', \'phone\']);');
        $this->line('       }');
        $this->line('   }');
        $this->newLine();
        $this->line('2. Apply the same pattern to other models containing personal data.');
        $this->newLine();
        $this->line('3. Use the consent middleware on routes requiring consent:');
        $this->line('   Route::middleware(\'gdpr.consent:marketing\')->group(...)');
        $this->newLine();

        $this->info('Installation complete!');

        return self::SUCCESS;
    }
}
