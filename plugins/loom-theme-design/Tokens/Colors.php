<?php
/**
 * Colors Design Tokens
 *
 * Provides color constants using CSS variables.
 * Values are managed by Theme Manager settings.
 *
 * @package Loom\ThemeManager\Tokens
 */



namespace Loom\ThemeManager\Tokens;

class Colors {
    // Primary
    public const primary = 'var(--loom-primary)';
    public const onPrimary = 'var(--loom-on-primary)';
    public const primaryContainer = 'var(--loom-primary-container)';
    public const onPrimaryContainer = 'var(--loom-on-primary-container)';

    // Secondary
    public const secondary = 'var(--loom-secondary)';
    public const onSecondary = 'var(--loom-on-secondary)';
    public const secondaryContainer = 'var(--loom-secondary-container)';
    public const onSecondaryContainer = 'var(--loom-on-secondary-container)';

    // Tertiary
    public const tertiary = 'var(--loom-tertiary)';
    public const onTertiary = 'var(--loom-on-tertiary)';
    public const tertiaryContainer = 'var(--loom-tertiary-container)';
    public const onTertiaryContainer = 'var(--loom-on-tertiary-container)';

    // Error
    public const error = 'var(--loom-error)';
    public const onError = 'var(--loom-on-error)';
    public const errorContainer = 'var(--loom-error-container)';
    public const onErrorContainer = 'var(--loom-on-error-container)';

    // Surface
    public const surface = 'var(--loom-surface)';
    public const onSurface = 'var(--loom-on-surface)';
    public const surfaceVariant = 'var(--loom-surface-variant)';
    public const onSurfaceVariant = 'var(--loom-on-surface-variant)';

    // Background
    public const background = 'var(--loom-background)';
    public const onBackground = 'var(--loom-on-background)';

    // Outline
    public const outline = 'var(--loom-outline)';
    public const outlineVariant = 'var(--loom-outline-variant)';

    // Inverse
    public const inverseSurface = 'var(--loom-inverse-surface)';
    public const inverseOnSurface = 'var(--loom-inverse-on-surface)';
    public const inversePrimary = 'var(--loom-inverse-primary)';

    // Semantic
    public const success = 'var(--loom-success)';
    public const onSuccess = 'var(--loom-on-success)';
    public const warning = 'var(--loom-warning)';
    public const onWarning = 'var(--loom-on-warning)';
    public const info = 'var(--loom-info)';
    public const onInfo = 'var(--loom-on-info)';

    // Text (aliases)
    public const text = 'var(--loom-text)';
    public const textSecondary = 'var(--loom-text-secondary)';
    public const textDisabled = 'var(--loom-text-disabled)';

    // Border
    public const border = 'var(--loom-border)';

    // Scrim & Shadow
    public const scrim = 'var(--loom-scrim)';
    public const shadow = 'var(--loom-shadow)';
}
