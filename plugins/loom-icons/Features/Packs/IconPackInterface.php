<?php
/**
 * Icon Pack Interface
 *
 * Base interface for all generated icon pack enums.
 *
 * @package IconManager\Features\Packs
 * @since 2.1.0
 */



namespace IconManager\Features\Packs;

interface IconPackInterface {
    public function getPackName(): string;
    public function getIconName(): string;
}
