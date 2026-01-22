<?php
/**
 * Queue Manager
 *
 * Manages notification queue using transients for cross-request persistence.
 *
 * @package Loom\Noti\Features\Notifications
 * @since 1.0.0
 */



namespace Loom\Noti\Features\Notifications;

class QueueManager {

    private const TRANSIENT_KEY = 'noti_queue_';
    private const EXPIRATION = 60; // 1 minute

    public static function add(array $notification): void {
        $queue = self::get();
        $queue[] = $notification;
        self::save($queue);
    }

    public static function get(): array {
        $key = self::getKey();
        $queue = get_transient($key);

        return is_array($queue) ? $queue : [];
    }

    public static function clear(): void {
        $key = self::getKey();
        delete_transient($key);
    }

    private static function save(array $queue): void {
        $key = self::getKey();
        set_transient($key, $queue, self::EXPIRATION);
    }

    private static function getKey(): string {
        $user_id = get_current_user_id();
        if ($user_id > 0) {
            return self::TRANSIENT_KEY . 'user_' . $user_id;
        }

        // For guests, use session-based key
        if (!session_id()) {
            @session_start();
        }
        return self::TRANSIENT_KEY . 'session_' . session_id();
    }
}
