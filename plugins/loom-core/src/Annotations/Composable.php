<?php
/**
 * Composable Annotation
 *
 * Marks a class as a UI component. Components must be pure and
 * should not contain side effects.
 *
 * @package Loom\Core\Annotations
 */

namespace Loom\Core\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Composable {

    public $pure = true;
    public $tag = null;

    public function __construct(
        $pure = true,
        $tag = null
    ) {
        $this->pure = $pure;
        $this->tag = $tag;
    }
}
