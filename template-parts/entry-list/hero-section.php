<?php
/**
 * Entry List - Hero Section
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$entry_list_page_id = function_exists('wc_get_page_id') ? (int) wc_get_page_id('lty_lottery_entry_list') : 0;
$page_title = $entry_list_page_id > 0 ? get_the_title($entry_list_page_id) : __('Entry List', 'nera-competitions');
$page_excerpt = $entry_list_page_id > 0 ? get_post_field('post_excerpt', $entry_list_page_id) : '';

if (empty($page_excerpt)) {
  $page_excerpt = __('Browse every competition and view its participant list in one place.', 'nera-competitions');
}

get_template_part('template-parts/components/shared/page-hero', null, [
  'title' => $page_title,
  'description' => $page_excerpt,
  'variant' => 'compact',
  'eyebrow_label' => __('Competition Participants', 'nera-competitions'),
  'eyebrow_icon' => 'groups',
]);
