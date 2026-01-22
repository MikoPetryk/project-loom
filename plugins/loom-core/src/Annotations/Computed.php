<?php
/**
 * Computed Annotation
 *
 * Marks a method as a computed property. Auto-updates when dependencies change.
 *
 * @package Loom\Core\Annotations
 */

namespace Loom\Core\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Computed {

    public $cached = true;

    public function __construct(
        $cached = true
    ) {
        $this->cached = $cached;
    }
}
