<?php
/**
 * State Storage
 *
 * Handles persisting state to various storage backends.
 *
 * @package Loom\Core\State
 */

namespace Loom\Core\State;

class StateStorage {

    private static $cache = [];

    /**
     * Get state from cache/transient storage
     */
    public static function get($sessionId, $stateClass) {
        $key = self::getKey($sessionId, $stateClass);

        // Check memory cache
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        // Check object cache (Redis/Memcached if available)
        $cached = wp_cache_get($key, 'loom_state');
        if ($cached !== false) {
            self::$cache[$key] = $cached;
            return $cached;
        }

        // Fall back to transient
        $data = get_transient($key);
        if ($data !== false) {
            self::$cache[$key] = $data;
            return $data;
        }

        return [];
    }

    /**
     * Save state to cache/transient storage
     */
    public static function save($sessionId, $stateClass, $data) {
        $key = self::getKey($sessionId, $stateClass);

        // Update memory cache
        self::$cache[$key] = $data;

        // Update object cache
        wp_cache_set($key, $data, 'loom_state', 3600);

        // Update transient (1 hour expiry)
        set_transient($key, $data, 3600);
    }

    /**
     * Get state from database
     */
    public static function getFromDatabase($sessionId, $stateClass) {
        global $wpdb;

        $key = self::getKey($sessionId, $stateClass);

        // Check cache first
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $row = $wpdb->get_var($wpdb->prepare(
            "SELECT state_data FROM {$wpdb->prefix}loom_state
             WHERE session_id = %s AND state_class = %s",
            $sessionId,
            $stateClass
        ));

        $data = $row ? json_decode($row, true) : [];
        self::$cache[$key] = $data;

        return $data;
    }

    /**
     * Save state to database
     */
    public static function saveToDatabase($sessionId, $stateClass, $data) {
        global $wpdb;

        $key = self::getKey($sessionId, $stateClass);
        self::$cache[$key] = $data;

        $wpdb->replace(
            "{$wpdb->prefix}loom_state",
            [
                'session_id' => $sessionId,
                'state_class' => $stateClass,
                'state_data' => json_encode($data),
                'updated_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s']
        );
    }

    /**
     * Delete state
     */
    public static function delete($sessionId, $stateClass) {
        global $wpdb;

        $key = self::getKey($sessionId, $stateClass);

        unset(self::$cache[$key]);
        wp_cache_delete($key, 'loom_state');
        delete_transient($key);

        $wpdb->delete(
            "{$wpdb->prefix}loom_state",
            [
                'session_id' => $sessionId,
                'state_class' => $stateClass,
            ]
        );
    }

    /**
     * Clear expired states
     */
    public static function cleanupExpired() {
        global $wpdb;

        // Delete state for expired sessions
        $deleted = $wpdb->query("
            DELETE s FROM {$wpdb->prefix}loom_state s
            LEFT JOIN {$wpdb->prefix}loom_sessions sess
                ON s.session_id = sess.session_id
            WHERE sess.id IS NULL
               OR sess.expires_at < NOW()
        ");

        return (int)$deleted;
    }

    /**
     * Generate cache key
     */
    private static function getKey($sessionId, $stateClass) {
        return "loom_state_{$sessionId}_{$stateClass}";
    }
}
