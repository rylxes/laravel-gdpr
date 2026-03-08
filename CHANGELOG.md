# Changelog

All notable changes to `laravel-gdpr` will be documented in this file.

## [1.0.0] - 2024-01-01

### Added
- Initial release
- `Exportable` and `Deletable` contracts for Eloquent models
- `HandlesGdpr` trait with consent management helpers
- Queue-backed data export with JSON, CSV, and XML format support
- Secure timed download links using Laravel signed URLs
- Right to erasure with configurable cooling-off period
- Anonymisation and hard-delete strategies
- Foreign key dependency ordering via `DependencyResolver`
- Consent log with IP, user-agent, and version tracking
- `gdpr:install` Artisan command for setup
- `gdpr:export {user}` command for manual data export
- `gdpr:erase {user}` command for manual erasure
- `gdpr:prune` command for retention-based cleanup
- `EnsureConsentGiven` middleware for route-level consent gating
- `Gdpr` facade for programmatic access
- CCPA compatibility via consent type system
- Comprehensive event system for audit integration
- Email notification with download link on export completion
