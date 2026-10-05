<?php
/**
 * ACF: "Add Component" area on the Product edit screen.
 *
 * Same flexible-content pattern as Page's `page_components` (see
 * lib/components.php), but on its own field (`product_components`) so it can be
 * toggled independently and never collides with Page data. Scoped to lottery
 * products only — the Related Competitions layout this renders above
 * (woocommerce/single-product.php) is built for the competition detail page, not
 * WooCommerce's default simple-product template.
 *
 * Registration itself is gated on the "Enable Product Page Components" switch
 * (Theme Settings → WooCommerce): off means the metabox does not exist for this
 * request at all, not just hidden by CSS — turning it back on does not lose any
 * previously saved component data, since that lives in postmeta independently of
 * whether the field group is currently registered.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

add_action('acf/init', function () {
  if (!function_exists('acf_add_local_field_group')) return;

  $enabled = function_exists('get_field') ? (bool) get_field('enable_product_components', 'option') : false;
  if (!$enabled) return;

  $layouts = function_exists('nera_get_component_layouts') ? nera_get_component_layouts() : [];
  if (empty($layouts)) return;

  acf_add_local_field_group([
    'key'      => 'group_product_components',
    'title'    => __('Product Components', 'nera-competitions-standard'),
    'fields'   => [
      [
        'key'          => 'field_product_components',
        'label'        => __('Components', 'nera-competitions-standard'),
        'name'         => 'product_components',
        'type'         => 'flexible_content',
        'instructions' => __('Add components to show on this product page, just above Related Competitions. Leave empty to show nothing.', 'nera-competitions-standard'),
        'button_label' => __('Add Component', 'nera-competitions-standard'),
        'layouts'      => $layouts,
      ],
    ],
    'location' => [
      [
        ['param' => 'post_type', 'operator' => '==', 'value' => 'product'],
        ['param' => 'post_taxonomy', 'operator' => '==', 'value' => 'product_type:lottery'],
      ],
    ],
    // Below 0 so this sits directly under "Product short description"
    // (WooCommerce's postexcerpt metabox) and above "Competition Settings"
    // (group_single_product_competition, menu_order 0) — both share this
    // screen's 'normal' context, where ties are broken by menu_order.
    'menu_order'            => -1,
    'position'              => 'normal',
    'style'                 => 'default',
    'label_placement'       => 'top',
    'instruction_placement' => 'label',
    'active'                => true,
    'description'           => 'Only shown when "Enable Product Page Components" is on (Theme Settings → WooCommerce) and this product is a lottery product.',
  ]);
});
