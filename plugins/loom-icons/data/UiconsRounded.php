<?php

namespace IconManager\IconPacks;

use IconManager\Features\Packs\IconPackInterface;

/**
 * UiconsRounded Icon Pack
 * Auto-generated - Do not edit manually
 *
 * Usage: Icon(UiconsRounded::Email)->size(24)->render()
 */
enum UiconsRounded: string implements IconPackInterface {
    case _00smusicdisc = '00s-music-disc';

    public function getPackName(): string {
        return 'Uicons-Rounded';
    }

    public function getIconName(): string {
        return $this->value;
    }
}
