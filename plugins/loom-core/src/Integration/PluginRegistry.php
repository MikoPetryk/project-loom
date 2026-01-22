<?php
/**
 * Plugin Registry
 *
 * Central registry for Loom plugin discovery and availability.
 * Enables graceful integration between plugins while maintaining
 * standalone capability.
 *
 * @package Loom\Core\Integration
 */

namespace Loom\Core\Integration;

/**
 * Central registry for Loom plugin discovery.
 *
 * Plugins register themselves here, allowing other plugins to
 * check availability before using features.
 */
class PluginRegistry {

    /** @var array<string, bool> Registered plugins */
    private static $plugins = [];

    /** @var array<string, array> Plugin capabilities */
    private static $capabilities = [];

    // Plugin identifiers
    public const ICON_MANAGER = 'icon_manager';
    public const THEME_MANAGER = 'theme_manager';
    public const NOTI = 'noti';
    public const LOOM_CORE = 'loom_core';

    /**
     * Register a plugin as available
     *
     * @param string $plugin Plugin identifier (use class constants)
     * @param array $capabilities List of features the plugin provides
     */
    public static function register($plugin, $capabilities = []) {
        self::$plugins[$plugin] = true;
        self::$capabilities[$plugin] = $capabilities;

        /**
         * Fires when a specific plugin is registered.
         *
         * @param array $capabilities Plugin capabilities
         */
        do_action("loom_plugin_registered_{$plugin}", $capabilities);

        /**
         * Fires when any plugin is registered.
         *
         * @param string $plugin Plugin identifier
         * @param array $capabilities Plugin capabilities
         */
        do_action('loom_plugin_registered', $plugin, $capabilities);
    }

    /**
     * Check if a plugin is available
     *
     * @param string $plugin Plugin identifier
     * @return bool True if plugin is registered
     */
    public static function has($plugin) {
        return isset(self::$plugins[$plugin]) && self::$plugins[$plugin] === true;
    }

    /**
     * Check if Icon Manager is available
     *
     * @return bool True if Icon Manager is registered and classes exist
     */
    public static function hasIconManager() {
        return self::has(self::ICON_MANAGER)
            && interface_exists(\IconManager\Features\Packs\IconPackInterface::class);
    }

    /**
     * Check if Theme Manager is available
     *
     * @return bool True if Theme Manager is registered and classes exist
     */
    public static function hasThemeManager() {
        return self::has(self::THEME_MANAGER)
            && class_exists(\Loom\ThemeManager\Features\Tokens\Colors::class);
    }

    /**
     * Check if Noti is available
     *
     * @return bool True if Noti is registered and classes exist
     */
    public static function hasNoti() {
        return self::has(self::NOTI)
            && class_exists(\Loom\Noti\Features\Notifications\Noti::class);
    }

    /**
     * Get all registered plugins
     *
     * @return array List of registered plugin identifiers
     */
    public static function getAll() {
        return array_keys(array_filter(self::$plugins));
    }

    /**
     * Get plugin capabilities
     *
     * @param string $plugin Plugin identifier
     * @return array List of capabilities
     */
    public static function getCapabilities($plugin) {
        return self::$capabilities[$plugin] ?? [];
    }

    /**
     * Check if a plugin has a specific capability
     *
     * @param string $plugin Plugin identifier
     * @param string $capability Capability to check
     * @return bool True if plugin has the capability
     */
    public static function hasCapability($plugin, $capability) {
        return in_array($capability, self::getCapabilities($plugin), true);
    }

    /**
     * Run callback when a plugin becomes available
     *
     * If the plugin is already available, runs immediately.
     * Otherwise, waits for plugin registration.
     *
     * @param string $plugin Plugin identifier
     * @param callable $callback Function to run when plugin is available
     */
    public static function whenAvailable($plugin, $callback) {
        if (self::has($plugin)) {
            $callback();
        } else {
            add_action("loom_plugin_registered_{$plugin}", $callback);
        }
    }

    /**
     * Unregister a plugin (primarily for testing)
     *
     * @param string $plugin Plugin identifier
     */
    public static function unregister($plugin) {
        unset(self::$plugins[$plugin], self::$capabilities[$plugin]);
    }

    /**
     * Reset registry (primarily for testing)
     */
    public static function reset() {
        self::$plugins = [];
        self::$capabilities = [];
    }
}
