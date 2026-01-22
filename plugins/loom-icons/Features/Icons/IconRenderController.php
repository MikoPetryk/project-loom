<?php
/**
 * Icon Render REST Controller
 *
 * Handles icon rendering via REST API for Gutenberg blocks.
 *
 * @package IconManager\Features\Icons
 * @since 2.1.0
 */



namespace IconManager\Features\Icons;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class IconRenderController extends WP_REST_Controller {

    protected $namespace = 'icon-manager/v1';
    protected $rest_base = 'render';

    public function register_routes(): void {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'render_icon'],
            'permission_callback' => [$this, 'render_permissions_check'],
            'args' => [
                'pack' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'icon' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'size' => [
                    'type' => 'integer',
                    'default' => null,
                ],
                'color' => [
                    'type' => 'string',
                    'default' => null,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'class' => [
                    'type' => 'string',
                    'default' => null,
                    'sanitize_callback' => 'sanitize_html_class',
                ],
            ],
        ]);
    }

    public function render_icon($request) {
        $pack = $request->get_param('pack');
        $icon = $request->get_param('icon');
        $size = $request->get_param('size');
        $color = $request->get_param('color');
        $class = $request->get_param('class');

        if (empty($pack) || empty($icon)) {
            return new WP_REST_Response([
                'error' => [
                    'code' => 'missing_params',
                    'message' => __('Pack and icon are required', 'icon-manager'),
                ],
            ], 400);
        }

        try {
            $svg = IconRenderer::render($pack, $icon, $size, $size, $class, null, null, $color);

            if (empty($svg)) {
                return new WP_REST_Response([
                    'error' => [
                        'code' => 'icon_not_found',
                        'message' => __('Icon not found', 'icon-manager'),
                    ],
                ], 404);
            }

            return new WP_REST_Response([
                'data' => ['svg' => $svg],
            ], 200);
        } catch (\Exception $e) {
            return new WP_REST_Response([
                'error' => [
                    'code' => 'render_failed',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    public function render_permissions_check($request): bool {
        // Allow logged-in users
        if (is_user_logged_in()) {
            return true;
        }

        // Allow requests with valid nonce
        $nonce = $request->get_header('X-WP-Nonce');
        if ($nonce && wp_verify_nonce($nonce, 'wp_rest')) {
            return true;
        }

        // Deny unauthenticated requests
        return false;
    }
}
