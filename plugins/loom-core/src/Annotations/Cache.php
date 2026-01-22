<?php
/**
 * Cache Annotation
 *
 * Caches the output of a component or method.
 *
 * @package Loom\Core\Annotations
 */

namespace Loom\Core\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Cache {

    public $ttl = 3600;
    public $key = null;
    public $tags = [];

    public function __construct(
        $ttl = 3600,
        $key = null,
        $tags = []
    ) {
        $this->ttl = $ttl;
        $this->key = $key;
        $this->tags = $tags;
    }
}
