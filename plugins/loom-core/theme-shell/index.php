<?php
/**
 * Loom Theme Shell - Main Template
 *
 * This file handles all template rendering through Loom's routing system.
 *
 * @package Loom\ThemeShell
 */



use Loom\Core\Loom;
use Loom\Core\Routing\Router;

// Get current route
$route = Router::getCurrentRoute();

if ($route) {
    // Render via Loom routing
    $route->render();
} else {
    // Fallback to default template
    get_header();
    ?>
    <main id="primary" class="site-main">
        <?php
        if (have_posts()) {
            while (have_posts()) {
                the_post();
                the_content();
            }
        }
        ?>
    </main>
    <?php
    get_footer();
}
