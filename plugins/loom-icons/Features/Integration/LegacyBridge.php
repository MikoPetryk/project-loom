<?php
/**
 * Legacy Bridge
 *
 * Provides backward compatibility for legacy method calls.
 *
 * @package IconManager\Features\Integration
 * @since 2.1.0
 */



namespace IconManager\Features\Integration;

use IconManager\Features\Icons\IconRenderer;
use IconManager\Features\Packs\IconPackManager;

class LegacyBridge {
    private static ?LegacyBridge $instance = null;

    private function __construct() {}

    public static function getInstance(): LegacyBridge {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __call(string $name, array $arguments): string {
        return $this->handleLegacyCall($name, $arguments);
    }

    public static function __callStatic(string $name, array $arguments): string {
        return self::getInstance()->handleLegacyCall($name, $arguments);
    }

    private function handleLegacyCall(string $name, array $arguments): string {
        $parsed = $this->parseMethodName($name);

        if (!$parsed) {
            return $this->getErrorMessage($name);
        }

        [$pack, $iconName] = $parsed;

        $width = $arguments[0] ?? null;
        $height = $arguments[1] ?? null;
        $class = $arguments[2] ?? null;
        $style = $arguments[3] ?? null;
        $id = $arguments[4] ?? null;

        return IconRenderer::render($pack, $iconName, $width, $height, $class, $style, $id);
    }

    private function parseMethodName(string $name): ?array {
        $packs = IconPackManager::getPackNames();

        usort($packs, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($packs as $pack) {
            if (stripos($name, $pack) === 0) {
                $iconName = substr($name, strlen($pack));
                if (!empty($iconName)) {
                    return [$pack, $iconName];
                }
            }
        }

        return null;
    }

    private function getErrorMessage(string $name): string {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            return sprintf('<!-- Legacy method not found: %s -->', esc_html($name));
        }
        return '';
    }
}
