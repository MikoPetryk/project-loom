<?php
/**
 * App Layout - Simple Layout for Welcome Page
 *
 * @package Loom\Theme\Layouts
 */

declare(strict_types=1);

namespace Loom\Theme\Layouts;

use Loom\Core\Components\Modifier;
use Loom\Core\Components\Head;
use Loom\Core\Tokens\Colors;
use Loom\Core\Tokens\Spacing;

class AppLayout {

    public static function render(string $title, \Closure $content, ?string $description = null): void {
        Head::reset();
        Head::set(
            title: $title,
            description: $description ?? 'Loom - A modern declarative UI framework for WordPress',
            ogType: 'website',
            themeColor: Colors::primary()
        );
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?> data-theme="light">
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html(Head::getTitle('Loom')); ?></title>
            <?php echo Head::render(); ?>
            <?php wp_head(); ?>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                    background: var(--loom-background);
                    color: var(--loom-text);
                    line-height: 1.6;
                    -webkit-font-smoothing: antialiased;
                }
            </style>
        </head>
        <body>
        <?php
        Column(
            modifier: Modifier::new()
                ->minHeight('100vh')
                ->padding(Spacing::xl),
            gap: Spacing::xl,
            align: 'center',
            content: $content
        );

        wp_footer();
        ?>
        </body>
        </html>
        <?php
    }
}
