<?php
/**
 * Icon REST Controller
 *
 * Handles icon read operations within packs via REST API.
 *
 * @package IconManager\Features\Icons
 * @since 2.1.0
 */



namespace IconManager\Features\Icons;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use IconManager\Features\Packs\IconPackManager;

class IconController extends WP_REST_Controller {

    protected $namespace = 'icon-manager/v1';
    protected $rest_base = 'packs/(?P<pack>[a-zA-Z0-9_-]+)/icons';

    public function register_routes(): void {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_items'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<icon>[a-zA-Z0-9_-]+)', [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_item'],
            'permission_callback' => [$this, 'permissions_check'],
            'args' => [
                'size' => ['type' => 'integer', 'default' => null],
                'color' => ['type' => 'string', 'default' => null],
            ],
        ]);
    }

    public function get_items($request) {
        $pack = $request->get_param('pack');

        if (!IconPackManager::packExists($pack)) {
            return new WP_REST_Response([
                'error' => ['code' => 'pack_not_found', 'message' => __('Pack not found', 'icon-manager')],
            ], 404);
        }

        $icons = IconPackManager::getPackIcons($pack);
        $packDir = ICON_MANAGER_ICONS_DIR . sanitize_file_name($pack);
        $iconData = [];

        foreach ($icons as $icon) {
            $filePath = $packDir . '/' . $icon;
            $svg = file_exists($filePath) ? file_get_contents($filePath) : '';
            $svg = preg_replace('/<\?xml.*?\?>\s*/i', '', $svg);

            $iconData[] = [
                'filename' => $icon,
                'name' => pathinfo($icon, PATHINFO_FILENAME),
                'svg' => $svg,
            ];
        }

        return new WP_REST_Response([
            'data' => $iconData,
            'meta' => ['total' => count($iconData), 'pack' => $pack],
        ], 200);
    }

    public function get_item($request) {
        $pack = $request->get_param('pack');
        $icon = $request->get_param('icon');
        $size = $request->get_param('size');
        $color = $request->get_param('color');

        if (!IconPackManager::packExists($pack)) {
            return new WP_REST_Response([
                'error' => ['code' => 'pack_not_found', 'message' => __('Pack not found', 'icon-manager')],
            ], 404);
        }

        try {
            $svg = IconRenderer::render($pack, $icon, $size, $size, null, null, null, $color);

            if (empty($svg)) {
                return new WP_REST_Response([
                    'error' => ['code' => 'icon_not_found', 'message' => __('Icon not found', 'icon-manager')],
                ], 404);
            }

            return new WP_REST_Response([
                'data' => ['pack' => $pack, 'icon' => $icon, 'svg' => $svg],
            ], 200);
        } catch (\Exception $e) {
            return new WP_REST_Response([
                'error' => ['code' => 'render_failed', 'message' => $e->getMessage()],
            ], 500);
        }
    }

    public function permissions_check($request): bool {
        return current_user_can('manage_options');
    }
}
