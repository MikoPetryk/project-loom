<?php
/**
 * Loom Theme - Welcome Page
 *
 * @package Loom\Theme
 */

declare(strict_types=1);

use Loom\Core\Components\Modifier;
use Loom\Core\Components\TextStyle;
use Loom\Core\Components\ButtonStyle;
use Loom\Core\Components\AlertType;
use Loom\Core\Components\Head;
use Loom\Core\Tokens\Colors;
use Loom\Core\Tokens\Spacing;
use Loom\Core\Tokens\Shapes;

// Ensure Loom Core is available
if (!class_exists('\Loom\Core\Components\Modifier')) {
    wp_die(
        'Loom Theme requires the Loom Core plugin to be installed and activated.',
        'Plugin Required',
        ['back_link' => true]
    );
}

Head::reset();
Head::set(
    title: 'Welcome to Loom',
    description: 'Loom - A modern declarative UI framework for WordPress',
    themeColor: '#336659'
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
        :root {
            --loom-primary: #336659;
            --loom-on-primary: #ffffff;
            --loom-surface: #ffffff;
            --loom-background: #f8faf9;
            --loom-text: #1a1c1b;
            --loom-text-secondary: #6b7280;
            --loom-border: #e5e7eb;
        }
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
// Main container
Column(
    modifier: Modifier::new()
        ->minHeight('100vh')
        ->padding(Spacing::xl),
    gap: Spacing::xl,
    align: 'center',
    content: function(): void {
        // Hero Section
        Column(
            modifier: Modifier::new()
                ->maxWidth(800)
                ->textAlign('center')
                ->marginTop(Spacing::xxl),
            gap: Spacing::lg,
            align: 'center',
            content: function(): void {
                // Logo
                Box(
                    modifier: Modifier::new()
                        ->size(80)
                        ->rounded(Shapes::lg)
                        ->background(Colors::primary)
                        ->flex()
                        ->alignItems('center')
                        ->justifyContent('center'),
                    content: function(): void {
                        echo '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>';
                    }
                );

                // Title
                Text('Welcome to Loom', style: TextStyle::H1);

                // Subtitle
                Text(
                    'A modern, declarative UI framework for WordPress. Build beautiful interfaces with PHP components inspired by Jetpack Compose.',
                    color: Colors::textSecondary,
                    modifier: Modifier::new()->fontSize(18)->maxWidth(600)
                );

                // CTA Buttons
                Row(
                    gap: Spacing::md,
                    modifier: Modifier::new()->marginTop(Spacing::md),
                    content: function(): void {
                        Button(
                            text: 'Get Started',
                            style: ButtonStyle::Filled,
                            onClick: "window.location.href='https://github.com/MikoPetryk/project-loom'"
                        );
                        Button(
                            text: 'Documentation',
                            style: ButtonStyle::Outlined,
                            onClick: "window.location.href='https://github.com/MikoPetryk/project-loom'"
                        );
                    }
                );
            }
        );

        // Features Section
        Column(
            modifier: Modifier::new()->maxWidth(1000)->marginTop(Spacing::xxl),
            gap: Spacing::lg,
            content: function(): void {
                Text('Built with Loom Components', style: TextStyle::H2, modifier: Modifier::new()->textAlign('center'));

                Row(
                    gap: Spacing::lg,
                    wrap: true,
                    justify: 'center',
                    content: function(): void {
                        // Feature cards
                        $features = [
                            ['title' => 'Declarative', 'desc' => 'Write UI as composable functions, just like modern frameworks.', 'icon' => '✨'],
                            ['title' => 'Type Safe', 'desc' => 'Full PHP 8.1+ type safety with IDE autocomplete.', 'icon' => '🔒'],
                            ['title' => 'Design Tokens', 'desc' => 'Consistent colors, spacing, and typography system.', 'icon' => '🎨'],
                        ];

                        foreach ($features as $feature) {
                            Card(
                                modifier: Modifier::new()->width(280),
                                padding: Spacing::lg,
                                rounded: Shapes::lg,
                                content: function() use ($feature): void {
                                    Column(gap: Spacing::sm, content: function() use ($feature): void {
                                        Text($feature['icon'], modifier: Modifier::new()->fontSize(32));
                                        Text($feature['title'], modifier: Modifier::new()->fontWeight(600)->fontSize(18));
                                        Text($feature['desc'], color: Colors::textSecondary, modifier: Modifier::new()->fontSize(14));
                                    });
                                }
                            );
                        }
                    }
                );
            }
        );

        // Components Demo
        Card(
            modifier: Modifier::new()->maxWidth(600)->marginTop(Spacing::xl),
            padding: Spacing::lg,
            rounded: Shapes::lg,
            content: function(): void {
                Column(gap: Spacing::md, content: function(): void {
                    Text('Component Examples', modifier: Modifier::new()->fontWeight(600)->fontSize(16));

                    Divider();

                    // Buttons
                    Column(gap: Spacing::sm, content: function(): void {
                        Text('Buttons', modifier: Modifier::new()->fontSize(14)->fontWeight(500));
                        Row(gap: Spacing::sm, wrap: true, content: function(): void {
                            Button(text: 'Filled', style: ButtonStyle::Filled);
                            Button(text: 'Outlined', style: ButtonStyle::Outlined);
                            Button(text: 'Text', style: ButtonStyle::Text);
                        });
                    });

                    // Chips
                    Column(gap: Spacing::sm, content: function(): void {
                        Text('Chips', modifier: Modifier::new()->fontSize(14)->fontWeight(500));
                        Row(gap: Spacing::sm, wrap: true, content: function(): void {
                            Chip(label: 'Default');
                            Chip(label: 'Selected', selected: true);
                            Chip(label: 'With Icon', icon: '⭐');
                        });
                    });

                    // Progress
                    Column(gap: Spacing::sm, content: function(): void {
                        Text('Progress', modifier: Modifier::new()->fontSize(14)->fontWeight(500));
                        Progress(value: 65, height: 8, color: Colors::primary);
                    });

                    // Alert
                    Alert(
                        message: 'This page is built entirely with Loom components!',
                        type: AlertType::Success,
                        title: 'Success'
                    );
                });
            }
        );

        // Footer
        Text(
            'Loom Theme v' . LOOM_THEME_VERSION . ' — Built with Loom Core',
            color: Colors::textSecondary,
            modifier: Modifier::new()->fontSize(14)->marginTop(Spacing::xxl)
        );
    }
);

wp_footer();
?>
</body>
</html>
