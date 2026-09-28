<?php
/**
 * Prize Safety & Add-ons — hooks.
 *
 * Each giveaway can show a free Safety list and sell optional Add-ons on its
 * prize page. Chosen add-ons become ONE cart line per draw ("Add-ons for: …"),
 * backed by a hidden "Add-on bundle" product, priced on the server from the
 * prize's ACF settings and never from the browser.
 *
 * Rules (agreed with the client):
 *  - fixed price per option, charged once per draw whatever the ticket count;
 *  - the Full bundle price applies only when every option is bought together;
 *  - options already paid for are locked ("Purchased") and cannot be bought
 *    again; top-ups always pay full price per option (option A);
 *  - no refund when the customer does not win.
 *
 * Data helpers: inc/helpers/prize-addons.php. Design notes:
 * docs/adr/0012-prize-add-on-lines.md.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/* -------------------------------------------------------------------------
 * Admin: stable option IDs, bundle price validation, hidden product
 * ---------------------------------------------------------------------- */

/**
 * Give every add-on option a stable, unique ID before ACF saves the rows.
 *
 * Runs ahead of ACF's own save (priority 10) so the IDs are saved with the
 * row. Duplicated rows (ACF's "duplicate row" copies the ID) get a fresh one.
 *
 * @return void
 */
function nera_prize_addons_acf_normalise_option_ids(): void
{
  // phpcs:ignore WordPress.Security.NonceVerification.Missing -- ACF verified its nonce before firing acf/save_post.
  if (empty($_POST['acf'][NERA_PRIZE_ADDON_ACF_ITEMS]) || !is_array($_POST['acf'][NERA_PRIZE_ADDON_ACF_ITEMS])) {
    return;
  }

  $seen = [];
  foreach ($_POST['acf'][NERA_PRIZE_ADDON_ACF_ITEMS] as $row_key => $row) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
    if (!is_array($row)) {
      continue;
    }
    $id = sanitize_key((string) ($row[NERA_PRIZE_ADDON_ACF_ITEM_ID] ?? ''));
    while ('' === $id || isset($seen[$id])) {
      $id = 'ao_' . strtolower(wp_generate_password(10, false, false));
    }
    $seen[$id] = true;
    $_POST['acf'][NERA_PRIZE_ADDON_ACF_ITEMS][$row_key][NERA_PRIZE_ADDON_ACF_ITEM_ID] = $id; // phpcs:ignore WordPress.Security.NonceVerification.Missing
  }
}
add_action('acf/save_post', 'nera_prize_addons_acf_normalise_option_ids', 5);

/**
 * Keep the option ID field out of the admin's way; it is generated on save.
 *
 * @param array|false $field ACF field.
 * @return array|false
 */
function nera_prize_addons_acf_hide_option_id($field)
{
  if (is_array($field)) {
    $field['wrapper']['class'] = trim(($field['wrapper']['class'] ?? '') . ' acf-hidden');
  }

  return $field;
}
add_filter('acf/prepare_field/key=' . NERA_PRIZE_ADDON_ACF_ITEM_ID, 'nera_prize_addons_acf_hide_option_id');

/**
 * Reject a Full bundle price that is not a real discount.
 *
 * @param bool|string $valid Current validity.
 * @param mixed       $value Submitted bundle price.
 * @return bool|string
 */
