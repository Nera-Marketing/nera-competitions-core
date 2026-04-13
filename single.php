<?php
/**
 * Single template fallback for post types without a dedicated single-{post_type}.php template.
 *
 * Standard posts use single-post.php.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

get_header();
?>

<main id="primary" class="site-main bg-background-secondary min-h-screen">

    <?php while (have_posts()):
      the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <div class="container mx-auto px-4 py-12">
                <div class="max-w-3xl mx-auto">

                    <header class="mb-8">
                        <?php the_title(
                          '<h1 class="text-3xl md:text-4xl font-bold text-text-primary">',
                          '</h1>',
                        ); ?>
                    </header>

                    <div class="prose prose-lg max-w-none text-text-secondary">
                        <?php the_content(); ?>
                    </div>

                    <?php wp_link_pages([
                      'before' =>
                        '<div class="page-links mt-8 py-4 border-t border-gray-200"><span class="text-text-primary font-semibold mr-4">' .
                        esc_html__('Pages:', 'nera-competitions') .
                        '</span>',
                      'after' => '</div>',
                      'link_before' =>
                        '<span class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200 transition-colors">',
                      'link_after' => '</span>',
                    ]); ?>

                </div>
            </div>

        </article>

    <?php
    endwhile; ?>

</main>

<?php get_footer();
