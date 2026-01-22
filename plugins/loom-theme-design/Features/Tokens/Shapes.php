<?php
/**
 * Shapes Token
 *
 * @package Loom\ThemeManager\Features\Tokens
 * @since 1.0.0
 *
 * @method static int none()
 * @method static int sm()
 * @method static int md()
 * @method static int lg()
 * @method static int xl()
 * @method static int full()
 */



namespace Loom\ThemeManager\Features\Tokens;

class Shapes {

    private static array $values = [
        'none' => 0,
        'sm' => 4,
        'md' => 8,
        'lg' => 16,
        'xl' => 24,
        'full' => 9999,
    ];

    public static function __callStatic(string $name, array $arguments): int {
        return self::$values[$name] ?? 0;
    }

    public static function load(array $shapes): void {
        foreach ($shapes as $key => $value) {
            if (array_key_exists($key, self::$values)) {
                self::$values[$key] = (int) $value;
            }
        }
    }

    public static function toArray(): array {
        return self::$values;
    }

    public static function getDefaults(): array {
        return ['none' => 0, 'sm' => 4, 'md' => 8, 'lg' => 16, 'xl' => 24, 'full' => 9999];
    }

    public static function getLabels(): array {
        return ['none' => 'None', 'sm' => 'Small', 'md' => 'Medium', 'lg' => 'Large', 'xl' => 'Extra Large', 'full' => 'Full (Pill)'];
    }
}
