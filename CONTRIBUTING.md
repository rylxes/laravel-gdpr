# Contributing to Laravel GDPR

Thank you for considering contributing to Laravel GDPR! This document provides guidelines and instructions for contributing.

## Development Setup

1. Fork and clone the repository:

```bash
git clone https://github.com/your-username/laravel-gdpr.git
cd laravel-gdpr
```

2. Install dependencies:

```bash
composer install
```

3. Run tests to verify your setup:

```bash
composer test
```

## Making Changes

1. Create a new branch for your feature or fix:

```bash
git checkout -b feature/your-feature-name
```

2. Make your changes, following the existing code style and conventions.

3. Add or update tests for your changes.

4. Ensure all tests pass:

```bash
composer test
```

5. Commit your changes with a clear, descriptive message.

## Code Style

- Follow PSR-12 coding standards
- Use type declarations for parameters and return types
- Add PHPDoc blocks for public methods
- Use `#[Test]` attributes (not `@test` annotations) for PHPUnit tests
- Keep methods focused and single-purpose
- Use meaningful variable and method names

## Testing

- Write unit tests for all new functionality
- Tests should extend `PHPUnit\Framework\TestCase`
- Use reflection-based testing for structure validation
- Use Mockery for mocking dependencies when needed
- Aim for comprehensive coverage of public API surfaces

### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage report
composer test-coverage
```

## Pull Requests

1. Update the CHANGELOG.md with your changes under an `[Unreleased]` section
2. Ensure all tests pass and no new warnings are introduced
3. Provide a clear description of what your PR does and why
4. Reference any related issues

## Reporting Issues

When reporting issues, please include:

- PHP and Laravel version
- Package version
- Steps to reproduce
- Expected vs actual behavior
- Any relevant error messages or stack traces

## Security Vulnerabilities

If you discover a security vulnerability, please email rylxes@gmail.com directly instead of using the issue tracker.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
