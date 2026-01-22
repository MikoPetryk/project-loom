<?php
/**
 * Theme Configuration
 *
 * Fluent theme customization API.
 *
 * Usage in theme's functions.php:
 *
 *   use Loom\ThemeManager\Theme;
 *
 *   Theme::configure(
 *       colors: [
 *           'primary' => '#e63946',
 *           'secondary' => '#457b9d',
 *       ],
 *       spacing: [
 *           'md' => 20,
 *       ]
 *   );
 *
 * Or fluent style:
 *
 *   Theme::colors()
 *       ->primary('#e63946')
 *       ->secondary('#457b9d');
 *
 *   Theme::spacing()
 *       ->md(20)
 *       ->lg(32);
 *
 * @package Loom\ThemeManager
 */



namespace Loom\ThemeManager;

use Loom\ThemeManager\Features\Tokens\TokenRegistry;

class Theme {

    private static array $customizations = [
        'colors' => [],
        'spacing' => [],
        'shapes' => [],
        'typography' => [],
    ];

    private static bool $applied = false;

    /**
     * Configure theme tokens (Compose-style)
     */
    public static function configure(
        ?array $colors = null,
        ?array $spacing = null,
        ?array $shapes = null,
        ?array $typography = null
    ): void {
        if ($colors) self::$customizations['colors'] = array_merge(self::$customizations['colors'], $colors);
        if ($spacing) self::$customizations['spacing'] = array_merge(self::$customizations['spacing'], $spacing);
        if ($shapes) self::$customizations['shapes'] = array_merge(self::$customizations['shapes'], $shapes);
        if ($typography) self::$customizations['typography'] = array_merge(self::$customizations['typography'], $typography);
    }

    /**
     * Fluent color configuration
     */
    public static function colors(): ColorBuilder {
        return new ColorBuilder();
    }

    /**
     * Fluent spacing configuration
     */
    public static function spacing(): SpacingBuilder {
        return new SpacingBuilder();
    }

    /**
     * Fluent shapes configuration
     */
    public static function shapes(): ShapesBuilder {
        return new ShapesBuilder();
    }

    /**
     * Fluent typography configuration
     */
    public static function typography(): TypographyBuilder {
        return new TypographyBuilder();
    }

    /**
     * Get all customizations (called by TokenRegistry)
     */
    public static function getCustomizations(): array {
        return self::$customizations;
    }

    /**
     * Apply customization to a token category
     * @internal
     */
    public static function apply(string $category, string $key, mixed $value): void {
        self::$customizations[$category][$key] = $value;
    }
}

/**
 * Fluent builder for colors
 *
 * @method self primary(string $color)
 * @method self onPrimary(string $color)
 * @method self primaryContainer(string $color)
 * @method self onPrimaryContainer(string $color)
 * @method self secondary(string $color)
 * @method self onSecondary(string $color)
 * @method self secondaryContainer(string $color)
 * @method self tertiary(string $color)
 * @method self onTertiary(string $color)
 * @method self background(string $color)
 * @method self onBackground(string $color)
 * @method self surface(string $color)
 * @method self onSurface(string $color)
 * @method self surfaceVariant(string $color)
 * @method self onSurfaceVariant(string $color)
 * @method self error(string $color)
 * @method self onError(string $color)
 * @method self success(string $color)
 * @method self warning(string $color)
 * @method self info(string $color)
 * @method self text(string $color)
 * @method self textSecondary(string $color)
 * @method self textDisabled(string $color)
 * @method self border(string $color)
 * @method self outline(string $color)
 * @method self outlineVariant(string $color)
 * @method self scrim(string $color)
 * @method self shadow(string $color)
 * @method self inverseSurface(string $color)
 * @method self inverseOnSurface(string $color)
 * @method self inversePrimary(string $color)
 */
class ColorBuilder {
    public function __call(string $name, array $args): self {
        if (isset($args[0]) && is_string($args[0])) {
            Theme::apply('colors', $name, $args[0]);
        }
        return $this;
    }
}

/**
 * Fluent builder for spacing
 *
 * @method self none(int $value)
 * @method self xxs(int $value)
 * @method self xs(int $value)
 * @method self sm(int $value)
 * @method self md(int $value)
 * @method self lg(int $value)
 * @method self xl(int $value)
 * @method self xxl(int $value)
 * @method self xxxl(int $value)
 */
class SpacingBuilder {
    public function __call(string $name, array $args): self {
        if (isset($args[0]) && is_int($args[0])) {
            Theme::apply('spacing', $name, $args[0]);
        }
        return $this;
    }
}

/**
 * Fluent builder for shapes
 *
 * @method self none(int $value)
 * @method self xs(int $value)
 * @method self sm(int $value)
 * @method self md(int $value)
 * @method self lg(int $value)
 * @method self xl(int $value)
 * @method self xxl(int $value)
 * @method self full(int $value)
 */
class ShapesBuilder {
    public function __call(string $name, array $args): self {
        if (isset($args[0]) && is_int($args[0])) {
            Theme::apply('shapes', $name, $args[0]);
        }
        return $this;
    }
}

/**
 * Fluent builder for typography
 *
 * @method self fontHeading(string $font)
 * @method self fontBody(string $font)
 * @method self fontMono(string $font)
 * @method self sizeBase(int $size)
 * @method self sizeH1(int $size)
 * @method self sizeH2(int $size)
 * @method self sizeH3(int $size)
 * @method self sizeH4(int $size)
 * @method self sizeH5(int $size)
 * @method self sizeH6(int $size)
 * @method self sizeSmall(int $size)
 * @method self lineHeight(float $value)
 * @method self letterSpacing(float $value)
 */
class TypographyBuilder {
    public function __call(string $name, array $args): self {
        if (isset($args[0])) {
            Theme::apply('typography', $name, $args[0]);
        }
        return $this;
    }
}
