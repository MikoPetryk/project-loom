<?php
/**
 * Loom Route
 *
 * Represents a single route.
 *
 * @package Loom\Core\Routing
 */

namespace Loom\Core\Routing;

use Loom\Core\Container\Container;

class Route {
    /**
     * Route parameters extracted from URL
     */
    private $params = [];

    public $path;
    public $class;
    public $method;
    public $httpMethod = 'GET';
    public $middleware = [];

    public function __construct(
        $path,
        $class,
        $method,
        $httpMethod = 'GET',
        $middleware = []
    ) {
        $this->path = $path;
        $this->class = $class;
        $this->method = $method;
        $this->httpMethod = $httpMethod;
        $this->middleware = $middleware;
    }

    /**
     * Check if this route matches the given URI and method
     */
    public function matches($uri, $requestMethod) {
        // Check HTTP method
        if (strtoupper($this->httpMethod) !== strtoupper($requestMethod)) {
            return false;
        }

        // Convert route pattern to regex
        $pattern = $this->pathToRegex($this->path);

        if (preg_match($pattern, $uri, $matches)) {
            // Extract named parameters
            $this->params = array_filter($matches, fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);
            return true;
        }

        return false;
    }

    /**
     * Convert route path to regex pattern
     * Supports: {param}, {param?} (optional), {param:regex}
     */
    private function pathToRegex($path) {
        // Escape special regex chars except our placeholders
        $pattern = preg_quote($path, '#');

        // Convert {param} to named capture group
        $pattern = preg_replace(
            '/\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\}/',
            '(?P<$1>[^/]+)',
            $pattern
        );

        // Convert {param?} to optional named capture group
        $pattern = preg_replace(
            '/\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\?\\\}/',
            '(?P<$1>[^/]*)?',
            $pattern
        );

        // Convert {param:pattern} to named capture group with custom pattern
        $pattern = preg_replace_callback(
            '/\\\{([a-zA-Z_][a-zA-Z0-9_]*):([^}]+)\\\}/',
            fn($m) => '(?P<' . $m[1] . '>' . stripslashes($m[2]) . ')',
            $pattern
        );

        return '#^' . $pattern . '$#';
    }

    /**
     * Get route parameters
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Get a specific parameter
     */
    public function getParam($name, $default = null) {
        return $this->params[$name] ?? $default;
    }

    /**
     * Get route name (class::method)
     */
    public function getName() {
        $shortClass = (new \ReflectionClass($this->class))->getShortName();
        return strtolower($shortClass) . '.' . $this->method;
    }

    /**
     * Generate URL for this route with given parameters
     */
    public function generateUrl($params = []) {
        $url = $this->path;

        foreach ($params as $key => $value) {
            $url = preg_replace('/\{' . $key . '\??\}/', (string) $value, $url);
        }

        // Remove any remaining optional parameters
        $url = preg_replace('/\{[^}]+\?\}/', '', $url);

        return $url;
    }

    /**
     * Render this route
     */
    public function render() {
        // Run middleware
        foreach ($this->middleware as $middleware) {
            $middlewareInstance = Container::get($middleware);
            $result = $middlewareInstance->handle();
            if ($result === false) {
                return; // Middleware blocked the request
            }
        }

        // Resolve the class
        $instance = Container::get($this->class);

        // Call the method with route parameters
        $reflection = new \ReflectionMethod($instance, $this->method);
        $args = [];

        foreach ($reflection->getParameters() as $param) {
            $paramName = $param->getName();
            if (isset($this->params[$paramName])) {
                $args[] = $this->params[$paramName];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                $args[] = null;
            }
        }

        // Render - page is responsible for full HTML output
        $result = $reflection->invokeArgs($instance, $args);

        // If result is a string, echo it
        if (is_string($result)) {
            echo $result;
        }
    }
}
