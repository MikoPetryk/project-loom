<?php
/**
 * Token Registry
 *
 * Central registry for loading and accessing design tokens.
 *
 * @package Loom\ThemeManager\Features\Tokens
 * @since 1.0.0
 */



namespace Loom\ThemeManager\Features\Tokens;

use Loom\ThemeManager\Theme;

class TokenRegistry {

    private static bool $loaded = false;
    private static array $tokens = [];

    /**
     * Load tokens from database + Theme::configure() customizations
     */
    public static function load(array $tokens): void {
        if (self::$loaded) return;

        // 1. Start with database tokens (from admin settings)
        $merged = $tokens;

        // 2. Merge Theme::configure() customizations (from theme's functions.php)
        $customizations = Theme::getCustomizations();
        $merged = self::deepMerge($merged, $customizations);

        // 3. Load into token classes
        if (isset($merged['colors'])) Colors::load($merged['colors']);
        if (isset($merged['typography'])) Typography::load($merged['typography']);
        if (isset($merged['spacing'])) Spacing::load($merged['spacing']);
        if (isset($merged['shapes'])) Shapes::load($merged['shapes']);

        self::$tokens = $merged;
        self::$loaded = true;
    }

    /**
     * Deep merge arrays (customizations override defaults)
     */
    private static function deepMerge(array $base, array $override): array {
        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = self::deepMerge($base[$key], $value);
            } elseif (!empty($value)) {
                $base[$key] = $value;
            }
        }
        return $base;
    }

    /**
     * Get all loaded tokens
     */
    public static function getAll(): array {
        return [
            'colors' => Colors::toFullArray(),
            'typography' => Typography::toArray(),
            'spacing' => Spacing::toArray(),
            'shapes' => Shapes::toArray(),
        ];
    }

    /**
     * Get a specific spacing value
     */
    public static function spacing(string $size): int {
        $method = $size;
        if (method_exists(Spacing::class, $method)) {
            return Spacing::$method();
        }
        return Spacing::md();
    }

    /**
     * Get a specific shape value
     */
    public static function shape(string $size): int {
        $method = $size;
        if (method_exists(Shapes::class, $method)) {
            return Shapes::$method();
        }
        return Shapes::md();
    }

    /**
     * Save tokens to database
     */
    public static function save(array $tokens): bool {
        self::$loaded = false;

        // Save to database (update_option returns false if value unchanged, which is fine)
        update_option('loom_theme_tokens', $tokens);

        // Reload tokens into memory
        self::load($tokens);

        // Verify save by checking if option exists and has data
        $saved = get_option('loom_theme_tokens');
        return !empty($saved);
    }

    /**
     * Check if tokens are loaded
     */
    public static function isLoaded(): bool {
        return self::$loaded;
    }

    /**
     * Force reload tokens
     */
    public static function reload(): void {
        self::$loaded = false;
        $tokens = get_option('loom_theme_tokens', []);
        self::load($tokens);
    }
}