function nera_prize_addons_acf_validate_bundle($valid, $value)
{
  if (true !== $valid || '' === $value || null === $value) {
    return $valid;
  }

  // phpcs:disable WordPress.Security.NonceVerification.Missing -- ACF validates its own nonce.
  $acf = isset($_POST['acf']) && is_array($_POST['acf']) ? wp_unslash($_POST['acf']) : [];
  // phpcs:enable
  if (empty($acf[NERA_PRIZE_ADDON_ACF_ENABLED])) {
    return $valid;
  }

  $count = 0;
  $total = 0.0;
  foreach ((array) ($acf[NERA_PRIZE_ADDON_ACF_ITEMS] ?? []) as $row) {
    $title = trim((string) ($row[NERA_PRIZE_ADDON_ACF_ITEM_TITLE] ?? ''));
    $price = (float) ($row[NERA_PRIZE_ADDON_ACF_ITEM_PRICE] ?? 0);
    if ('' !== $title && $price > 0) {
      $count++;
      $total += $price;
    }
  }

  if ($count < 2) {
    return __('A Full bundle price needs at least two options. Add another option or leave the bundle price empty.', 'nera-competitions');
  }

  if ((float) $value >= $total) {
    return sprintf(
      /* translators: %s: options total */
      __('The Full bundle price must be lower than the options total (%s). Leave it empty for no bundle discount.', 'nera-competitions'),
      nera_prize_addons_money_text($total)
    );
  }

  return $valid;
}
add_filter('acf/validate_value/key=' . NERA_PRIZE_ADDON_ACF_BUNDLE, 'nera_prize_addons_acf_validate_bundle', 10, 2);

/**
 * Plain message for an option price of 0 or less.
 *
 * ACF's own number check formats the 0.01 minimum with %d, so it reads
 * "must be equal to or higher than 0" — no help to someone who typed 0.
 *
 * @param bool|string $valid Current validity.
 * @param mixed       $value Submitted price.
 * @return bool|string
 */
function nera_prize_addons_acf_validate_price($valid, $value)
{
  if ('' === $value || null === $value || !is_numeric($value)) {
    return $valid;
  }

  return (float) $value > 0
    ? $valid
    : __('Price must be greater than 0. Free items belong in the Safety tab.', 'nera-competitions');
}
add_filter('acf/validate_value/key=' . NERA_PRIZE_ADDON_ACF_ITEM_PRICE, 'nera_prize_addons_acf_validate_price', 20, 2);

/**
 * Icon preview for the Safety icon dropdown on product edit screens.
 *
 * @return void
 */
function nera_prize_addons_admin_assets(): void
{
  $screen = function_exists('get_current_screen') ? get_current_screen() : null;
  if (!$screen || 'product' !== $screen->post_type) {
    return;
  }

  wp_enqueue_style(
    'nera-admin-material-symbols',
    'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block',
    [],
    null
  );
  wp_enqueue_style(
    'nera-admin-prize-addons',
    get_template_directory_uri() . '/assets/css/admin-prize-addons.css',
    ['nera-admin-material-symbols'],
    NERA_VERSION
  );
  wp_enqueue_script(
    'nera-admin-prize-icon-picker',
    get_template_directory_uri() . '/assets/js/admin-prize-icon-picker.js',
    ['jquery', 'acf-input'],
    NERA_VERSION,
    true
  );
}
add_action('acf/input/admin_enqueue_scripts', 'nera_prize_addons_admin_assets');

/**
 * The hidden product every add-on line uses, created on first need.
 *
 * Created lazily rather than on theme activation: the theme updates through
 * the update checker, which never fires after_switch_theme.
 *
 * @return int Product ID, or 0 if it could not be created.
 */
function nera_prize_addons_ensure_product(): int
{
  $id = nera_prize_addons_product_id();
  if ($id) {
    return $id;
  }

  $product = new WC_Product_Simple();
  $product->set_name(__('Add-on bundle', 'nera-competitions'));
  $product->set_status('publish');
  $product->set_catalog_visibility('hidden');
  $product->set_virtual(true);
  $product->set_sold_individually(true);
  $product->set_reviews_allowed(false);
  // The real price is set per cart line; a price is still needed for WooCommerce
  // to treat the product as purchasable.
  $product->set_regular_price('0');
  $product->set_short_description(__('Used by the theme for prize add-ons. Do not delete or edit.', 'nera-competitions'));
  $id = (int) $product->save();

  if ($id) {
    update_option(NERA_PRIZE_ADDON_PRODUCT_OPTION, $id, false);
  }

  return $id;
}

/**
 * Label the hidden product in the Products list so nobody deletes it.
 *
 * @param array   $states Post states.
 * @param WP_Post $post   Post.
 * @return array
 */
