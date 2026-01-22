<?php
/**
 * Asset Manager
 *
 * Handles Loom Core assets and state hydration.
 *
 * @package Loom\Core\Assets
 */



namespace Loom\Core\Assets;

use Loom\Core\Session\SessionManager;
use Loom\Core\State\StateManager;

class AssetManager {

    /** @var bool Whether to use file modification time for versioning */
    private static bool $useFileMtime = true;

    /**
     * Get version string for an asset (file mtime for cache busting, or static version)
     */
    private static function getAssetVersion(string $relativePath): string {
        if (!self::$useFileMtime) {
            return LOOM_CORE_VERSION;
        }

        $filePath = LOOM_CORE_PATH . $relativePath;
        if (file_exists($filePath)) {
            return (string) filemtime($filePath);
        }

        return LOOM_CORE_VERSION;
    }

    /**
     * Enqueue Loom Core assets
     */
    public static function enqueue(): void {
        // Core runtime JS (use file mtime for cache busting)
        wp_enqueue_script(
            'loom-runtime',
            LOOM_CORE_URL . 'assets/js/loom-runtime.js',
            [],
            self::getAssetVersion('assets/js/loom-runtime.js'),
            true
        );

        // Localize with config
        wp_localize_script('loom-runtime', 'LoomConfig', [
            'stateUrl' => home_url('/loom/state/'),
            'eventsUrl' => home_url('/loom/events/'),
            'nonce' => SessionManager::generateNonce(),
            'session' => SessionManager::getToken(),
            'userId' => SessionManager::getUserId(),
            'isLoggedIn' => SessionManager::isLoggedIn(),
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
        ]);

        // Core CSS (use file mtime for cache busting)
        wp_enqueue_style(
            'loom-core',
            LOOM_CORE_URL . 'assets/css/loom-core.css',
            [],
            self::getAssetVersion('assets/css/loom-core.css')
        );

        // Add cache control headers filter
        add_filter('style_loader_tag', [self::class, 'addCacheHeaders'], 10, 2);
        add_filter('script_loader_tag', [self::class, 'addScriptCacheHeaders'], 10, 2);
    }

    /**
     * Add cache hint via resource hints (preconnect, prefetch)
     */
    public static function addResourceHints(): void {
        add_action('wp_head', function() {
            // Preload critical assets
            $cssUrl = LOOM_CORE_URL . 'assets/css/loom-core.css';
            echo '<link rel="preload" href="' . esc_url($cssUrl) . '" as="style">' . "\n";
        }, 1);
    }

    /**
     * Filter to suggest immutable caching for versioned assets
     * Note: Actual Cache-Control headers require server config
     */
    public static function addCacheHeaders(string $html, string $handle): string {
        // For loom assets, we could add data attributes that a service worker could use
        if (strpos($handle, 'loom') === 0) {
            return str_replace('<link', '<link data-cache="immutable"', $html);
        }
        return $html;
    }

    /**
     * Filter for script tags
     */
    public static function addScriptCacheHeaders(string $html, string $handle): string {
        if (strpos($handle, 'loom') === 0) {
            return str_replace('<script', '<script data-cache="immutable"', $html);
        }
        return $html;
    }

    /**
     * Render state hydration in footer
     */
    public static function renderStateHydration(): void {
        $states = StateManager::getHydrationData();
        $actions = StateManager::getActionsMetadata();

        if (empty($states)) {
            return;
        }

        $hydrationData = json_encode([
            'states' => $states,
            'actions' => $actions,
        ]);

        echo "<script id=\"loom-hydration\" type=\"application/json\">{$hydrationData}</script>\n";
        echo "<script>window.LoomState && window.LoomState.hydrate();</script>\n";
    }
}
