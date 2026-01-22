<?php
/**
 * Icon Renderer
 *
 * Handles SVG rendering with caching and modifications.
 *
 * @package IconManager\Features\Icons
 * @since 2.1.0
 */



namespace IconManager\Features\Icons;

class IconRenderer {
    private static array $cache = [];
    private static string $defaultIcon = 'magicIcon.svg';

    public static function render(
        string $pack,
        string $name,
        ?int $width = null,
        ?int $height = null,
        ?string $class = null,
        ?string $style = null,
        ?string $id = null,
        ?string $color = null
    ): string {
        $filename = self::normalizeFilename($name);
        $filePath = self::getIconPath($pack, $filename);

        $svg = self::getSvgContent($filePath, $pack);

        if (empty($svg)) {
            return self::getErrorPlaceholder($pack, $name);
        }

        return self::modifySvg($svg, $filename, $width, $height, $class, $style, $id, $color);
    }

    private static function getIconPath(string $pack, string $filename): string {
        return ICON_MANAGER_ICONS_DIR . sanitize_file_name($pack) . '/' . sanitize_file_name($filename);
    }

    private static function getSvgContent(string $filePath, string $pack): string {
        $cacheKey = md5($filePath);

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
        } else {
            $defaultPath = ICON_MANAGER_ICONS_DIR . sanitize_file_name($pack) . '/' . self::$defaultIcon;
            if (file_exists($defaultPath)) {
                $content = file_get_contents($defaultPath);
            } else {
                return '';
            }
        }

        $content = preg_replace('/<\?xml.*?\?>\s*/i', '', $content);

        self::$cache[$cacheKey] = $content;

        return $content;
    }

    private static function modifySvg(
        string $svg,
        string $filename,
        ?int $width,
        ?int $height,
        ?string $class,
        ?string $style,
        ?string $id,
        ?string $color = null
    ): string {
        $dom = new \DOMDocument();

        // Use internal errors instead of @ suppression
        $previousErrors = libxml_use_internal_errors(true);
        $dom->loadXML($svg);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $svgElement = $dom->getElementsByTagName('svg')->item(0);

        if (!$svgElement) {
            return $svg;
        }

        if ($id) {
            $svgElement->setAttribute('id', esc_attr($id));
        } elseif (!$svgElement->hasAttribute('id')) {
            $iconName = pathinfo($filename, PATHINFO_FILENAME);
            $svgElement->setAttribute('id', esc_attr($iconName));
        }

        if ($width) {
            $svgElement->setAttribute('width', (string) $width);
        }
        if ($height) {
            $svgElement->setAttribute('height', (string) $height);
        }

        if ($class) {
            $existingClass = $svgElement->getAttribute('class');
            $newClass = $existingClass ? $existingClass . ' ' . $class : $class;
            $svgElement->setAttribute('class', esc_attr($newClass));
        }

        if ($style) {
            $existingStyle = $svgElement->getAttribute('style');
            $newStyle = $existingStyle ? $existingStyle . '; ' . $style : $style;
            $svgElement->setAttribute('style', esc_attr($newStyle));
        }

        if ($color) {
            $fillableElements = ['path', 'circle', 'rect', 'ellipse', 'polygon', 'polyline', 'line'];

            foreach ($fillableElements as $tagName) {
                $elements = $svgElement->getElementsByTagName($tagName);
                foreach ($elements as $element) {
                    $currentFill = $element->getAttribute('fill');
                    if ($currentFill !== 'none') {
                        $element->setAttribute('fill', esc_attr($color));
                    }

                    $currentStroke = $element->getAttribute('stroke');
                    if ($currentStroke && $currentStroke !== 'none') {
                        $element->setAttribute('stroke', esc_attr($color));
                    }
                }
            }
        }

        return $dom->saveXML($svgElement);
    }

    private static function normalizeFilename(string $name): string {
        if (substr($name, -4) !== '.svg') {
            return $name . '.svg';
        }
        return $name;
    }

    private static function getErrorPlaceholder(string $pack, string $name): string {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            return sprintf(
                '<!-- Icon not found: %s/%s -->',
                esc_html($pack),
                esc_html($name)
            );
        }
        return '';
    }

    public static function clearCache(): void {
        self::$cache = [];
    }
}
