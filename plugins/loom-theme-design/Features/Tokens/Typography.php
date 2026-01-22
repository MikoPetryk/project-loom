<?php
/**
 * Typography Token
 *
 * @package Loom\ThemeManager\Features\Tokens
 * @since 1.0.0
 *
 * @method static string fontHeading()
 * @method static string fontBody()
 * @method static int sizeBase()
 * @method static int sizeH1()
 * @method static int sizeH2()
 * @method static int sizeH3()
 * @method static int sizeH4()
 * @method static int sizeH5()
 * @method static int sizeH6()
 * @method static float lineHeight()
 */



namespace Loom\ThemeManager\Features\Tokens;

class Typography {

    private static array $values = [
        'fontHeading' => 'Poppins',
        'fontBody' => 'Inter',
        'sizeBase' => 16,
        'sizeH1' => 48,
        'sizeH2' => 36,
        'sizeH3' => 28,
        'sizeH4' => 24,
        'sizeH5' => 20,
        'sizeH6' => 18,
        'lineHeight' => 1.6,
    ];

    public static function __callStatic(string $name, array $arguments): string|int|float {
        return self::$values[$name] ?? '';
    }

    public static function load(array $typography): void {
        foreach ($typography as $key => $value) {
            if (array_key_exists($key, self::$values)) {
                if (in_array($key, ['fontHeading', 'fontBody'])) {
                    self::$values[$key] = (string) $value;
                } elseif ($key === 'lineHeight') {
                    self::$values[$key] = (float) $value;
                } else {
                    self::$values[$key] = (int) $value;
                }
            }
        }
    }

    public static function toArray(): array {
        return self::$values;
    }

    public static function getDefaults(): array {
        return [
            'fontHeading' => 'Poppins',
            'fontBody' => 'Inter',
            'sizeBase' => 16,
            'sizeH1' => 48,
            'sizeH2' => 36,
            'sizeH3' => 28,
            'sizeH4' => 24,
            'sizeH5' => 20,
            'sizeH6' => 18,
            'lineHeight' => 1.6,
        ];
    }

    public static function getLabels(): array {
        return [
            'fontHeading' => 'Heading Font',
            'fontBody' => 'Body Font',
            'sizeBase' => 'Base Size',
            'sizeH1' => 'H1 Size',
            'sizeH2' => 'H2 Size',
            'sizeH3' => 'H3 Size',
            'sizeH4' => 'H4 Size',
            'sizeH5' => 'H5 Size',
            'sizeH6' => 'H6 Size',
            'lineHeight' => 'Line Height',
        ];
    }

    public static function getFontOptions(): array {
        return [
            'Inter' => 'Inter',
            'Poppins' => 'Poppins',
            'Roboto' => 'Roboto',
            'Open Sans' => 'Open Sans',
            'Lato' => 'Lato',
            'Montserrat' => 'Montserrat',
        ];
    }
}
