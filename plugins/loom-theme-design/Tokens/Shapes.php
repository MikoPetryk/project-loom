<?php
/**
 * Shapes Design Tokens
 *
 * Provides border-radius values. Use constants for standard values,
 * or methods to get customized values from Theme Manager settings.
 *
 * @package Loom\ThemeManager\Tokens
 */



namespace Loom\ThemeManager\Tokens;

use Loom\ThemeManager\Features\Tokens\TokenRegistry;

class Shapes {
    // Default constants (use these in code)
    public const none = 0;
    public const xs = 2;
    public const sm = 4;
    public const md = 8;
    public const lg = 12;
    public const xl = 16;
    public const xxl = 24;
    public const full = 9999;

    /**
     * Get customized shape value from Theme Manager settings
     * Use this when you need the actual configured value
     */
    public static function get(string $size): int {
        return TokenRegistry::shape($size);
    }
}
