<?php
/**
 * Custom Menu Walker Classes for Nera Theme
 *
 * Provides TailwindCSS styling for WordPress navigation menus
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

/**
 * Desktop Header Menu Walker
 * Applies TailwindCSS classes to desktop navigation menu items
 */
class Nera_Header_Menu_Walker extends Walker_Nav_Menu
{
  /**
   * Start the element output.
   */
  public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
  {
    $classes = ['menu-item'];

    // Add active class if current
    if (
      in_array('current-menu-item', $item->classes) ||
      in_array('current_page_item', $item->classes)
    ) {
      $classes[] = 'current-menu-item';
    }

    $class_names = implode(
      ' ',
      apply_filters('nav_menu_css_class', array_filter($classes), $item, $args, $depth),
    );

    $output .= '<li class="' . esc_attr($class_names) . '">';

    // Build link attributes
    $atts = [];
    $atts['href'] = !empty($item->url) ? $item->url : '';
    $atts['title'] = !empty($item->attr_title) ? $item->attr_title : '';
    $atts['target'] = !empty($item->target) ? $item->target : '';
    $atts['rel'] = !empty($item->xfn) ? $item->xfn : '';

    // Apply TailwindCSS classes
    $link_classes = 'text-text-secondary hover:text-primary font-medium transition-colors';

    // Add active styling
    if (
      in_array('current-menu-item', $item->classes) ||
      in_array('current_page_item', $item->classes)
    ) {
      $link_classes = 'text-primary font-semibold transition-colors';
    }

    $atts['class'] = $link_classes;

    $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);

    $attributes = '';
    foreach ($atts as $attr => $value) {
      if (!empty($value)) {
        $value = 'href' === $attr ? esc_url($value) : esc_attr($value);
        $attributes .= ' ' . $attr . '="' . $value . '"';
      }
    }

    $item_output = isset($args->before) ? $args->before : '';
    $item_output .= '<a' . $attributes . '>';
    $item_output .=
      (isset($args->link_before) ? $args->link_before : '') .
      apply_filters('the_title', $item->title, $item->ID) .
      (isset($args->link_after) ? $args->link_after : '');
    $item_output .= '</a>';
    $item_output .= isset($args->after) ? $args->after : '';

    $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
  }

  /**
   * End the element output.
   */
  public function end_el(&$output, $item, $depth = 0, $args = null)
  {
    $output .= '</li>';
  }
}

/**
 * Mobile Menu Walker
 * Applies TailwindCSS classes to mobile navigation menu items
 */
class Nera_Mobile_Menu_Walker extends Walker_Nav_Menu
{
  /**
   * Start the element output.
   */
  public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
  {
    $classes = ['menu-item'];

    // Add active class if current
    if (
      in_array('current-menu-item', $item->classes) ||
      in_array('current_page_item', $item->classes)
    ) {
      $classes[] = 'current-menu-item';
    }

    $class_names = implode(
      ' ',
      apply_filters('nav_menu_css_class', array_filter($classes), $item, $args, $depth),
    );

    $output .= '<li class="' . esc_attr($class_names) . '">';

    // Build link attributes
    $atts = [];
    $atts['href'] = !empty($item->url) ? $item->url : '';
    $atts['title'] = !empty($item->attr_title) ? $item->attr_title : '';
    $atts['target'] = !empty($item->target) ? $item->target : '';
    $atts['rel'] = !empty($item->xfn) ? $item->xfn : '';

    // Apply TailwindCSS classes for mobile
    $link_classes =
      'block text-text-secondary hover:text-primary font-medium py-2 transition-colors';

    // Add active styling
    if (
      in_array('current-menu-item', $item->classes) ||
      in_array('current_page_item', $item->classes)
    ) {
      $link_classes = 'block text-primary font-semibold py-2 transition-colors';
    }

    $atts['class'] = $link_classes;

    $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);

    $attributes = '';
    foreach ($atts as $attr => $value) {
      if (!empty($value)) {
        $value = 'href' === $attr ? esc_url($value) : esc_attr($value);
        $attributes .= ' ' . $attr . '="' . $value . '"';
      }
    }

    $item_output = isset($args->before) ? $args->before : '';
    $item_output .= '<a' . $attributes . '>';
    $item_output .=
      (isset($args->link_before) ? $args->link_before : '') .
      apply_filters('the_title', $item->title, $item->ID) .
      (isset($args->link_after) ? $args->link_after : '');
    $item_output .= '</a>';
    $item_output .= isset($args->after) ? $args->after : '';

    $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
  }

  /**
   * End the element output.
   */
  public function end_el(&$output, $item, $depth = 0, $args = null)
  {
    $output .= '</li>';
  }
}
