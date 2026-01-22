<?php
/**
 * Loom Core Bootstrap
 *
 * Initializes all core systems.
 *
 * @package Loom\Core
 */

namespace Loom\Core;

use Loom\Core\Annotations\AnnotationProcessor;
use Loom\Core\State\StateManager;
use Loom\Core\Realtime\EventBroadcaster;
use Loom\Core\Session\SessionManager;
use Loom\Core\Assets\AssetManager;
use Loom\Core\Routing\Router;
use Loom\Core\Integration\PluginRegistry;
use Loom\Core\Integration\NotiSnackbarBridge;

class Bootstrap {

    private static $initialized = false;

    public static function init() {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        // Create cache directory
        self::ensureCacheDirectory();

        // Initialize core systems
        self::initAnnotations();
        self::initComponents();
        self::initState();
        self::initSession();
        self::initAssets();
        self::initRealtime();
        self::initRoutes();

        // Register activation/deactivation hooks
        register_activation_hook(LOOM_CORE_PATH . 'loom-core.php', array(self::class, 'activate'));
        register_deactivation_hook(LOOM_CORE_PATH . 'loom-core.php', array(self::class, 'deactivate'));

        // Initialize plugin integrations
        self::initIntegrations();

        // Load the Loom facade
        require_once LOOM_CORE_PATH . 'src/Loom.php';

        // Fire loom_core_loaded after theme loads so themes can hook into it
        add_action('after_setup_theme', function() {
            do_action('loom_core_loaded');
        }, 0);
    }

    private static function initIntegrations() {
        // Load integration classes
        require_once LOOM_CORE_PATH . 'src/Integration/PluginRegistry.php';
        require_once LOOM_CORE_PATH . 'src/Integration/TokenHelper.php';
        require_once LOOM_CORE_PATH . 'src/Integration/IconHelper.php';
        require_once LOOM_CORE_PATH . 'src/Integration/NotiSnackbarBridge.php';

        // Register Loom Core itself
        PluginRegistry::register(
            PluginRegistry::LOOM_CORE,
            array('components', 'state', 'routing', 'session', 'realtime')
        );

        // Setup cross-plugin integrations when all plugins are loaded
        add_action('plugins_loaded', array(self::class, 'setupIntegrations'), 20);
    }

    public static function setupIntegrations() {
        // Auto-enable Noti-Snackbar bridge if both plugins are present
        if (PluginRegistry::hasNoti()) {
            $enableBridge = apply_filters('loom_noti_snackbar_bridge', true);

            if ($enableBridge) {
                NotiSnackbarBridge::enable();
            }
        }

        do_action('loom_integrations_ready');
    }

