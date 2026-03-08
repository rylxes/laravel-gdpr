# Laravel GDPR

> **[Full Documentation](https://rylxes.com/docs/laravel-gdpr)** — Complete usage guide, configuration reference, and API docs.

[![Latest Version](https://img.shields.io/packagist/v/rylxes/laravel-gdpr.svg?style=flat-square)](https://packagist.org/packages/rylxes/laravel-gdpr)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg?style=flat-square)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-10.x%20%7C%2011.x%20%7C%2012.x-red.svg?style=flat-square)](https://laravel.com)

GDPR and CCPA compliance toolkit for Laravel applications. Provides data export (portability), right to erasure, consent management, and audit trails in a single package.

## Features

- **Data Export (Portability)** - Queue-backed export of user data as JSON, CSV, or XML with secure timed download links
- **Right to Erasure** - Orchestrated deletion or anonymisation respecting foreign key dependencies
- **Consent Management** - Audit-ready consent log with IP, user-agent, and version tracking
- **Cooling-Off Period** - Configurable delay before erasure execution, allowing cancellation
- **Artisan Commands** - `gdpr:export`, `gdpr:erase`, `gdpr:prune` for compliance officer workflows
- **Consent Middleware** - Gate routes by consent type with `gdpr.consent:marketing`
- **Signed Download Links** - Time-limited, tamper-proof URLs via Laravel's signed routes
- **CCPA Compatible** - "Do not sell" opt-out support via the consent type system
- **Event System** - `DataExported`, `DataErased`, `ConsentRecorded`, `ErasureRequested` events
- **Retention Policies** - Configurable auto-cleanup for exports and audit logs
- **Polymorphic Users** - Works with any authenticatable model, not just `App\Models\User`
- **Facade & Trait API** - Use `Gdpr::export($user)` or `$user->recordConsent('marketing')`

## Installation

### 1. Install via Composer

```bash
composer require rylxes/laravel-gdpr
```

### 2. Run the installer

```bash
php artisan gdpr:install
```

This publishes the configuration file and runs migrations.

### 3. Implement contracts on your models

```php
use Rylxes\Gdpr\Contracts\Exportable;
use Rylxes\Gdpr\Contracts\Deletable;
use Rylxes\Gdpr\Concerns\HandlesGdpr;

class User extends Authenticatable implements Exportable, Deletable
{
    use HandlesGdpr;

    public function exportData(): array
    {
        return $this->only(['name', 'email', 'phone', 'created_at']);
    }

    public function eraseData(): void
    {
        $this->anonymise(['name', 'email', 'phone', 'address']);
    }
}
```

Apply `Exportable` and `Deletable` to any model containing personal data:

```php
class Order extends Model implements Exportable, Deletable
{
    use HandlesGdpr;

    public function exportData(): array
    {
        return $this->only(['id', 'total', 'status', 'created_at']);
    }

    public function eraseData(): void
    {
        $this->anonymise(['shipping_address', 'billing_address']);
    }

    // Child records erased before parent (lower priority = erased first)
    public function erasurePriority(): int
    {
        return 50;
    }
}
```

## Usage

### Data Export

```php
use Rylxes\Gdpr\Facades\Gdpr;

// Dispatch an export job (user gets email with download link)
$export = Gdpr::export($user);
$export = Gdpr::export($user, 'csv'); // CSV format

// Via Artisan
php artisan gdpr:export 42
php artisan gdpr:export 42 --format=csv
php artisan gdpr:export 42 --sync  // Run synchronously
```

### Right to Erasure

```php
// Initiate erasure with cooling-off period
$request = Gdpr::erase($user);
$request = Gdpr::erase($user, 'delete', 'User requested account deletion');

// Cancel during cooling-off
$request->cancel('User changed their mind');

// Via Artisan
php artisan gdpr:erase 42
php artisan gdpr:erase 42 --force     // Skip cooling-off
php artisan gdpr:erase 42 --strategy=delete
```

### Consent Management

```php
// Record consent
$user->recordConsent('marketing', '1.0', $request->ip());
$user->recordConsent('analytics');

// Or via facade
Gdpr::recordConsent($user, 'terms_of_service', $request->ip());

// Check consent
$user->hasConsent('marketing');         // true/false
Gdpr::hasConsent($user, 'marketing');   // true/false

// Revoke consent
$user->revokeConsent('marketing');

// Get all active consent types
$user->activeConsentTypes();  // ['analytics', 'terms_of_service']

// Query consent logs
$user->consentLogs()->active()->get();
```

### Consent Middleware

Gate routes that require specific consent:

```php
Route::middleware('gdpr.consent:marketing')->group(function () {
    Route::get('/promotional-offers', [OffersController::class, 'index']);
});

Route::middleware('gdpr.consent:analytics,tracking')->group(function () {
    // Requires both analytics AND tracking consent
});
```

### Data Cleanup

```php
// Prune expired exports and old audit logs
php artisan gdpr:prune
php artisan gdpr:prune --force  // Skip confirmation

// Schedule automatic pruning (in app/Console/Kernel.php)
$schedule->command('gdpr:prune --force')->daily();
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=gdpr-config
```

### Key Configuration Options

| Option | Default | Description |
|--------|---------|-------------|
| `export.default_format` | `json` | Default export format (json, csv, xml) |
| `export.storage_disk` | `local` | Filesystem disk for export files |
| `export.download_link_expiry_minutes` | `60` | Download link lifetime |
| `erasure.strategy` | `anonymize` | Default: anonymize or delete |
| `erasure.cooling_off_days` | `14` | Days before erasure executes |
| `consent.version` | `1.0` | Current consent version |
| `consent.log_ip_address` | `true` | Log IP with consent events |
| `queue.enabled` | `true` | Queue export/erasure jobs |
| `queue.queue_name` | `gdpr` | Queue name for GDPR jobs |
| `audit.consent_logs_retention_days` | `2555` | ~7 years retention |

### Per-Model Strategy Overrides

```php
// config/gdpr.php
'erasure' => [
    'strategy' => 'anonymize',  // default
    'model_strategies' => [
        App\Models\Comment::class => 'delete',
        App\Models\Order::class => 'anonymize',
    ],
],
```

### Environment Variables

```env
GDPR_ENABLED=true
GDPR_QUEUE_ENABLED=true
GDPR_QUEUE_NAME=gdpr
GDPR_ERASURE_STRATEGY=anonymize
GDPR_COOLING_OFF_DAYS=14
GDPR_EXPORT_FORMAT=json
GDPR_DOWNLOAD_EXPIRY=60
GDPR_CONSENT_VERSION=1.0
GDPR_LOG_IP=true
GDPR_CCPA_ENABLED=false
```

## Events

Listen to GDPR events for custom integrations:

| Event | When |
|-------|------|
| `DataExported` | After a data export is completed |
| `DataErased` | After user data has been erased |
| `ConsentRecorded` | When a user gives consent |
| `ErasureRequested` | When an erasure request is created |

```php
// EventServiceProvider
protected $listen = [
    \Rylxes\Gdpr\Events\DataErased::class => [
        \App\Listeners\NotifyDpoOfErasure::class,
    ],
];
```

## Database Schema

| Table | Purpose |
|-------|---------|
| `gdpr_consent_logs` | Consent events with timestamps, IP, and version |
| `gdpr_erasure_requests` | Erasure request lifecycle and audit trail |
| `gdpr_data_exports` | Export records with download tokens and status |

All tables use a configurable prefix (`gdpr_` by default).

## Testing

```bash
composer test
```

### Local Development

Add the package as a path repository in your Laravel app's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../path/to/laravel-gdpr"
        }
    ],
    "require": {
        "rylxes/laravel-gdpr": "*"
    }
}
```

Then run:

```bash
composer update rylxes/laravel-gdpr
php artisan gdpr:install
```

## Security

- Download links use Laravel's `URL::temporarySignedRoute()` for tamper-proof, time-limited access
- Export files are stored on a configurable disk (default: `local`, not publicly accessible)
- Consent logs record IP addresses for audit trail compliance
- The cooling-off period prevents accidental data loss
- All GDPR operations are logged with metadata for compliance audits

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Credits

- [Sherriff Agboola](https://github.com/rylxes)
- [All Contributors](../../contributors)

## Support

- [Issues](https://github.com/rylxes/laravel-gdpr/issues)
- [Discussions](https://github.com/rylxes/laravel-gdpr/discussions)
- Email: rylxes@gmail.com
