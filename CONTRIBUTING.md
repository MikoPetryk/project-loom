# Contributing to Loom

Thank you for your interest in contributing to Loom! This document provides guidelines for contributing to the project.

## How to Contribute

### Reporting Issues

1. Check existing issues to avoid duplicates
2. Use the issue template when available
3. Provide clear steps to reproduce bugs
4. Include PHP version, WordPress version, and browser info

### Submitting Changes

1. Fork the repository
2. Create a feature branch from `main`
3. Make your changes
4. Test thoroughly
5. Submit a pull request

### Branch Naming

- `feature/description` - New features
- `fix/description` - Bug fixes
- `docs/description` - Documentation updates

## Development Setup

### Requirements

- PHP 8.1 or higher
- WordPress 6.0 or higher
- Composer (for development)

### Local Setup

1. Clone your fork
2. Set up a local WordPress installation
3. Symlink or copy plugins to `wp-content/plugins/`
4. Symlink or copy theme to `wp-content/themes/`
5. Activate Loom Core plugin first

## Code Standards

### PHP

- Follow PSR-12 coding standards
- Use strict types: `declare(strict_types=1);`
- Use PHP 8.1+ features (named arguments, enums, etc.)
- Add type hints for parameters and return types

### Components

- Use design tokens instead of hardcoded values
- Follow existing component patterns
- Document public APIs with PHPDoc

### Example

```php
<?php
declare(strict_types=1);

namespace Loom\Core\Components;

function MyComponent(
    string $content,
    ?Modifier $modifier = null
): void {
    // Implementation
}
```

## Pull Request Guidelines

1. Keep changes focused and atomic
2. Update documentation if needed
3. Add tests for new features
4. Ensure all tests pass
5. Follow the existing code style

## Questions?

Open an issue with the "question" label or start a discussion.

Thank you for contributing!
