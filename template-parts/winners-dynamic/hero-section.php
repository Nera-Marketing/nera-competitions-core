<?php
/**
 * Dynamic Winners - Hero Section
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$page_title = get_the_title();
$page_excerpt = get_the_excerpt();

if (empty($page_excerpt)) {
  $page_excerpt = __('Celebrate our latest winners from finished competitions.', 'nera-competitions');
}

get_template_part('template-parts/components/shared/page-hero', null, [
  'title' => $page_title,
  'description' => $page_excerpt,
  'variant' => 'default',
  'eyebrow_label' => __('Winners', 'nera-competitions'),
  'eyebrow_icon' => 'emoji_events',
]);
