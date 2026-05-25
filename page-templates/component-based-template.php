<?php
/**
 * Template Name: Component Based Template
 * Template Post Type: page
 *
 * Empty canvas template — content composed entirely from ACF Page Components.
 * Reuses the homepage's <main> wrapper styling. No legacy fallback.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

get_header();
?>

<main id="main" class="nera-component-page" role="main">

  <?php if (function_exists('nera_render_page_components') && nera_render_page_components()): ?>
    <?php // Composed via ACF Page Components. ?>
  <?php else: ?>
    <?php if (current_user_can('edit_pages')): ?>
      <div class="max-w-3xl mx-auto px-6 py-24 text-center">
        <h1 class="text-3xl font-heading font-bold text-text-primary mb-4">
          <?php the_title(); ?>
        </h1>
        <p class="text-text-secondary">
          <?php _e('No components added yet. Edit this page and add components from the \"Page Components\" metabox.', 'nera-competitions'); ?>
        </p>
      </div>
    <?php endif; ?>
  <?php endif; ?>

</main>

<?php get_footer(); ?>
