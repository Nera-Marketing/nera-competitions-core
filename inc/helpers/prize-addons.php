<?php
/**
 * Prize Safety & Add-ons — data helpers.
 *
 * Pure functions only (no hooks). The hooks that use them live in
 * inc/prize-addons.php. See docs/adr/0012-prize-add-on-lines.md.
 *
 * Safety is display only. Add-ons are paid extras chosen on the prize page:
 * a fixed price per option, charged once per draw, with an optional Full
 * bundle price that applies only when every option is bought in one purchase.
 * Options a customer already paid for (option A) are locked and never
 * count towards the bundle.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/** Option holding the ID of the hidden "Add-on bundle" product. */
const NERA_PRIZE_ADDON_PRODUCT_OPTION = 'nera_prize_addon_product_id';

/** Cart item data key that marks an add-on line and links it to its draw. */
const NERA_PRIZE_ADDON_CART_KEY = 'nera_prize_addon';

/** ACF field keys the save/validate hooks need to reach into $_POST['acf']. */
const NERA_PRIZE_ADDON_ACF_ENABLED = 'field_nera_psa_addons_enabled';
const NERA_PRIZE_ADDON_ACF_BUNDLE = 'field_nera_psa_addons_bundle_price';

/**
 * Customer-facing label, filterable so child themes can reword it.
 *
 * @param string $key Label key.
 * @return string
 */
function nera_prize_addons_label(string $key): string
{
  $labels = [
    'safety_title' => __('Included free with this prize', 'nera-competitions'),
    'safety_free' => __('Free', 'nera-competitions'),
    'eyebrow' => __('Optional add-ons', 'nera-competitions'),
    'total' => __('Add-ons total', 'nera-competitions'),
    'non_refundable' => __(
      "Add-ons are charged once per draw and are non-refundable if you don't win.",
      'nera-competitions'
    ),
    'purchased' => __('Purchased', 'nera-competitions'),
    'select_all' => __('Select all', 'nera-competitions'),
    'clear' => __('Clear', 'nera-competitions'),
    'hide' => __('Hide add-ons', 'nera-competitions'),
    'show' => __('Show add-ons', 'nera-competitions'),
    /* translators: 1: number of options, 2: bundle price, 3: amount saved */
    'bundle_offer' => __('Select all %1$d for %2$s (save %3$s)', 'nera-competitions'),
    /* translators: 1: options already bought, 2: options in total */
    'partial' => __(
      'You already have %1$d of %2$d extras for this draw. The bundle price applies only when every extra is bought together, so the rest are charged at their own price.',
      'nera-competitions'
    ),
    'all_purchased' => __('You already have every extra for this draw.', 'nera-competitions'),
    /* translators: 1: options selected, 2: options available */
    'summary_selected' => __('%1$d of %2$d selected', 'nera-competitions'),
    /* translators: %d: options available */
    'summary_none' => __('None selected · %d extras available', 'nera-competitions'),
    /* translators: %s: prize name */
    'line_name' => __('Add-ons for: %s', 'nera-competitions'),
    'meta_for' => __('For', 'nera-competitions'),
    'meta_options' => __('Options', 'nera-competitions'),
    'meta_full_bundle' => __('Full bundle', 'nera-competitions'),
    'yes' => __('Yes', 'nera-competitions'),
    'no' => __('No', 'nera-competitions'),
  ];

  $text = $labels[$key] ?? '';

  /**
   * Filter a Prize Safety & Add-ons label.
   *
   * @param string $text Label text.
   * @param string $key  Label key.
   */
  return (string) apply_filters('nera_prize_addons_label', $text, $key);
}

/**
 * Whether a product is a Lottery for WooCommerce product.
 *
 * @param mixed $product Product object.
 * @return bool
 */
function nera_prize_addons_is_lottery($product): bool
{
  return is_object($product) && function_exists('lty_is_lottery_product') && lty_is_lottery_product($product);
}

/**
 * Money as plain text ("£30.00"), for places that must not carry HTML.
 *
 * @param float $amount Amount.
 * @return string
 */
function nera_prize_addons_money_text(float $amount): string
{
  return html_entity_decode(wp_strip_all_tags(wc_price($amount)), ENT_QUOTES, 'UTF-8');
}

