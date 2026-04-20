<?php
/**
 * Template Name: Nera Entry List Listing
 * Template Post Type: page
 *
 * Themed archive wrapper for the Lottery entry-list page.
 * This is auto-applied via template_include when the plugin query var is present.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

get_header();
?>

<main id="main" class="nera-entry-list-listing" role="main">
  <?php
  get_template_part('template-parts/entry-list/hero-section');
  get_template_part('template-parts/entry-list/listing-grid');
  ?>
</main>

<?php get_footer();
