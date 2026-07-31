<?php
/**
 * Admin: Competition catalog order — Order column, Quick Edit, notice.
 * (Products-list drag-and-drop is disabled; order is set via Quick Edit.)
 *
 * Catalog Featured ordering for WP_Query (frontend + CMS product list).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Ensure products expose Menu Order in the editor / Quick Edit.
 */
add_action(
  'init',
  static function () {
    add_post_type_support('product', 'page-attributes');
  },
  20
);

/**
 * Inject WooCommerce Featured into ORDER BY.
 *
 * nera_catalog_featured = 'first'     → Featured → (existing orderby)
 * nera_catalog_featured = 'tiebreak'  → (primary) → Featured → menu_order → date
 *
 * @param array     $clauses Query clauses.
 * @param \WP_Query $query   Query.
 * @return array
 */
add_filter(
  'posts_clauses',
  static function (array $clauses, $query): array {
    if (!$query instanceof WP_Query) {
      return $clauses;
    }

    $mode = $query->get('nera_catalog_featured');
    if (!$mode) {
      // Back-compat with earlier flag.
      $mode = $query->get('nera_catalog_featured_first') ? 'first' : '';
    }
    if ($mode !== 'first' && $mode !== 'tiebreak') {
      return $clauses;
    }
    if (!function_exists('wc_get_product_visibility_term_ids')) {
      return $clauses;
    }

    $terms = wc_get_product_visibility_term_ids();
    $tt_id = isset($terms['featured']) ? (int) $terms['featured'] : 0;
    if ($tt_id < 1) {
      return $clauses;
    }

    global $wpdb;
    $alias = 'nera_feat';
    if (false === strpos((string) $clauses['join'], $alias)) {
      $clauses['join'] .= $wpdb->prepare(
        " LEFT JOIN {$wpdb->term_relationships} AS {$alias} ON ({$wpdb->posts}.ID = {$alias}.object_id AND {$alias}.term_taxonomy_id = %d) ",
        $tt_id
      );
    }

    $feat_order = "({$alias}.object_id IS NOT NULL) DESC";
    $catalog    = "{$feat_order}, {$wpdb->posts}.menu_order ASC, {$wpdb->posts}.post_date DESC";

    if ($mode === 'first') {
      if (!empty($clauses['orderby'])) {
        // Prefer explicit catalog stack when defaulting to Featured-first.
        $clauses['orderby'] = $catalog;
      } else {
        $clauses['orderby'] = $catalog;
      }

      return $clauses;
    }

    // Tie-break: keep the first ORDER BY expression as primary.
    $orderby = trim((string) $clauses['orderby']);
    if ($orderby === '') {
      $clauses['orderby'] = $catalog;

      return $clauses;
    }

    $parts   = preg_split('/\s*,\s*/', $orderby, 2);
    $primary = $parts[0];
    $clauses['orderby'] = $primary . ', ' . $catalog;

    return $clauses;
  },
  20,
  2
);

/**
 * Whether the Products list URL is our Catalog-default signature (Order ASC).
 * Still queries Featured → menu_order → date; header shows Order ASC (+ Date DESC painted).
 *
 * @return bool
 */
