<?php
/**
 * Template Name: Nera Product Listing
 * Template Post Type: page
 *
 * Product listing page template with filters and AJAX functionality
 * Based on Stitch design "Competition Listings Minimalist Light"
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

get_header();
?>

<main id="main" class="nera-product-listing" role="main">

  <?php
  // 1. Hero Section - Page title and tagline
  get_template_part('template-parts/product-listing/hero-section');

  // 2. Filter Bar - Category, Price, Status, Sort dropdowns
  // get_template_part('template-parts/product-listing/filter-bar');
  get_template_part('template-parts/homepage/categories-filter');

  // 3. Product Grid - Competition cards
  // get_template_part('template-parts/homepage/categories-competitions', null, array('show_view_all' => false));

  // 4. Trust Features - 3 trust badges
  get_template_part('template-parts/product-listing/trust-features');
  ?>

</main>

<?php get_footer();
?>
