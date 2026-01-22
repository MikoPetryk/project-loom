<?php
/**
 * Block Registrar
 *
 * Registers Gutenberg blocks for icons.
 *
 * @package IconManager\Features\Blocks
 * @since 2.1.0
 */



namespace IconManager\Features\Blocks;

class BlockRegistrar {

    public function __construct() {
        add_action('init', [$this, 'registerBlock']);
    }

    public function registerBlock(): void {
        if (!function_exists('register_block_type')) {
            return;
        }

        register_block_type('icon-manager/icon', [
            'render_callback' => [$this, 'renderCallback'],
            'attributes' => [
                'pack' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'iconName' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'size' => [
                    'type' => 'number',
                    'default' => 24
                ],
                'color' => [
                    'type' => 'string',
                    'default' => ''
                ],
                'cssClass' => [
                    'type' => 'string',
                    'default' => ''
                ]
            ]
        ]);
    }

    public function renderCallback($attributes): string {
        $pack = $attributes['pack'] ?? '';
        $iconName = $attributes['iconName'] ?? '';
        $size = $attributes['size'] ?? 24;
        $color = $attributes['color'] ?? '';
        $cssClass = $attributes['cssClass'] ?? '';

        if (empty($pack) || empty($iconName)) {
            return '';
        }

        $shortcode = sprintf(
            '[icon pack="%s" name="%s" size="%d"',
            esc_attr($pack),
            esc_attr($iconName),
            $size
        );

        if ($color) {
            $shortcode .= sprintf(' color="%s"', esc_attr($color));
        }

        if ($cssClass) {
            $shortcode .= sprintf(' class="%s"', esc_attr($cssClass));
        }

        $shortcode .= ']';

        return do_shortcode($shortcode);
    }
}