function nera_admin_product_list_is_catalog_default_url(): bool
{
  if (!isset($_GET['post_type']) || sanitize_key(wp_unslash($_GET['post_type'])) !== 'product') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    return false;
  }
  if (!isset($_GET['orderby'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    return false;
  }
  $orderby = strtolower(str_replace('+', ' ', (string) wp_unslash($_GET['orderby']))); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
  $orderby = preg_replace('/\s+/', ' ', trim($orderby));
  if ($orderby !== 'menu_order' && $orderby !== 'menu_order title') {
    return false;
  }
  $order = isset($_GET['order']) ? strtolower((string) wp_unslash($_GET['order'])) : 'asc'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

  return ($order === '' || $order === 'asc');
}

/**
 * Redirect bare Products list to orderby=menu_order&order=asc so Order ASC is the active header.
 */
add_action(
  'load-edit.php',
  static function (): void {
    if (!isset($_GET['post_type']) || sanitize_key(wp_unslash($_GET['post_type'])) !== 'product') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
      return;
    }
    if (isset($_GET['orderby']) && (string) wp_unslash($_GET['orderby']) !== '') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
      return;
    }
    if (!empty($_GET['s']) || !empty($_REQUEST['action']) || !empty($_REQUEST['action2'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
      // Keep search/bulk flows; pre_get_posts still applies Catalog default without redirect.
      return;
    }

    $args = [
      'post_type' => 'product',
      'orderby'   => 'menu_order',
      'order'     => 'asc',
    ];
    foreach (['post_status', 'product_cat', 'product_type', 'stock_status', 'paged'] as $key) {
      if (!empty($_GET[$key])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $args[$key] = sanitize_text_field(wp_unslash($_GET[$key])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
      }
    }

    wp_safe_redirect(add_query_arg($args, admin_url('edit.php')));
    exit;
  }
);

/**
 * Products list: Catalog default (incl. Order ASC URL) vs chosen column + tie-breakers.
 *
 * @param \WP_Query $query Query.
 */
add_action(
  'pre_get_posts',
  static function ($query): void {
    if (!is_admin() || !$query instanceof WP_Query || !$query->is_main_query()) {
      return;
    }

    $post_type = $query->get('post_type');
    $is_product = ($post_type === 'product')
      || (is_array($post_type) && in_array('product', $post_type, true))
      || (isset($_GET['post_type']) && sanitize_key(wp_unslash($_GET['post_type'])) === 'product'); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    if (!$is_product) {
      return;
    }

    $no_orderby = !isset($_GET['orderby']) || (string) wp_unslash($_GET['orderby']) === ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    // Catalog default: bare list or our Order ASC URL signature.
    if ($no_orderby || nera_admin_product_list_is_catalog_default_url()) {
      $query->set('nera_catalog_featured', 'first');
      $query->set(
        'orderby',
        [
          'menu_order' => 'ASC',
          'date'       => 'DESC',
        ]
      );
      $query->set('order', 'ASC');

      return;
    }

    // Admin chose another column / Order DESC → primary stays, Catalog layers tie-break.
    $query->set('nera_catalog_featured', 'tiebreak');
  },
  20
);

/**
 * Tip on Products list — Featured star + Quick Edit → Order.
 */
add_action(
  'admin_notices',
  static function () {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'edit-product') {
      return;
    }

    echo '<div class="notice notice-info"><p>';
    echo esc_html__(
      'Catalog order (Shop, homepage, and this list by default): Featured star first, then Order (Quick Edit → Order), then publish date. Choosing a column sort uses that column first, then the same Catalog tie-breakers.',
      'nera-competitions'
    );
    echo '</p></div>';
  }
);

/**
 * Show Order column on the product list.
 *
 * @param array<string, string> $columns
 * @return array<string, string>
 */
add_filter(
  'manage_edit-product_columns',
  static function (array $columns): array {
    if (!isset($columns['menu_order'])) {
      $columns['menu_order'] = __('Order', 'nera-competitions');
    }

    return $columns;
  },
  20
);

/**
 * @param string $column
 * @param int    $post_id
 */
add_action(
  'manage_product_posts_custom_column',
  static function (string $column, int $post_id): void {
    if ($column !== 'menu_order') {
      return;
    }
    echo (int) get_post_field('menu_order', $post_id);
  },
  10,
  2
);

/**
 * Make Order column sortable.
 *
 * @param array<string, string> $columns
 * @return array<string, string>
 */
add_filter(
  'manage_edit-product_sortable_columns',
  static function (array $columns): array {
    $columns['menu_order'] = 'menu_order';

    return $columns;
  }
);

/**
 * Order column width + Catalog-default dual header (Order ASC + Date DESC).
 *
 * @param string $hook
 */
add_action(
  'admin_enqueue_scripts',
  static function (string $hook): void {
    if ($hook !== 'edit.php') {
      return;
    }
    if (!isset($_GET['post_type']) || sanitize_key(wp_unslash($_GET['post_type'])) !== 'product') {
      return;
    }

    // Drag-and-drop catalog sort is disabled — admins set Order via Quick Edit.

    wp_add_inline_style(
      'wp-admin',
      '.wp-list-table.posts th.column-menu_order,'
      . '.wp-list-table.posts td.column-menu_order{'
      . 'width:4.5em;min-width:4.5em;max-width:5.5em;'
      . 'text-align:center;white-space:nowrap;overflow:visible;'
      . '}'
      . '.wp-list-table.posts td.column-menu_order{'
      . 'font-variant-numeric:tabular-nums;'
      . '}'
      /* Secondary Catalog layer: Date DESC while Order ASC is the primary header. */
      . '.wp-list-table.posts.nera-catalog-default-headers th.column-date.sorted.desc .sorting-indicators .sorting-indicator.asc,'
      . '.wp-list-table.posts.nera-catalog-default-headers th.column-date .sorting-indicator.asc{opacity:.3;}'
    );

    if (!nera_admin_product_list_is_catalog_default_url()) {
      return;
    }

    wp_add_inline_script(
      'jquery',
      "jQuery(function($){"
      . "var \$table=$('.wp-list-table.posts');"
      . "if(!\$table.length)return;"
      . "\$table.addClass('nera-catalog-default-headers');"
      . "var \$date=\$table.find('th.column-date');"
      . "\$date.removeClass('sortable asc').addClass('sorted desc');"
      . "var \$order=\$table.find('th.column-menu_order');"
      . "\$order.removeClass('sortable desc').addClass('sorted asc');"
      . "});"
    );
  }
);
