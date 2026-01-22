<?php
/**
 * Welcome Page - Loom Demo
 *
 * @package Loom\Theme\Pages
 */

declare(strict_types=1);

namespace Loom\Theme\Pages;

use Loom\Core\Annotations\Route;
use Loom\Core\Components\Modifier;
use Loom\Core\Components\TextStyle;
use Loom\Core\Components\ButtonStyle;
use Loom\Core\Components\AlertType;
use Loom\Core\Tokens\Colors;
use Loom\Core\Tokens\Spacing;
use Loom\Core\Tokens\Shapes;
use Loom\Theme\Layouts\AppLayout;

#[Route(path: '/')]
class WelcomePage {

    public function render(): void {
        AppLayout::render('Welcome to Loom', function(): void {
            // Theme Toggle
            self::renderThemeToggle();

            // Hero Section
            self::renderHero();

            // Features Section
            self::renderFeatures();

            // Components Demo
            self::renderComponentsDemo();

            // Footer
            self::renderFooter();
        });
    }

    private static function renderThemeToggle(): void {
        // Sun icon SVG
        $sunIcon = '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="5"></circle>
            <line x1="12" y1="1" x2="12" y2="3"></line>
            <line x1="12" y1="21" x2="12" y2="23"></line>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
            <line x1="1" y1="12" x2="3" y2="12"></line>
            <line x1="21" y1="12" x2="23" y2="12"></line>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
        </svg>';

        // Moon icon SVG
        $moonIcon = '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>';

        Row(
            modifier: Modifier::new()
                ->fixed()
                ->style('top', '20px')
                ->style('right', '20px')
                ->zIndex(1000)
                ->cursor('pointer')
                ->id('theme-toggle'),
            gap: Spacing::sm,
            align: 'center',
            content: function() use ($sunIcon, $moonIcon): void {
                // Sun icon (light mode indicator)
                Box(
                    modifier: Modifier::new()
                        ->style('display', 'flex')
                        ->color(Colors::text())
                        ->id('theme-icon-light'),
                    content: fn() => print($sunIcon)
                );

                // Moon icon (dark mode indicator)
                Box(
                    modifier: Modifier::new()
                        ->style('display', 'none')
                        ->color(Colors::text())
                        ->id('theme-icon-dark'),
                    content: fn() => print($moonIcon)
                );

                // Toggle track
                Box(
                    modifier: Modifier::new()
                        ->width(52)
                        ->height(28)
                        ->rounded(14)
                        ->background(Colors::border())
                        ->relative()
                        ->transition('background 0.3s ease')
                        ->id('theme-toggle-track'),
                    content: function(): void {
                        // Toggle knob
                        Box(
                            modifier: Modifier::new()
                                ->size(22)
                                ->roundedFull()
                                ->background(Colors::surface())
                                ->shadow(1)
                                ->absolute()
                                ->style('top', '3px')
                                ->style('left', '3px')
                                ->transition('transform 0.3s ease')
                                ->id('theme-toggle-knob'),
                            content: fn() => null
                        );
                    }
                );
            }
        );

        // Theme toggle script
        ?>
        <script>
        (function() {
            var html = document.documentElement;

            function initThemeToggle() {
                var toggle = document.getElementById('theme-toggle');
                var track = document.getElementById('theme-toggle-track');
                var knob = document.getElementById('theme-toggle-knob');
                var iconLight = document.getElementById('theme-icon-light');
                var iconDark = document.getElementById('theme-icon-dark');

                if (!toggle || !track || !knob) {
                    console.error('Theme toggle elements not found');
                    return;
                }

                function updateUI(theme) {
                    if (theme === 'dark') {
                        knob.style.transform = 'translateX(24px)';
                        track.style.backgroundColor = '#4a9e8c';
                        if (iconLight) iconLight.style.display = 'none';
                        if (iconDark) iconDark.style.display = 'flex';
                    } else {
                        knob.style.transform = 'translateX(0)';
                        track.style.backgroundColor = '';
                        if (iconLight) iconLight.style.display = 'flex';
                        if (iconDark) iconDark.style.display = 'none';
                    }
                }

                function setTheme(theme) {
                    html.setAttribute('data-theme', theme);
                    localStorage.setItem('loom-theme', theme);
                    updateUI(theme);
                }

                function toggleTheme(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var currentTheme = html.getAttribute('data-theme') || 'light';
                    var newTheme = currentTheme === 'light' ? 'dark' : 'light';
                    setTheme(newTheme);
                }

                // Get initial theme
                var savedTheme = localStorage.getItem('loom-theme');
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                var initialTheme = savedTheme || (prefersDark ? 'dark' : 'light');

                // Apply initial theme to HTML
                html.setAttribute('data-theme', initialTheme);
                updateUI(initialTheme);

                // Attach click handler
                toggle.onclick = toggleTheme;
            }

            initThemeToggle();
        })();
        </script>
        <?php
    }

    private static function renderHero(): void {
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
                        ->background(Colors::primary())
                        ->flex()
                        ->alignItems('center')
                        ->justifyContent('center'),
                    content: function(): void {
                        echo '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="' . Colors::onPrimary() . '" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>';
                    }
                );

                // Title
                Text('Welcome to Loom', style: TextStyle::H1);

                // Subtitle
                Text(
                    'A modern, declarative UI framework for WordPress. Build beautiful interfaces with PHP components inspired by Jetpack Compose.',
                    color: Colors::textSecondary(),
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
    }

    private static function renderFeatures(): void {
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
                                        Text($feature['desc'], color: Colors::textSecondary(), modifier: Modifier::new()->fontSize(14));
                                    });
                                }
                            );
                        }
                    }
                );
            }
        );
    }

    private static function renderComponentsDemo(): void {
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
                        Progress(value: 65, height: 8, color: Colors::primary());
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
    }

    private static function renderFooter(): void {
        Text(
            'Loom Theme v' . LOOM_THEME_VERSION . ' — Built with Loom Core',
            color: Colors::textSecondary(),
            modifier: Modifier::new()->fontSize(14)->marginTop(Spacing::xxl)
        );
    }
}
