<?php
/**
 * Activator
 *
 * Plugin activation tasks.
 *
 * @package IconManager\Support
 * @since 2.1.0
 */



namespace IconManager\Support;

use IconManager\Features\Packs\IconPackGenerator;

class Activator {

    public static function activate(): void {
        if (!file_exists(ICON_MANAGER_ICONS_DIR)) {
            wp_mkdir_p(ICON_MANAGER_ICONS_DIR);
        }

        $indexFile = ICON_MANAGER_ICONS_DIR . 'index.php';
        if (!file_exists($indexFile)) {
            file_put_contents($indexFile, '<?php // Silence is golden');
        }

        self::createHtaccess();

        $dataDir = ICON_MANAGER_PLUGIN_DIR . 'data';
        if (!file_exists($dataDir)) {
            wp_mkdir_p($dataDir);
        }

        try {
            if (class_exists('IconManager\Features\Packs\IconPackGenerator')) {
                IconPackGenerator::regenerateAllEnums();
            }
        } catch (\Exception $e) {
            error_log('Icon Manager: Could not regenerate enums: ' . $e->getMessage());
        }

        add_option('icon_manager_version', ICON_MANAGER_VERSION);
        add_option('icon_manager_max_file_size', 512000);
        add_option('icon_manager_max_icons_per_pack', 500);
    }

    private static function createHtaccess(): void {
        $htaccessFile = ICON_MANAGER_ICONS_DIR . '.htaccess';

        $content = "# Icon Manager Security\n";
        $content .= "<Files *.svg>\n";
        $content .= "    AddType image/svg+xml .svg\n";
        $content .= "    <IfModule mod_headers.c>\n";
        $content .= "        Header set Content-Security-Policy \"default-src 'none'; style-src 'unsafe-inline'; frame-ancestors 'none'\"\n";
        $content .= "        Header set X-Content-Type-Options \"nosniff\"\n";
        $content .= "    </IfModule>\n";
        $content .= "</Files>\n\n";
        $content .= "<Files ~ \"\\.(php|phtml|php3|php4|php5|phps|cgi|pl|py|jsp|asp|sh|exe)$\">\n";
        $content .= "    Order allow,deny\n";
        $content .= "    Deny from all\n";
        $content .= "</Files>\n";

        file_put_contents($htaccessFile, $content);
    }
}
