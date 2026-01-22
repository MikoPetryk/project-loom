<?php
/**
 * Shortcode Integration
 *
 * Usage: [icon pack="General" name="Email" size="24" color="#333"]
 *
 * @package IconManager\Features\Integration
 * @since 2.1.0
 */



namespace IconManager\Features\Integration;

class Shortcode {

    public function __construct() {
        add_shortcode('icon', [$this, 'renderShortcode']);
    }

    public function renderShortcode($atts): string {
        $atts = shortcode_atts([
            'pack' => '',
            'name' => '',
            'size' => null,
            'width' => null,
            'height' => null,
            'color' => '',
            'class' => '',
            'style' => '',
            'id' => '',
        ], $atts);

        if (empty($atts['pack']) || empty($atts['name'])) {
            return '<!-- Icon Manager: pack and name are required -->';
        }

        $enumClass = "IconManager\\IconPacks\\" . $atts['pack'];

        if (!class_exists($enumClass)) {
            return '<!-- Icon Manager: Pack not found: ' . esc_html($atts['pack']) . ' -->';
        }

        try {
            $cleanName = $atts['name'];
            if (stripos($cleanName, $atts['pack']) === 0) {
                $cleanName = substr($cleanName, strlen($atts['pack']));
            }

            if (defined($enumClass . '::' . $cleanName)) {
                $iconEnum = constant($enumClass . '::' . $cleanName);
            } elseif (defined($enumClass . '::' . $atts['name'])) {
                $iconEnum = constant($enumClass . '::' . $atts['name']);
            } else {
                return '<!-- Icon Manager: Icon not found: ' . esc_html($atts['name']) . ' -->';
            }
        } catch (\Exception $e) {
            return '<!-- Icon Manager: Error: ' . esc_html($e->getMessage()) . ' -->';
        }

        $icon = \Icon($iconEnum);

        if ($atts['size']) {
            $icon->size((int) $atts['size']);
        } else {
            if ($atts['width']) $icon->width((int) $atts['width']);
            if ($atts['height']) $icon->height((int) $atts['height']);
        }

        if ($atts['color']) $icon->color($atts['color']);
        if ($atts['style']) $icon->style($atts['style']);
        if ($atts['class']) $icon->class($atts['class']);
        if ($atts['id']) $icon->id($atts['id']);

        return (string) $icon;
    }
}
