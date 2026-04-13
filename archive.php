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
    <div class="container mx-auto px-4 py-10 md:py-12">

        <header class="mb-8 max-w-6xl mx-auto">
            <?php
            the_archive_title('<h1 class="font-heading text-2xl font-bold text-text-primary md:text-3xl">', '</h1>');
            the_archive_description(
              '<div class="mt-2 max-w-2xl text-sm text-text-secondary md:text-base">',
              '</div>',
            );
            ?>
        </header>

        <?php
        get_template_part('template-parts/blog/loop', null, [
          'empty_description' => __('No content matches this archive.', 'nera-competitions'),
        ]);
        ?>

    </div>
</main>

<?php get_footer();
