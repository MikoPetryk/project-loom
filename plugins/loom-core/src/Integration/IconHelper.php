<?php
/**
 * Icon Helper
 *
 * Provides icon resolution from multiple input types with graceful
 * fallback when Icon Manager is not available.
 *
 * @package Loom\Core\Integration
 */

namespace Loom\Core\Integration;

/**
 * Icon integration wrapper for graceful degradation.
 *
 * Accepts: string (raw SVG), IconPackInterface, IconBuilder, or null
 * Returns: string (rendered SVG or empty string)
 */
class IconHelper {

    /**
     * Resolve any icon input to an SVG string
     *
     * @param string|object|null $icon Can be:
     *   - string: Raw SVG markup
     *   - IconPackInterface enum: When Icon Manager is active
     *   - IconBuilder: When Icon Manager is active
     *   - null: Returns empty string
     * @param int|null $size Optional size override
     * @param string|null $color Optional color override
     * @return string Rendered SVG or empty string
     */
    public static function resolve(
        $icon,
        $size = null,
        $color = null
    ) {
        if ($icon === null) {
            return '';
        }

        // Raw SVG string - return as-is or with size wrapper
        if (is_string($icon)) {
            if ($size !== null) {
                return self::wrapWithSize($icon, $size);
            }
            return $icon;
        }

        // Check if Icon Manager is available
        if (!PluginRegistry::hasIconManager()) {
            return '';
        }

        // IconBuilder instance
        if (self::isIconBuilder($icon)) {
            return self::renderFromBuilder($icon, $size, $color);
        }

        // IconPackInterface enum
        if (self::isIconEnum($icon)) {
            return self::renderFromEnum($icon, $size, $color);
        }

        return '';
    }

    /**
     * Check if input is an IconPackInterface enum
     *
     * @param mixed $icon Input to check
     * @return bool True if icon is an IconPackInterface
     */
    public static function isIconEnum($icon) {
        if (!is_object($icon)) {
            return false;
        }

        return interface_exists(\IconManager\Features\Packs\IconPackInterface::class)
            && $icon instanceof \IconManager\Features\Packs\IconPackInterface;
    }

    /**
     * Check if input is an IconBuilder
     *
     * @param mixed $icon Input to check
     * @return bool True if icon is an IconBuilder
     */
    public static function isIconBuilder($icon) {
        if (!is_object($icon)) {
            return false;
        }

        return class_exists(\IconManager\Features\Icons\IconBuilder::class)
            && $icon instanceof \IconManager\Features\Icons\IconBuilder;
    }

    /**
     * Render icon from enum using IconBuilder
     *
     * @param object $enum Icon enum
     * @param int|null $size Size override
     * @param string|null $color Color override
     * @return string Rendered SVG
     */
    private static function renderFromEnum(
        $enum,
        $size,
        $color
    ) {
        if (!function_exists('Icon')) {
            return '';
        }

        $builder = \Icon($enum);

        if ($size !== null) {
            $builder->size($size);
        }

        if ($color !== null) {
            $builder->color($color);
        }

        return (string) $builder;
    }

    /**
     * Render icon from IconBuilder with optional overrides
     *
     * @param object $builder IconBuilder instance
     * @param int|null $size Size override
     * @param string|null $color Color override
     * @return string Rendered SVG
     */
    private static function renderFromBuilder(
        $builder,
        $size,
        $color
    ) {
        // IconBuilder supports chaining, apply overrides
        if ($size !== null && method_exists($builder, 'size')) {
            $builder->size($size);
        }

        if ($color !== null && method_exists($builder, 'color')) {
            $builder->color($color);
        }

        return (string) $builder;
    }

    /**
     * Wrap SVG string with size container
     *
     * @param string $svg Raw SVG markup
     * @param int $size Size in pixels
     * @return string Wrapped SVG
     */
    private static function wrapWithSize($svg, $size) {
        return sprintf(
            '<span style="display:inline-flex;width:%dpx;height:%dpx;align-items:center;justify-content:center;">%s</span>',
            $size,
            $size,
            $svg
        );
    }

    /**
     * Check if Icon Manager is available
     *
     * @return bool True if icons can be resolved from enums
     */
    public static function hasIconManager() {
        return PluginRegistry::hasIconManager();
    }
}
