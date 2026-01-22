<?php
/**
 * Colors Token
 *
 * Manages the complete color system with light and dark mode support.
 *
 * @package Loom\ThemeManager\Features\Tokens
 * @since 1.0.0
 */



namespace Loom\ThemeManager\Features\Tokens;

class Colors {

    /**
     * Light mode color values
     */
    private static array $light = [];

    /**
     * Dark mode color values
     */
    private static array $dark = [];

    /**
     * User overrides for dark mode (manual customizations)
     */
    private static array $darkOverrides = [];

    /**
     * Initialize with defaults
     */
    private static bool $initialized = false;

    /**
     * Ensure defaults are loaded
     */
    private static function ensureInitialized(): void {
        if (!self::$initialized) {
            $defaults = self::getDefaults();
            self::$light = $defaults['light'];
            self::$dark = $defaults['dark'];
            self::$initialized = true;
        }
    }

    /**
     * Magic method for backward compatibility - returns light mode color
     */
    public static function __callStatic(string $name, array $arguments): string {
        self::ensureInitialized();
        return self::$light[$name] ?? '';
    }

    /**
     * Get a light mode color value
     */
    public static function light(string $name): string {
        self::ensureInitialized();
        return self::$light[$name] ?? '';
    }

    /**
     * Get a dark mode color value
     */
    public static function dark(string $name): string {
        self::ensureInitialized();
        // Apply overrides first
        if (isset(self::$darkOverrides[$name])) {
            return self::$darkOverrides[$name];
        }
        return self::$dark[$name] ?? '';
    }

    /**
     * Load colors from database/config
     */
    public static function load(array $colors): void {
        self::ensureInitialized();

        // Handle new nested structure
        if (isset($colors['light'])) {
            foreach ($colors['light'] as $key => $value) {
                if (array_key_exists($key, self::$light)) {
                    self::$light[$key] = $value;
                }
            }
        }

        if (isset($colors['dark'])) {
            foreach ($colors['dark'] as $key => $value) {
                if (array_key_exists($key, self::$dark)) {
                    self::$dark[$key] = $value;
                }
            }
        }

        if (isset($colors['darkOverrides'])) {
            self::$darkOverrides = $colors['darkOverrides'];
        }

        // Handle legacy flat structure (migration)
        if (!isset($colors['light']) && !isset($colors['dark'])) {
            foreach ($colors as $key => $value) {
                if (array_key_exists($key, self::$light)) {
                    self::$light[$key] = $value;
                }
            }
            // Regenerate dark from updated light
            self::$dark = ColorGenerator::generateDarkPalette(self::$light);
        }
    }

    /**
     * Get all light mode colors
     */
    public static function toArray(): array {
        self::ensureInitialized();
        return self::$light;
    }

    /**
     * Get all dark mode colors (with overrides applied)
     */
    public static function toDarkArray(): array {
        self::ensureInitialized();
        return array_merge(self::$dark, self::$darkOverrides);
    }

    /**
     * Get complete color data (light, dark, overrides)
     */
    public static function toFullArray(): array {
        self::ensureInitialized();
        return [
            'light' => self::$light,
            'dark' => self::$dark,
            'darkOverrides' => self::$darkOverrides,
        ];
    }

    /**
     * Get default light mode colors
     */
    public static function getLightDefaults(): array {
        return [
            // Accent: Primary
            'primary' => '#336659',
            'onPrimary' => '#ffffff',
            'primaryContainer' => '#b8f1dd',
            'onPrimaryContainer' => '#00201a',

            // Accent: Secondary
            'secondary' => '#4A90A4',
            'onSecondary' => '#ffffff',
            'secondaryContainer' => '#cde5ff',
            'onSecondaryContainer' => '#001d31',

            // Accent: Tertiary
            'tertiary' => '#7c5635',
            'onTertiary' => '#ffffff',
            'tertiaryContainer' => '#ffdcc2',
            'onTertiaryContainer' => '#2c1600',

            // Semantic: Error
            'error' => '#ba1a1a',
            'onError' => '#ffffff',
            'errorContainer' => '#ffdad6',
            'onErrorContainer' => '#410002',

            // Semantic: Success
            'success' => '#2e7d32',
            'onSuccess' => '#ffffff',

            // Semantic: Warning
            'warning' => '#f57c00',
            'onWarning' => '#ffffff',

            // Semantic: Info
            'info' => '#0288d1',
            'onInfo' => '#ffffff',

            // Surface & Background
            'background' => '#fbfdf9',
            'onBackground' => '#191c1a',
            'surface' => '#fbfdf9',
            'onSurface' => '#191c1a',
            'surfaceVariant' => '#dce5dc',
            'onSurfaceVariant' => '#404943',

            // Outline
            'outline' => '#707972',
            'outlineVariant' => '#c0c9c1',

            // Inverse
            'inverseSurface' => '#2e312e',
            'inverseOnSurface' => '#f0f1ed',
            'inversePrimary' => '#9cd5c2',

            // Aliases (backward compatibility)
            'text' => '#191c1a',
            'textSecondary' => '#404943',
            'textDisabled' => '#707972',
            'border' => '#c0c9c1',

            // Utility
            'scrim' => '#000000',
            'shadow' => '#000000',
        ];
    }

