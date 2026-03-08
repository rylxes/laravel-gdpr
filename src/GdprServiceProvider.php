<?php

namespace Rylxes\Gdpr;

use Illuminate\Support\ServiceProvider;
use Rylxes\Gdpr\Console\Commands\EraseCommand;
use Rylxes\Gdpr\Console\Commands\ExportCommand;
use Rylxes\Gdpr\Console\Commands\InstallCommand;
use Rylxes\Gdpr\Console\Commands\PruneCommand;
use Rylxes\Gdpr\Support\DataPackager;
use Rylxes\Gdpr\Support\DependencyResolver;
use Rylxes\Gdpr\Support\DownloadLinkGenerator;

class GdprServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/gdpr.php', 'gdpr');

        // Register singletons
        $this->app->singleton(DependencyResolver::class);
        $this->app->singleton(DataPackager::class);
        $this->app->singleton(DownloadLinkGenerator::class);

        // Register facade accessor
        $this->app->singleton('gdpr', function ($app) {
            return new GdprManager($app);
        });
    }

    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/gdpr.php' => config_path('gdpr.php'),
        ], 'gdpr-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'gdpr-migrations');

        // Load migrations from package
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load routes (download endpoint)
        if (config('gdpr.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/gdpr.php');
        }

        // Register middleware alias
        $router = $this->app['router'];
        $router->aliasMiddleware('gdpr.consent', \Rylxes\Gdpr\Http\Middleware\EnsureConsentGiven::class);

        // Register Artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                ExportCommand::class,
                EraseCommand::class,
                PruneCommand::class,
            ]);
        }
    }

    public function provides(): array
    {
        return [
            'gdpr',
            DependencyResolver::class,
            DataPackager::class,
            DownloadLinkGenerator::class,
        ];
    }
}
