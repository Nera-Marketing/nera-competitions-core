<?php
/**
 * The template for displaying archive pages
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

get_header();
?>

<main id="primary" class="site-main bg-background-secondary min-h-screen">
    <div class="container mx-auto px-4 py-12">

        <header class="mb-12 text-center">
            <?php
            the_archive_title('<h1 class="text-4xl font-bold text-text-primary mb-4">', '</h1>');
            the_archive_description(
              '<div class="text-lg text-text-secondary max-w-2xl mx-auto">',
              '</div>',
            );
            ?>
        </header>

        <?php if (have_posts()): ?>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <?php while (have_posts()):
                  the_post(); ?>

                    <article id="post-<?php the_ID(); ?>" <?php post_class(
  'bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition-shadow',
); ?>>
                        <?php if (has_post_thumbnail()): ?>
                            <a href="<?php the_permalink(); ?>" class="block aspect-video overflow-hidden">
                                <?php the_post_thumbnail('medium_large', [
                                  'class' => 'w-full h-full object-cover',
                                ]); ?>
                            </a>
                        <?php endif; ?>

                        <div class="p-6">
                            <header class="mb-4">
                                <?php the_title(
                                  '<h2 class="text-xl font-bold text-text-primary hover:text-primary transition-colors"><a href="' .
                                    esc_url(get_permalink()) .
                                    '">',
                                  '</a></h2>',
                                ); ?>

                                <div class="mt-2 text-sm text-text-secondary">
                                    <time datetime="<?php echo esc_attr(
                                      get_the_date('c'),
                                    ); ?>"><?php echo get_the_date(); ?></time>
                                    <span class="mx-2">•</span>
                                    <span><?php the_author(); ?></span>
                                </div>
                            </header>

                            <div class="text-text-secondary line-clamp-3">
                                <?php the_excerpt(); ?>
                            </div>

                            <a href="<?php the_permalink(); ?>" class="inline-flex items-center mt-4 text-primary font-semibold hover:text-primary-dark transition-colors">
                                <?php esc_html_e('Read more', 'nera-competitions'); ?>
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </article>

                <?php
                endwhile; ?>
            </div>

            <nav class="mt-12 flex justify-center gap-4">
                <?php
                $prev_link = get_previous_posts_link(__('&larr; Newer Posts', 'nera-competitions'));
                $next_link = get_next_posts_link(__('Older Posts &rarr;', 'nera-competitions'));

                if ($prev_link) {
                  echo '<span class="px-6 py-3 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow font-semibold text-text-primary">' .
                    $prev_link .
                    '</span>';
                }
                if ($next_link) {
                  echo '<span class="px-6 py-3 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow font-semibold text-text-primary">' .
                    $next_link .
                    '</span>';
                }
                ?>
            </nav>

        <?php else: ?>

            <div class="text-center py-16">
                <div class="max-w-md mx-auto">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    <h2 class="text-2xl font-bold text-text-primary mb-2"><?php esc_html_e(
                      'No posts found',
                      'nera-competitions',
                    ); ?></h2>
                    <p class="text-text-secondary"><?php esc_html_e(
                      'No content matches this archive.',
                      'nera-competitions',
                    ); ?></p>
                </div>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php get_footer();
