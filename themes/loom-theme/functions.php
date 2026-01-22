<?php
/**
 * Loom Theme Functions
 *
 * @package Loom\Theme
 * @since 1.0.0
 */

declare(strict_types=1);

define('LOOM_THEME_VERSION', '1.0.0');

/**
 * Theme setup
 */
add_action('after_setup_theme', function(): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);
});

/**
 * Check if Loom Core plugin is active
 */
function loom_theme_is_core_active(): bool {
    return class_exists('\Loom\Core\Components\Modifier');
}

/**
 * Admin notice if Loom Core is not active
 */
add_action('admin_notices', function(): void {
    if (!loom_theme_is_core_active()) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>Loom Theme:</strong> This theme requires the Loom Core plugin to be installed and activated.</p>';
        echo '</div>';
    }
});
