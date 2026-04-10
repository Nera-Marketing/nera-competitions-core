<?php
/**
 * Winners dataset helpers for server-rendered winners page.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Build normalized winners dataset for template rendering.
 *
 * @param int|null $page_id Winners page ID.
 * @return array{
 *   winners: array<int, array<string, string>>,
 *   filter_items: array<int, array{value:string,label:string,count:int}>
 * }
 */
function nera_winners_get_page_dataset($page_id = null)
{
  $page_id = $page_id ? (int) $page_id : get_the_ID();
  $categories = nera_get_win_categories($page_id);
  $counts = array_fill_keys(array_keys($categories), 0);
  $rows = $page_id ? get_field('winners_list', $page_id) : [];
  $winners = [];

  if (is_array($rows)) {
    foreach ($rows as $index => $row) {
      $name = sanitize_text_field($row['name'] ?? '');
      $prize = sanitize_text_field($row['prize'] ?? '');
      if ($name === '' || $prize === '') {
        continue;
      }

      $category = sanitize_key($row['category'] ?? 'live-draw');
      if (!isset($categories[$category])) {
        $category = 'live-draw';
      }
      $counts[$category] = (int) ($counts[$category] ?? 0) + 1;

      $image = $row['image'] ?? null;
      $image_url = '';
      if (is_array($image)) {
        $image_url = $image['sizes']['large'] ?? ($image['url'] ?? '');
      } elseif (is_string($image)) {
        $image_url = $image;
      }

      $date = sanitize_text_field($row['date'] ?? '');
      $winners[] = [
        'id' => md5($name . '|' . $prize . '|' . $date . '|' . (string) $index),
        'name' => $name,
        'prize' => $prize,
        'date' => $date,
        'quote' => sanitize_text_field($row['quote'] ?? ''),
        'category' => $category,
        'category_label' => sanitize_text_field($categories[$category]),
        'image' => esc_url($image_url),
      ];
    }
  }

  $filter_items = [
    [
      'value' => 'all',
      'label' => __('All Winners', 'nera-competitions'),
      'count' => count($winners),
    ],
  ];

  foreach ($categories as $value => $label) {
    $filter_items[] = [
      'value' => sanitize_key($value),
      'label' => sanitize_text_field($label),
      'count' => (int) ($counts[$value] ?? 0),
    ];
  }

  return [
    'winners' => $winners,
    'filter_items' => $filter_items,
  ];
}