/**
 * The Catalog entries a prize has picked, in the order the admin picked them.
 *
 * Picks that no longer resolve (an entry that was deleted, or a stale ID) are skipped
 * rather than shown broken. The Catalog itself refuses to delete an entry a prize uses.
 *
 * @param int    $product_id Lottery product ID.
 * @param string $kind       'safety' or 'addons'.
 * @return array<string,array<string,mixed>> Catalog entries keyed by ID.
 */
function nera_prize_addons_picked_entries(int $product_id, string $kind): array
{
  $catalog = nera_prize_addons_catalog($kind);
  $picked = (array) get_field('safety' === $kind ? 'safety_item_ids' : 'addon_option_ids', $product_id);

  $entries = [];
  foreach ($picked as $id) {
    $id = sanitize_key((string) $id);
    if (isset($catalog[$id])) {
      $entries[$id] = $catalog[$id];
    }
  }

  return $entries;
}

/**
 * A prize's own title or description, or the settings default when it has none.
 *
 * Read when it is shown, not copied: changing the default reaches every prize that has
 * no text of its own.
 *
 * @param int    $product_id Lottery product ID.
 * @param string $field      Prize field name (e.g. 'safety_title').
 * @return string
 */
function nera_prize_addons_text(int $product_id, string $field): string
{
  $own = trim((string) get_field($field, $product_id));
  if ('' !== $own) {
    return $own;
  }

  return trim((string) get_field(NERA_PRIZE_ADDON_GLOBAL_PREFIX . $field, 'option'));
}

/**
 * Safety block settings for one prize.
 *
 * @param int $product_id Lottery product ID.
 * @return array{enabled:bool,title:string,description:string,items:list<array{id:string,icon:string,title:string,description:string}>}
 */
function nera_prize_safety_config(int $product_id): array
{
  $config = ['enabled' => false, 'title' => '', 'description' => '', 'items' => []];

  if (!$product_id || !nera_prize_addons_site_enabled() || !function_exists('get_field') || !get_field('safety_enabled', $product_id)) {
    return $config;
  }

  $items = array_values(nera_prize_addons_picked_entries($product_id, 'safety'));
  if (empty($items)) {
    return $config;
  }

  $title = nera_prize_addons_text($product_id, 'safety_title');

  return [
    'enabled' => true,
    'title' => '' !== $title ? $title : nera_prize_addons_label('safety_title'),
    'description' => nera_prize_addons_text($product_id, 'safety_description'),
    'items' => $items,
  ];
}

/**
 * Add-ons settings for one prize.
 *
 * The options are the Catalog entries the prize picked, so their titles, descriptions
 * and prices are the Catalog's. The bundle price is the prize's own and only counts
 * when there are at least two options and it is below their total: anything else means
 * "no bundle discount".
 *
 * @param int $product_id Lottery product ID.
 * @return array{enabled:bool,title:string,description:string,options:array<string,array{id:string,title:string,description:string,price:float}>,total:float,bundle_price:?float}
 */
function nera_prize_addons_config(int $product_id): array
{
  static $cache = [];
  if (isset($cache[$product_id])) {
    return $cache[$product_id];
  }

  $config = [
    'enabled' => false,
    'title' => '',
    'description' => '',
    'options' => [],
    'total' => 0.0,
    'bundle_price' => null,
  ];

  if (!$product_id || !nera_prize_addons_site_enabled() || !function_exists('get_field') || !get_field('addons_enabled', $product_id)) {
    return $cache[$product_id] = $config;
  }

  $options = nera_prize_addons_picked_entries($product_id, 'addons');
  if (empty($options)) {
    return $cache[$product_id] = $config;
  }

  $total = round(array_sum(array_column($options, 'price')), 2);
  $bundle_raw = get_field('addons_bundle_price', $product_id);
  $bundle = '' === $bundle_raw || null === $bundle_raw || false === $bundle_raw ? 0.0 : round((float) $bundle_raw, 2);

  return $cache[$product_id] = [
    'enabled' => true,
    'title' => nera_prize_addons_text($product_id, 'addons_title'),
    'description' => nera_prize_addons_text($product_id, 'addons_description'),
    'options' => $options,
    'total' => $total,
    'bundle_price' => count($options) >= 2 && $bundle > 0 && $bundle < $total ? $bundle : null,
  ];
}

