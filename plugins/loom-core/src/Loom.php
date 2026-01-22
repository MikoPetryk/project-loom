<?php
/**
 * Loom Facade
 *
 * Main entry point for Loom Core. Provides a clean API
 * that hides WordPress complexity.
 *
 * @package Loom\Core
 */



namespace Loom\Core;

use Loom\Core\Container\Container;
use Loom\Core\Session\SessionManager;
use Loom\Core\State\StateManager;
use Loom\Core\Realtime\EventBroadcaster;

class Loom {

    // ════════════════════════════════════════════════════════════════════════
    // LIFECYCLE - Hide add_action complexity
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Run when WordPress is fully loaded
     */
    public static function onReady(callable $callback): void {
        add_action('wp_loaded', $callback);
    }

    /**
     * Run before template rendering
     */
    public static function onRender(callable $callback): void {
        add_action('template_redirect', $callback);
    }

    /**
     * Run in wp_head
     */
    public static function onHead(callable $callback): void {
        add_action('wp_head', $callback);
    }

    /**
     * Run in wp_footer
     */
    public static function onFooter(callable $callback): void {
        add_action('wp_footer', $callback);
    }

    /**
     * Run on admin init
     */
    public static function onAdmin(callable $callback): void {
        add_action('admin_init', $callback);
    }

    // ════════════════════════════════════════════════════════════════════════
    // ASSETS - Hide wp_enqueue complexity
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Enqueue a stylesheet
     */
    public static function style(string $name, string $path, array $deps = []): void {
        add_action('wp_enqueue_scripts', function () use ($name, $path, $deps) {
            $url = self::resolveAssetUrl($path);
            wp_enqueue_style("loom-{$name}", $url, $deps, LOOM_CORE_VERSION);
        });
    }

    /**
     * Enqueue a script
     */
    public static function script(
        string $name,
        string $path,
        array $deps = [],
        bool $inFooter = true,
        bool $defer = false
    ): void {
        add_action('wp_enqueue_scripts', function () use ($name, $path, $deps, $inFooter, $defer) {
            $url = self::resolveAssetUrl($path);
            $handle = "loom-{$name}";

            wp_enqueue_script($handle, $url, $deps, LOOM_CORE_VERSION, $inFooter);

            if ($defer) {
                wp_script_add_data($handle, 'defer', true);
            }
        });
    }

    /**
     * Add inline CSS
     */
    public static function inlineStyle(string $css): void {
        add_action('wp_head', function () use ($css) {
            echo "<style>{$css}</style>";
        });
    }

    /**
     * Add inline JS
     */
    public static function inlineScript(string $js, bool $inFooter = true): void {
        $hook = $inFooter ? 'wp_footer' : 'wp_head';
        add_action($hook, function () use ($js) {
            echo "<script>{$js}</script>";
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    // STATE - Reactive state management
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Register a state class
     */
    public static function state(string $stateClass): void {
        StateManager::registerState($stateClass);

        // Store in registry for lightweight endpoint
        $registry = get_option('loom_state_registry', []);
        $name = self::getStateName($stateClass);
        $registry[$name] = $stateClass;
        update_option('loom_state_registry', $registry);
    }

    /**
     * Get state instance
     */
    public static function getState(string $name): object {
        return StateManager::getState($name);
    }

    // ════════════════════════════════════════════════════════════════════════
    // CONTAINER - Dependency injection
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Register a service
     */
    public static function bind(string $abstract, string|callable $concrete): void {
        Container::bind($abstract, $concrete);
    }

    /**
     * Register a singleton
     */
    public static function singleton(string $abstract, object $instance): void {
        Container::singleton($abstract, $instance);
    }

    /**
     * Get from container
     */
    public static function get(string $abstract): object {
        return Container::get($abstract);
    }

    // ════════════════════════════════════════════════════════════════════════
    // EVENTS - Real-time updates
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Publish an event
     */
    public static function publish(string $channel, array $data): void {
        EventBroadcaster::publish($channel, $data);
    }

    /**
     * Broadcast to all clients
     */
    public static function broadcast(string $channel, array $data): void {
        EventBroadcaster::broadcast($channel, $data);
    }

    // ════════════════════════════════════════════════════════════════════════
    // SESSION - User session management
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Get current session ID
     */
    public static function sessionId(): ?string {
        return SessionManager::getSessionId();
    }

    /**
     * Get current user ID
     */
    public static function userId(): ?int {
        return SessionManager::getUserId();
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool {
        return SessionManager::isLoggedIn();
    }

    // ════════════════════════════════════════════════════════════════════════
    // PLUGINS
    // ════════════════════════════════════════════════════════════════════════

    private static array $plugins = [];

    /**
     * Register a plugin with Loom
     */
    public static function plugin(string $name, callable $callback): void {
        self::$plugins[$name] = $callback;
        $callback(new PluginBuilder($name));
    }

    /**
     * Get a registered plugin
     */
    public static function getPlugin(string $name): ?object {
        return self::$plugins[$name] ?? null;
    }

    // ════════════════════════════════════════════════════════════════════════
    // ESCAPE HATCH - Direct WP access when needed
    // ════════════════════════════════════════════════════════════════════════

    /**
     * Execute raw WordPress action
     */
    public static function action(string $action, callable $callback, int $priority = 10): void {
        add_action($action, $callback, $priority);
    }

    /**
     * Execute raw WordPress filter
     */
    public static function filter(string $filter, callable $callback, int $priority = 10): void {
        add_filter($filter, $callback, $priority);
    }

    /**
     * Get WordPress database
     */
    public static function db(): \wpdb {
        global $wpdb;
        return $wpdb;
    }

    // ════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════════════════════

    private static function resolveAssetUrl(string $path): string {
        // Absolute URL
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Theme asset
        if (str_starts_with($path, 'theme:')) {
            return get_template_directory_uri() . '/' . substr($path, 6);
        }

        // Plugin asset
        if (str_starts_with($path, 'plugin:')) {
            return plugins_url(substr($path, 7));
        }

        // Default: relative to Loom Core
        return LOOM_CORE_URL . 'assets/' . $path;
    }

    private static function getStateName(string $class): string {
        $parts = explode('\\', $class);
        $name = end($parts);
        return lcfirst(preg_replace('/State$/', '', $name));
    }
}

/**
 * Plugin Builder for fluent plugin registration
 */
class PluginBuilder {

    private string $name;
    private array $config = [];

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function name(string $name): self {
        $this->config['name'] = $name;
        return $this;
    }

    public function version(string $version): self {
        $this->config['version'] = $version;
        return $this;
    }

    public function service(string $class): self {
        Container::bind($class, $class);
        return $this;
    }

    public function state(string $class): self {
        Loom::state($class);
        return $this;
    }

    public function api(string $controller): self {
        // Register REST controller
        add_action('rest_api_init', function () use ($controller) {
            $instance = new $controller();
            if (method_exists($instance, 'register_routes')) {
                $instance->register_routes();
            }
        });
        return $this;
    }

    public function adminPage(string $slug, string $class): self {
        add_action('admin_menu', function () use ($slug, $class) {
            $page = new $class();
            if (method_exists($page, 'register')) {
                $page->register();
            }
        });
        return $this;
    }

    public function assets(string $path): self {
        $this->config['assets'] = $path;
        return $this;
    }

    public function hook(string $hook, callable $callback): self {
        add_action("loom_{$hook}", $callback);
        return $this;
    }

    public function extends(string $plugin, string $hook, callable $callback): self {
        add_filter("loom_{$plugin}_{$hook}", $callback, 10, 2);
        return $this;
    }
}
