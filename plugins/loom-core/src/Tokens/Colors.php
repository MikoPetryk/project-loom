<?php
/**
 * Colors Design Tokens
 *
 * Dynamic color tokens that integrate with Theme Design plugin.
 * When Theme Design is active, colors come from user settings.
 * Otherwise, falls back to CSS variables with defaults.
 *
 * @package Loom\Core\Tokens
 */

namespace Loom\Core\Tokens;

class Colors {

    private static bool $initialized = false;
    private static array $colors = [];

    /**
     * Default colors (CSS variable references with fallbacks)
     */
    private static array $defaults = [
        // Primary
        'primary' => 'var(--loom-primary, #336659)',
        'onPrimary' => 'var(--loom-on-primary, #ffffff)',
        'primaryContainer' => 'var(--loom-primary-container, #b6f2e0)',
        'onPrimaryContainer' => 'var(--loom-on-primary-container, #00201a)',

        // Secondary
        'secondary' => 'var(--loom-secondary, #4a635c)',
        'onSecondary' => 'var(--loom-on-secondary, #ffffff)',
        'secondaryContainer' => 'var(--loom-secondary-container, #cce8df)',
        'onSecondaryContainer' => 'var(--loom-on-secondary-container, #06201a)',

        // Tertiary
        'tertiary' => 'var(--loom-tertiary, #416277)',
        'onTertiary' => 'var(--loom-on-tertiary, #ffffff)',
        'tertiaryContainer' => 'var(--loom-tertiary-container, #c4e7ff)',
        'onTertiaryContainer' => 'var(--loom-on-tertiary-container, #001e2d)',

        // Error
        'error' => 'var(--loom-error, #ba1a1a)',
        'onError' => 'var(--loom-on-error, #ffffff)',
        'errorContainer' => 'var(--loom-error-container, #ffdad6)',
        'onErrorContainer' => 'var(--loom-on-error-container, #410002)',

        // Surface
        'surface' => 'var(--loom-surface, #f8faf8)',
        'onSurface' => 'var(--loom-on-surface, #191c1b)',
        'surfaceVariant' => 'var(--loom-surface-variant, #dbe5e0)',
        'onSurfaceVariant' => 'var(--loom-on-surface-variant, #3f4945)',

        // Background
        'background' => 'var(--loom-background, #f8faf8)',
        'onBackground' => 'var(--loom-on-background, #191c1b)',

        // Outline
        'outline' => 'var(--loom-outline, #6f7975)',
        'outlineVariant' => 'var(--loom-outline-variant, #bfc9c4)',

        // Inverse
        'inverseSurface' => 'var(--loom-inverse-surface, #2d312f)',
        'inverseOnSurface' => 'var(--loom-inverse-on-surface, #eff1ef)',
        'inversePrimary' => 'var(--loom-inverse-primary, #9ad6c5)',

        // Semantic
        'success' => 'var(--loom-success, #2e7d32)',
        'onSuccess' => 'var(--loom-on-success, #ffffff)',
        'warning' => 'var(--loom-warning, #ed6c02)',
        'onWarning' => 'var(--loom-on-warning, #ffffff)',
        'info' => 'var(--loom-info, #0288d1)',
        'onInfo' => 'var(--loom-on-info, #ffffff)',

        // Text (aliases)
        'text' => 'var(--loom-text, #191c1b)',
        'textSecondary' => 'var(--loom-text-secondary, #3f4945)',
        'textDisabled' => 'var(--loom-text-disabled, #6f7975)',

        // Border
        'border' => 'var(--loom-border, #bfc9c4)',

        // Utility
        'scrim' => 'var(--loom-scrim, #000000)',
        'shadow' => 'var(--loom-shadow, #000000)',
    ];