/**
 * Price a selection of add-on options for one draw.
 *
 * Options already bought, or no longer offered, are dropped and reported so the
 * caller can tell the customer. The Full bundle price applies only when every
 * option is chosen now and none was bought before.
 *
 * @param int      $draw_id       Lottery product ID.
 * @param string[] $option_ids    Chosen option IDs.
 * @param string[] $purchased_ids Option IDs already paid for by this customer.
 * @return array{options:array<string,array>,dropped:array<string,string>,subtotal:float,total:float,full_bundle:bool}
 */
function nera_prize_addons_quote(int $draw_id, array $option_ids, array $purchased_ids = []): array
{
  $config = nera_prize_addons_config($draw_id);
  $purchased = array_fill_keys(array_map('strval', $purchased_ids), true);

  $options = [];
  $dropped = [];
  foreach (array_unique(array_map('strval', $option_ids)) as $id) {
    if (!isset($config['options'][$id])) {
      $dropped[$id] = 'unavailable';
    } elseif (isset($purchased[$id])) {
      $dropped[$id] = 'purchased';
    } else {
      $options[$id] = $config['options'][$id];
    }
  }

  // Keep the admin's option order, not the order the browser sent.
  $ordered = [];
  foreach ($config['options'] as $id => $option) {
    if (isset($options[$id])) {
      $ordered[$id] = $option;
    }
  }

  $subtotal = round(array_sum(array_column($ordered, 'price')), 2);
  $bought_any = (bool) array_intersect_key($config['options'], $purchased);
  $full = null !== $config['bundle_price']
    && !$bought_any
    && count($ordered) === count($config['options']);

  return [
    'options' => $ordered,
    'dropped' => $dropped,
    'subtotal' => $subtotal,
    'total' => $full ? (float) $config['bundle_price'] : $subtotal,
    'full_bundle' => $full,
  ];
}

/**
 * Order statuses that count as "paid" when locking options already bought.
 *
 * @return string[]
 */
function nera_prize_addons_paid_statuses(): array
{
  return (array) apply_filters('nera_prize_addons_paid_statuses', ['wc-processing', 'wc-completed', 'wc-on-hold']);
}

/**
 * Add-on options a customer has already paid for, grouped by draw.
 *
 * Reads the snapshot stored on each add-on order item (so it survives the
 * admin reordering or renaming options) and skips items refunded in full.
 *
 * @param int $user_id Customer user ID.
 * @return array<int,string[]> Draw ID => option IDs.
 */
function nera_prize_addons_purchased_map(int $user_id): array
{
  static $cache = [];
  if ($user_id < 1 || !function_exists('wc_get_orders')) {
    return [];
  }
  if (isset($cache[$user_id])) {
    return $cache[$user_id];
  }

  $order_ids = wc_get_orders([
    'customer_id' => $user_id,
    'status' => nera_prize_addons_paid_statuses(),
    'type' => 'shop_order',
    'limit' => -1,
    'return' => 'ids',
  ]);
  $order_ids = array_values(array_filter(array_map('absint', (array) $order_ids)));
  if (empty($order_ids)) {
    return $cache[$user_id] = [];
  }

  // Order items live in the same tables with or without HPOS, so one query
  // narrows the list to orders that actually hold an add-on line.
  global $wpdb;
  $placeholders = implode(',', array_fill(0, count($order_ids), '%d'));
  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- placeholders built above.
  $with_addons = $wpdb->get_col($wpdb->prepare(
    "SELECT DISTINCT oi.order_id
       FROM {$wpdb->prefix}woocommerce_order_items oi
       INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta m
         ON m.order_item_id = oi.order_item_id AND m.meta_key = '_nera_addon_draw_id'
      WHERE oi.order_item_type = 'line_item' AND oi.order_id IN ($placeholders)",
    ...$order_ids
  ));

  $map = [];
  foreach ((array) $with_addons as $order_id) {
    $order = wc_get_order((int) $order_id);
    if (!$order instanceof WC_Order) {
      continue;
    }
    foreach ($order->get_items('line_item') as $item_id => $item) {
      $draw_id = (int) $item->get_meta('_nera_addon_draw_id', true);
      if (!$draw_id) {
        continue;
      }
      $qty = max(1, (int) $item->get_quantity());
      $refunded_qty = abs((int) $order->get_qty_refunded_for_item($item_id));
      $refunded_total = abs((float) $order->get_total_refunded_for_item($item_id));
      $line_total = (float) $item->get_total();
      if ($refunded_qty >= $qty || ($line_total > 0 && $refunded_total >= $line_total)) {
        continue;
      }
      foreach ((array) $item->get_meta('_nera_addon_options', true) as $option) {
        $id = sanitize_key((string) ($option['id'] ?? ''));
        if ('' !== $id) {
          $map[$draw_id][$id] = $id;
        }
      }
    }
  }

  return $cache[$user_id] = array_map('array_values', $map);
}

