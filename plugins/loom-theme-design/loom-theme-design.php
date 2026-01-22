<?php
/**
 * Plugin Name: Loom Theme Design
 * Plugin URI: https://github.com/MikoPetryk/project-loom
 * Description: Centralized design tokens system for Project Loom.
 * Version: 1.0.0 (PoC)
 * Author: Mykola Petryk
 * Author URI: https://github.com/MikoPetryk
 * License: GPL-2.0-or-later
 * Text Domain: loom-theme-design
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) exit;

define('THEME_MANAGER_VERSION', '1.0.0');
define('THEME_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('THEME_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'Loom\\ThemeManager\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;

    $relative = substr($class, strlen($prefix));
    $file = THEME_MANAGER_PLUGIN_DIR . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) require_once $file;
});

// Activation
register_activation_hook(__FILE__, function () {
    if (get_option('loom_theme_tokens') === false) {
        add_option('loom_theme_tokens', array(
            'colors' => \Loom\ThemeManager\Features\Tokens\Colors::getDefaults(),
            'typography' => \Loom\ThemeManager\Features\Tokens\Typography::getDefaults(),
            'spacing' => \Loom\ThemeManager\Features\Tokens\Spacing::getDefaults(),
            'shapes' => \Loom\ThemeManager\Features\Tokens\Shapes::getDefaults(),
        ));
    }
});

// Initialize
add_action('plugins_loaded', function () {
    // Load tokens
    $tokens = get_option('loom_theme_tokens', array());
    \Loom\ThemeManager\Features\Tokens\TokenRegistry::load($tokens);

    // CSS output
    add_action('wp_head', array('\Loom\ThemeManager\Features\Tokens\CssGenerator', 'output'), 1);
    add_action('admin_head', array('\Loom\ThemeManager\Features\Tokens\CssGenerator', 'output'), 1);

    // Frontend theme switcher script
    add_action('wp_enqueue_scripts', function () {
        wp_enqueue_script(
            'loom-theme-switcher',
            THEME_MANAGER_PLUGIN_URL . 'assets/js/theme-switcher.js',
            array(),
            THEME_MANAGER_VERSION,
            array('in_footer' => false, 'strategy' => 'defer')
        );
    });

    // REST API
    add_action('rest_api_init', function () {
        (new \Loom\ThemeManager\Features\Tokens\TokenController())->register_routes();
    });

    // Admin
    if (is_admin()) {
        new \Loom\ThemeManager\Features\Settings\SettingsPage();
    }

    // Register with Loom Plugin Registry if available
    if (class_exists('\Loom\Core\Integration\PluginRegistry')) {
        \Loom\Core\Integration\PluginRegistry::register(
            \Loom\Core\Integration\PluginRegistry::THEME_MANAGER,
            array('colors', 'typography', 'spacing', 'shapes', 'css_variables', 'rest_api')
        );
    }

    /**
     * Fires when Theme Manager is fully loaded.
     */
    do_action('theme_manager_loaded');
});
