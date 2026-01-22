<?php
/**
 * Notification REST Controller
 *
 * Handles notification operations via REST API.
 *
 * @package Loom\Noti\Features\Notifications
 * @since 1.0.0
 */



namespace Loom\Noti\Features\Notifications;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class NotificationController extends WP_REST_Controller {

    protected $namespace = 'noti/v1';
    protected $rest_base = 'notifications';

    public function register_routes(): void {
        // GET /notifications - Get queued notifications
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'check_read_permission'],
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_item'],
                'permission_callback' => [$this, 'check_create_permission'],
                'args' => [
                    'type' => [
                        'required' => true,
                        'type' => 'string',
                        'enum' => ['success', 'info', 'warning', 'error', 'log'],
                    ],
                    'message' => [
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'options' => [
                        'type' => 'object',
                        'default' => [],
                    ],
                ],
            ],
        ]);

        // DELETE /notifications - Clear queue
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => [$this, 'delete_items'],
            'permission_callback' => [$this, 'check_delete_permission'],
        ]);
    }

    /**
     * Check permission for reading notifications
     */
    public function check_read_permission($request): bool {
        // Allow logged-in users or valid nonce
        return is_user_logged_in() || wp_verify_nonce($request->get_header('X-WP-Nonce'), 'wp_rest');
    }

    /**
     * Check permission for creating notifications
     */
    public function check_create_permission($request): bool {
        // Require logged-in user with edit_posts capability
        return current_user_can('edit_posts');
    }

    /**
     * Check permission for deleting notifications
     */
    public function check_delete_permission($request): bool {
        // Require admin capability
        return current_user_can('manage_options');
    }

    public function get_items($request) {
        $notifications = QueueManager::get();

        return new WP_REST_Response([
            'data' => $notifications,
            'meta' => ['count' => count($notifications)],
        ], 200);
    }

    public function create_item($request) {
        $type = $request->get_param('type');
        $message = $request->get_param('message');
        $options = $request->get_param('options') ?? [];

        QueueManager::add([
            'type' => $type,
            'message' => $message,
            'options' => $options,
            'time' => time(),
        ]);

        return new WP_REST_Response([
            'data' => [
                'type' => $type,
                'message' => $message,
            ],
            'message' => 'Notification queued',
        ], 201);
    }

    public function delete_items($request) {
        QueueManager::clear();

        return new WP_REST_Response(null, 204);
    }
}