/**
 * Options the signed-in customer already paid for on one draw.
 *
 * Guests have none: checkout requires an account, so the lock applies as soon
 * as they sign in, and the cart check removes anything they already own.
 *
 * @param int $draw_id Lottery product ID.
 * @return string[]
 */
function nera_prize_addons_current_user_purchased(int $draw_id): array
{
  if (!is_user_logged_in()) {
    return [];
  }
  $map = nera_prize_addons_purchased_map(get_current_user_id());

  return $map[$draw_id] ?? [];
}

/**
 * ID of the hidden "Add-on bundle" product, or 0 when it does not exist yet.
 *
 * @return int
 */
function nera_prize_addons_product_id(): int
{
  $id = (int) get_option(NERA_PRIZE_ADDON_PRODUCT_OPTION, 0);
  if ($id < 1 || 'product' !== get_post_type($id)) {
    return 0;
  }
  $status = get_post_status($id);

  return $status && 'trash' !== $status ? $id : 0;
}

/**
 * Whether a product is the hidden add-on product.
 *
 * @param mixed $product Product object or ID.
 * @return bool
 */
function nera_prize_addons_is_addon_product($product): bool
{
  $addon_id = nera_prize_addons_product_id();
  if (!$addon_id) {
    return false;
  }
  $id = is_object($product) && method_exists($product, 'get_id') ? (int) $product->get_id() : (int) $product;

  return $id === $addon_id;
}

/**
 * Whether a cart item is a valid add-on line.
 *
 * @param mixed $cart_item Cart item array.
 * @return bool
 */
function nera_prize_addons_is_addon_cart_item($cart_item): bool
{
  return is_array($cart_item) && !empty($cart_item[NERA_PRIZE_ADDON_CART_KEY]['draw_id']);
}

/**
 * Whether an order line item is an add-on line.
 *
 * Reads order item meta, not the product, so it still works after the draw or
 * the add-on product has been deleted.
 *
 * @param mixed $item Order item.
 * @return bool
 */
function nera_prize_addons_is_order_item($item): bool
{
  return is_object($item) && method_exists($item, 'get_meta') && (int) $item->get_meta('_nera_addon_draw_id', true) > 0;
}

/**
 * The add-on line for a draw in the current cart.
 *
 * @param int $draw_id Lottery product ID.
 * @return array{0:string,1:array}|null Cart item key and item, or null.
 */
function nera_prize_addons_find_cart_line(int $draw_id): ?array
{
  if (!function_exists('WC') || !WC()->cart) {
    return null;
  }
  foreach (WC()->cart->get_cart() as $key => $item) {
    if (nera_prize_addons_is_addon_cart_item($item) && (int) $item[NERA_PRIZE_ADDON_CART_KEY]['draw_id'] === $draw_id) {
      return [$key, $item];
    }
  }

  return null;
}

/**
 * Whether the current cart still holds tickets for a draw.
 *
 * @param int $draw_id Lottery product ID.
 * @return bool
 */
function nera_prize_addons_draw_has_tickets(int $draw_id): bool
{
  if (!function_exists('WC') || !WC()->cart) {
    return false;
  }
  foreach (WC()->cart->get_cart() as $item) {
    if (!nera_prize_addons_is_addon_cart_item($item) && (int) ($item['product_id'] ?? 0) === $draw_id) {
      return true;
    }
  }

  return false;
}

/**
 * Name of the draw an add-on line belongs to.
 *
 * @param int $draw_id Lottery product ID.
 * @return string
 */
function nera_prize_addons_draw_name(int $draw_id): string
{
  $product = wc_get_product($draw_id);

  return $product ? $product->get_name() : sprintf('#%d', $draw_id);
}
