<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

get_header();

if (
  function_exists('is_account_page') &&
  is_account_page() &&
  !is_user_logged_in()
) {
  $active_tab =
    isset($_GET['action']) && $_GET['action'] === 'register'
      ? 'register'
      : 'login';

  get_template_part('template-parts/components/shared/page-hero', null, [
    'title' =>
      $active_tab === 'register'
        ? __('Create Account', 'woocommerce')
        : __('Welcome Back', 'woocommerce'),
    'description' =>
      $active_tab === 'register'
        ? __(
          'Join us and start entering competitions today',
          'woocommerce',
        )
        : __('Log in to your account to continue', 'woocommerce'),
    'variant' => 'compact',
    'eyebrow_label' => __('Your Account', 'woocommerce'),
    'eyebrow_icon' => 'lock',
  ]);
}
?>

<main id="primary" class="site-main bg-background-secondary min-h-screen<?php echo (function_exists(
  'is_cart',
) &&
  is_cart()) ||
(function_exists('is_checkout') && is_checkout())
  ? ' !py-0'
  : ''; ?>">

    <?php while (have_posts()):
      the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <?php if (has_post_thumbnail() && !is_front_page()): ?>
                <div class="w-full aspect-[21/9] overflow-hidden">
                    <?php the_post_thumbnail('full', ['class' => 'w-full h-full object-cover']); ?>
                </div>
            <?php endif; ?>

            <?php if (
              !is_front_page() &&
              !(function_exists('is_checkout') && is_checkout()) &&
              !(function_exists('is_cart') && is_cart())
            ): ?>
                <header class="mb-8">
                    <?php the_title(
                      '<h1 class="text-4xl md:text-5xl font-bold text-text-primary">',
                      '</h1>',
                    ); ?>
                </header>
            <?php endif; ?>

            <?php the_content(); ?>

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

            </article>
  
              <?php if (comments_open() || get_comments_number()) {
                echo '<div class="container mx-auto px-4 pb-12"><div class="max-w-4xl mx-auto bg-surface rounded-2xl p-8 shadow-sm">';
                comments_template();
                echo '</div></div>';
              } ?>

    <?php
    endwhile; ?>

</main>

<?php get_footer();
