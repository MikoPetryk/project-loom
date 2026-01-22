<?php
/**
 * Annotation Processor
 *
 * Reads and processes PHP 8 attributes at runtime.
 *
 * @package Loom\Core\Annotations
 */

namespace Loom\Core\Annotations;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class AnnotationProcessor {

    private static $cache = [];

    public static function init() {
        // Pre-load annotation classes
        $annotations = [
            Composable::class,
            Inject::class,
            State::class,
            Observable::class,
            Computed::class,
            Action::class,
            Route::class,
            Api::class,
            Cache::class,
            Auth::class,
        ];

        foreach ($annotations as $annotation) {
            class_exists($annotation);
        }
    }

    /**
     * Get all attributes of a class
     */
    public static function getClassAttributes($class) {
        $cacheKey = "class:{$class}";

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $reflection = new ReflectionClass($class);
        $attributes = [];

        foreach ($reflection->getAttributes() as $attribute) {
            $attributes[$attribute->getName()] = $attribute->newInstance();
        }

        self::$cache[$cacheKey] = $attributes;
        return $attributes;
    }

    /**
     * Check if a class has a specific attribute
     */
    public static function hasAttribute($class, $attribute) {
        $attributes = self::getClassAttributes($class);
        return isset($attributes[$attribute]);
    }

    /**
     * Get a specific attribute from a class
     */
    public static function getAttribute($class, $attribute) {
        $attributes = self::getClassAttributes($class);
        return $attributes[$attribute] ?? null;
    }

    /**
     * Get all Inject attributes from a class
     */
    public static function getInjections($class) {
        $reflection = new ReflectionClass($class);
        $injections = [];

        foreach ($reflection->getAttributes(Inject::class) as $attribute) {
            $injections[] = $attribute->newInstance();
        }

        return $injections;
    }

    /**
     * Get method attributes
     */
    public static function getMethodAttributes($class, $method) {
        $cacheKey = "method:{$class}:{$method}";

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $reflection = new ReflectionMethod($class, $method);
        $attributes = [];

        foreach ($reflection->getAttributes() as $attribute) {
            $attributes[$attribute->getName()] = $attribute->newInstance();
        }

        self::$cache[$cacheKey] = $attributes;
        return $attributes;
    }

    /**
     * Get property attributes
     */
    public static function getPropertyAttributes($class, $property) {
        $cacheKey = "prop:{$class}:{$property}";

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $reflection = new ReflectionProperty($class, $property);
        $attributes = [];

        foreach ($reflection->getAttributes() as $attribute) {
            $attributes[$attribute->getName()] = $attribute->newInstance();
        }

        self::$cache[$cacheKey] = $attributes;
        return $attributes;
    }

    /**
     * Get all methods with a specific attribute
     */
    public static function getMethodsWithAttribute($class, $attribute) {
        $reflection = new ReflectionClass($class);
        $methods = [];

        foreach ($reflection->getMethods() as $method) {
            $attrs = $method->getAttributes($attribute);
            if (!empty($attrs)) {
                $methods[$method->getName()] = $attrs[0]->newInstance();
            }
        }

        return $methods;
    }

    /**
     * Get all properties with a specific attribute
     */
    public static function getPropertiesWithAttribute($class, $attribute) {
        $reflection = new ReflectionClass($class);
        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            $attrs = $property->getAttributes($attribute);
            if (!empty($attrs)) {
                $properties[$property->getName()] = $attrs[0]->newInstance();
            }
        }

        return $properties;
    }

    /**
     * Check if class is a Composable
     */
    public static function isComposable($class) {
        return self::hasAttribute($class, Composable::class);
    }

    /**
     * Check if class is a State container
     */
    public static function isState($class) {
        return self::hasAttribute($class, State::class);
    }

    /**
     * Check if class or any method has Route attribute
     */
    public static function hasRoute($class) {
        // Check class-level route
        if (self::hasAttribute($class, Route::class)) {
            return true;
        }

        // Check method-level routes
        $reflection = new ReflectionClass($class);
        foreach ($reflection->getMethods() as $method) {
            if (!empty($method->getAttributes(Route::class))) {
                return true;
            }
        }

        return false;
    }
}
