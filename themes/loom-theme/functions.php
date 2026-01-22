<?php
/**
 * Loom Theme Functions
 *
 * @package Loom\Theme
 * @since 1.0.0
 */

declare(strict_types=1);

define('LOOM_THEME_VERSION', '1.0.0');

/**
 * Require Loom Core - theme cannot function without it
 */
add_action('after_setup_theme', function(): void {
    if (!class_exists('\Loom\Core\Components\Modifier')) {
        add_action('admin_notices', function(): void {
            echo '<div class="notice notice-error">';
            echo '<p><strong>Loom Theme:</strong> This theme requires the Loom Core plugin. Please install and activate it.</p>';
            echo '</div>';
        });

        add_action('template_redirect', function(): void {
            wp_die(
                'Loom Theme requires the Loom Core plugin to be installed and activated.',
                'Loom Core Required',
                ['back_link' => true]
            );
        });
    }
}, 1);

/**
 * PSR-4 Autoloader for Loom Theme
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'Loom\\Theme\\';
    $baseDir = get_template_directory() . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Auto-discover and register routes from Pages directory
 */
add_action('loom_core_loaded', function(): void {
    $pagesDir = get_template_directory() . '/src/Pages';

    if (!is_dir($pagesDir)) {
        return;
    }

    foreach (glob($pagesDir . '/*.php') as $file) {
        $className = 'Loom\\Theme\\Pages\\' . basename($file, '.php');

        if (class_exists($className)) {
            $reflection = new ReflectionClass($className);
            $attributes = $reflection->getAttributes(\Loom\Core\Annotations\Route::class);

            if (!empty($attributes)) {
                \Loom\Core\Routing\Router::register($className);
            }
        }
    }

    add_action('template_redirect', function(): void {
        $route = \Loom\Core\Routing\Router::getCurrentRoute();
        if ($route !== null) {
            $route->render();
            exit;
        }
    }, 1);

    add_filter('query_vars', function(array $vars): array {
        $vars[] = 'loom_route';
        return $vars;
    });
}, 5);

/**
 * Theme setup
 */
add_action('after_setup_theme', function(): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);
}, 10);