    /**
     * Initialize colors - uses CSS variables to support light/dark mode switching
     *
     * Colors always reference CSS variables so that theme switching works automatically.
     * Theme Design plugin outputs CSS variables for both light and dark modes,
     * and when data-theme changes, the CSS variables resolve to the appropriate values.
     */
    private static function init(): void {
        if (self::$initialized) {
            return;
        }

        // Always use CSS variable references for automatic dark mode support
        // The CSS variables are populated by either:
        // 1. Theme Design plugin (with user-customized colors for light/dark)
        // 2. Loom Core's default CSS (loom-core.css with fallback colors)
        self::$colors = self::$defaults;

        self::$initialized = true;
    }

    /**
     * Get a color value
     */
    public static function get(string $name): string {
        self::init();
        return self::$colors[$name] ?? '';
    }

    /**
     * Set colors from Theme Design or custom source
     */
    public static function setColors(array $colors): void {
        self::init();
        foreach ($colors as $key => $value) {
            if (isset(self::$colors[$key])) {
                self::$colors[$key] = $value;
            }
        }
    }

    /**
     * Reset to reload colors (useful after Theme Design saves)
     */
    public static function reset(): void {
        self::$initialized = false;
        self::$colors = [];
    }

    /**
     * Magic getter for static property access: Colors::$primary
     */
    public static function __callStatic(string $name, array $arguments): string {
        return self::get($name);
    }

    // Convenient static getters for IDE autocomplete and backwards compatibility
    public static function primary(): string { return self::get('primary'); }
    public static function onPrimary(): string { return self::get('onPrimary'); }
    public static function primaryContainer(): string { return self::get('primaryContainer'); }
    public static function onPrimaryContainer(): string { return self::get('onPrimaryContainer'); }

    public static function secondary(): string { return self::get('secondary'); }
    public static function onSecondary(): string { return self::get('onSecondary'); }
    public static function secondaryContainer(): string { return self::get('secondaryContainer'); }
    public static function onSecondaryContainer(): string { return self::get('onSecondaryContainer'); }

    public static function tertiary(): string { return self::get('tertiary'); }
    public static function onTertiary(): string { return self::get('onTertiary'); }
    public static function tertiaryContainer(): string { return self::get('tertiaryContainer'); }
    public static function onTertiaryContainer(): string { return self::get('onTertiaryContainer'); }

    public static function error(): string { return self::get('error'); }
    public static function onError(): string { return self::get('onError'); }
    public static function errorContainer(): string { return self::get('errorContainer'); }
    public static function onErrorContainer(): string { return self::get('onErrorContainer'); }

    public static function surface(): string { return self::get('surface'); }
    public static function onSurface(): string { return self::get('onSurface'); }
    public static function surfaceVariant(): string { return self::get('surfaceVariant'); }
    public static function onSurfaceVariant(): string { return self::get('onSurfaceVariant'); }

    public static function background(): string { return self::get('background'); }
    public static function onBackground(): string { return self::get('onBackground'); }

    public static function outline(): string { return self::get('outline'); }
    public static function outlineVariant(): string { return self::get('outlineVariant'); }

    public static function inverseSurface(): string { return self::get('inverseSurface'); }
    public static function inverseOnSurface(): string { return self::get('inverseOnSurface'); }
    public static function inversePrimary(): string { return self::get('inversePrimary'); }

    public static function success(): string { return self::get('success'); }
    public static function onSuccess(): string { return self::get('onSuccess'); }
    public static function warning(): string { return self::get('warning'); }
    public static function onWarning(): string { return self::get('onWarning'); }
    public static function info(): string { return self::get('info'); }
    public static function onInfo(): string { return self::get('onInfo'); }

    public static function text(): string { return self::get('text'); }
    public static function textSecondary(): string { return self::get('textSecondary'); }
    public static function textDisabled(): string { return self::get('textDisabled'); }

    public static function border(): string { return self::get('border'); }
    public static function scrim(): string { return self::get('scrim'); }
    public static function shadow(): string { return self::get('shadow'); }
}
