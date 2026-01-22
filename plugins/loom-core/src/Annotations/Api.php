<?php
/**
 * Api Annotation
 *
 * Marks a class as a REST API controller.
 *
 * @package Loom\Core\Annotations
 */

namespace Loom\Core\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Api {

    public $path;
    public $namespace = 'loom/v1';

    public function __construct(
        $path,
        $namespace = 'loom/v1'
    ) {
        $this->path = $path;
        $this->namespace = $namespace;
    }
}

#[Attribute(Attribute::TARGET_METHOD)]
class Get {
    public $path = '';

    public function __construct(
        $path = ''
    ) {
        $this->path = $path;
    }
}

#[Attribute(Attribute::TARGET_METHOD)]
class Post {
    public $path = '';

    public function __construct(
        $path = ''
    ) {
        $this->path = $path;
    }
}

#[Attribute(Attribute::TARGET_METHOD)]
class Put {
    public $path = '';

    public function __construct(
        $path = ''
    ) {
        $this->path = $path;
    }
}

#[Attribute(Attribute::TARGET_METHOD)]
class Delete {
    public $path = '';

    public function __construct(
        $path = ''
    ) {
        $this->path = $path;
    }
}
