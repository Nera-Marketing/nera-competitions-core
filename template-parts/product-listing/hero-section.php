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

get_template_part('template-parts/components/shared/page-hero', null, [
  'title' => $page_title,
  'description' => $page_tagline,
  'variant' => 'default',
]);
