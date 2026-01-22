<?php
/**
 * Event Broadcaster
 *
 * Publishes events for real-time updates via SSE.
 *
 * @package Loom\Core\Realtime
 */

namespace Loom\Core\Realtime;

use Loom\Core\Session\SessionManager;

class EventBroadcaster {

    private static $initialized = false;

    public static function init() {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        // Hook into state updates
        add_action('loom_state_updated', [self::class, 'onStateUpdated'], 10, 3);

        // Hook into WooCommerce if available
        if (class_exists('WooCommerce')) {
            add_action('woocommerce_new_product', [self::class, 'onProductCreated']);
            add_action('woocommerce_update_product', [self::class, 'onProductUpdated']);
            add_action('woocommerce_delete_product', [self::class, 'onProductDeleted']);
        }

        // General post hooks
        add_action('save_post', [self::class, 'onPostSaved'], 10, 3);
        add_action('delete_post', [self::class, 'onPostDeleted']);

        // Schedule cleanup
        add_action('loom_events_cleanup', [self::class, 'cleanup']);

        if (!wp_next_scheduled('loom_events_cleanup')) {
            wp_schedule_event(time(), 'hourly', 'loom_events_cleanup');
        }
    }

    /**
     * Publish an event
     */
    public static function publish($channel, $data, $broadcast = false) {
        global $wpdb;

        // Add session info
        if (!$broadcast) {
            $data['_session'] = SessionManager::getSessionId();
        }
        $data['_broadcast'] = $broadcast;

        $wpdb->insert(
            "{$wpdb->prefix}loom_events",
            [
                'channel' => $channel,
                'data' => json_encode($data),
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s']
        );

        // Also publish to Redis if available
        if (class_exists('Redis')) {
            try {
                $redis = new \Redis();
                if ($redis->connect('127.0.0.1', 6379, 0.5)) {
                    $redis->publish("loom:{$channel}", json_encode($data));
                }
            } catch (\Exception $e) {
                // Redis not available, skip
            }
        }

        do_action('loom_event_published', $channel, $data);
    }

    /**
     * Broadcast to all sessions
     */
    public static function broadcast($channel, $data) {
        self::publish($channel, $data, true);
    }

    /**
     * Handler: State updated
     */
    public static function onStateUpdated($stateName, $data, $sessionId) {
        self::publish("state.{$stateName}", [
            'state' => $stateName,
            'data' => $data,
            'action' => 'updated',
        ]);
    }

    /**
     * Handler: Product created
     */
    public static function onProductCreated($productId) {
        $product = wc_get_product($productId);
        if (!$product) {
            return;
        }

        self::broadcast('products.created', [
            'id' => $productId,
            'name' => $product->get_name(),
            'price' => $product->get_price(),
            'permalink' => $product->get_permalink(),
            'action' => 'created',
        ]);
    }

    /**
     * Handler: Product updated
     */
    public static function onProductUpdated($productId) {
        $product = wc_get_product($productId);
        if (!$product) {
            return;
        }

        self::broadcast('products.updated', [
            'id' => $productId,
            'name' => $product->get_name(),
            'price' => $product->get_price(),
            'stock_status' => $product->get_stock_status(),
            'permalink' => $product->get_permalink(),
            'action' => 'updated',
        ]);
    }

    /**
     * Handler: Product deleted
     */
    public static function onProductDeleted($productId) {
        self::broadcast('products.deleted', [
            'id' => $productId,
            'action' => 'deleted',
        ]);
    }

    /**
     * Handler: Post saved
     */
    public static function onPostSaved($postId, $post, $update) {
        // Skip autosaves and revisions
        if (wp_is_post_autosave($postId) || wp_is_post_revision($postId)) {
            return;
        }

        // Skip products (handled separately)
        if ($post->post_type === 'product') {
            return;
        }

        $action = $update ? 'updated' : 'created';

        self::broadcast("posts.{$action}", [
            'id' => $postId,
            'type' => $post->post_type,
            'title' => $post->post_title,
            'status' => $post->post_status,
            'action' => $action,
        ]);
    }

    /**
     * Handler: Post deleted
     */
    public static function onPostDeleted($postId) {
        $post = get_post($postId);
        if (!$post) {
            return;
        }

        self::broadcast('posts.deleted', [
            'id' => $postId,
            'type' => $post->post_type,
            'action' => 'deleted',
        ]);
    }

    /**
     * Cleanup old events
     */
    public static function cleanup() {
        global $wpdb;

        // Delete events older than 1 hour
        $deleted = $wpdb->query(
            "DELETE FROM {$wpdb->prefix}loom_events
             WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        );

        return (int)$deleted;
    }
}
