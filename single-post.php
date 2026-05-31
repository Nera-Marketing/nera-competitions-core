<?php
/**
 * Single blog post template.
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
      the_post();

      $content_raw = get_post_field('post_content', get_the_ID());
      $word_count = $content_raw ? str_word_count(wp_strip_all_tags($content_raw)) : 0;
      $read_mins = max(1, (int) ceil($word_count / 200));
      $read_label = sprintf(
        /* translators: %d: estimated reading time in minutes */
        _n('%d MIN READ', '%d MIN READ', $read_mins, 'nera-competitions'),
        $read_mins,
      );

      $categories = get_the_category();
      if (!empty($categories)) {
        $primary_cat = $categories[0];
        $category_label = $primary_cat->name;
        $category_url = get_category_link($primary_cat->term_id);
      } else {
        $default_cat_id = (int) get_option('default_category');
        $category_label = __('Uncategorized', 'nera-competitions');
        $category_url = $default_cat_id ? get_category_link($default_cat_id) : '';
      }
      ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <?php if (has_post_thumbnail()): ?>
                <section class="relative flex min-h-[min(32rem,75svh)] w-full flex-col overflow-hidden md:min-h-[min(36rem,78vh)]" aria-label="<?php esc_attr_e('Post header', 'nera-competitions'); ?>">
                    <div class="absolute inset-0 z-0">
                        <?php the_post_thumbnail('full', [
                          'class' => 'min-h-full w-full max-h-none object-cover !h-full',
                        ]); ?>
                    </div>
                    <div class="absolute inset-0 z-[1] bg-gradient-to-t from-black/80 via-black/45 to-black/25" aria-hidden="true"></div>

                    <div class="relative z-10 flex flex-1 flex-col justify-end">
                        <div class="container mx-auto px-4 pb-10 pt-24 md:pb-14 md:pt-32">
                            <div class="max-w-3xl">
                                <p class="mb-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium uppercase tracking-wide text-white/90">
                                    <?php if ($category_url): ?>
                                        <a href="<?php echo esc_url($category_url); ?>" class="inline-flex items-center rounded-full border border-white/40 bg-black/35 px-3 py-1 text-[11px] font-semibold !text-white backdrop-blur-sm hover:bg-black/50 hover:!text-white">
                                            <?php echo esc_html(strtoupper($category_label)); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="inline-flex items-center rounded-full border border-white/40 bg-black/35 px-3 py-1 text-[11px] font-semibold text-white backdrop-blur-sm">
                                            <?php echo esc_html(strtoupper($category_label)); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="text-white/70" aria-hidden="true">•</span>
                                    <span><?php echo esc_html($read_label); ?></span>
                                </p>

                                <?php the_title(
                                  '<h1 class="font-heading mb-8 text-3xl font-bold leading-tight text-white md:text-4xl lg:text-5xl">',
                                  '</h1>',
                                ); ?>

                                <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:gap-12">
                                    <div class="flex items-start gap-3">
                                        <?php echo get_avatar(get_the_author_meta('ID'), 48, '', '', [
                                          'class' => 'h-12 w-12 shrink-0 rounded-full ring-2 ring-white/30',
                                        ]); ?>
                                        <div>
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-white/70"><?php esc_html_e(
                                              'Written by',
                                              'nera-competitions',
                                            ); ?></p>
                                            <p class="text-base font-semibold text-white"><?php the_author(); ?></p>
                                        </div>
                                    </div>
                                    <div class="sm:pt-0">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-white/70"><?php esc_html_e(
                                          'Published',
                                          'nera-competitions',
                                        ); ?></p>
                                        <time class="text-base font-semibold text-white" datetime="<?php echo esc_attr(
                                          get_the_date('c'),
                                        ); ?>"><?php echo esc_html(get_the_date()); ?></time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php else: ?>
                <section class="border-b border-gray-200 bg-background-secondary">
                    <div class="container mx-auto max-w-6xl px-4 py-10 md:py-14">
                        <div class="max-w-3xl">
                            <p class="mb-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium uppercase tracking-wide text-text-secondary">
                                <?php if ($category_url): ?>
                                    <a href="<?php echo esc_url($category_url); ?>" class="inline-flex items-center rounded-full border border-gray-300 bg-gray-100 px-3 py-1 text-[11px] font-semibold text-text-primary hover:border-primary hover:text-primary">
                                        <?php echo esc_html(strtoupper($category_label)); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="inline-flex items-center rounded-full border border-gray-300 bg-gray-100 px-3 py-1 text-[11px] font-semibold text-text-primary">
                                        <?php echo esc_html(strtoupper($category_label)); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="text-text-secondary" aria-hidden="true">•</span>
                                <span><?php echo esc_html($read_label); ?></span>
                            </p>

                            <?php the_title(
                              '<h1 class="font-heading mb-8 text-3xl font-bold text-text-primary md:text-4xl lg:text-5xl">',
                              '</h1>',
                            ); ?>

                            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:gap-12">
                                <div class="flex items-start gap-3">
                                    <?php echo get_avatar(get_the_author_meta('ID'), 48, '', '', [
                                      'class' => 'h-12 w-12 shrink-0 rounded-full ring-2 ring-gray-200',
                                    ]); ?>
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-text-secondary"><?php esc_html_e(
                                          'Written by',
                                          'nera-competitions',
                                        ); ?></p>
                                        <p class="text-base font-semibold text-text-primary"><?php the_author(); ?></p>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-text-secondary"><?php esc_html_e(
                                      'Published',
                                      'nera-competitions',
                                    ); ?></p>
                                    <time class="text-base font-semibold text-text-primary" datetime="<?php echo esc_attr(
                                      get_the_date('c'),
                                    ); ?>"><?php echo esc_html(get_the_date()); ?></time>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <div class="bg-background-secondary py-10 md:py-14">
                <div class="container mx-auto px-4">

                    <div class="prose prose-lg max-w-none text-text-secondary prose-headings:font-heading prose-headings:text-text-primary prose-a:text-primary prose-strong:text-text-primary">
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

            <?php get_template_part('template-parts/blog/related-articles'); ?>

        </article>

      

    <?php
    endwhile; ?>

</main>

<?php get_footer();
