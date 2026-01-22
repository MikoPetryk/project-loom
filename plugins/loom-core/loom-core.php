<?php
/**
 * Plugin Name: Loom Core
 * Plugin URI: https://github.com/MikoPetryk/project-loom
 * Description: The foundation of Project Loom - A modern, declarative PHP framework for WordPress
 * Version: 1.0.0 (PoC)
 * Author: Mykola Petryk
 * Author URI: https://github.com/MikoPetryk
 * License: GPL-2.0-or-later
 * Requires PHP: 8.1
 *
 * @package Loom\Core
 */



// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('LOOM_CORE_VERSION', '1.0.0');
define('LOOM_CORE_PATH', plugin_dir_path(__FILE__));
define('LOOM_CORE_URL', plugin_dir_url(__FILE__));
define('LOOM_CACHE_PATH', WP_CONTENT_DIR . '/cache/loom/');

/**
 * PSR-4 Autoloader
 */
spl_autoload_register(function (string $class): void {
    $prefixes = [
        'Loom\\Core\\' => LOOM_CORE_PATH . 'src/',
        'Loom\\Components\\' => LOOM_CORE_PATH . 'src/Components/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// Initialize Loom Core
require_once LOOM_CORE_PATH . 'src/Bootstrap.php';

// Register activation/deactivation hooks
register_activation_hook(__FILE__, [\Loom\Core\Bootstrap::class, 'activate']);
register_deactivation_hook(__FILE__, [\Loom\Core\Bootstrap::class, 'deactivate']);

// Boot the application
\Loom\Core\Bootstrap::init();

// Ensure tables exist (for plugins already activated before this code)
add_action('plugins_loaded', function(): void {
    global $wpdb;
    $table = $wpdb->prefix . 'loom_sessions';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
        \Loom\Core\Bootstrap::activate();
    }
}, 5);
