<?php
/**
 * The template for displaying search results
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
            <h1 class="text-4xl font-bold text-text-primary mb-4">
                <?php printf(
                  /* translators: %s: search query */
                  esc_html__('Search results for: %s', 'nera-competitions'),
                  '<span class="text-primary">' . get_search_query() . '</span>',
                ); ?>
            </h1>

            <div class="max-w-xl mx-auto mt-6">
                <form role="search" method="get" class="flex gap-2" action="<?php echo esc_url(
                  home_url('/'),
                ); ?>">
                    <input
                        type="search"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        placeholder="<?php esc_attr_e('Search...', 'nera-competitions'); ?>"
                        value="<?php echo get_search_query(); ?>"
                        name="s"
                    >
                    <button type="submit" class="px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition-colors">
                        <?php esc_html_e('Search', 'nera-competitions'); ?>
                    </button>
                </form>
            </div>
        </header>

        <?php if (have_posts()): ?>

            <p class="text-center text-text-secondary mb-8">
                <?php printf(
                  /* translators: %d: number of results */
                  esc_html(
                    _n(
                      '%d result found',
                      '%d results found',
                      $wp_query->found_posts,
                      'nera-competitions',
                    ),
                  ),
                  $wp_query->found_posts,
                ); ?>
            </p>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <?php while (have_posts()):
                  the_post(); ?>

                    <article id="post-<?php the_ID(); ?>" <?php post_class(
  'bg-surface rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition-shadow',
); ?>>
                        <?php if (has_post_thumbnail()): ?>
                            <a href="<?php the_permalink(); ?>" class="block aspect-video overflow-hidden">
                                <?php the_post_thumbnail('medium_large', [
                                  'class' => 'w-full h-full object-cover',
                                ]); ?>
                            </a>
                        <?php endif; ?>

                        <div class="p-6">
                            <span class="inline-block px-2 py-1 mb-3 text-xs font-medium bg-gray-100 text-text-secondary rounded">
                                <?php echo get_post_type_object(get_post_type())->labels
                                  ->singular_name; ?>
                            </span>

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
                $prev_link = get_previous_posts_link(__('&larr; Previous', 'nera-competitions'));
                $next_link = get_next_posts_link(__('Next &rarr;', 'nera-competitions'));

                if ($prev_link) {
                  echo '<span class="px-6 py-3 bg-surface rounded-lg shadow-sm hover:shadow-md transition-shadow font-semibold text-text-primary">' .
                    $prev_link .
                    '</span>';
                }
                if ($next_link) {
                  echo '<span class="px-6 py-3 bg-surface rounded-lg shadow-sm hover:shadow-md transition-shadow font-semibold text-text-primary">' .
                    $next_link .
                    '</span>';
                }
                ?>
            </nav>

        <?php else: ?>

            <div class="text-center py-16">
                <div class="max-w-md mx-auto">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <h2 class="text-2xl font-bold text-text-primary mb-2"><?php esc_html_e(
                      'No results found',
                      'nera-competitions',
                    ); ?></h2>
                    <p class="text-text-secondary mb-6"><?php esc_html_e(
                      'Sorry, no content matched your search. Please try again with different keywords.',
                      'nera-competitions',
                    ); ?></p>

                    <a href="<?php echo esc_url(
                      home_url('/'),
                    ); ?>" class="inline-flex items-center px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition-colors">
                        <?php esc_html_e('Back to Home', 'nera-competitions'); ?>
                    </a>
                </div>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php get_footer();
