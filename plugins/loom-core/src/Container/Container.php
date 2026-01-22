<?php
/**
 * Dependency Injection Container
 *
 * Simple DI container for Loom Core.
 *
 * @package Loom\Core\Container
 */

namespace Loom\Core\Container;

use Loom\Core\Annotations\AnnotationProcessor;
use Loom\Core\Annotations\State;

class Container {

    private static $instances = [];
    private static $bindings = [];
    private static $factories = [];

    /**
     * Bind a class or interface to an implementation
     */
    public static function bind($abstract, $concrete) {
        if (is_callable($concrete)) {
            self::$factories[$abstract] = $concrete;
        } else {
            self::$bindings[$abstract] = $concrete;
        }
    }

    /**
     * Bind a singleton instance
     */
    public static function singleton($abstract, $instance) {
        self::$instances[$abstract] = $instance;
    }

    /**
     * Get an instance from the container
     */
    public static function get($abstract) {
        // Already instantiated?
        if (isset(self::$instances[$abstract])) {
            return self::$instances[$abstract];
        }

        // Factory binding?
        if (isset(self::$factories[$abstract])) {
            $instance = (self::$factories[$abstract])();
            self::$instances[$abstract] = $instance;
            return $instance;
        }

        // Class binding?
        $concrete = self::$bindings[$abstract] ?? $abstract;

        // Create instance
        $instance = self::make($concrete);

        // Check if it's a State class - singletons by default
        if (AnnotationProcessor::isState($concrete)) {
            self::$instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Create a new instance (not cached)
     */
    public static function make($class) {
        if (!class_exists($class)) {
            throw new \RuntimeException("Class {$class} not found");
        }

        $reflection = new \ReflectionClass($class);

        // Check constructor dependencies
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $class();
        }

        $params = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                // Inject dependency
                $params[] = self::get($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException(
                    "Cannot resolve parameter \${$param->getName()} for {$class}"
                );
            }
        }

        return new $class(...$params);
    }

    /**
     * Check if a binding exists
     */
    public static function has($abstract) {
        return isset(self::$instances[$abstract])
            || isset(self::$factories[$abstract])
            || isset(self::$bindings[$abstract])
            || class_exists($abstract);
    }

    /**
     * Clear all bindings (for testing)
     */
    public static function clear() {
        self::$instances = [];
        self::$bindings = [];
        self::$factories = [];
    }
}
