<?php
/**
 * Product Listing Hero Section Template Part
 *
 * Displays the page title and tagline/excerpt
 * Based on Stitch design "Competition Listings Minimalist Light"
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

// Get page title and excerpt context
$post_id = isset($args['post_id']) && $args['post_id'] ? $args['post_id'] : get_the_ID();

// Get page title and excerpt
$page_title = get_the_title($post_id);
$page_tagline = get_the_excerpt($post_id);

// Fallback tagline if no excerpt is set
if (empty($page_tagline)) {
  $page_tagline = __(
    'Enter to win amazing prizes with our exclusive competitions.',
    'nera-competitions',
  );
}
?>

<section class="py-16 md:py-20 bg-background-light">
  <div class="max-w-[1200px] mx-auto px-4 lg:px-8">
    <div class="text-center max-w-2xl mx-auto">
      <!-- Page Title -->
      <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-text-primary tracking-tight mb-4"
        data-aos="fade-up">
        <?php echo esc_html($page_title); ?>
      </h1>

      <!-- Tagline -->
      <p class="text-lg md:text-xl text-text-secondary font-medium" data-aos="fade-up" data-aos-delay="100">
        <?php echo esc_html($page_tagline); ?>
      </p>
    </div>
  </div>
</section>