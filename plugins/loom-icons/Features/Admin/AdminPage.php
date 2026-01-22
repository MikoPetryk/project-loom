<?php
/**
 * Admin Page
 *
 * Admin menu, assets, and notices for Icon Manager.
 *
 * @package IconManager\Features\Admin
 * @since 3.0.0
 */



namespace IconManager\Features\Admin;

use IconManager\Features\Packs\IconPackManager;

class AdminPage {

    public function __construct() {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_notices', [$this, 'displayNotices']);
    }

    public function registerMenu(): void {
        add_menu_page(
            __('Icon Manager', 'icon-manager'),
            __('Icon Manager', 'icon-manager'),
            'manage_options',
            'icon-manager',
            [$this, 'render'],
            'dashicons-art',
            6
        );
    }

    public function render(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'icon-manager'));
        }

        $currentCategory = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
        $categories = IconPackManager::getPackNames();

        if (empty($currentCategory) && !empty($categories)) {
            $currentCategory = $categories[0];
        }

        include __DIR__ . '/AdminView.php';
    }

    public function enqueueAssets(string $hook): void {
        if ($hook !== 'toplevel_page_icon-manager') {
            return;
        }

        $version = ICON_MANAGER_VERSION;
        $url = ICON_MANAGER_PLUGIN_URL . 'assets/';

        wp_enqueue_style('icon-manager-admin', $url . 'css/admin.css', [], $version);

        // Main scripts
        wp_enqueue_script('icon-manager-api-client', $url . 'js/api-client.js', [], $version, true);
        wp_enqueue_script('icon-manager-admin', $url . 'js/admin.js', ['icon-manager-api-client'], $version, true);

        wp_localize_script('icon-manager-admin', 'iconManagerData', [
            'nonce' => wp_create_nonce('wp_rest'),
            'apiUrl' => rest_url('icon-manager/v1'),
            'pluginUrl' => ICON_MANAGER_PLUGIN_URL,
            'currentCategory' => isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '',
            'i18n' => [
                'confirmDelete' => __('Are you sure you want to delete this icon?', 'icon-manager'),
                'confirmDeleteCategory' => __('Are you sure you want to delete this category and all its icons?', 'icon-manager'),
                'confirmBulkDelete' => __('Are you sure you want to delete the selected icons?', 'icon-manager'),
                'copied' => __('Copied to clipboard!', 'icon-manager'),
                'uploadSuccess' => __('Icons uploaded successfully!', 'icon-manager'),
                'uploadError' => __('Failed to upload icons.', 'icon-manager'),
                'categoryCreated' => __('Category created!', 'icon-manager'),
                'categoryDeleted' => __('Category deleted!', 'icon-manager'),
                'iconsDeleted' => __('Icons deleted!', 'icon-manager'),
                'enumsRegenerated' => __('Enums regenerated!', 'icon-manager'),
                'exportStarted' => __('Export started...', 'icon-manager'),
                'importSuccess' => __('Import successful!', 'icon-manager'),
                'sessionExpired' => __('Session expired. Please refresh the page.', 'icon-manager'),
                'networkError' => __('Network error. Please check your connection.', 'icon-manager'),
            ],
        ]);
    }

    public function displayNotices(): void {
        if (!isset($_GET['page']) || $_GET['page'] !== 'icon-manager') {
            return;
        }

        if (isset($_GET['upload_success'])) {
            $this->showNotice(__('Icons uploaded successfully!', 'icon-manager'), 'success');
        }

        if (isset($_GET['category_created'])) {
            $categoryName = sanitize_text_field($_GET['category_created']);
            $this->showNotice(sprintf(__('Icon category "%s" created!', 'icon-manager'), esc_html($categoryName)), 'success');
        }

        if (isset($_GET['category_deleted'])) {
            $this->showNotice(__('Icon category deleted successfully!', 'icon-manager'), 'success');
        }

        if (isset($_GET['regenerate_success'])) {
            $this->showNotice(__('All enums regenerated successfully!', 'icon-manager'), 'success');
        }

        if (isset($_GET['error'])) {
            $error = sanitize_text_field($_GET['error']);
            $this->showNotice($this->getErrorMessage($error), 'is-error');
        }
    }

    private function showNotice(string $message, string $type = 'info'): void {
        $class = 'notice notice-' . $type . ' is-dismissible';
        printf('<div class="%s"><p>%s</p></div>', esc_attr($class), esc_html($message));
    }

    private function getErrorMessage(string $errorCode): string {
        $messages = [
            'invalid_nonce' => __('Security check failed.', 'icon-manager'),
            'no_permission' => __('Insufficient permissions.', 'icon-manager'),
            'invalid_category_name' => __('Invalid icon category name.', 'icon-manager'),
            'category_exists' => __('Icon category already exists.', 'icon-manager'),
            'category_not_found' => __('Icon category not found.', 'icon-manager'),
            'upload_failed' => __('Failed to upload icons.', 'icon-manager'),
            'delete_failed' => __('Failed to delete icon category.', 'icon-manager'),
            'export_failed' => __('Failed to export category.', 'icon-manager'),
            'import_failed' => __('Failed to import icons.', 'icon-manager'),
        ];

        return $messages[$errorCode] ?? __('An unknown error occurred.', 'icon-manager');
    }
}