    private static function ensureCacheDirectory() {
        $dirs = array(
            LOOM_CACHE_PATH,
            LOOM_CACHE_PATH . 'functions/',
            LOOM_CACHE_PATH . 'views/',
            LOOM_CACHE_PATH . 'state/',
        );

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    private static function initAnnotations() {
        AnnotationProcessor::init();
    }

    private static function initComponents() {
        // Load component classes
        require_once LOOM_CORE_PATH . 'src/Components/Modifier.php';
        require_once LOOM_CORE_PATH . 'src/Components/Component.php';
        require_once LOOM_CORE_PATH . 'src/Components/Foundation.php';
        require_once LOOM_CORE_PATH . 'src/Components/Basic.php';
        require_once LOOM_CORE_PATH . 'src/Components/Containment.php';
        require_once LOOM_CORE_PATH . 'src/Components/Feedback.php';
        require_once LOOM_CORE_PATH . 'src/Components/Input.php';

        // Load global functions
        require_once LOOM_CORE_PATH . 'src/Components/functions.php';

        // Add component CSS
        add_action('wp_head', array(self::class, 'renderComponentStyles'), 1);
        add_action('admin_head', array(self::class, 'renderComponentStyles'), 1);
    }

    public static function renderComponentStyles() {
        echo '<style id="loom-components">
/* Loom Component Styles */
.loom-tooltip-wrapper:hover .loom-tooltip { opacity: 1; }

/* Interactive states */
.loom-hoverable {
    transition: background-color 0.2s ease, opacity 0.2s ease;
}
.loom-hoverable:hover {
    background-color: rgba(0, 0, 0, 0.04);
}
.loom-focusable:focus {
    outline: 2px solid var(--loom-primary);
    outline-offset: 2px;
}
.loom-focusable:focus:not(:focus-visible) {
    outline: none;
}
.loom-focusable:focus-visible {
    outline: 2px solid var(--loom-primary);
    outline-offset: 2px;
}

/* Progress animations */
@keyframes loom-progress-indeterminate {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(400%); }
}
@keyframes loom-progress-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Slider styling */
.loom-slider::-webkit-slider-runnable-track {
    height: 4px;
    background: var(--loom-border);
    border-radius: 2px;
}
.loom-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 16px;
    height: 16px;
    background: var(--loom-primary);
    border-radius: 50%;
    margin-top: -6px;
    cursor: pointer;
}
.loom-slider::-moz-range-track {
    height: 4px;
    background: var(--loom-border);
    border-radius: 2px;
}
.loom-slider::-moz-range-thumb {
    width: 16px;
    height: 16px;
    background: var(--loom-primary);
    border-radius: 50%;
    border: none;
    cursor: pointer;
}

/* Smooth scroll */
.loom-scroll-smooth { scroll-behavior: smooth; }
</style>';
    }

    private static function initState() {
        add_action('init', array(StateManager::class, 'init'), 1);
    }

    private static function initSession() {
        add_action('init', array(SessionManager::class, 'start'), 0);
    }

    private static function initAssets() {
        // Frontend assets
        add_action('wp_enqueue_scripts', array(AssetManager::class, 'enqueue'), 1);
        add_action('wp_footer', array(AssetManager::class, 'renderStateHydration'), 99);

        // Admin assets (for demo page and admin components)
        add_action('admin_enqueue_scripts', array(AssetManager::class, 'enqueue'), 1);
        add_action('admin_footer', array(AssetManager::class, 'renderStateHydration'), 99);
    }

    private static function initRealtime() {
        EventBroadcaster::init();
    }

    private static function initRoutes() {
        // Register lightweight state endpoint
        add_action('init', function () {
            add_rewrite_rule(
                '^loom/state/?$',
                'index.php?loom_endpoint=state',
                'top'
            );
            add_rewrite_rule(
                '^loom/events/?$',
                'index.php?loom_endpoint=events',
                'top'
            );
        });

        add_filter('query_vars', function ($vars) {
            $vars[] = 'loom_endpoint';
            return $vars;
        });

        add_action('template_redirect', array(self::class, 'handleEndpoints'));
    }

    public static function handleEndpoints() {
        $endpoint = get_query_var('loom_endpoint');

        if (!$endpoint) {
            return;
        }

        switch ($endpoint) {
            case 'state':
                require_once LOOM_CORE_PATH . 'endpoints/state.php';
                exit;
            case 'events':
                require_once LOOM_CORE_PATH . 'endpoints/events.php';
                exit;
        }
    }

    public static function activate() {
        self::createDatabaseTables();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        flush_rewrite_rules();
    }

    private static function createDatabaseTables() {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();

        $sessions_table = "CREATE TABLE {$wpdb->prefix}loom_sessions (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            session_id varchar(64) NOT NULL,
            token varchar(64) NOT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            data longtext NOT NULL,
            expires_at datetime NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY session_id (session_id),
            UNIQUE KEY token (token),
            KEY idx_user_id (user_id),
            KEY idx_expires (expires_at)
        ) {$charset};";

        $state_table = "CREATE TABLE {$wpdb->prefix}loom_state (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            session_id varchar(64) NOT NULL,
            state_class varchar(255) NOT NULL,
            state_data longtext NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY unique_session_state (session_id,state_class),
            KEY idx_session (session_id)
        ) {$charset};";

        $events_table = "CREATE TABLE {$wpdb->prefix}loom_events (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            channel varchar(255) NOT NULL,
            data longtext NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY idx_channel (channel),
            KEY idx_created (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sessions_table);
        dbDelta($state_table);
        dbDelta($events_table);
    }
}