function nera_prize_addons_post_state($states, $post)
{
  if ($post instanceof WP_Post && (int) $post->ID === nera_prize_addons_product_id()) {
    $states['nera_prize_addon'] = __('Prize add-ons (used by the theme)', 'nera-competitions');
  }

  return $states;
}
add_filter('display_post_states', 'nera_prize_addons_post_state', 10, 2);

/**
 * The hidden product has no page of its own.
 *
 * @return void
 */
function nera_prize_addons_block_product_page(): void
{
  $id = nera_prize_addons_product_id();
  if ($id && is_singular('product') && (int) get_queried_object_id() === $id) {
    wp_safe_redirect(home_url('/'));
    exit;
  }
}
add_action('template_redirect', 'nera_prize_addons_block_product_page');

/* -------------------------------------------------------------------------
 * Prize page
 * ---------------------------------------------------------------------- */

/**
 * Render the Safety and Add-ons blocks between the ticket quantity and the
 * entry form of the purchase card.
 *
 * @param WC_Product|null $product Lottery product.
 * @param array           $args    Purchase card args (is_expired, …).
 * @return void
 */
function nera_prize_addons_render_purchase_card($product, $args = []): void
{
  if (!nera_prize_addons_is_lottery($product) || !function_exists('nera_render_component')) {
    return;
  }
  if (!empty($args['is_expired']) || (method_exists($product, 'is_closed') && $product->is_closed())) {
    return;
  }

  ob_start();
  nera_render_component('PrizeSafety', ['product' => $product]);
  nera_render_component('PrizeAddOns', ['product' => $product]);
  $html = trim((string) ob_get_clean());

  if ('' !== $html) {
    echo '<div class="px-6 pb-6 flex flex-col gap-4">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component output is escaped in its view.
  }
}
add_action('nera_purchase_card_before_enter_form', 'nera_prize_addons_render_purchase_card', 10, 2);

/* -------------------------------------------------------------------------
 * Cart: create, update, price, clean up
 * ---------------------------------------------------------------------- */

/**
 * Whether the theme itself is adding an add-on line right now.
 *
 * @param bool|null $set Pass a value to set the flag.
 * @return bool
 */
function nera_prize_addons_internal_add(?bool $set = null): bool
{
  static $internal = false;
  if (null !== $set) {
    $internal = $set;
  }

  return $internal;
}

/**
 * Apply the add-ons chosen on the prize page after its tickets were added.
 *
 * Only runs when the request carries the add-on field, so the listing
 * quick-add (which never sends it) leaves an existing add-on line alone.
 *
 * @param int    $product_id    Lottery product ID.
 * @param string $cart_item_key Ticket cart item key.
 * @return void
 */
function nera_prize_addons_sync_from_request($product_id, $cart_item_key = ''): void
{
  // phpcs:disable WordPress.Security.NonceVerification.Missing -- same unauthenticated add-to-cart request as the tickets; prices are never read from it.
  if (empty($_POST['nera_addons_submitted'])) {
    return;
  }
  $raw = isset($_POST['nera_addon_ids']) ? (array) wp_unslash($_POST['nera_addon_ids']) : [];
  // phpcs:enable

  $ids = array_values(array_unique(array_filter(array_map('sanitize_key', array_map('strval', $raw)))));
  nera_prize_addons_set_cart_selection((int) $product_id, $ids);
}
add_action('nera_ajax_add_to_cart_success', 'nera_prize_addons_sync_from_request', 10, 2);

/**
 * Make the cart's add-on line for a draw match a selection.
 *
 * One line per draw: an existing line is updated, an empty selection removes it.
 *
 * @param int      $draw_id Lottery product ID.
 * @param string[] $ids     Chosen option IDs.
 * @return void
 */
