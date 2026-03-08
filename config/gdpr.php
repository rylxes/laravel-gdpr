<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GDPR Compliance Master Switch
    |--------------------------------------------------------------------------
    |
    | Enable or disable the GDPR compliance features globally.
    |
    */
    'enabled' => env('GDPR_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | The database connection used for GDPR tables. Set to null to use
    | the application's default database connection.
    |
    */
    'database_connection' => env('GDPR_DB_CONNECTION', null),

    /*
    |--------------------------------------------------------------------------
    | Table Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix applied to all GDPR database tables. This allows the package
    | tables to coexist with your application tables without conflicts.
    |
    */
    'table_prefix' => env('GDPR_TABLE_PREFIX', 'gdpr_'),

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The fully qualified class name of your User model. This is used by
    | Artisan commands and the GdprManager to resolve user instances.
    |
    */
    'user_model' => env('GDPR_USER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Model Directories
    |--------------------------------------------------------------------------
    |
    | Directories to scan for Exportable and Deletable model classes.
    | Each entry should be an array with 'path' and 'namespace' keys.
    | If left empty, defaults to app/Models with App\Models namespace.
    |
    | Example:
    |  [
    |      ['path' => app_path('Models'), 'namespace' => 'App\\Models'],
    |      ['path' => app_path('Domain/Billing/Models'), 'namespace' => 'App\\Domain\\Billing\\Models'],
    |  ]
    |
    */
    'model_directories' => [],

    /*
    |--------------------------------------------------------------------------
    | Data Export Settings
    |--------------------------------------------------------------------------
    |
    | Configure how personal data exports are generated, stored, and
    | delivered to users.
    |
    */
    'export' => [
        // Supported export formats
        'formats' => ['json', 'csv', 'xml'],

        // Default export format when none specified
        'default_format' => env('GDPR_EXPORT_FORMAT', 'json'),

        // Filesystem disk for storing export archives
        'storage_disk' => env('GDPR_EXPORT_DISK', 'local'),

        // Directory within the disk for export files
        'storage_path' => env('GDPR_EXPORT_PATH', 'gdpr-exports'),

        // Minutes before the download link expires
        'download_link_expiry_minutes' => (int) env('GDPR_DOWNLOAD_EXPIRY', 60),

        // Maximum export file size in megabytes
        'max_export_size_mb' => (int) env('GDPR_MAX_EXPORT_SIZE', 100),

        // Days before completed exports are cleaned up
        'cleanup_after_days' => (int) env('GDPR_EXPORT_CLEANUP_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Right to Erasure Settings
    |--------------------------------------------------------------------------
    |
    | Configure how personal data erasure requests are handled. Supports
    | anonymisation (nullify personal fields) or full deletion.
    |
    */
    'erasure' => [
        // Default strategy: 'anonymize' preserves records with nullified PII,
        // 'delete' removes records entirely via forceDelete()
        'strategy' => env('GDPR_ERASURE_STRATEGY', 'anonymize'),

        // Cooling-off period in days before erasure is executed.
        // Allows users to cancel their request within this window.
        'cooling_off_days' => (int) env('GDPR_COOLING_OFF_DAYS', 14),

        // Whether to respect foreign key ordering during erasure
        'respect_foreign_keys' => true,

        // Per-model strategy overrides.
        // Example: ['App\Models\Order' => 'anonymize', 'App\Models\Comment' => 'delete']
        'model_strategies' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Consent Management
    |--------------------------------------------------------------------------
    |
    | Configure how user consent is tracked and managed for
    | compliance audit purposes.
    |
    */
    'consent' => [
        // Current consent version (update when T&C change)
        'version' => env('GDPR_CONSENT_VERSION', '1.0'),

        // Whether explicit opt-in is required (GDPR) vs opt-out (CCPA)
        'require_explicit' => true,

        // Log the IP address with each consent event
        'log_ip_address' => (bool) env('GDPR_LOG_IP', true),

        // Log the user agent string with each consent event
        'log_user_agent' => (bool) env('GDPR_LOG_USER_AGENT', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how GDPR jobs (export, erasure) are dispatched. Disable
    | to run jobs synchronously.
    |
    */
    'queue' => [
        'enabled' => (bool) env('GDPR_QUEUE_ENABLED', true),

        // Queue connection (null = default)
        'connection' => env('GDPR_QUEUE_CONNECTION', null),

        // Queue name for GDPR jobs
        'queue_name' => env('GDPR_QUEUE_NAME', 'gdpr'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configure notifications sent during GDPR operations.
    |
    */
    'notifications' => [
        'export_ready' => [
            // Send email when export is ready for download
            'mail_enabled' => (bool) env('GDPR_MAIL_ENABLED', true),

            // From address (null = application default)
            'from_address' => env('GDPR_MAIL_FROM', null),

            // From name (null = application default)
            'from_name' => env('GDPR_MAIL_FROM_NAME', null),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit & Retention
    |--------------------------------------------------------------------------
    |
    | Configure how long GDPR audit records are retained. Consent logs
    | default to ~7 years as GDPR requires proof of consent for the
    | duration it was relied upon plus statute of limitations.
    |
    */
    'audit' => [
        // Consent log retention in days (~7 years)
        'consent_logs_retention_days' => (int) env('GDPR_CONSENT_LOG_RETENTION_DAYS', 2555),

        // Erasure request log retention in days (~7 years)
        'erasure_logs_retention_days' => (int) env('GDPR_ERASURE_LOG_RETENTION_DAYS', 2555),

        // Export log retention in days (1 year)
        'export_logs_retention_days' => (int) env('GDPR_EXPORT_LOG_RETENTION_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Configure the download endpoint route for data exports.
    |
    */
    'routes' => [
        // Enable the built-in download route
        'enabled' => (bool) env('GDPR_ROUTES_ENABLED', true),

        // URL prefix for GDPR routes
        'prefix' => env('GDPR_ROUTE_PREFIX', 'gdpr'),

        // Middleware applied to GDPR routes
        'middleware' => ['web', 'signed'],
    ],

    /*
    |--------------------------------------------------------------------------
    | CCPA Compatibility
    |--------------------------------------------------------------------------
    |
    | Enable CCPA-specific features alongside GDPR. The consent log
    | system handles "do not sell" opt-outs as a consent type.
    |
    */
    'ccpa' => [
        'enabled' => (bool) env('GDPR_CCPA_ENABLED', false),

        // Database field name for the "do not sell" flag
        'do_not_sell_field' => 'do_not_sell_personal_info',
    ],

];
