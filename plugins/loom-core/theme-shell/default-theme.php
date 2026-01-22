<?php
/**
 * Default Theme
 *
 * Fallback theme when no developer theme is configured.
 *
 * @package Loom\ThemeShell
 */



use Loom\Core\Loom;

// Simple default header
add_action('loom_header', function () {
    ?>
    <header style="padding: 20px; background: var(--loom-surface, #fff); border-bottom: 1px solid #eee;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
            <a href="<?php echo esc_url(home_url('/')); ?>" style="font-size: 24px; font-weight: bold; text-decoration: none; color: var(--loom-primary, #336659);">
                <?php bloginfo('name'); ?>
            </a>
            <nav>
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container' => false,
                    'menu_class' => 'loom-nav-menu',
                    'fallback_cb' => false,
                ]);
                ?>
            </nav>
        </div>
    </header>
    <style>
        .loom-nav-menu {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 20px;
        }
        .loom-nav-menu a {
            text-decoration: none;
            color: var(--loom-on-surface, #333);
        }
        .loom-nav-menu a:hover {
            color: var(--loom-primary, #336659);
        }
    </style>
    <?php
});

// Simple default footer
add_action('loom_footer', function () {
    ?>
    <footer style="padding: 40px 20px; background: var(--loom-surface-variant, #f5f5f5); margin-top: 40px;">
        <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
            <p style="color: var(--loom-on-surface-variant, #666);">
                &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. Powered by Loom.
            </p>
        </div>
    </footer>
    <?php
});