function nera_prize_addons_set_cart_selection(int $draw_id, array $ids): void
{
  if (!function_exists('WC') || !WC()->cart) {
    return;
  }

  $line = nera_prize_addons_find_cart_line($draw_id);
  $config = nera_prize_addons_config($draw_id);
  $quote = $config['enabled']
    ? nera_prize_addons_quote($draw_id, $ids, nera_prize_addons_current_user_purchased($draw_id))
    : ['options' => []];
  $valid_ids = array_keys($quote['options']);

  if (empty($valid_ids)) {
    if ($line) {
      WC()->cart->remove_cart_item($line[0]);
    }
    return;
  }

  if ($line) {
    WC()->cart->cart_contents[$line[0]][NERA_PRIZE_ADDON_CART_KEY]['option_ids'] = $valid_ids;
  } else {
    $addon_product_id = nera_prize_addons_ensure_product();
    if (!$addon_product_id) {
      return;
    }
    nera_prize_addons_internal_add(true);
    WC()->cart->add_to_cart($addon_product_id, 1, 0, [], [
      NERA_PRIZE_ADDON_CART_KEY => ['draw_id' => $draw_id, 'option_ids' => $valid_ids],
    ]);
    nera_prize_addons_internal_add(false);
  }

  nera_prize_addons_sort_cart();
  WC()->cart->calculate_totals();
}

/**
 * Fold several add-on lines for the same draw into one.
 *
 * WooCommerce merges a guest basket into the customer's saved basket on sign-in,
 * which can leave two lines for one draw. The kept line takes every option either
 * line held; pricing and the "already bought" check still run on it afterwards.
 *
 * @return bool Whether anything changed.
 */
function nera_prize_addons_merge_duplicate_lines(): bool
{
  if (!function_exists('WC') || !WC()->cart) {
    return false;
  }

  $first = [];
  $changed = false;
  foreach (WC()->cart->get_cart_contents() as $key => $item) {
    if (!nera_prize_addons_is_addon_cart_item($item)) {
      continue;
    }
    $draw_id = (int) $item[NERA_PRIZE_ADDON_CART_KEY]['draw_id'];
    if (!isset($first[$draw_id])) {
      $first[$draw_id] = $key;
      continue;
    }
    $keep = $first[$draw_id];
    $merged = array_values(array_unique(array_merge(
      (array) (WC()->cart->cart_contents[$keep][NERA_PRIZE_ADDON_CART_KEY]['option_ids'] ?? []),
      (array) ($item[NERA_PRIZE_ADDON_CART_KEY]['option_ids'] ?? [])
    )));
    WC()->cart->cart_contents[$keep][NERA_PRIZE_ADDON_CART_KEY]['option_ids'] = $merged;
    unset(WC()->cart->cart_contents[$key]);
    $changed = true;
  }

  return $changed;
}

/**
 * Keep each add-on line directly after the last ticket line of its draw.
 *
 * @return void
 */
function nera_prize_addons_sort_cart(): void
{
  if (!function_exists('WC') || !WC()->cart) {
    return;
  }

  nera_prize_addons_merge_duplicate_lines();

  $contents = WC()->cart->get_cart_contents();
  $addons = [];
  foreach ($contents as $key => $item) {
    if (nera_prize_addons_is_addon_cart_item($item)) {
      $addons[(int) $item[NERA_PRIZE_ADDON_CART_KEY]['draw_id']][$key] = $item;
    }
  }
  if (empty($addons)) {
    return;
  }

  $last_ticket = [];
  foreach ($contents as $key => $item) {
    if (!nera_prize_addons_is_addon_cart_item($item)) {
      $last_ticket[(int) ($item['product_id'] ?? 0)] = $key;
    }
  }

  $sorted = [];
  foreach ($contents as $key => $item) {
    if (nera_prize_addons_is_addon_cart_item($item)) {
      $draw = (int) $item[NERA_PRIZE_ADDON_CART_KEY]['draw_id'];
      if (isset($last_ticket[$draw])) {
        continue; // Placed after its tickets below.
      }
      $sorted[$key] = $item;
      continue;
    }
    $sorted[$key] = $item;
    $draw = (int) ($item['product_id'] ?? 0);
    if (($last_ticket[$draw] ?? null) === $key && isset($addons[$draw])) {
      foreach ($addons[$draw] as $addon_key => $addon) {
        $sorted[$addon_key] = $addon;
      }
    }
  }

  if (array_keys($sorted) !== array_keys($contents)) {
    WC()->cart->set_cart_contents($sorted);
  }
}
add_action('woocommerce_cart_loaded_from_session', 'nera_prize_addons_sort_cart', 20);

