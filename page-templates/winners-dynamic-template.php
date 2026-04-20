<?php
/**
 * Template Name: Nera Winners (Dynamic)
 * Template Post Type: page
 *
 * Winners page — main draw + instant winners from Lottery for WooCommerce logs.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

get_header();
?>

<main id="main" class="nera-winners-dynamic-page" role="main">

  <?php
  get_template_part('template-parts/winners-dynamic/hero-section');
  get_template_part('template-parts/winners-dynamic/winners-grid');
  ?>

</main>

<?php
get_footer();
