<?php
/**
 * Loom Router
 *
 * Handles routing based on #[Route] annotations.
 *
 * @package Loom\Core\Routing
 */

namespace Loom\Core\Routing;

use Loom\Core\Annotations\AnnotationProcessor;
use Loom\Core\Annotations\Route as RouteAttribute;
use Loom\Core\Container\Container;

class Router {
    /**
     * Registered routes
     */
    private static $routes = [];

    /**
     * Current matched route
     */
    private static $currentRoute = null;

    /**
     * Register routes from a class with #[Route] annotations
     */
    public static function register($class) {
        $reflection = new \ReflectionClass($class);

        // Check for class-level route (base path)
        $classRoutes = $reflection->getAttributes(RouteAttribute::class);
        $basePath = '';
        if (!empty($classRoutes)) {
            $attr = $classRoutes[0]->newInstance();
            $basePath = rtrim($attr->path, '/');
        }

        // Check methods for routes
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $methodRoutes = $method->getAttributes(RouteAttribute::class);
            foreach ($methodRoutes as $routeAttr) {
                $attr = $routeAttr->newInstance();
                $fullPath = $basePath . '/' . ltrim($attr->path, '/');
                $fullPath = '/' . trim($fullPath, '/');

                self::$routes[] = new Route(
                    path: $fullPath,
                    class: $class,
                    method: $method->getName(),
                    httpMethod: $attr->method,
                    middleware: $attr->middleware
                );
            }
        }

        // If class itself has route but no methods, treat render() as default
        if (!empty($classRoutes) && $reflection->hasMethod('render')) {
            $attr = $classRoutes[0]->newInstance();
            $fullPath = '/' . trim($basePath, '/');
            if ($fullPath === '/') {
                $fullPath = '/';
            }

            // Check if not already registered
            $exists = false;
            foreach (self::$routes as $route) {
                if ($route->path === $fullPath && $route->class === $class) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                self::$routes[] = new Route(
                    path: $fullPath,
                    class: $class,
                    method: 'render',
                    httpMethod: $attr->method,
                    middleware: $attr->middleware
                );
            }
        }
    }

    /**
     * Get current route based on request URI
     */
    public static function getCurrentRoute() {
        if (self::$currentRoute !== null) {
            return self::$currentRoute;
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Remove query string
        if (($pos = strpos($requestUri, '?')) !== false) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        // Normalize path
        $requestUri = '/' . trim($requestUri, '/');
        if ($requestUri === '/') {
            $requestUri = '/';
        }

        foreach (self::$routes as $route) {
            if ($route->matches($requestUri, $requestMethod)) {
                self::$currentRoute = $route;
                return $route;
            }
        }

        return null;
    }

    /**
     * Get all registered routes
     */
    public static function getRoutes() {
        return self::$routes;
    }

    /**
     * Clear all routes (for testing)
     */
    public static function clear() {
        self::$routes = [];
        self::$currentRoute = null;
    }

    /**
     * Generate URL for a named route
     */
    public static function url($name, $params = []) {
        foreach (self::$routes as $route) {
            if ($route->getName() === $name) {
                return $route->generateUrl($params);
            }
        }

        return '/';
    }
}
