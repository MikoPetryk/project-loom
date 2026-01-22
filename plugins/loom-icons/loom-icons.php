<?php
/**
 * Plugin Name: Loom Icons
 * Plugin URI: https://github.com/MikoPetryk/project-loom
 * Description: A modern icon management system with IDE autocomplete and type safety
 * Version: 1.0.0 (PoC)
 * Author: Mykola Petryk
 * Author URI: https://github.com/MikoPetryk
 * License: GPL-2.0-or-later
 * Text Domain: loom-icons
 * Requires at least: 6.0
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) exit;

define('ICON_MANAGER_VERSION', '1.0.0');
define('ICON_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ICON_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ICON_MANAGER_ICONS_DIR', ICON_MANAGER_PLUGIN_DIR . 'materials/icons/');
define('ICON_MANAGER_ICONS_URL', ICON_MANAGER_PLUGIN_URL . 'materials/icons/');

// PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'IconManager\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;

    $relative = substr($class, strlen($prefix));
    $file = ICON_MANAGER_PLUGIN_DIR . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) require_once $file;
});

// Autoloader for enum files
spl_autoload_register(function ($class) {
    if (strpos($class, 'IconManager\\IconPacks\\') === 0) {
        $enumName = str_replace('IconManager\\IconPacks\\', '', $class);
        $file = ICON_MANAGER_PLUGIN_DIR . 'data/' . $enumName . '.php';

        if (file_exists($file)) require_once $file;
    }
});

// Initialize plugin
add_action('plugins_loaded', function () {
    // Admin
    if (is_admin()) {
        new \IconManager\Features\Admin\AdminPage();
        new \IconManager\Features\Integration\TinyMCEButton();
        new \IconManager\Features\Blocks\BlockAssets();
    }

    // Blocks
    new \IconManager\Features\Blocks\BlockRegistrar();

    // Shortcode
    new \IconManager\Features\Integration\Shortcode();

    // REST API
    add_action('rest_api_init', function () {
        $controllers = array(
            new \IconManager\Features\Packs\IconPackController(),
            new \IconManager\Features\Icons\IconController(),
            new \IconManager\Features\Icons\IconBatchController(),
            new \IconManager\Features\Icons\IconRenderController(),
            new \IconManager\Features\Packs\StatsController(),
        );

        foreach ($controllers as $controller) {
            $controller->register_routes();
        }
    });
});

// Activation
register_activation_hook(__FILE__, function () {
    \IconManager\Support\Activator::activate();
});

// Deactivation
register_deactivation_hook(__FILE__, function () {
    delete_transient('icon_manager_upload_errors');
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
});

// Helper function
if (!function_exists('Icon')) {
    function Icon($iconEnum) {
        return new \IconManager\Features\Icons\IconBuilder($iconEnum);
    }
}

// Load generated IconsManager if exists
$generatedIconsManager = ICON_MANAGER_PLUGIN_DIR . 'data/IconsManager.php';
if (file_exists($generatedIconsManager)) {
    require_once $generatedIconsManager;
}

// Register with Loom Plugin Registry if available
add_action('plugins_loaded', function () {
    if (class_exists('\Loom\Core\Integration\PluginRegistry')) {
        \Loom\Core\Integration\PluginRegistry::register(
            \Loom\Core\Integration\PluginRegistry::ICON_MANAGER,
            array('icon_builder', 'icon_packs', 'icon_renderer', 'rest_api')
        );
    }

    /**
     * Fires when Icon Manager is fully loaded.
     */
    do_action('icon_manager_loaded');
}, 15);

// Fallback IconsManager with magic methods
if (!class_exists('IconsManager')) {
    class IconsManager {
        private static $instance = null;

        public static function getInstance() {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __call($name, $arguments) {
            return $this->renderIcon($name, $arguments);
        }

        public static function __callStatic($name, $arguments) {
            return self::getInstance()->renderIcon($name, $arguments);
        }

        private function renderIcon($name, $arguments) {
            $packs = \IconManager\Features\Packs\IconPackManager::getPackNames();

            usort($packs, function($a, $b) {
                return strlen($b) - strlen($a);
            });

            foreach ($packs as $pack) {
                if (stripos($name, $pack) === 0) {
                    $iconName = substr($name, strlen($pack));
                    if (!empty($iconName)) {
                        $width = isset($arguments[0]) ? $arguments[0] : null;
                        $height = isset($arguments[1]) ? $arguments[1] : null;
                        $class = isset($arguments[2]) ? $arguments[2] : null;
                        $style = isset($arguments[3]) ? $arguments[3] : null;
                        $id = isset($arguments[4]) ? $arguments[4] : null;

                        return \IconManager\Features\Icons\IconRenderer::render(
                            $pack, $iconName, $width, $height, $class, $style, $id
                        );
                    }
                }
            }

            if (defined('WP_DEBUG') && WP_DEBUG) {
                return '<!-- IconsManager: Method not found: ' . esc_html($name) . ' -->';
            }
            return '';
        }
    }
}
