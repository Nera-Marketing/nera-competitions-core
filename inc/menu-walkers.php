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
 * Top-level + one submenu level; hover/focus dropdown on desktop.
 */
class Nera_Header_Menu_Walker extends Walker_Nav_Menu
{
  /**
   * @param string $output
   * @param int    $depth
   * @param object|null $args
   */
  public function start_lvl(&$output, $depth = 0, $args = null)
  {
    if ($depth !== 0) {
      return;
    }

    $indent = str_repeat("\t", $depth + 1);
    $sub_classes =
      'sub-menu absolute left-0 top-full z-[60] mt-1 min-w-[12.5rem] border border-gray-100 bg-surface py-2 shadow-lg ' .
      'opacity-0 invisible translate-y-0.5 transition-all duration-200 ease-out ' .
      'pointer-events-none group-hover:pointer-events-auto group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 ' .
      'group-focus-within:pointer-events-auto group-focus-within:opacity-100 group-focus-within:visible group-focus-within:translate-y-0';

    $output .= "\n{$indent}<ul class=\"" . esc_attr($sub_classes) . "\">\n";
  }

  /**
   * @param string $output
   * @param int    $depth
   * @param object|null $args
   */
  public function end_lvl(&$output, $depth = 0, $args = null)
  {
    if ($depth !== 0) {
      return;
    }

    $indent = str_repeat("\t", $depth + 1);
    $output .= "{$indent}</ul>\n";
  }

  /**
   * Start the element output.
   *
   * @param string $output
   * @param WP_Post $item
   * @param int $depth
   * @param object|null $args
   * @param int $id
   */
  public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
  {
    $classes = empty($item->classes) ? [] : (array) $item->classes;
    $has_children = in_array('menu-item-has-children', $classes, true);

    if (0 === $depth && $has_children) {
      $classes[] = 'relative';
      $classes[] = 'group';
    }

    $class_names = implode(
      ' ',
      apply_filters('nav_menu_css_class', array_filter(array_unique($classes)), $item, $args, $depth),
    );

    $output .= '<li class="' . esc_attr($class_names) . '">';

    $atts = [];
    $atts['href'] = !empty($item->url) ? $item->url : '';
    $atts['title'] = !empty($item->attr_title) ? $item->attr_title : '';
    $atts['target'] = !empty($item->target) ? $item->target : '';
    $atts['rel'] = !empty($item->xfn) ? $item->xfn : '';

    $is_current =
      in_array('current-menu-item', $item->classes, true) ||
      in_array('current_page_item', $item->classes, true);
    $is_ancestor =
      in_array('current-menu-ancestor', $item->classes, true) ||
      in_array('current-menu-parent', $item->classes, true);

    if (0 === $depth) {
      $link_classes =
        'text-text-secondary hover:text-primary font-medium transition-colors inline-flex items-center gap-1';
      if ($has_children) {
        $link_classes .= ' py-1';
      }
      if ($is_current || $is_ancestor) {
        $link_classes = 'text-primary font-semibold transition-colors inline-flex items-center gap-1 py-1';
      }
    } else {
      $link_classes =
        'block px-4 py-2.5 text-sm !text-text-secondary transition-colors hover:bg-gray-50 hover:!text-primary';
      if ($is_current) {
        $link_classes =
          'block px-4 py-2.5 text-sm font-semibold !text-primary transition-colors bg-primary/5 hover:!text-primary';
      }
    }

    $atts['class'] = $link_classes;

    if ($has_children && 0 === $depth) {
      $atts['aria-haspopup'] = 'true';
    }

    $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);

    $attributes = '';
    foreach ($atts as $attr => $value) {
      if ('' === $value || false === $value || null === $value) {
        continue;
      }
      $value = 'href' === $attr ? esc_url($value) : esc_attr($value);
      $attributes .= ' ' . $attr . '="' . $value . '"';
    }

    $item_output = isset($args->before) ? $args->before : '';
    $item_output .= '<a' . $attributes . '>';
    $item_output .=
      (isset($args->link_before) ? $args->link_before : '') .
      apply_filters('the_title', $item->title, $item->ID) .
      (isset($args->link_after) ? $args->link_after : '');

    if ($has_children && 0 === $depth) {
      $item_output .=
        '<span class="inline-flex shrink-0" aria-hidden="true">' .
        '<svg class="h-4 w-4 opacity-70" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>' .
        '</span>';
    }

    $item_output .= '</a>';
    $item_output .= isset($args->after) ? $args->after : '';

    $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
  }

  /**
   * End the element output.
   *
   * @param string $output
   * @param WP_Post $item
   * @param int $depth
   * @param object|null $args
   */
  public function end_el(&$output, $item, $depth = 0, $args = null)
  {
    $output .= '</li>';
  }
}

/**
 * Mobile Menu Walker
 * Top-level + expandable second level via .nera-mobile-submenu-toggle.
 */
class Nera_Mobile_Menu_Walker extends Walker_Nav_Menu
{
  /**
   * @var string
   */
  private $mobile_submenu_id = '';

  /**
   * Whether the next mobile submenu <ul> should start expanded (current ancestor/page).
   *
   * @var bool
   */
  private $mobile_submenu_start_open = false;

