<?php
/**
 * Default “How It Works” hero steps (icons, gradients, copy).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Ordered keys matching the default visual sequence (steps 1–4).
 *
 * @return string[]
 */
function nera_get_hiw_step_style_order()
{
  return ['select_prize', 'choose_tickets', 'answer_question', 'win_prize'];
}

/**
 * Single step preset by style key (icon SVG, Tailwind classes, default strings).
 *
 * @param string $style One of select_prize|choose_tickets|answer_question|win_prize.
 * @return array<string, string>|null
 */
function nera_get_hiw_step_preset($style)
{
  $presets = [
    'select_prize' => [
      'icon' =>
        '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>',
      'title' => __('Select a Prize', 'nera-competitions'),
      'description' => __(
        'Browse our exciting competitions and choose your favourite prize from our curated selection.',
        'nera-competitions',
      ),
      'color' => 'from-accent to-accent',
      'bg_color' => 'bg-accent/10',
    ],
    'choose_tickets' => [
      'icon' =>
        '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>',
      'title' => __('Choose Your Tickets', 'nera-competitions'),
      'description' => __(
        'Select how many entries you want and pick your lucky numbers from the available options.',
        'nera-competitions',
      ),
      'color' => 'from-primary to-info',
      'bg_color' => 'bg-primary/10',
    ],
    'answer_question' => [
      'icon' =>
        '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
      'title' => __('Answer Question', 'nera-competitions'),
      'description' => __(
        'Answer a simple skill-based question correctly to validate and confirm your entry.',
        'nera-competitions',
      ),
      'color' => 'from-success to-success',
      'bg_color' => 'bg-success/10',
    ],
    'win_prize' => [
      'icon' =>
        '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>',
      'title' => __('Win Your Prize!', 'nera-competitions'),
      'description' => __(
        'Watch the live draw and be our next lucky winner! We deliver prizes directly to you.',
        'nera-competitions',
      ),
      'color' => 'from-warning to-warning',
      'bg_color' => 'bg-warning/10',
    ],
  ];

  return isset($presets[$style]) ? $presets[$style] : null;
}

/**
 * Default ordered list of four hero steps (full structure for the template part).
 *
 * @return array<int, array<string, string>>
 */
function nera_get_hiw_default_steps()
{
  $out = [];
  foreach (nera_get_hiw_step_style_order() as $style) {
    $p = nera_get_hiw_step_preset($style);
    if ($p) {
      $out[] = $p;
    }
  }
  return $out;
}

/**
 * Icon markup for a step: uploaded image from ACF or fallback inline SVG.
 *
 * @param mixed  $acf_image         ACF image field (array, attachment ID, or empty).
 * @param string $fallback_svg_html Default inline SVG from presets.
 * @return string Safe HTML (img tag or SVG string).
 */
function nera_hiw_step_icon_html($acf_image, $fallback_svg_html)
{
  $attachment_id = 0;
  if (is_numeric($acf_image) && (int) $acf_image > 0) {
    $attachment_id = (int) $acf_image;
  } elseif (is_array($acf_image) && !empty($acf_image['ID'])) {
    $attachment_id = (int) $acf_image['ID'];
  }

  if ($attachment_id <= 0 || 'attachment' !== get_post_type($attachment_id)) {
    return $fallback_svg_html;
  }

  $img = wp_get_attachment_image(
    $attachment_id,
    'thumbnail',
    false,
    [
      'class' => 'w-8 h-8 object-contain max-w-[2rem] max-h-[2rem]',
      'loading' => 'lazy',
      'decoding' => 'async',
    ],
  );

  return $img !== '' ? $img : $fallback_svg_html;
}

/**
 * Merge ACF repeater rows with presets; empty input returns full defaults.
 *
 * Gradients follow row index (1–4). Icons use optional ACF image or default SVG for that index.
 *
 * @param mixed $acf_rows Repeater value from get_field('hiw_hero_steps').
 * @return array<int, array<string, string>>
 */
function nera_get_hiw_merged_hero_steps($acf_rows)
{
  $defaults = nera_get_hiw_default_steps();
  $order = nera_get_hiw_step_style_order();

  if (empty($acf_rows) || !is_array($acf_rows)) {
    return $defaults;
  }

  $merged = [];
  foreach ($acf_rows as $i => $row) {
    if (!is_array($row)) {
      continue;
    }
    $title = isset($row['title']) ? trim((string) $row['title']) : '';
    $description = isset($row['description']) ? trim((string) $row['description']) : '';
    if ($title === '' && $description === '') {
      continue;
    }

    $fallback_style = isset($order[$i]) ? $order[$i] : $order[0];
    $preset = nera_get_hiw_step_preset($fallback_style);
    if (!$preset) {
      continue;
    }

    $acf_icon = isset($row['step_icon']) ? $row['step_icon'] : null;
    $icon_html = nera_hiw_step_icon_html($acf_icon, $preset['icon']);

    $merged[] = [
      'icon' => $icon_html,
      'color' => $preset['color'],
      'bg_color' => $preset['bg_color'],
      'title' => $title !== '' ? $title : $preset['title'],
      'description' => $description !== '' ? $description : $preset['description'],
    ];
  }

  if (empty($merged)) {
    return $defaults;
  }

  return $merged;
}
