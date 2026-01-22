<?php
/**
 * Spacing Token
 *
 * @package Loom\ThemeManager\Features\Tokens
 * @since 1.0.0
 *
 * @method static int xs()
 * @method static int sm()
 * @method static int md()
 * @method static int lg()
 * @method static int xl()
 * @method static int xxl()
 */



namespace Loom\ThemeManager\Features\Tokens;

class Spacing {

    private static array $values = [
        'xs' => 4,
        'sm' => 8,
        'md' => 16,
        'lg' => 24,
        'xl' => 32,
        'xxl' => 48,
    ];

    public static function __callStatic(string $name, array $arguments): int {
        return self::$values[$name] ?? 0;
    }

    public static function load(array $spacing): void {
        foreach ($spacing as $key => $value) {
            if (array_key_exists($key, self::$values)) {
                self::$values[$key] = (int) $value;
            }
        }
    }

    public static function toArray(): array {
        return self::$values;
    }

    public static function getDefaults(): array {
        return ['xs' => 4, 'sm' => 8, 'md' => 16, 'lg' => 24, 'xl' => 32, 'xxl' => 48];
    }

    public static function getLabels(): array {
        return ['xs' => 'Extra Small', 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large', 'xl' => 'Extra Large', 'xxl' => 'XX Large'];
    }
}
