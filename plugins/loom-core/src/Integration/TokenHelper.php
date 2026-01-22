<?php
/**
 * Token Helper
 *
 * Provides access to design tokens with graceful fallbacks
 * when Theme Manager is not available.
 *
 * @package Loom\Core\Integration
 */

namespace Loom\Core\Integration;

/**
 * Theme token integration wrapper.
 *
 * Provides access to design tokens with CSS variable fallbacks
 * when Theme Manager is not available.
 */
class TokenHelper {

    /** @var array<string, string> Default color fallbacks */
    private const FALLBACK_COLORS = [
        'primary' => '#336659',
        'primaryDark' => '#2a544a',
        'primaryLight' => '#e8f0ee',
        'secondary' => '#4A90A4',
        'success' => '#2ecc71',
        'warning' => '#f39c12',
        'error' => '#e74c3c',
        'info' => '#3498db',
        'background' => '#ffffff',
        'surface' => '#f8f9fa',
        'surfaceVariant' => '#e9ecef',
        'border' => '#e0e0e0',
        'text' => '#1a1a1a',
        'textSecondary' => '#6c757d',
        'inverseSurface' => '#323232',
        'inverseOnSurface' => '#ffffff',
    ];

    /** @var array<string, int> Default spacing fallbacks (in pixels) */
    private const FALLBACK_SPACING = [
        'xs' => 4,
        'sm' => 8,
        'md' => 16,
        'lg' => 24,
        'xl' => 32,
        'xxl' => 48,
    ];

    /** @var array<string, int> Default shape fallbacks (border-radius in pixels) */
    private const FALLBACK_SHAPES = [
        'none' => 0,
        'xs' => 2,
        'sm' => 4,
        'md' => 8,
        'lg' => 16,
        'xl' => 24,
        'full' => 9999,
    ];

    /**
     * Get a color token value
     *
     * @param string $name Color name (primary, secondary, success, error, etc.)
     * @param bool $asCssVar Return as CSS variable reference
     * @return string Color value or CSS variable
     */
    public static function color($name, $asCssVar = false) {
        $cssVarName = self::toCssVarName($name);
        $fallback = self::FALLBACK_COLORS[$name] ?? '#000000';

        if ($asCssVar) {
            return "var(--loom-{$cssVarName}, {$fallback})";
        }

        if (PluginRegistry::hasThemeManager()) {
            $colorsClass = \Loom\ThemeManager\Features\Tokens\Colors::class;
            if (method_exists($colorsClass, $name)) {
                return $colorsClass::$name();
            }
        }

        return $fallback;
    }

    /**
     * Get a spacing token value
     *
     * @param string $name Spacing size (xs, sm, md, lg, xl, xxl)
     * @param bool $asCssVar Return as CSS variable reference
     * @return int|string Spacing value in pixels or CSS variable
     */
    public static function spacing($name, $asCssVar = false) {
        $fallback = self::FALLBACK_SPACING[$name] ?? 16;

        if ($asCssVar) {
            return "var(--loom-spacing-{$name}, {$fallback}px)";
        }

        if (PluginRegistry::hasThemeManager()) {
            $spacingClass = \Loom\ThemeManager\Features\Tokens\Spacing::class;
            if (method_exists($spacingClass, $name)) {
                return $spacingClass::$name();
            }
        }

        return $fallback;
    }

    /**
     * Get a shape (border-radius) token value
     *
     * @param string $name Shape size (none, xs, sm, md, lg, xl, full)
     * @param bool $asCssVar Return as CSS variable reference
     * @return int|string Border radius value or CSS variable
     */
    public static function shape($name, $asCssVar = false) {
        $fallback = self::FALLBACK_SHAPES[$name] ?? 8;

        if ($asCssVar) {
            return "var(--loom-rounded-{$name}, {$fallback}px)";
        }

        if (PluginRegistry::hasThemeManager()) {
            $shapesClass = \Loom\ThemeManager\Features\Tokens\Shapes::class;
            if (method_exists($shapesClass, $name)) {
                return $shapesClass::$name();
            }
        }

        return $fallback;
    }

    /**
     * Check if we have actual token values (not just fallbacks)
     *
     * @return bool True if Theme Manager is available
     */
    public static function hasTokens() {
        return PluginRegistry::hasThemeManager();
    }

    /**
     * Get all colors as array
     *
     * @return array<string, string> Color name => value map
     */
    public static function allColors() {
        if (PluginRegistry::hasThemeManager()) {
            $colorsClass = \Loom\ThemeManager\Features\Tokens\Colors::class;
            if (method_exists($colorsClass, 'toArray')) {
                return $colorsClass::toArray();
            }
        }

        return self::FALLBACK_COLORS;
    }

    /**
     * Get all spacing as array
     *
     * @return array<string, int> Spacing name => value map
     */
    public static function allSpacing() {
        if (PluginRegistry::hasThemeManager()) {
            $spacingClass = \Loom\ThemeManager\Features\Tokens\Spacing::class;
            if (method_exists($spacingClass, 'toArray')) {
                return $spacingClass::toArray();
            }
        }

        return self::FALLBACK_SPACING;
    }

    /**
     * Get all shapes as array
     *
     * @return array<string, int> Shape name => value map
     */
    public static function allShapes() {
        if (PluginRegistry::hasThemeManager()) {
            $shapesClass = \Loom\ThemeManager\Features\Tokens\Shapes::class;
            if (method_exists($shapesClass, 'toArray')) {
                return $shapesClass::toArray();
            }
        }

        return self::FALLBACK_SHAPES;
    }

    /**
     * Convert camelCase to kebab-case for CSS variables
     *
     * @param string $name Token name
     * @return string CSS variable compatible name
     */
    private static function toCssVarName($name) {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $name));
    }
}
