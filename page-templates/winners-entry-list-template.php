<?php
/**
 * Template Name: Winners Entry List
 * Template Post Type: page
 *
 * Winners page with View Participants modal (entry-list REST) on each card.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

get_header();
?>

<main id="main" class="nera-winners-entry-list-page" role="main">

  <?php if (nera_render_page_components()): ?>
    <?php // page-components rendered via ACF Flexible Content ?>
  <?php else: ?>
  <?php
  get_template_part('template-parts/winners-dynamic/hero-section');
  get_template_part('template-parts/winners-dynamic/winners-grid', null, [
    'show_participants_cta'    => true,
    'include_entry_list_modal' => true,
    'stack_layout'             => true,
  ]);
  ?>
  <?php endif; ?>

</main>

<?php
get_footer();
