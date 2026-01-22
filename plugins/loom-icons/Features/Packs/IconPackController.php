<?php
/**
 * Icon Pack REST Controller
 *
 * Handles CRUD operations for icon packs (categories) via REST API.
 *
 * @package IconManager\Features\Packs
 * @since 3.0.0
 */



namespace IconManager\Features\Packs;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use IconManager\Support\IconsManagerGenerator;
use ZipArchive;

class IconPackController extends WP_REST_Controller {

    protected $namespace = 'icon-manager/v1';
    protected $rest_base = 'packs';

    public function register_routes(): void {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_item'],
                'permission_callback' => [$this, 'permissions_check'],
                'args' => [
                    'name' => [
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                        'validate_callback' => function ($value) {
                            return IconPackManager::isValidPackName($value);
                        },
                    ],
                ],
            ],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<name>[a-zA-Z0-9_-]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_item'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        // Export endpoint
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<name>[a-zA-Z0-9_-]+)/export', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'export_item'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        // Import endpoint
        register_rest_route($this->namespace, '/' . $this->rest_base . '/import', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'import_items'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);

        // Regenerate enums endpoint
        register_rest_route($this->namespace, '/regenerate', [
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'regenerate_enums'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);
    }

    public function get_items($request) {
        try {
            $packNames = IconPackManager::getPackNames();
            $packs = [];

            foreach ($packNames as $name) {
                $stats = IconPackManager::getPackStats($name);
                $packs[] = [
                    'name' => $name,
                    'icon_count' => $stats['count'] ?? 0,
                ];
            }

            return new WP_REST_Response([
                'data' => $packs,
                'meta' => ['total' => count($packs)],
            ], 200);
        } catch (\Exception $e) {
            return new WP_REST_Response([
                'error' => ['code' => 'fetch_failed', 'message' => $e->getMessage()],
            ], 500);
        }
    }

    public function get_item($request) {
        $name = $request->get_param('name');

        if (!IconPackManager::packExists($name)) {
            return new WP_REST_Response([
                'error' => ['code' => 'pack_not_found', 'message' => __('Icon pack not found', 'icon-manager')],
            ], 404);
        }

        $stats = IconPackManager::getPackStats($name);
        $icons = IconPackManager::getPackIcons($name);

        return new WP_REST_Response([
            'data' => [
                'name' => $name,
                'icon_count' => $stats['count'] ?? 0,
                'icons' => array_map(fn($icon) => pathinfo($icon, PATHINFO_FILENAME), $icons),
            ],
        ], 200);
    }

    public function create_item($request) {
        $name = sanitize_text_field($request->get_param('name'));

        if (IconPackManager::packExists($name)) {
            return new WP_REST_Response([
                'error' => ['code' => 'pack_exists', 'message' => __('Pack already exists', 'icon-manager')],
            ], 400);
        }

        if (IconPackManager::createPack($name)) {
            IconPackGenerator::generatePackEnum($name);

            return new WP_REST_Response([
                'data' => ['name' => $name],
                'message' => __('Pack created successfully', 'icon-manager'),
            ], 201);
        }

        return new WP_REST_Response([
            'error' => ['code' => 'creation_failed', 'message' => __('Failed to create pack', 'icon-manager')],
        ], 500);
    }

    public function delete_item($request) {
        $name = $request->get_param('name');

        if (!IconPackManager::packExists($name)) {
            return new WP_REST_Response([
                'error' => ['code' => 'pack_not_found', 'message' => __('Pack not found', 'icon-manager')],
            ], 404);
        }

        if (IconPackManager::deletePack($name)) {
            IconPackGenerator::deletePackEnum($name);
            IconsManagerGenerator::generate();

            return new WP_REST_Response(null, 204);
        }

        return new WP_REST_Response([
            'error' => ['code' => 'deletion_failed', 'message' => __('Failed to delete pack', 'icon-manager')],
        ], 500);
    }

    public function permissions_check($request): bool {
        return current_user_can('manage_options');
    }

    /**
     * Export a category as a ZIP file
     */
    public function export_item($request) {
        $name = $request->get_param('name');

        if (!IconPackManager::packExists($name)) {
            return new WP_REST_Response([
                'error' => ['code' => 'category_not_found', 'message' => __('Category not found', 'icon-manager')],
            ], 404);
        }

        if (!class_exists('ZipArchive')) {
            return new WP_REST_Response([
                'error' => ['code' => 'zip_not_available', 'message' => __('ZIP extension not available', 'icon-manager')],
            ], 500);
        }

        $packDir = ICON_MANAGER_ICONS_DIR . sanitize_file_name($name);
        $icons = IconPackManager::getPackIcons($name);

        if (empty($icons)) {
            return new WP_REST_Response([
                'error' => ['code' => 'category_empty', 'message' => __('Category has no icons to export', 'icon-manager')],
            ], 400);
        }

        // Create temp file for ZIP
        $tempFile = wp_tempnam($name . '.zip');
        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return new WP_REST_Response([
                'error' => ['code' => 'zip_creation_failed', 'message' => __('Failed to create ZIP file', 'icon-manager')],
            ], 500);
        }

        // Add icons to ZIP
        foreach ($icons as $icon) {
            $filePath = $packDir . '/' . $icon;
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $name . '/' . $icon);
            }
        }

        $zip->close();

        // Read ZIP content and encode as base64
        $zipContent = file_get_contents($tempFile);
        $base64 = base64_encode($zipContent);

        // Clean up temp file
        @unlink($tempFile);

        return new WP_REST_Response([
            'data' => [
                'filename' => $name . '.zip',
                'content' => $base64,
                'size' => strlen($zipContent),
            ],
        ], 200);
    }

    /**
     * Import icons from a ZIP file
     */
    public function import_items($request) {
        if (!class_exists('ZipArchive')) {
            return new WP_REST_Response([
                'error' => ['code' => 'zip_not_available', 'message' => __('ZIP extension not available', 'icon-manager')],
            ], 500);
        }

        $files = $request->get_file_params();

        if (empty($files['file'])) {
            return new WP_REST_Response([
                'error' => ['code' => 'no_file', 'message' => __('No file uploaded', 'icon-manager')],
            ], 400);
        }

        $file = $files['file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_REST_Response([
                'error' => ['code' => 'upload_error', 'message' => __('File upload failed', 'icon-manager')],
            ], 400);
        }

        // Validate file type
        $fileType = wp_check_filetype($file['name']);
        if ($fileType['ext'] !== 'zip') {
            return new WP_REST_Response([
                'error' => ['code' => 'invalid_file_type', 'message' => __('Only ZIP files are allowed', 'icon-manager')],
            ], 400);
        }

        $zip = new ZipArchive();
        if ($zip->open($file['tmp_name']) !== true) {
            return new WP_REST_Response([
                'error' => ['code' => 'zip_open_failed', 'message' => __('Failed to open ZIP file', 'icon-manager')],
            ], 400);
        }

        $imported = [];
        $errors = [];

        // Process ZIP contents
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $pathInfo = pathinfo($filename);

            // Skip directories and non-SVG files
            if (empty($pathInfo['extension']) || strtolower($pathInfo['extension']) !== 'svg') {
                continue;
            }

            // Determine category from directory structure
            $parts = explode('/', $filename);
            $category = count($parts) > 1 ? sanitize_file_name($parts[0]) : 'Imported';
            $iconName = sanitize_file_name($pathInfo['filename']);

            // Create category if it doesn't exist
            if (!IconPackManager::packExists($category)) {
                IconPackManager::createPack($category);
            }

            // Extract and save icon
            $content = $zip->getFromIndex($i);
            if ($content !== false) {
                $packDir = ICON_MANAGER_ICONS_DIR . $category;
                $iconPath = $packDir . '/' . $iconName . '.svg';

                // Basic SVG validation
                if (strpos($content, '<svg') !== false && strpos($content, '</svg>') !== false) {
                    if (file_put_contents($iconPath, $content)) {
                        if (!isset($imported[$category])) {
                            $imported[$category] = 0;
                        }
                        $imported[$category]++;
                    } else {
                        $errors[] = $filename;
                    }
                } else {
                    $errors[] = $filename . ' (invalid SVG)';
                }
            }
        }

        $zip->close();

        // Regenerate enums for all affected categories
        foreach (array_keys($imported) as $category) {
            IconPackGenerator::generatePackEnum($category);
        }
        IconsManagerGenerator::generate();

        return new WP_REST_Response([
            'data' => [
                'imported' => $imported,
                'total' => array_sum($imported),
                'errors' => $errors,
            ],
            'message' => sprintf(__('Imported %d icons', 'icon-manager'), array_sum($imported)),
        ], 200);
    }

    /**
     * Regenerate all enums
     */
    public function regenerate_enums($request) {
        try {
            $packs = IconPackManager::getPackNames();

            foreach ($packs as $pack) {
                IconPackGenerator::generatePackEnum($pack);
            }

            IconsManagerGenerator::generate();

            return new WP_REST_Response([
                'message' => __('Enums regenerated successfully', 'icon-manager'),
            ], 200);
        } catch (\Exception $e) {
            return new WP_REST_Response([
                'error' => ['code' => 'regeneration_failed', 'message' => $e->getMessage()],
            ], 500);
        }
    }
}