/**
 * Block adding the hidden product any way other than through a prize page.
 *
 * @param bool $passed     Validation result.
 * @param int  $product_id Product being added.
 * @return bool
 */
function nera_prize_addons_block_direct_add($passed, $product_id)
{
  if (nera_prize_addons_is_addon_product((int) $product_id) && !nera_prize_addons_internal_add()) {
    wc_add_notice(__('Add-ons can only be chosen on a prize page, together with tickets.', 'nera-competitions'), 'error');
    return false;
  }

  return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'nera_prize_addons_block_direct_add', 5, 2);

/**
 * Price each add-on line from the prize settings, once per draw, quantity 1.
 *
 * @param WC_Cart $cart Cart.
 * @return void
 */
function nera_prize_addons_set_prices($cart): void
{
  if (!$cart instanceof WC_Cart || (is_admin() && !wp_doing_ajax())) {
    return;
  }

  foreach ($cart->get_cart() as $key => $item) {
    if (!nera_prize_addons_is_addon_cart_item($item) || !isset($item['data']) || !is_object($item['data'])) {
      continue;
    }
    $draw_id = (int) $item[NERA_PRIZE_ADDON_CART_KEY]['draw_id'];
    $config = nera_prize_addons_config($draw_id);
    $price = 0.0;
    if ($config['enabled']) {
      $quote = nera_prize_addons_quote(
        $draw_id,
        (array) ($item[NERA_PRIZE_ADDON_CART_KEY]['option_ids'] ?? []),
        nera_prize_addons_current_user_purchased($draw_id)
      );
      $price = $quote['total'];
    }
    $item['data']->set_price($price);

    if (1 !== (int) $item['quantity']) {
      $cart->cart_contents[$key]['quantity'] = 1;
    }
  }
}
add_action('woocommerce_before_calculate_totals', 'nera_prize_addons_set_prices', 20);

/**
 * Remove add-on lines that no longer belong in the cart, and tell the customer.
 *
 * Runs on the cart page, the checkout page, after each add to cart, and while
 * placing an order. At that last point anything removed is an error, so the
 * order is not placed at a total the customer never saw.
 *
 * @return void
 */
function nera_prize_addons_check_cart_items(): void
{
  if (!function_exists('WC') || !WC()->cart) {
    return;
  }

  $type = did_action('woocommerce_before_checkout_process') ? 'error' : 'notice';
  $changed = nera_prize_addons_merge_duplicate_lines();

  foreach (WC()->cart->get_cart() as $key => $item) {
    $is_line = nera_prize_addons_is_addon_cart_item($item);

    // The hidden product without our data got in some other way: drop it.
    if (!$is_line) {
      if (nera_prize_addons_is_addon_product((int) ($item['product_id'] ?? 0))) {
        WC()->cart->remove_cart_item($key);
        $changed = true;
      }
      continue;
    }

    $draw_id = (int) $item[NERA_PRIZE_ADDON_CART_KEY]['draw_id'];
    $draw_name = nera_prize_addons_draw_name($draw_id);

    // Tickets gone (removed, expired hold, closed draw): the reason was
    // already given for the tickets, so this removal stays quiet.
    if (!nera_prize_addons_draw_has_tickets($draw_id)) {
      WC()->cart->remove_cart_item($key);
      $changed = true;
      continue;
    }

    $config = nera_prize_addons_config($draw_id);
    if (!$config['enabled']) {
      WC()->cart->remove_cart_item($key);
      /* translators: %s: prize name */
      wc_add_notice(sprintf(__('Add-ons for %s are no longer available and were removed from your basket.', 'nera-competitions'), $draw_name), $type);
      $changed = true;
      continue;
    }

    $quote = nera_prize_addons_quote(
      $draw_id,
      (array) ($item[NERA_PRIZE_ADDON_CART_KEY]['option_ids'] ?? []),
      nera_prize_addons_current_user_purchased($draw_id)
    );
    if (empty($quote['dropped'])) {
      continue;
    }

    foreach ($quote['dropped'] as $option_id => $reason) {
      if ('purchased' === $reason) {
        $title = $config['options'][$option_id]['title'] ?? '';
        wc_add_notice(sprintf(
          /* translators: 1: option name, 2: prize name */
          __('%1$s was removed from your add-ons for %2$s because you already bought it for this draw.', 'nera-competitions'),
          $title,
          $draw_name
        ), $type);
      } else {
        wc_add_notice(sprintf(
          /* translators: %s: prize name */
          __('An add-on for %s is no longer available and was removed from your basket.', 'nera-competitions'),
          $draw_name
        ), $type);
      }
    }

    if (empty($quote['options'])) {
      WC()->cart->remove_cart_item($key);
    } else {
      WC()->cart->cart_contents[$key][NERA_PRIZE_ADDON_CART_KEY]['option_ids'] = array_keys($quote['options']);
    }
    $changed = true;
  }

  if ($changed) {
    nera_prize_addons_sort_cart();
    WC()->cart->calculate_totals();
  }
}
add_action('woocommerce_check_cart_items', 'nera_prize_addons_check_cart_items', 20);

