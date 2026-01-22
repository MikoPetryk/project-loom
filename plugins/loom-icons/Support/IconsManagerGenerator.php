<?php
/**
 * IconsManager Generator
 *
 * Generates IconsManager class with actual static methods.
 *
 * @package IconManager\Support
 * @since 2.1.0
 */



namespace IconManager\Support;

use IconManager\Features\Packs\IconPackManager;

class IconsManagerGenerator {

    public static function generate(): bool {
        $packs = IconPackManager::getPackNames();
        $methods = [];

        foreach ($packs as $pack) {
            $icons = IconPackManager::getPackIcons($pack);

            foreach ($icons as $icon) {
                $iconName = pathinfo($icon, PATHINFO_FILENAME);
                $methodName = $iconName;

                $methods[] = self::generateMethod($pack, $iconName, $methodName);
            }
        }

        $content = self::buildClassContent($methods);
        $filePath = ICON_MANAGER_PLUGIN_DIR . 'data/IconsManager.php';

        return file_put_contents($filePath, $content) !== false;
    }

    private static function generateMethod(string $pack, string $iconName, string $methodName): string {
        $safeMethodName = preg_replace('/[^a-zA-Z0-9_]/', '', $methodName);

        if (is_numeric($safeMethodName[0] ?? '')) {
            $safeMethodName = '_' . $safeMethodName;
        }

        if (empty($safeMethodName)) {
            return '';
        }

        return <<<PHP

    public static function {$safeMethodName}(?int \$width = null, ?int \$height = null, ?string \$class = null, ?string \$style = null, ?string \$id = null): string {
        return \IconManager\Features\Icons\IconRenderer::render('{$pack}', '{$iconName}', \$width, \$height, \$class, \$style, \$id);
    }
PHP;
    }

    private static function buildClassContent(array $methods): string {
        $methods = array_filter($methods);
        $methodsString = implode("\n", $methods);
        $date = date('Y-m-d H:i:s');

        return <<<PHP
<?php

use IconManager\Features\Icons\IconRenderer;

/**
 * IconsManager - Auto-generated static methods
 * DO NOT EDIT MANUALLY
 *
 * Generated: {$date}
 */
class IconsManager {
    private static ?IconsManager \$instance = null;

    public static function getInstance(): self {
        if (self::\$instance === null) {
            self::\$instance = new self();
        }
        return self::\$instance;
    }
{$methodsString}
}

PHP;
    }

    public static function delete(): bool {
        $filePath = ICON_MANAGER_PLUGIN_DIR . 'data/IconsManager.php';

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return true;
    }
}
