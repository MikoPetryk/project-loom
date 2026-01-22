<?php
/**
 * Settings Page
 *
 * Admin menu, settings fields, and assets for Noti settings.
 *
 * @package Loom\Noti\Features\Settings
 * @since 1.0.0
 */



namespace Loom\Noti\Features\Settings;

class SettingsPage {

    private const OPTION_NAME = 'noti_settings';

    public function __construct() {
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('admin_init', [$this, 'initSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function addMenu(): void {
        add_options_page(
            'Noti Notifications',
            'Noti',
            'manage_options',
            'noti_plugin',
            [$this, 'render']
        );
    }

    public function render(): void {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions.'));
        }
        include __DIR__ . '/SettingsView.php';
    }

    public function enqueueAssets(): void {
        wp_enqueue_style('noti-css', NOTI_PLUGIN_URL . 'assets/css/noti.css', [], NOTI_VERSION);
        wp_enqueue_script('noti-toast-builder', NOTI_PLUGIN_URL . 'assets/js/toast-builder.js', [], NOTI_VERSION, true);
        wp_enqueue_script('noti-js', NOTI_PLUGIN_URL . 'assets/js/noti.js', ['noti-toast-builder'], NOTI_VERSION, true);

        $settings = get_option(self::OPTION_NAME);
        $settings = wp_parse_args($settings ?? [], self::getDefaults());

        $settings['apiUrl'] = rest_url('noti/v1');
        $settings['nonce'] = wp_create_nonce('wp_rest');

        wp_localize_script('noti-js', 'NotiSettings', $settings);
    }

    public function initSettings(): void {
        register_setting('noti_plugin_group', self::OPTION_NAME, [
            'sanitize_callback' => [$this, 'sanitize'],
        ]);

        add_settings_section(
            'noti_section_main',
            'General Settings',
            function() { echo '<p>Customize notification behavior and colors.</p>'; },
            'noti_plugin'
        );

        add_settings_field('noti_position', 'Position', [$this, 'fieldPosition'], 'noti_plugin', 'noti_section_main');
        add_settings_field('noti_autohide', 'Auto-hide (ms)', [$this, 'fieldAutohide'], 'noti_plugin', 'noti_section_main');
        add_settings_field('noti_override', 'Console Override', [$this, 'fieldOverride'], 'noti_plugin', 'noti_section_main');
        add_settings_field('noti_types', 'Type Colors', [$this, 'fieldTypes'], 'noti_plugin', 'noti_section_main');
    }

    public function fieldPosition(): void {
        $opt = get_option(self::OPTION_NAME);
        $pos = $opt['position'] ?? 'top-right';
        $positions = ['top-right', 'top-left', 'bottom-right', 'bottom-left'];
        echo '<select name="' . esc_attr(self::OPTION_NAME) . '[position]">';
        foreach ($positions as $p) {
            echo '<option value="' . esc_attr($p) . '" ' . selected($pos, $p, false) . '>' . esc_html(ucwords(str_replace('-', ' ', $p))) . '</option>';
        }
        echo '</select>';
    }

    public function fieldAutohide(): void {
        $opt = get_option(self::OPTION_NAME);
        $val = (int) ($opt['autohide'] ?? 5000);
        echo '<input type="number" name="' . esc_attr(self::OPTION_NAME) . '[autohide]" value="' . esc_attr((string) $val) . '" min="0" /> <span class="description">0 = never auto-hide</span>';
    }

    public function fieldOverride(): void {
        $opt = get_option(self::OPTION_NAME);
        $val = (bool) ($opt['override_console'] ?? false);
        echo '<input type="checkbox" name="' . esc_attr(self::OPTION_NAME) . '[override_console]" value="1" ' . checked($val, true, false) . ' /> Show console.log/warn/error as notifications';
    }

    public function fieldTypes(): void {
        $opt = get_option(self::OPTION_NAME);
        $types = $opt['types'] ?? [];
        $defaults = self::getDefaults()['types'];

        foreach ($defaults as $type => $default) {
            $val = $types[$type] ?? $default;
            echo '<label style="display:block;margin-bottom:6px;">';
            echo ucfirst($type) . ' <input type="color" name="' . esc_attr(self::OPTION_NAME) . '[types][' . $type . ']" value="' . esc_attr($val) . '" />';
            echo '</label>';
        }
    }

    public function sanitize(array $input): array {
        $defaults = self::getDefaults();
        $out = wp_parse_args($input, $defaults);

        $out['autohide'] = absint($out['autohide']);
        $out['override_console'] = !empty($out['override_console']);

        $allowed = ['top-right', 'top-left', 'bottom-right', 'bottom-left'];
        if (!in_array($out['position'], $allowed, true)) {
            $out['position'] = 'top-right';
        }

        if (!is_array($out['types'])) {
            $out['types'] = $defaults['types'];
        }
        foreach ($defaults['types'] as $k => $v) {
            $out['types'][$k] = sanitize_hex_color($out['types'][$k] ?? '') ?: $v;
        }

        return $out;
    }

    public static function getDefaults(): array {
        return [
            'position' => 'top-right',
            'autohide' => 5000,
            'override_console' => false,
            'types' => [
                'success' => '#2ecc71',
                'info' => '#3498db',
                'warning' => '#f39c12',
                'error' => '#e74c3c',
                'log' => '#34495e',
            ],
        ];
    }
}