/**
 * Removing the last ticket line of a draw removes that draw's add-ons too.
 *
 * @param string  $cart_item_key Removed item key.
 * @param WC_Cart $cart          Cart.
 * @return void
 */
function nera_prize_addons_after_item_removed($cart_item_key, $cart): void
{
  if (!$cart instanceof WC_Cart) {
    return;
  }
  $removed = $cart->removed_cart_contents[$cart_item_key] ?? null;
  if (!is_array($removed) || nera_prize_addons_is_addon_cart_item($removed)) {
    return;
  }

  $draw_id = (int) ($removed['product_id'] ?? 0);
  if (!$draw_id || nera_prize_addons_draw_has_tickets($draw_id)) {
    return;
  }

  $line = nera_prize_addons_find_cart_line($draw_id);
  if ($line) {
    $cart->remove_cart_item($line[0]);
  }
}
add_action('woocommerce_cart_item_removed', 'nera_prize_addons_after_item_removed', 10, 2);

/**
 * Add-on lines are not tickets: leave them out of the header basket count.
 *
 * @param int $count Cart contents count.
 * @return int
 */
function nera_prize_addons_cart_count($count)
{
  if (!function_exists('WC') || !WC()->cart) {
    return $count;
  }
  foreach (WC()->cart->get_cart() as $item) {
    if (nera_prize_addons_is_addon_cart_item($item)) {
      $count -= (int) $item['quantity'];
    }
  }

  return max(0, (int) $count);
}
add_filter('woocommerce_cart_contents_count', 'nera_prize_addons_cart_count');

/* -------------------------------------------------------------------------
 * Coupons: tickets only
 * ---------------------------------------------------------------------- */

/**
 * Product-level coupons never apply to add-ons.
 *
 * @param bool       $valid   Validity.
 * @param WC_Product $product Product.
 * @return bool
 */
function nera_prize_addons_coupon_product($valid, $product)
{
  return nera_prize_addons_is_addon_product($product) ? false : $valid;
}
add_filter('woocommerce_coupon_is_valid_for_product', 'nera_prize_addons_coupon_product', 20, 2);

/**
 * Cart-level coupons (fixed cart) skip add-on lines when spreading the discount.
 *
 * @param array $items Items the coupon applies to.
 * @return array
 */
function nera_prize_addons_coupon_items($items)
{
  return array_values(array_filter((array) $items, static function ($item) {
    return !(is_object($item) && isset($item->object) && nera_prize_addons_is_addon_cart_item($item->object));
  }));
}
add_filter('woocommerce_coupon_get_items_to_apply', 'nera_prize_addons_coupon_items', 20);

/* -------------------------------------------------------------------------
 * Cart and mini-cart display (theme cart templates render their own row)
 * ---------------------------------------------------------------------- */

