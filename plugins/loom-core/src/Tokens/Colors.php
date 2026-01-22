<?php
/**
 * Colors Design Tokens
 *
 * Provides color constants with CSS variable fallbacks.
 * Works standalone, enhanced when Theme Manager is active.
 *
 * @package Loom\Core\Tokens
 */



namespace Loom\Core\Tokens;

class Colors {
    // Primary
    public const primary = 'var(--loom-primary, #336659)';
    public const onPrimary = 'var(--loom-on-primary, #ffffff)';
    public const primaryContainer = 'var(--loom-primary-container, #b6f2e0)';
    public const onPrimaryContainer = 'var(--loom-on-primary-container, #00201a)';

    // Secondary
    public const secondary = 'var(--loom-secondary, #4a635c)';
    public const onSecondary = 'var(--loom-on-secondary, #ffffff)';
    public const secondaryContainer = 'var(--loom-secondary-container, #cce8df)';
    public const onSecondaryContainer = 'var(--loom-on-secondary-container, #06201a)';

    // Tertiary
    public const tertiary = 'var(--loom-tertiary, #416277)';
    public const onTertiary = 'var(--loom-on-tertiary, #ffffff)';
    public const tertiaryContainer = 'var(--loom-tertiary-container, #c4e7ff)';
    public const onTertiaryContainer = 'var(--loom-on-tertiary-container, #001e2d)';

    // Error
    public const error = 'var(--loom-error, #ba1a1a)';
    public const onError = 'var(--loom-on-error, #ffffff)';
    public const errorContainer = 'var(--loom-error-container, #ffdad6)';
    public const onErrorContainer = 'var(--loom-on-error-container, #410002)';

    // Surface
    public const surface = 'var(--loom-surface, #f8faf8)';
    public const onSurface = 'var(--loom-on-surface, #191c1b)';
    public const surfaceVariant = 'var(--loom-surface-variant, #dbe5e0)';
    public const onSurfaceVariant = 'var(--loom-on-surface-variant, #3f4945)';

    // Background
    public const background = 'var(--loom-background, #f8faf8)';
    public const onBackground = 'var(--loom-on-background, #191c1b)';

    // Outline
    public const outline = 'var(--loom-outline, #6f7975)';
    public const outlineVariant = 'var(--loom-outline-variant, #bfc9c4)';

    // Inverse
    public const inverseSurface = 'var(--loom-inverse-surface, #2d312f)';
    public const inverseOnSurface = 'var(--loom-inverse-on-surface, #eff1ef)';
    public const inversePrimary = 'var(--loom-inverse-primary, #9ad6c5)';

    // Semantic
    public const success = 'var(--loom-success, #2e7d32)';
    public const onSuccess = 'var(--loom-on-success, #ffffff)';
    public const warning = 'var(--loom-warning, #ed6c02)';
    public const onWarning = 'var(--loom-on-warning, #ffffff)';
    public const info = 'var(--loom-info, #0288d1)';
    public const onInfo = 'var(--loom-on-info, #ffffff)';

    // Text (aliases for convenience)
    public const text = 'var(--loom-text, #191c1b)';
    public const textSecondary = 'var(--loom-text-secondary, #3f4945)';
    public const textDisabled = 'var(--loom-text-disabled, #6f7975)';

    // Border
    public const border = 'var(--loom-border, #bfc9c4)';

    // Scrim
    public const scrim = 'var(--loom-scrim, #000000)';

    // Shadow
    public const shadow = 'var(--loom-shadow, #000000)';
}
