<?php
/**
 * Main Template File
 *
 * WordPress fallback template. Loom routes are handled via template_redirect.
 *
 * @package Loom\Theme
 */

declare(strict_types=1);

get_header();
?>

<main id="main" class="site-main">
    <?php
    if (have_posts()) :
        while (have_posts()) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title(is_singular() ? '<h1 class="entry-title">' : '<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '">', is_singular() ? '</h1>' : '</a></h2>'); ?>
                </header>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
            <?php
        endwhile;
        the_posts_navigation();
    else :
        ?>
        <section class="no-results not-found">
            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e('Nothing Found', 'loom-theme'); ?></h1>
            </header>
            <div class="page-content">
                <p><?php esc_html_e('It seems we can\'t find what you\'re looking for.', 'loom-theme'); ?></p>
            </div>
        </section>
        <?php
    endif;
    ?>
</main>

<?php
get_footer();
