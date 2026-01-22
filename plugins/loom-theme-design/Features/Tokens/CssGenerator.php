<?php
/**
 * CSS Generator
 *
 * Generates CSS custom properties from design tokens with light/dark mode support.
 *
 * @package Loom\ThemeManager\Features\Tokens
 * @since 1.0.0
 */



namespace Loom\ThemeManager\Features\Tokens;

class CssGenerator {

    /**
     * Output CSS to page head
     */
    public static function output(): void {
        echo '<style id="loom-design-tokens">' . self::generate() . '</style>';
    }

    /**
     * Generate complete CSS with light and dark mode
     */
    public static function generate(): string {
        $css = self::generateLightMode();
        $css .= "\n" . self::generateDarkMode();
        return $css;
    }

    /**
     * Generate light mode CSS (default :root)
     */
    private static function generateLightMode(): string {
        $css = ":root {\n";
        $css .= self::generateColorVariables(Colors::toArray());
        $css .= self::generateTypographyVariables();
        $css .= self::generateSpacingVariables();
        $css .= self::generateShapeVariables();
        $css .= "}\n";
        return $css;
    }

    /**
     * Generate dark mode CSS (html.dark selector)
     */
    private static function generateDarkMode(): string {
        $css = "html.dark {\n";
        $css .= self::generateColorVariables(Colors::toDarkArray());
        $css .= "}\n";
        return $css;
    }

    /**
     * Generate CSS variables for colors
     */
    private static function generateColorVariables(array $colors): string {
        $css = "  /* Colors */\n";

        foreach ($colors as $key => $value) {
            $cssKey = self::camelToKebab($key);
            $css .= "  --loom-{$cssKey}: {$value};\n";
        }

        return $css;
    }

    /**
     * Generate CSS variables for typography
     */
    private static function generateTypographyVariables(): string {
        $css = "\n  /* Typography */\n";
        $css .= "  --loom-font-heading: " . Typography::fontHeading() . ", sans-serif;\n";
        $css .= "  --loom-font-body: " . Typography::fontBody() . ", sans-serif;\n";
        $css .= "  --loom-size-base: " . Typography::sizeBase() . "px;\n";
        $css .= "  --loom-size-h1: " . Typography::sizeH1() . "px;\n";
        $css .= "  --loom-size-h2: " . Typography::sizeH2() . "px;\n";
        $css .= "  --loom-size-h3: " . Typography::sizeH3() . "px;\n";
        $css .= "  --loom-size-h4: " . Typography::sizeH4() . "px;\n";
        $css .= "  --loom-size-h5: " . Typography::sizeH5() . "px;\n";
        $css .= "  --loom-size-h6: " . Typography::sizeH6() . "px;\n";
        $css .= "  --loom-line-height: " . Typography::lineHeight() . ";\n";
        return $css;
    }

    /**
     * Generate CSS variables for spacing
     */
    private static function generateSpacingVariables(): string {
        $css = "\n  /* Spacing */\n";
        $css .= "  --loom-spacing-xs: " . Spacing::xs() . "px;\n";
        $css .= "  --loom-spacing-sm: " . Spacing::sm() . "px;\n";
        $css .= "  --loom-spacing-md: " . Spacing::md() . "px;\n";
        $css .= "  --loom-spacing-lg: " . Spacing::lg() . "px;\n";
        $css .= "  --loom-spacing-xl: " . Spacing::xl() . "px;\n";
        $css .= "  --loom-spacing-xxl: " . Spacing::xxl() . "px;\n";
        return $css;
    }

    /**
     * Generate CSS variables for shapes
     */
    private static function generateShapeVariables(): string {
        $css = "\n  /* Shapes */\n";
        $css .= "  --loom-rounded-none: " . Shapes::none() . "px;\n";
        $css .= "  --loom-rounded-sm: " . Shapes::sm() . "px;\n";
        $css .= "  --loom-rounded-md: " . Shapes::md() . "px;\n";
        $css .= "  --loom-rounded-lg: " . Shapes::lg() . "px;\n";
        $css .= "  --loom-rounded-xl: " . Shapes::xl() . "px;\n";
        $css .= "  --loom-rounded-full: " . Shapes::full() . "px;\n";
        return $css;
    }

    /**
     * Convert camelCase to kebab-case
     */
    private static function camelToKebab(string $str): string {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $str));
    }
}
