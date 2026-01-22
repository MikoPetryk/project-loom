<?php

use IconManager\Features\Icons\IconRenderer;

/**
 * IconsManager - Auto-generated static methods
 * DO NOT EDIT MANUALLY
 *
 * Generated: 2026-01-05 23:08:58
 */
class IconsManager {
    private static ?IconsManager $instance = null;

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function _00smusicdisc(?int $width = null, ?int $height = null, ?string $class = null, ?string $style = null, ?string $id = null): string {
        return \IconManager\Features\Icons\IconRenderer::render('Uicons-Rounded', '00s-music-disc', $width, $height, $class, $style, $id);
    }
}
