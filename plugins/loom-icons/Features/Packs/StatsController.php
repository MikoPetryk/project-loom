<?php
/**
 * Stats REST Controller
 *
 * Handles statistics and regeneration operations via REST API.
 *
 * @package IconManager\Features\Packs
 * @since 2.1.0
 */



namespace IconManager\Features\Packs;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use IconManager\Support\IconsManagerGenerator;

class StatsController extends WP_REST_Controller {

    protected $namespace = 'icon-manager/v1';
    protected $rest_base = 'stats';

    public function register_routes(): void {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            'methods' => WP_REST_Server::READABLE,
            'callback' => [$this, 'get_stats'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

        register_rest_route($this->namespace, '/regenerate', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'regenerate'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);
    }

    public function get_stats($request) {
        try {
            $packs = IconPackManager::getPackNames();
            $totalIcons = 0;
            $packStats = [];

            foreach ($packs as $pack) {
                $stats = IconPackManager::getPackStats($pack);
                $count = $stats['count'] ?? 0;
                $totalIcons += $count;
                $packStats[] = [
                    'name' => $pack,
                    'icon_count' => $count,
                ];
            }

            return new WP_REST_Response([
                'data' => [
                    'total_packs' => count($packs),
                    'total_icons' => $totalIcons,
                    'packs' => $packStats,
                ],
            ], 200);
        } catch (\Exception $e) {
            return new WP_REST_Response([
                'error' => ['code' => 'stats_failed', 'message' => $e->getMessage()],
            ], 500);
        }
    }

    public function regenerate($request) {
        try {
            IconPackGenerator::regenerateAllEnums();
            IconsManagerGenerator::generate();

            return new WP_REST_Response([
                'data' => ['regenerated' => true],
                'message' => __('All enums and legacy functions regenerated', 'icon-manager'),
            ], 200);
        } catch (\Exception $e) {
            return new WP_REST_Response([
                'error' => ['code' => 'regeneration_failed', 'message' => $e->getMessage()],
            ], 500);
        }
    }

    public function permissions_check($request): bool {
        return current_user_can('manage_options');
    }
}
