<?php
/**
 * Blog posts index (when a static front page is set and a "Posts page" is assigned).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

get_header();

$_blog_page_id = (int) get_option('page_for_posts');
$_blog_title   = $_blog_page_id ? get_the_title($_blog_page_id) : '';
$_blog_title   = $_blog_title ?: __('Blog', 'nera-competitions');
$_blog_desc    = $_blog_page_id ? get_the_excerpt($_blog_page_id) : '';
?>

<main id="main" class="nera-blog-home" role="main">
    <?php
    get_template_part('template-parts/components/shared/page-hero', null, [
        'title'         => $_blog_title,
        'description'   => $_blog_desc,
    ]);
    ?>
    <div class="bg-background-secondary min-h-screen">
        <div class="max-w-7xl mx-auto px-4 py-10 md:py-12">

            <?php get_template_part('template-parts/blog/loop'); ?>

        </div>
    </div>
</main>

<?php get_footer();