  /**
   * @param string $output
   * @param int    $depth
   * @param object|null $args
   */
  public function start_lvl(&$output, $depth = 0, $args = null)
  {
    if (0 !== $depth) {
      return;
    }

    $id = $this->mobile_submenu_id;
    $open = $this->mobile_submenu_start_open;
    $this->mobile_submenu_start_open = false;

    $visually_hidden = $open ? '' : ' hidden';
    $panel_classes =
      'sub-menu nera-mobile-sub-menu mt-1 ml-2 space-y-0 border-l-2 border-gray-100 pl-2 overflow-hidden transition-[max-height] duration-300 ease-out' .
      $visually_hidden;

    $output .= '<ul id="' . esc_attr($id) . '" class="' . esc_attr($panel_classes) . '"' . ($open ? '' : ' hidden') . '>';

    $this->mobile_submenu_id = '';
  }

  /**
   * @param string $output
   * @param int    $depth
   * @param object|null $args
   */
  public function end_lvl(&$output, $depth = 0, $args = null)
  {
    if (0 !== $depth) {
      return;
    }

    $output .= '</ul>';
  }

  /**
   * Start the element output.
   *
   * @param string $output
   * @param WP_Post $item
   * @param int $depth
   * @param object|null $args
   * @param int $id
   */
  public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
  {
    $classes = empty($item->classes) ? [] : (array) $item->classes;
    $has_children = in_array('menu-item-has-children', $classes, true);

    $class_names = implode(
      ' ',
      apply_filters('nav_menu_css_class', array_filter(array_unique($classes)), $item, $args, $depth),
    );

    $output .= '<li class="' . esc_attr($class_names) . '">';

    $is_current =
      in_array('current-menu-item', $item->classes, true) ||
      in_array('current_page_item', $item->classes, true);
    $is_ancestor =
      in_array('current-menu-ancestor', $item->classes, true) ||
      in_array('current-menu-parent', $item->classes, true);

    if (0 === $depth && $has_children) {
      $this->mobile_submenu_id = function_exists('wp_unique_id')
        ? wp_unique_id('nera-mobile-sub-')
        : 'nera-mobile-sub-' . str_replace('.', '-', uniqid('', true));
      $sub_id = $this->mobile_submenu_id;
      $start_open = $is_current || $is_ancestor;
      $this->mobile_submenu_start_open = $start_open;

      $link_classes =
        'min-w-0 flex-1 text-text-secondary hover:text-primary font-medium py-2 pr-2 transition-colors no-underline';
      if ($is_current || $is_ancestor) {
        $link_classes =
          'min-w-0 flex-1 text-primary font-semibold py-2 pr-2 transition-colors no-underline';
      }

      $atts = [
        'href' => !empty($item->url) ? $item->url : '',
        'title' => !empty($item->attr_title) ? $item->attr_title : '',
        'target' => !empty($item->target) ? $item->target : '',
        'rel' => !empty($item->xfn) ? $item->xfn : '',
        'class' => $link_classes,
      ];

      $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);

      $attributes = '';
      foreach ($atts as $attr => $value) {
        if ('' === $value || false === $value || null === $value) {
          continue;
        }
        $value = 'href' === $attr ? esc_url($value) : esc_attr($value);
        $attributes .= ' ' . $attr . '="' . $value . '"';
      }

      $title = apply_filters('the_title', $item->title, $item->ID);
      $label_open = sprintf(
        /* translators: %s: parent menu item title */
        __('Toggle submenu for %s', 'nera-competitions'),
        wp_strip_all_tags($title),
      );

      $item_output = isset($args->before) ? $args->before : '';
      $item_output .= '<div class="flex items-stretch gap-0">';
      $item_output .= '<a' . $attributes . '>';
      $item_output .= (isset($args->link_before) ? $args->link_before : '') . $title . (isset($args->link_after) ? $args->link_after : '');
      $item_output .= '</a>';
      $item_output .=
        '<button type="button" class="nera-mobile-submenu-toggle flex shrink-0 items-center justify-center px-3 py-2 text-text-secondary transition-colors hover:text-primary" ' .
        'aria-expanded="' .
        ($start_open ? 'true' : 'false') .
        '" aria-controls="' .
        esc_attr($sub_id) .
        '" aria-label="' .
        esc_attr($label_open) .
        '">';
      $item_output .=
        '<svg class="nera-mobile-submenu-chevron h-5 w-5 transition-transform duration-200" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>';
      $item_output .= '</button>';
      $item_output .= '</div>';

      $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);

      return;
    }

    // Depth 0 leaf, or depth 1 child.
    $atts = [];
    $atts['href'] = !empty($item->url) ? $item->url : '';
    $atts['title'] = !empty($item->attr_title) ? $item->attr_title : '';
    $atts['target'] = !empty($item->target) ? $item->target : '';
    $atts['rel'] = !empty($item->xfn) ? $item->xfn : '';

    if (0 === $depth) {
      $link_classes =
        'block text-text-secondary hover:text-primary font-medium py-2 transition-colors';
      if ($is_current) {
        $link_classes = 'block text-primary font-semibold py-2 transition-colors';
      }
    } else {
      $link_classes =
        'block rounded-lg py-2 pl-1 text-sm text-text-secondary transition-colors hover:text-primary';
      if ($is_current) {
        $link_classes =
          'block rounded-lg py-2 pl-1 text-sm font-semibold text-primary transition-colors';
      }
    }

    $atts['class'] = $link_classes;

    $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);

    $attributes = '';
    foreach ($atts as $attr => $value) {
      if ('' === $value || false === $value || null === $value) {
        continue;
      }
      $value = 'href' === $attr ? esc_url($value) : esc_attr($value);
      $attributes .= ' ' . $attr . '="' . $value . '"';
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
   *
   * @param string $output
   * @param WP_Post $item
   * @param int $depth
   * @param object|null $args
   */
  public function end_el(&$output, $item, $depth = 0, $args = null)
  {
    $output .= '</li>';
  }
}