/**
 * "Add-ons for: [prize]" as the line name.
 *
 * @param string $name      Item name.
 * @param array  $cart_item Cart item.
 * @return string
 */
function nera_prize_addons_cart_item_name($name, $cart_item)
{
  if (!nera_prize_addons_is_addon_cart_item($cart_item)) {
    return $name;
  }

  return esc_html(sprintf(
    nera_prize_addons_label('line_name'),
    nera_prize_addons_draw_name((int) $cart_item[NERA_PRIZE_ADDON_CART_KEY]['draw_id'])
  ));
}
add_filter('woocommerce_cart_item_name', 'nera_prize_addons_cart_item_name', 20, 2);

/**
 * The chosen options, for templates that print item data (mini-cart).
 *
 * @param array $item_data Item data rows.
 * @param array $cart_item Cart item.
 * @return array
 */
function nera_prize_addons_cart_item_data($item_data, $cart_item)
{
  if (!nera_prize_addons_is_addon_cart_item($cart_item)) {
    return $item_data;
  }
  $draw_id = (int) $cart_item[NERA_PRIZE_ADDON_CART_KEY]['draw_id'];
  $quote = nera_prize_addons_quote(
    $draw_id,
    (array) ($cart_item[NERA_PRIZE_ADDON_CART_KEY]['option_ids'] ?? []),
    nera_prize_addons_current_user_purchased($draw_id)
  );

  $item_data[] = [
    'key' => nera_prize_addons_label('meta_options'),
    'value' => implode(', ', array_column($quote['options'], 'title')),
  ];
  if ($quote['full_bundle']) {
    $item_data[] = ['key' => nera_prize_addons_label('meta_full_bundle'), 'value' => nera_prize_addons_label('yes')];
  }

  return $item_data;
}
add_filter('woocommerce_get_item_data', 'nera_prize_addons_cart_item_data', 20, 2);

/**
 * Link the add-on line to its prize page, not to the hidden product.
 *
 * @param string $permalink Permalink.
 * @param array  $cart_item Cart item.
 * @return string
 */
function nera_prize_addons_cart_item_permalink($permalink, $cart_item)
{
  if (!nera_prize_addons_is_addon_cart_item($cart_item)) {
    return $permalink;
  }
  $draw = wc_get_product((int) $cart_item[NERA_PRIZE_ADDON_CART_KEY]['draw_id']);

  return $draw ? $draw->get_permalink() : '';
}
add_filter('woocommerce_cart_item_permalink', 'nera_prize_addons_cart_item_permalink', 20, 2);

/**
 * Show the prize image beside its add-on line.
 *
 * @param string $thumbnail Thumbnail HTML.
 * @param array  $cart_item Cart item.
 * @return string
 */
function nera_prize_addons_cart_item_thumbnail($thumbnail, $cart_item)
{
  if (!nera_prize_addons_is_addon_cart_item($cart_item)) {
    return $thumbnail;
  }
  $draw = wc_get_product((int) $cart_item[NERA_PRIZE_ADDON_CART_KEY]['draw_id']);

  return $draw ? $draw->get_image() : $thumbnail;
}
add_filter('woocommerce_cart_item_thumbnail', 'nera_prize_addons_cart_item_thumbnail', 20, 2);

/**
 * The add-on quantity is always 1 and cannot be edited.
 *
 * @param string $product_quantity Quantity HTML.
 * @param string $cart_item_key    Cart item key.
 * @param array  $cart_item        Cart item.
 * @return string
 */
function nera_prize_addons_cart_item_quantity($product_quantity, $cart_item_key, $cart_item = [])
{
  if (!nera_prize_addons_is_addon_cart_item($cart_item)) {
    return $product_quantity;
  }

  return sprintf('1 <input type="hidden" name="cart[%s][qty]" value="1" />', esc_attr($cart_item_key));
}
add_filter('woocommerce_cart_item_quantity', 'nera_prize_addons_cart_item_quantity', 20, 3);

