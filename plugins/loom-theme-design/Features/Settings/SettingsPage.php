<?php
/**
 * Settings Page
 *
 * Admin menu and assets for Theme Manager settings.
 *
 * @package Loom\ThemeManager\Features\Settings
 * @since 1.0.0
 */



namespace Loom\ThemeManager\Features\Settings;

use Loom\ThemeManager\Features\Tokens\TokenRegistry;

class SettingsPage {

    public function __construct() {
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function addMenu(): void {
        add_menu_page(
            'Theme Manager',
            'Theme Manager',
            'manage_options',
            'theme-manager',
            [$this, 'render'],
            'dashicons-art',
            59
        );
    }

    public function render(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions.'));
        }
        include __DIR__ . '/SettingsView.php';
    }

    public function enqueueAssets(string $hook): void {
        if ($hook !== 'toplevel_page_theme-manager') return;

        wp_enqueue_style('theme-manager-admin', THEME_MANAGER_PLUGIN_URL . 'assets/css/admin.css', [], THEME_MANAGER_VERSION);

        // Localize data BEFORE scripts that need it
        wp_register_script('theme-manager-api', THEME_MANAGER_PLUGIN_URL . 'assets/js/api-client.js', [], THEME_MANAGER_VERSION, true);
        wp_localize_script('theme-manager-api', 'themeManagerData', [
            'nonce' => wp_create_nonce('wp_rest'),
            'apiUrl' => rest_url('theme-manager/v1'),
            'tokens' => TokenRegistry::getAll(),
        ]);
        wp_enqueue_script('theme-manager-api');

        wp_enqueue_script('theme-manager-editor', THEME_MANAGER_PLUGIN_URL . 'assets/js/token-editor.js', ['theme-manager-api'], THEME_MANAGER_VERSION, true);
        wp_enqueue_script('theme-manager-admin', THEME_MANAGER_PLUGIN_URL . 'assets/js/admin.js', ['theme-manager-api', 'theme-manager-editor'], THEME_MANAGER_VERSION, true);
    }
}
