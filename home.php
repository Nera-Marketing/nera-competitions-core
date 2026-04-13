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
?>

<main id="primary" class="site-main bg-background-secondary min-h-screen">
    <div class="container mx-auto px-4 py-10 md:py-12">

        <?php get_template_part('template-parts/blog/loop'); ?>

    </div>
</main>

<?php get_footer();