/* -------------------------------------------------------------------------
 * Orders
 * ---------------------------------------------------------------------- */

/**
 * Save the draw, the options and the prices paid on the order line.
 *
 * The snapshot is what the "Purchased" lock and the admin read later, so it
 * never depends on the prize settings staying the same.
 *
 * @param WC_Order_Item_Product $item          Order item.
 * @param string                $cart_item_key Cart item key.
 * @param array                 $values        Cart item.
 * @return void
 */
function nera_prize_addons_create_order_line_item($item, $cart_item_key, $values): void
{
  if (!nera_prize_addons_is_addon_cart_item($values)) {
    return;
  }

  $draw_id = (int) $values[NERA_PRIZE_ADDON_CART_KEY]['draw_id'];
  $draw_name = nera_prize_addons_draw_name($draw_id);
  $quote = nera_prize_addons_quote(
    $draw_id,
    (array) ($values[NERA_PRIZE_ADDON_CART_KEY]['option_ids'] ?? []),
    nera_prize_addons_current_user_purchased($draw_id)
  );

  $options = [];
  foreach ($quote['options'] as $option) {
    $options[] = ['id' => $option['id'], 'title' => $option['title'], 'price' => $option['price']];
  }

  $item->set_name(sprintf(nera_prize_addons_label('line_name'), $draw_name));
  $item->add_meta_data('_nera_addon_draw_id', $draw_id, true);
  $item->add_meta_data('_nera_addon_draw_name', $draw_name, true);
  $item->add_meta_data('_nera_addon_options', $options, true);
  $item->add_meta_data('_nera_addon_full_bundle', $quote['full_bundle'] ? 'yes' : 'no', true);
}
add_action('woocommerce_checkout_create_order_line_item', 'nera_prize_addons_create_order_line_item', 10, 3);

/**
 * Show For / Options / Full bundle on the order in emails, My Account and admin.
 *
 * Built from the hidden snapshot at display time so labels stay translatable.
 *
 * @param array         $formatted_meta Formatted meta.
 * @param WC_Order_Item $item           Order item.
 * @return array
 */
function nera_prize_addons_formatted_meta($formatted_meta, $item)
{
  if (!nera_prize_addons_is_order_item($item)) {
    return $formatted_meta;
  }

  $draw_name = (string) $item->get_meta('_nera_addon_draw_name', true);
  $options = [];
  foreach ((array) $item->get_meta('_nera_addon_options', true) as $option) {
    $options[] = sprintf(
      '%s (%s)',
      (string) ($option['title'] ?? ''),
      nera_prize_addons_money_text((float) ($option['price'] ?? 0))
    );
  }
  $full = 'yes' === $item->get_meta('_nera_addon_full_bundle', true);

  $rows = [
    'nera_addon_for' => [nera_prize_addons_label('meta_for'), $draw_name],
    'nera_addon_options' => [nera_prize_addons_label('meta_options'), implode(', ', $options)],
    'nera_addon_full_bundle' => [nera_prize_addons_label('meta_full_bundle'), nera_prize_addons_label($full ? 'yes' : 'no')],
  ];
  foreach ($rows as $key => [$label, $value]) {
    if ('' === $value) {
      continue;
    }
    $formatted_meta[$key] = (object) [
      'key' => $key,
      'value' => $value,
      'display_key' => esc_html($label),
      'display_value' => esc_html($value),
    ];
  }

  return $formatted_meta;
}
add_filter('woocommerce_order_item_get_formatted_meta_data', 'nera_prize_addons_formatted_meta', 20, 2);

/**
 * Keep the raw snapshot keys out of the admin order screen.
 *
 * @param string[] $keys Hidden meta keys.
 * @return string[]
 */
function nera_prize_addons_hidden_order_itemmeta($keys)
{
  return array_merge((array) $keys, [
    '_nera_addon_draw_id',
    '_nera_addon_draw_name',
    '_nera_addon_options',
    '_nera_addon_full_bundle',
  ]);
}
add_filter('woocommerce_hidden_order_itemmeta', 'nera_prize_addons_hidden_order_itemmeta');
