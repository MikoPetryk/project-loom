<?php
/**
 * Icon Pack Manager
 *
 * Manages icon packs: create, delete, list, stats.
 *
 * @package IconManager\Features\Packs
 * @since 2.1.0
 */



namespace IconManager\Features\Packs;

use IconManager\Features\Icons\IconRenderer;

class IconPackManager {

    public static function getPackNames(): array {
        $iconsDir = ICON_MANAGER_ICONS_DIR;

        if (!is_dir($iconsDir)) {
            return [];
        }

        $items = scandir($iconsDir);
        $packs = [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $iconsDir . $item;
            if (is_dir($path)) {
                $packs[] = $item;
            }
        }

        return $packs;
    }

    public static function getPackIcons(string $pack): array {
        $packDir = ICON_MANAGER_ICONS_DIR . sanitize_file_name($pack);

        if (!is_dir($packDir)) {
            return [];
        }

        $items = scandir($packDir);
        $icons = [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (pathinfo($item, PATHINFO_EXTENSION) === 'svg') {
                $icons[] = $item;
            }
        }

        return $icons;
    }

    public static function packExists(string $pack): bool {
        $packDir = ICON_MANAGER_ICONS_DIR . sanitize_file_name($pack);
        return is_dir($packDir);
    }

    public static function createPack(string $pack): bool {
        if (!self::isValidPackName($pack)) {
            return false;
        }

        $packDir = ICON_MANAGER_ICONS_DIR . sanitize_file_name($pack);

        if (is_dir($packDir)) {
            return false;
        }

        if (!wp_mkdir_p($packDir)) {
            return false;
        }

        self::createHtaccess($packDir);

        return true;
    }

    public static function deletePack(string $pack): bool {
        $packDir = ICON_MANAGER_ICONS_DIR . sanitize_file_name($pack);

        if (!is_dir($packDir)) {
            return false;
        }

        return self::deleteDirectory($packDir);
    }

    public static function deleteIcons(string $pack, array $icons): bool {
        $packDir = ICON_MANAGER_ICONS_DIR . sanitize_file_name($pack);

        if (!is_dir($packDir)) {
            return false;
        }

        $success = true;
        foreach ($icons as $icon) {
            $iconPath = $packDir . '/' . sanitize_file_name($icon);

            if (file_exists($iconPath)) {
                if (!unlink($iconPath)) {
                    $success = false;
                }
            }
        }

        IconRenderer::clearCache();

        return $success;
    }

    public static function isValidPackName(string $pack): bool {
        return preg_match('/^[a-zA-Z0-9_-]+$/', $pack) === 1 && strlen($pack) <= 50;
    }

    private static function deleteDirectory(string $dir): bool {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    private static function createHtaccess(string $dir): void {
        $htaccess = $dir . '/.htaccess';
        $content = "<Files *.svg>\n";
        $content .= "    AddType image/svg+xml .svg\n";
        $content .= "</Files>\n";

        file_put_contents($htaccess, $content);
    }

    public static function getPackStats(string $pack): array {
        $icons = self::getPackIcons($pack);
        $packDir = ICON_MANAGER_ICONS_DIR . sanitize_file_name($pack);

        $totalSize = 0;
        foreach ($icons as $icon) {
            $filePath = $packDir . '/' . $icon;
            if (file_exists($filePath)) {
                $totalSize += filesize($filePath);
            }
        }

        return [
            'count' => count($icons),
            'size' => $totalSize,
            'size_formatted' => size_format($totalSize)
        ];
    }
}
