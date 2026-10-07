<?php
/**
 * Mobile column count for competition listing grids.
 *
 * One site-wide switch (Theme Settings → WooCommerce → Mobile Grid Layout)
 * controls every grid that lists competitions as CompetitionCards: All
 * Competitions, Closed Prizes, Giveaway Entry List, the homepage competitions
 * section, Related Competitions, and Cart Cross-sells. It only ever changes
 * the base (mobile) Tailwind grid-cols-* class — each grid keeps its own
 * md:/lg:/xl: classes untouched.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * The raw setting, as a boolean: true for Multiple (2 per row), false for
 * Feature (1 per row, the default). Child themes whose grids aren't built on
 * Tailwind's grid-cols-* utilities (e.g. a hand-rolled CSS grid system) read
 * this directly instead of nera_product_grid_mobile_class() below.
 *
 * @return bool
 */
function nera_product_grid_mobile_is_multiple(): bool
{
  static $is_multiple = null;
  if ($is_multiple !== null) {
    return $is_multiple;
  }

  $mode = function_exists('get_field') ? get_field('product_grid_mobile_layout', 'option') : 'feature';
  $is_multiple = $mode === 'multiple';

  return $is_multiple;
}

/**
 * Tailwind class for the mobile grid-cols-* breakpoint.
 *
 * @return string 'grid-cols-1' (Feature, default) or 'grid-cols-2' (Multiple).
 */
function nera_product_grid_mobile_class(): string
{
  return nera_product_grid_mobile_is_multiple() ? 'grid-cols-2' : 'grid-cols-1';
}
