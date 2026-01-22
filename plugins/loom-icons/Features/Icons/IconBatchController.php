<?php
/**
 * Icon Batch REST Controller
 *
 * Handles batch icon operations (upload, delete) via REST API.
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
use IconManager\Features\Packs\IconPackGenerator;
use IconManager\Features\Upload\IconUploader;
use IconManager\Support\IconsManagerGenerator;

class IconBatchController extends WP_REST_Controller {

    protected $namespace = 'icon-manager/v1';

    public function register_routes(): void {
        register_rest_route($this->namespace, '/packs/(?P<pack>[a-zA-Z0-9_-]+)/icons', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'upload_icons'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

        register_rest_route($this->namespace, '/packs/(?P<pack>[a-zA-Z0-9_-]+)/icons/batch', [
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => [$this, 'delete_icons'],
            'permission_callback' => [$this, 'permissions_check'],
            'args' => [
                'icons' => [
                    'required' => true,
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
        ]);
    }

    public function upload_icons($request) {
        $pack = $request->get_param('pack');

        if (!IconPackManager::packExists($pack)) {
            return new WP_REST_Response([
                'error' => ['code' => 'pack_not_found', 'message' => __('Pack not found', 'icon-manager')],
            ], 404);
        }

        $files = $request->get_file_params();

        if (empty($files['icons'])) {
            return new WP_REST_Response([
                'error' => ['code' => 'no_files', 'message' => __('No files uploaded', 'icon-manager')],
            ], 400);
        }

        $uploadFiles = [
            'name' => $files['icons']['name'],
            'type' => $files['icons']['type'],
            'tmp_name' => $files['icons']['tmp_name'],
            'error' => $files['icons']['error'],
            'size' => $files['icons']['size'],
        ];

        $uploader = new IconUploader();
        $success = $uploader->upload($pack, $uploadFiles);

        if ($success) {
            IconPackGenerator::generatePackEnum($pack);
            IconsManagerGenerator::generate();

            $count = is_array($uploadFiles['name']) ? count($uploadFiles['name']) : 1;

            return new WP_REST_Response([
                'data' => ['uploaded' => $count],
                'message' => sprintf(__('%d icon(s) uploaded successfully', 'icon-manager'), $count),
            ], 200);
        }

        return new WP_REST_Response([
            'error' => ['code' => 'upload_failed', 'message' => implode(', ', $uploader->getErrors())],
        ], 400);
    }

    public function delete_icons($request) {
        $pack = $request->get_param('pack');
        $icons = $request->get_param('icons');

        if (!IconPackManager::packExists($pack)) {
            return new WP_REST_Response([
                'error' => ['code' => 'pack_not_found', 'message' => __('Pack not found', 'icon-manager')],
            ], 404);
        }

        $icons = array_map('sanitize_file_name', (array) $icons);

        if (IconPackManager::deleteIcons($pack, $icons)) {
            IconPackGenerator::generatePackEnum($pack);
            IconsManagerGenerator::generate();

            return new WP_REST_Response([
                'data' => ['deleted' => count($icons)],
                'message' => sprintf(__('%d icon(s) deleted', 'icon-manager'), count($icons)),
            ], 200);
        }

        return new WP_REST_Response([
            'error' => ['code' => 'deletion_failed', 'message' => __('Failed to delete icons', 'icon-manager')],
        ], 500);
    }

    public function permissions_check($request): bool {
        return current_user_can('manage_options');
    }
}
