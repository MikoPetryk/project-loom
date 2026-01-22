<?php
/**
 * Inject Annotation
 *
 * Injects a dependency into a class as a property.
 *
 * @package Loom\Core\Annotations
 */

namespace Loom\Core\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Inject {

    public $class;
    public $as = null;

    public function __construct(
        $class,
        $as = null
    ) {
        $this->class = $class;
        $this->as = $as;
    }

    /**
     * Get the property name for injection
     */
    public function getPropertyName() {
        if ($this->as) {
            return $this->as;
        }

        // Extract class name without namespace and lowercase first letter
        $parts = explode('\\', $this->class);
        $className = end($parts);

        // Remove common suffixes
        $className = preg_replace('/(State|Manager|Service|Repository)$/', '', $className);

        return lcfirst($className);
    }
}
