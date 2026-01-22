<?php
/**
 * Spacing Design Tokens
 *
 * Provides spacing values. Use constants for standard values,
 * or methods to get customized values from Theme Manager settings.
 *
 * @package Loom\ThemeManager\Tokens
 */



namespace Loom\ThemeManager\Tokens;

use Loom\ThemeManager\Features\Tokens\TokenRegistry;

class Spacing {
    // Default constants (use these in code)
    public const none = 0;
    public const xxs = 2;
    public const xs = 4;
    public const sm = 8;
    public const md = 16;
    public const lg = 24;
    public const xl = 32;
    public const xxl = 48;
    public const xxxl = 64;

    /**
     * Get customized spacing value from Theme Manager settings
     * Use this when you need the actual configured value
     */
    public static function get(string $size): int {
        return TokenRegistry::spacing($size);
    }
}
