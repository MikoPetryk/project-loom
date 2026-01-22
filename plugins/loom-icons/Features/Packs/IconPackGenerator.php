<?php
/**
 * Icon Pack Generator
 *
 * Generates PHP enum files for IDE autocomplete.
 *
 * @package IconManager\Features\Packs
 * @since 2.1.0
 */



namespace IconManager\Features\Packs;

class IconPackGenerator {

    public static function generatePackEnum(string $packName): bool {
        $icons = IconPackManager::getPackIcons($packName);

        if (empty($icons)) {
            $icons = [];
        }

        $enumContent = self::buildEnumContent($packName, $icons);
        $enumPath = self::getEnumPath($packName);

        $enumDir = dirname($enumPath);
        if (!file_exists($enumDir)) {
            wp_mkdir_p($enumDir);
        }

        return file_put_contents($enumPath, $enumContent) !== false;
    }

    public static function deletePackEnum(string $packName): bool {
        $enumPath = self::getEnumPath($packName);

        if (file_exists($enumPath)) {
            return unlink($enumPath);
        }

        return true;
    }

    public static function regenerateAllEnums(): void {
        $packs = IconPackManager::getPackNames();

        foreach ($packs as $pack) {
            self::generatePackEnum($pack);
        }
    }

    private static function buildEnumContent(string $packName, array $icons): string {
        $enumName = self::sanitizeEnumName($packName);
        $cases = [];

        foreach ($icons as $icon) {
            $iconName = pathinfo($icon, PATHINFO_FILENAME);

            $cleanName = $iconName;
            if (stripos($iconName, $packName) === 0) {
                $cleanName = substr($iconName, strlen($packName));
            }

            $caseName = self::sanitizeCaseName($cleanName ?: $iconName);
            $cases[] = "    case {$caseName} = '{$iconName}';";
        }

        $casesString = empty($cases) ? '    // Icons will appear here after upload' : implode("\n", $cases);

        return <<<PHP
<?php

namespace IconManager\IconPacks;

use IconManager\Features\Packs\IconPackInterface;

/**
 * {$enumName} Icon Pack
 * Auto-generated - Do not edit manually
 *
 * Usage: Icon({$enumName}::Email)->size(24)->render()
 */
enum {$enumName}: string implements IconPackInterface {
{$casesString}

    public function getPackName(): string {
        return '{$packName}';
    }

    public function getIconName(): string {
        return \$this->value;
    }
}

PHP;
    }

    private static function getEnumPath(string $packName): string {
        $enumName = self::sanitizeEnumName($packName);
        return ICON_MANAGER_PLUGIN_DIR . 'data/' . $enumName . '.php';
    }

    private static function sanitizeEnumName(string $packName): string {
        $name = preg_replace('/[^a-zA-Z0-9_]/', '', $packName);

        if (is_numeric($name[0] ?? '')) {
            $name = 'Icon' . $name;
        }

        return ucfirst($name);
    }

    private static function sanitizeCaseName(string $iconName): string {
        $name = preg_replace('/[^a-zA-Z0-9_]/', '', $iconName);

        if (is_numeric($name[0] ?? '')) {
            $name = '_' . $name;
        }

        return $name;
    }
}
