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
 * Tailwind class for the mobile grid-cols-* breakpoint.
 *
 * @return string 'grid-cols-1' (Feature, default) or 'grid-cols-2' (Multiple).
 */
function nera_product_grid_mobile_class(): string
{
  static $class = null;
  if ($class !== null) {
    return $class;
  }

  $mode = function_exists('get_field') ? get_field('product_grid_mobile_layout', 'option') : 'feature';
  $class = $mode === 'multiple' ? 'grid-cols-2' : 'grid-cols-1';

  return $class;
}
