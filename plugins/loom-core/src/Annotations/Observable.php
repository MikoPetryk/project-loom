<?php
/**
 * Observable Annotation
 *
 * Marks a property as observable. Changes trigger UI updates.
 *
 * @package Loom\Core\Annotations
 */

namespace Loom\Core\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Observable {

    public $deep = false;

    public function __construct(
        $deep = false
    ) {
        $this->deep = $deep;
    }
}
