<?php
/**
 * Catalog order helpers for active Competition grids.
 *
 * Default: WooCommerce Featured first, then menu_order ASC, then post_date DESC.
 * Chosen sorts: primary key first, then Featured → menu_order → date as tie-breakers.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Default WP_Query orderby fragment for catalog grids (Featured first).
 *
 * @return array{nera_catalog_featured: string, orderby: array<string, string>}
 */
function nera_catalog_orderby_args(): array
{
  return [
    'nera_catalog_featured' => 'first',
    'orderby'               => [
      'menu_order' => 'ASC',
      'date'       => 'DESC',
    ],
  ];
}

/**
 * Apply default catalog order to WP_Query args (clears sort-only meta_key / order).
 *
 * @param array<string, mixed> $args
 * @return array<string, mixed>
 */
function nera_wp_query_args_with_catalog_order(array $args): array
{
  unset($args['meta_key'], $args['order']);

  return array_merge($args, nera_catalog_orderby_args());
}

/**
 * Apply a visitor sort key with catalog-order tie-breakers
 * (Featured → menu_order → date after the primary key).
 *
 * Supported $sort: default|recommended|ending-soon|newest|price-low|price-high|popularity
 *
 * @param array<string, mixed> $args
 * @param string               $sort
 * @return array<string, mixed>
 */
function nera_wp_query_args_with_layered_sort(array $args, string $sort): array
{
  unset($args['meta_key'], $args['order']);

  switch ($sort) {
    case 'ending-soon':
      $args['meta_key']               = '_lty_end_date_gmt';
      $args['orderby']                = 'meta_value';
      $args['order']                  = 'ASC';
      $args['nera_catalog_featured']  = 'tiebreak';
      break;

    case 'newest':
      $args['orderby']               = 'date';
      $args['order']                 = 'DESC';
      $args['nera_catalog_featured'] = 'tiebreak';
      break;

    case 'price-low':
      $args['meta_key']              = '_price';
      $args['orderby']               = 'meta_value_num';
      $args['order']                 = 'ASC';
      $args['nera_catalog_featured'] = 'tiebreak';
      break;

    case 'price-high':
      $args['meta_key']              = '_price';
      $args['orderby']               = 'meta_value_num';
      $args['order']                 = 'DESC';
      $args['nera_catalog_featured'] = 'tiebreak';
      break;

    case 'popularity':
      $args['meta_key']              = 'total_sales';
      $args['orderby']               = 'meta_value_num';
      $args['order']                 = 'DESC';
      $args['nera_catalog_featured'] = 'tiebreak';
      break;

    case 'default':
    case 'recommended':
    default:
      $args = array_merge($args, nera_catalog_orderby_args());
      break;
  }

  return $args;
}

/**
 * Re-order product IDs by catalog order (Featured → menu_order ASC → date DESC).
 *
 * @param int[] $ids
 * @return int[]
 */
function nera_sort_competition_ids_by_catalog_order(array $ids): array
{
  $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
  if (empty($ids)) {
    return [];
  }

  $query = new WP_Query(
    array_merge(
      [
        'post_type'              => 'product',
        'post_status'            => 'any',
        'post__in'               => $ids,
        'posts_per_page'         => count($ids),
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
      ],
      nera_catalog_orderby_args()
    )
  );

  return array_map('intval', $query->posts);
}

/**
 * Whether a product is WooCommerce Featured (Products list star).
 *
 * @param int|\WC_Product $product Product ID or object.
 * @return bool
 */
function nera_product_is_wc_featured($product): bool
{
  if (class_exists('WC_Product') && $product instanceof WC_Product) {
    return (bool) $product->get_featured();
  }
  $id = (int) $product;
  if ($id < 1 || !function_exists('wc_get_product')) {
    return false;
  }
  $p = wc_get_product($id);

  return $p ? (bool) $p->get_featured() : false;
}
