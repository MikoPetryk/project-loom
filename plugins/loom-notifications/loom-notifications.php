<?php
/**
 * Plugin Name: Loom Notifications
 * Plugin URI: https://github.com/MikoPetryk/project-loom
 * Description: Toast notification system with PHP and JS API, Loom Icons integration, and action support.
 * Version: 1.0.0 (PoC)
 * Author: Mykola Petryk
 * Author URI: https://github.com/MikoPetryk
 * License: GPL-2.0-or-later
 * Text Domain: loom-notifications
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) exit;

define('NOTI_VERSION', '1.0.0');
define('NOTI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NOTI_PLUGIN_URL', plugin_dir_url(__FILE__));

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'Loom\\Noti\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;

    $relative = substr($class, strlen($prefix));
    $file = NOTI_PLUGIN_DIR . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) require_once $file;
});

// Activation
register_activation_hook(__FILE__, function () {
    if (get_option('noti_settings') === false) {
        add_option('noti_settings', \Loom\Noti\Features\Settings\SettingsPage::getDefaults());
    }
});

// Initialize
add_action('plugins_loaded', function () {
    // Settings
    new \Loom\Noti\Features\Settings\SettingsPage();

    // REST API
    add_action('rest_api_init', function () {
        (new \Loom\Noti\Features\Notifications\NotificationController())->register_routes();
    });

    // Render queue in footer
    add_action('wp_footer', array('\Loom\Noti\Features\Notifications\Noti', 'renderQueue'));
    add_action('admin_footer', array('\Loom\Noti\Features\Notifications\Noti', 'renderQueue'));

    // Register with Loom Plugin Registry if available
    if (class_exists('\Loom\Core\Integration\PluginRegistry')) {
        \Loom\Core\Integration\PluginRegistry::register(
            \Loom\Core\Integration\PluginRegistry::NOTI,
            array('notifications', 'queue', 'progress', 'rest_api')
        );
    }

    /**
     * Fires when Noti is fully loaded.
     */
    do_action('noti_loaded');
});

// Global function for easy access
if (!function_exists('noti')) {
    function noti($type, $message, $options = array()) {
        \Loom\Noti\Features\Notifications\Noti::enqueue($type, $message, $options);
    }
}
