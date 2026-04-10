<?php
/**
 * The template for displaying single posts
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

    <?php while (have_posts()):
      the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <?php if (has_post_thumbnail()): ?>
                <div class="w-full aspect-[21/9] overflow-hidden">
                    <?php the_post_thumbnail('full', ['class' => 'w-full h-full object-cover']); ?>
                </div>
            <?php endif; ?>

            <div class="container mx-auto px-4 py-12">
                <div class="max-w-3xl mx-auto">

                    <header class="mb-8">
                        <?php the_title(
                          '<h1 class="text-4xl md:text-5xl font-bold text-text-primary mb-4">',
                          '</h1>',
                        ); ?>

                        <div class="flex flex-wrap items-center gap-4 text-text-secondary">
                            <div class="flex items-center gap-2">
                                <?php echo get_avatar(get_the_author_meta('ID'), 40, '', '', [
                                  'class' => 'rounded-full',
                                ]); ?>
                                <span class="font-medium"><?php the_author(); ?></span>
                            </div>

                            <span class="text-gray-300">•</span>

                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                <?php echo get_the_date(); ?>
                            </time>

                            <?php if (get_the_category()): ?>
                                <span class="text-gray-300">•</span>
                                <div class="flex gap-2">
                                    <?php
                                    $categories = get_the_category();
                                    foreach ($categories as $category) {
                                      echo '<a href="' .
                                        esc_url(get_category_link($category->term_id)) .
                                        '" class="text-primary hover:text-primary-dark transition-colors">' .
                                        esc_html($category->name) .
                                        '</a>';
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
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

                    <?php if (has_tag()): ?>
                        <footer class="mt-8 pt-8 border-t border-gray-200">
                            <div class="flex flex-wrap gap-2">
                                <?php
                                $tags = get_the_tags();
                                foreach ($tags as $tag) {
                                  echo '<a href="' .
                                    esc_url(get_tag_link($tag->term_id)) .
                                    '" class="px-3 py-1 bg-gray-100 rounded-full text-sm text-text-secondary hover:bg-gray-200 transition-colors">#' .
                                    esc_html($tag->name) .
                                    '</a>';
                                }
                                ?>
                            </div>
                        </footer>
                    <?php endif; ?>

                </div>
            </div>

        </article>

        <nav class="container mx-auto px-4 pb-12">
            <div class="max-w-3xl mx-auto flex justify-between gap-4">
                <?php
                $prev_post = get_previous_post();
                $next_post = get_next_post();
                ?>

                <?php if ($prev_post): ?>
                    <a href="<?php echo get_permalink(
                      $prev_post,
                    ); ?>" class="flex-1 p-6 bg-surface rounded-xl shadow-sm hover:shadow-md transition-shadow group">
                        <span class="text-sm text-text-secondary"><?php esc_html_e(
                          'Previous',
                          'nera-competitions',
                        ); ?></span>
                        <h4 class="font-semibold text-text-primary group-hover:text-primary transition-colors line-clamp-2"><?php echo get_the_title(
                          $prev_post,
                        ); ?></h4>
                    </a>
                <?php else: ?>
                    <div class="flex-1"></div>
                <?php endif; ?>

                <?php if ($next_post): ?>
                    <a href="<?php echo get_permalink(
                      $next_post,
                    ); ?>" class="flex-1 p-6 bg-surface rounded-xl shadow-sm hover:shadow-md transition-shadow group text-right">
                        <span class="text-sm text-text-secondary"><?php esc_html_e(
                          'Next',
                          'nera-competitions',
                        ); ?></span>
                        <h4 class="font-semibold text-text-primary group-hover:text-primary transition-colors line-clamp-2"><?php echo get_the_title(
                          $next_post,
                        ); ?></h4>
                    </a>
                <?php endif; ?>
            </div>
        </nav>

        <?php if (comments_open() || get_comments_number()) {
          echo '<div class="container mx-auto px-4 pb-12"><div class="max-w-3xl mx-auto bg-surface rounded-2xl p-8 shadow-sm">';
          comments_template();
          echo '</div></div>';
        } ?>

    <?php
    endwhile; ?>

</main>

<?php get_footer();
