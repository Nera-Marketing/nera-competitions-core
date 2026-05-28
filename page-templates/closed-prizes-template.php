<?php
/**
 * Template Name: Nera Closed Prizes
 * Template Post Type: page
 *
 * Closed Prizes page — lists finished/closed competitions with results cards.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

get_header();
?>

<main id="main" class="nera-closed-prizes-page" role="main">

  <?php
  get_template_part('template-parts/closed-prizes/hero-section');
  get_template_part('template-parts/closed-prizes/prizes-grid');
  ?>

</main>

<?php get_footer();
