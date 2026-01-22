<?php
/**
 * Block Assets
 *
 * Enqueues Gutenberg block editor assets.
 *
 * @package IconManager\Features\Blocks
 * @since 2.1.0
 */



namespace IconManager\Features\Blocks;

class BlockAssets {

    public function __construct() {
        add_action('enqueue_block_editor_assets', [$this, 'enqueueEditorAssets']);
    }

    public function enqueueEditorAssets(): void {
        $asset_file = ICON_MANAGER_PLUGIN_DIR . 'build/blocks/index.asset.php';

        if (!file_exists($asset_file)) {
            wp_enqueue_script(
                'icon-manager-block-editor',
                ICON_MANAGER_PLUGIN_URL . 'build/blocks/index.js',
                ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-api-fetch'],
                ICON_MANAGER_VERSION,
                true
            );
        } else {
            $asset = include $asset_file;

            wp_enqueue_script(
                'icon-manager-block-editor',
                ICON_MANAGER_PLUGIN_URL . 'build/blocks/index.js',
                $asset['dependencies'],
                $asset['version'],
                true
            );
        }

        wp_localize_script('icon-manager-block-editor', 'iconManagerData', [
            'nonce' => wp_create_nonce('wp_rest'),
            'restUrl' => rest_url('icon-manager/v1/'),
            'pluginUrl' => ICON_MANAGER_PLUGIN_URL
        ]);

        $css_file = ICON_MANAGER_PLUGIN_DIR . 'build/blocks/index.css';
        if (file_exists($css_file)) {
            $version = file_exists($asset_file) ? (include $asset_file)['version'] : ICON_MANAGER_VERSION;
            wp_enqueue_style(
                'icon-manager-block-editor',
                ICON_MANAGER_PLUGIN_URL . 'build/blocks/index.css',
                [],
                $version
            );
        }
    }
}