    /**
     * Get default colors (light + auto-generated dark)
     */
    public static function getDefaults(): array {
        $light = self::getLightDefaults();
        $dark = ColorGenerator::generateDarkPalette($light);

        return [
            'light' => $light,
            'dark' => $dark,
            'darkOverrides' => [],
        ];
    }

    /**
     * Get labels for UI display
     */
    public static function getLabels(): array {
        return [
            // Accent: Primary
            'primary' => 'Primary',
            'onPrimary' => 'On Primary',
            'primaryContainer' => 'Primary Container',
            'onPrimaryContainer' => 'On Primary Container',

            // Accent: Secondary
            'secondary' => 'Secondary',
            'onSecondary' => 'On Secondary',
            'secondaryContainer' => 'Secondary Container',
            'onSecondaryContainer' => 'On Secondary Container',

            // Accent: Tertiary
            'tertiary' => 'Tertiary',
            'onTertiary' => 'On Tertiary',
            'tertiaryContainer' => 'Tertiary Container',
            'onTertiaryContainer' => 'On Tertiary Container',

            // Semantic
            'error' => 'Error',
            'onError' => 'On Error',
            'errorContainer' => 'Error Container',
            'onErrorContainer' => 'On Error Container',
            'success' => 'Success',
            'onSuccess' => 'On Success',
            'warning' => 'Warning',
            'onWarning' => 'On Warning',
            'info' => 'Info',
            'onInfo' => 'On Info',

            // Surface & Background
            'background' => 'Background',
            'onBackground' => 'On Background',
            'surface' => 'Surface',
            'onSurface' => 'On Surface',
            'surfaceVariant' => 'Surface Variant',
            'onSurfaceVariant' => 'On Surface Variant',

            // Outline
            'outline' => 'Outline',
            'outlineVariant' => 'Outline Variant',

            // Inverse
            'inverseSurface' => 'Inverse Surface',
            'inverseOnSurface' => 'Inverse On Surface',
            'inversePrimary' => 'Inverse Primary',

            // Aliases
            'text' => 'Text',
            'textSecondary' => 'Text Secondary',
            'textDisabled' => 'Text Disabled',
            'border' => 'Border',

            // Utility
            'scrim' => 'Scrim',
            'shadow' => 'Shadow',
        ];
    }

    /**
     * Get color groups for organized UI display
     */
    public static function getColorGroups(): array {
        return [
            'accent' => [
                'label' => 'Accent Colors',
                'description' => 'Primary brand colors and their variants',
                'colors' => [
                    'primary' => ['primary', 'onPrimary', 'primaryContainer', 'onPrimaryContainer'],
                    'secondary' => ['secondary', 'onSecondary', 'secondaryContainer', 'onSecondaryContainer'],
                    'tertiary' => ['tertiary', 'onTertiary', 'tertiaryContainer', 'onTertiaryContainer'],
                ],
            ],
            'semantic' => [
                'label' => 'Semantic Colors',
                'description' => 'Status and feedback colors',
                'colors' => [
                    'error' => ['error', 'onError', 'errorContainer', 'onErrorContainer'],
                    'success' => ['success', 'onSuccess'],
                    'warning' => ['warning', 'onWarning'],
                    'info' => ['info', 'onInfo'],
                ],
            ],
            'surface' => [
                'label' => 'Surface & Background',
                'description' => 'Background and container colors',
                'colors' => [
                    'background' => ['background', 'onBackground'],
                    'surface' => ['surface', 'onSurface', 'surfaceVariant', 'onSurfaceVariant'],
                    'outline' => ['outline', 'outlineVariant'],
                    'inverse' => ['inverseSurface', 'inverseOnSurface', 'inversePrimary'],
                ],
            ],
            'aliases' => [
                'label' => 'Legacy Aliases',
                'description' => 'Backward compatible color aliases',
                'colors' => [
                    'text' => ['text', 'textSecondary', 'textDisabled'],
                    'other' => ['border', 'scrim', 'shadow'],
                ],
            ],
        ];
    }

    /**
     * Regenerate dark palette from current light colors
     */
    public static function regenerateDark(): array {
        self::ensureInitialized();
        self::$dark = ColorGenerator::generateDarkPalette(self::$light);
        self::$darkOverrides = []; // Clear overrides
        return self::$dark;
    }

    /**
     * Set a dark mode override
     */
    public static function setDarkOverride(string $key, string $value): void {
        self::ensureInitialized();
        if (array_key_exists($key, self::$dark)) {
            self::$darkOverrides[$key] = $value;
        }
    }

    /**
     * Clear all dark mode overrides
     */
    public static function clearDarkOverrides(): void {
        self::$darkOverrides = [];
    }
}
