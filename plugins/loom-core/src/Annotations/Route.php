<?php
/**
 * Route Annotation
 *
 * Maps a class to a URL route.
 *
 * @package Loom\Core\Annotations
 */

namespace Loom\Core\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route {

    public $path;
    public $method = 'GET';
    public $name = null;
    public $middleware = [];
    public $priority = 10;

    public function __construct(
        $path,
        $method = 'GET',
        $name = null,
        $middleware = [],
        $priority = 10
    ) {
        $this->path = $path;
        $this->method = $method;
        $this->name = $name;
        $this->middleware = $middleware;
        $this->priority = $priority;
    }
}
