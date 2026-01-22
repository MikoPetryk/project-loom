<?php
/**
 * Color Generator
 *
 * Generates dark mode color palette from light mode colors using HSL transformations.
 *
 * @package Loom\ThemeManager\Features\Tokens
 * @since 1.1.0
 */



namespace Loom\ThemeManager\Features\Tokens;

class ColorGenerator {

    /**
     * Generate a complete dark palette from light mode colors
     */
    public static function generateDarkPalette(array $light): array {
        $dark = [];
        foreach ($light as $key => $hex) {
            $dark[$key] = self::transformToDark($key, $hex);
        }
        return $dark;
    }

    /**
     * Transform a single color to its dark mode equivalent
     */
    private static function transformToDark(string $key, string $hex): string {
        // Handle non-color values
        if (!self::isValidHex($hex)) {
            return $hex;
        }

        [$h, $s, $l] = self::hexToHsl($hex);

        if (self::isAccentColor($key)) {
            // Accent colors: invert lightness, keep hue
            // Light mode: darker (L: 25-45) -> Dark mode: lighter (L: 55-75)
            $l = 100 - $l;
            // Slightly boost saturation for vibrancy
            $s = min(100, $s * 1.05);
        } elseif (self::isOnColor($key)) {
            // "on*" colors: flip between light and dark
            // Light text on dark becomes dark text on light
            $l = $l > 50 ? 15 : 90;
        } elseif (self::isContainerColor($key)) {
            // Containers: make dark but elevated from surface
            // Light mode: light containers (L: 85-95) -> Dark mode: dark (L: 20-35)
            $l = max(20, min(35, 100 - $l + 5));
            // Reduce saturation for subtlety
            $s = $s * 0.7;
        } elseif (self::isSurfaceColor($key)) {
            // Surfaces: very dark with minimal tint
            // Light mode (L: 95-100) -> Dark mode (L: 6-12)
            $l = self::mapSurfaceLightness($key, $l);
            // Reduce saturation significantly
            $s = $s * 0.3;
        } elseif (self::isOutlineColor($key)) {
            // Outlines: medium contrast
            $l = $l > 50 ? 35 : 65;
            $s = $s * 0.5;
        } elseif (self::isInverseColor($key)) {
            // Inverse colors: swap to light values
            $l = $l < 50 ? 90 : 10;
        } elseif ($key === 'scrim' || $key === 'shadow') {
            // Keep black for scrim/shadow
            return $hex;
        } else {
            // Default: simple inversion
            $l = 100 - $l;
        }

        return self::hslToHex($h, $s, $l);
    }

    /**
     * Map surface lightness for different surface variants
     */
    private static function mapSurfaceLightness(string $key, float $l): float {
        return match ($key) {
            'background' => 6,
            'surface' => 6,
            'surfaceVariant' => 18,
            default => 10,
        };
    }

    /**
     * Check if color is an accent color (primary, secondary, tertiary, error, success, warning, info)
     */
    private static function isAccentColor(string $key): bool {
        $accents = ['primary', 'secondary', 'tertiary', 'error', 'success', 'warning', 'info'];
        return in_array($key, $accents, true);
    }

    /**
     * Check if color is an "on" color (text color for a surface)
     */
    private static function isOnColor(string $key): bool {
        return str_starts_with($key, 'on') && $key !== 'outline' && $key !== 'outlineVariant';
    }

    /**
     * Check if color is a container color
     */
    private static function isContainerColor(string $key): bool {
        return str_contains($key, 'Container');
    }

    /**
     * Check if color is a surface/background color
     */
    private static function isSurfaceColor(string $key): bool {
        return in_array($key, ['background', 'surface', 'surfaceVariant'], true);
    }

    /**
     * Check if color is an outline color
     */
    private static function isOutlineColor(string $key): bool {
        return in_array($key, ['outline', 'outlineVariant', 'border'], true);
    }

    /**
     * Check if color is an inverse color
     */
    private static function isInverseColor(string $key): bool {
        return str_starts_with($key, 'inverse');
    }

    /**
     * Validate hex color string
     */
    private static function isValidHex(string $hex): bool {
        return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $hex);
    }

    /**
     * Convert hex color to HSL
     *
     * @return array{0: float, 1: float, 2: float} [hue, saturation, lightness]
     */
    public static function hexToHsl(string $hex): array {
        $hex = ltrim($hex, '#');

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            $h = $s = 0.0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

            $h = match ($max) {
                $r => (($g - $b) / $d + ($g < $b ? 6 : 0)) / 6,
                $g => (($b - $r) / $d + 2) / 6,
                $b => (($r - $g) / $d + 4) / 6,
                default => 0.0,
            };
        }

        return [
            round($h * 360, 1),
            round($s * 100, 1),
            round($l * 100, 1),
        ];
    }

    /**
     * Convert HSL to hex color
     */
    public static function hslToHex(float $h, float $s, float $l): string {
        $h = $h / 360;
        $s = $s / 100;
        $l = $l / 100;

        if ($s === 0.0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;

            $r = self::hueToRgb($p, $q, $h + 1 / 3);
            $g = self::hueToRgb($p, $q, $h);
            $b = self::hueToRgb($p, $q, $h - 1 / 3);
        }

        return sprintf(
            '#%02x%02x%02x',
            (int) round($r * 255),
            (int) round($g * 255),
            (int) round($b * 255)
        );
    }

    /**
     * Helper for HSL to RGB conversion
     */
    private static function hueToRgb(float $p, float $q, float $t): float {
        if ($t < 0) $t += 1;
        if ($t > 1) $t -= 1;

        if ($t < 1 / 6) return $p + ($q - $p) * 6 * $t;
        if ($t < 1 / 2) return $q;
        if ($t < 2 / 3) return $p + ($q - $p) * (2 / 3 - $t) * 6;

        return $p;
    }
}
