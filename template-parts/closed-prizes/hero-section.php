<?php
/**
 * Closed Prizes - Hero Section
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$page_title = get_the_title();
$page_excerpt = get_the_excerpt();

if (empty($page_excerpt)) {
  $page_excerpt = __('Browse our past competitions and see the lucky winners.', 'nera-competitions');
}

get_template_part('template-parts/components/shared/page-hero', null, [
  'title' => $page_title,
  'description' => $page_excerpt,
  'variant' => 'compact',
  'eyebrow_label' => __('Past Competitions', 'nera-competitions'),
  'eyebrow_icon' => 'trophy',
]);
