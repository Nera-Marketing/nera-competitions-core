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
 * Admin: the Catalog, a prize's picks, hidden product
 * ---------------------------------------------------------------------- */

/**
 * Give every Catalog entry a stable, unique ID before ACF saves the rows.
 *
 * Runs ahead of ACF's own save (priority 10) so the IDs are saved with the row. A
 * prize refers to an entry by this ID, and the "Purchased" lock and placed orders
 * match add-on options by it, so it must survive renaming and reordering. Duplicated
 * rows (ACF's "duplicate row" copies the ID) get a fresh one.
 *
 * @return void
 */
function nera_prize_addons_acf_normalise_ids(): void
{
  $catalogs = [
    [NERA_PRIZE_ADDON_CATALOG_SAFETY, NERA_PRIZE_ADDON_CATALOG_SAFETY_ID, 'si_'],
    [NERA_PRIZE_ADDON_CATALOG_ADDONS, NERA_PRIZE_ADDON_CATALOG_ADDONS_ID, 'ao_'],
  ];

  foreach ($catalogs as [$items_key, $id_key, $id_prefix]) {
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- ACF verified its nonce before firing acf/save_post.
    if (empty($_POST['acf'][$items_key]) || !is_array($_POST['acf'][$items_key])) {
      continue;
    }

    $seen = [];
    foreach ($_POST['acf'][$items_key] as $row_key => $row) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
      if (!is_array($row)) {
        continue;
      }
      $id = sanitize_key((string) ($row[$id_key] ?? ''));
      while ('' === $id || isset($seen[$id])) {
        $id = $id_prefix . strtolower(wp_generate_password(10, false, false));
      }
      $seen[$id] = true;
      $_POST['acf'][$items_key][$row_key][$id_key] = $id; // phpcs:ignore WordPress.Security.NonceVerification.Missing
    }
  }
}
add_action('acf/save_post', 'nera_prize_addons_acf_normalise_ids', 5);

/**
 * Keep the ID field out of the admin's way; it is generated on save.
 *
 * @param array|false $field ACF field.
 * @return array|false
 */
function nera_prize_addons_acf_hide_id($field)
{
  if (is_array($field)) {
    $field['wrapper']['class'] = trim(($field['wrapper']['class'] ?? '') . ' acf-hidden');
  }

  return $field;
}
add_filter('acf/prepare_field/key=' . NERA_PRIZE_ADDON_CATALOG_SAFETY_ID, 'nera_prize_addons_acf_hide_id');
add_filter('acf/prepare_field/key=' . NERA_PRIZE_ADDON_CATALOG_ADDONS_ID, 'nera_prize_addons_acf_hide_id');

/**
 * Reject a Full bundle price above the total of the options the prize picked.
 *
 * The field is locked until an option is picked and capped in the browser too
 * (assets/js/admin-prize-addons.js); this is the authoritative check. A price equal to
 * the total is accepted but never offered to customers, since it saves nothing
 * (nera_prize_addons_config()).
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
    return $valid; // Add-ons are not shown on this prize: nothing to price.
  }

  $catalog = nera_prize_addons_catalog('addons');
  $total = 0.0;
  $count = 0;
  foreach (array_unique(array_map('sanitize_key', array_map('strval', (array) ($acf[NERA_PRIZE_ADDON_PICK_ADDONS] ?? [])))) as $id) {
    if (isset($catalog[$id])) {
      $total += $catalog[$id]['price'];
      $count++;
    }
  }
  $total = round($total, 2);

  if (0 === $count) {
    return __('Choose add-on options first: the Full bundle price is for the options chosen above. Leave it empty to have none.', 'nera-competitions');
  }

  if ((float) $value > $total) {
    return sprintf(
      /* translators: %s: options total */
      __('The Full bundle price cannot be higher than the total of the chosen options (%s).', 'nera-competitions'),
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
    : __('Price must be greater than 0. Free items belong in the Safety catalog.', 'nera-competitions');
}
add_filter('acf/validate_value/key=' . NERA_PRIZE_ADDON_KEY_GLOBAL . 'addon_price', 'nera_prize_addons_acf_validate_price', 20, 2);

/**
 * The prizes that have picked a Catalog entry.
 *
 * @param string $meta_key 'safety_item_ids' or 'addon_option_ids'.
 * @param string $id       Catalog entry ID.
 * @return int[] Product IDs (trash and auto-drafts left out).
 */
function nera_prize_addons_products_using(string $meta_key, string $id): array
{
  global $wpdb;

  // A pick list is stored as a serialized array of strings, so the ID appears quoted.
  $like = '%' . $wpdb->esc_like('"' . $id . '"') . '%';
  $ids = $wpdb->get_col(
    $wpdb->prepare(
      "SELECT DISTINCT m.post_id
         FROM {$wpdb->postmeta} m
         INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id
        WHERE m.meta_key = %s AND m.meta_value LIKE %s
          AND p.post_type = 'product' AND p.post_status NOT IN ('trash', 'auto-draft')
        ORDER BY p.post_title",
      $meta_key,
      $like
    )
  );

  return array_map('intval', $ids);
}

/**
 * Refuse to delete a Catalog entry that a prize has picked.
 *
 * The message names each prize with the address of its edit page. ACF escapes the text
 * of a validation error, so the addresses arrive as plain text and
 * assets/js/admin-prize-addons.js turns them into links.
 *
 * @param bool|string $valid Current validity.
 * @param mixed       $value Submitted catalog rows.
 * @param array       $field ACF repeater.
 * @return bool|string
 */
function nera_prize_addons_acf_guard_catalog_delete($valid, $value, $field)
{
  if (true !== $valid) {
    return $valid;
  }

  $is_safety = NERA_PRIZE_ADDON_CATALOG_SAFETY === ($field['key'] ?? '');
  $kind = $is_safety ? 'safety' : 'addons';
  $id_key = $is_safety ? NERA_PRIZE_ADDON_CATALOG_SAFETY_ID : NERA_PRIZE_ADDON_CATALOG_ADDONS_ID;

  $submitted = [];
  foreach ((array) $value as $row) {
    $id = is_array($row) ? sanitize_key((string) ($row[$id_key] ?? '')) : '';
    if ('' !== $id) {
      $submitted[$id] = true;
    }
  }

  // What is saved now, before this request replaces it, minus what was submitted.
  $messages = [];
  foreach (array_diff_key(nera_prize_addons_catalog($kind), $submitted) as $id => $entry) {
    $products = nera_prize_addons_products_using($is_safety ? 'safety_item_ids' : 'addon_option_ids', $id);
    if (empty($products)) {
      continue;
    }

    $prizes = [];
    foreach ($products as $product_id) {
      // Built by hand: get_edit_post_link() returns nothing to someone without the capability to edit that product.
      $prizes[] = sprintf('%s (%s)', get_the_title($product_id), admin_url('post.php?post=' . $product_id . '&action=edit'));
    }
    $messages[] = sprintf(
      /* translators: 1: entry name, 2: number of prizes, 3: prizes with the address of their edit page */
      _n(
        '"%1$s" is used by %2$d prize and cannot be deleted. Remove it from that prize first: %3$s',
        '"%1$s" is used by %2$d prizes and cannot be deleted. Remove it from these prizes first: %3$s',
        count($products),
        'nera-competitions'
      ),
      $entry['title'],
      count($products),
      implode('; ', $prizes)
    );
  }

  return empty($messages) ? $valid : implode("\n", $messages);
}
add_filter('acf/validate_value/key=' . NERA_PRIZE_ADDON_CATALOG_SAFETY, 'nera_prize_addons_acf_guard_catalog_delete', 10, 3);
add_filter('acf/validate_value/key=' . NERA_PRIZE_ADDON_CATALOG_ADDONS, 'nera_prize_addons_acf_guard_catalog_delete', 10, 3);

/**
 * Admin styling and scripts for the prize box and the Add-ons Bundles settings.
 *
 * @return void
 */
function nera_prize_addons_admin_assets(): void
{
  $screen = function_exists('get_current_screen') ? get_current_screen() : null;
  // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check.
  $on_settings = isset($_GET['page']) && 'acf-options-woocommerce' === sanitize_key(wp_unslash($_GET['page']));
  if (!$screen || ('product' !== $screen->post_type && !$on_settings)) {
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
  wp_enqueue_script(
    'nera-admin-prize-addons',
    get_template_directory_uri() . '/assets/js/admin-prize-addons.js',
    ['jquery', 'acf-input'],
    NERA_VERSION,
    true
  );
  wp_localize_script('nera-admin-prize-addons', 'neraPsaAdmin', [
    // Option ID => price: the prize's Full bundle price is capped at the total of its picks.
    'prices' => array_map(static fn($entry) => $entry['price'], nera_prize_addons_catalog('addons')),
    'pickerKey' => NERA_PRIZE_ADDON_PICK_ADDONS,
    'bundleKey' => NERA_PRIZE_ADDON_ACF_BUNDLE,
    'currency' => [
      'symbol' => nera_prize_addons_currency_symbol(),
      'decimals' => function_exists('wc_get_price_decimals') ? (int) wc_get_price_decimals() : 2,
    ],
    'i18n' => [
      /* translators: %s: options total, already formatted with the currency */
      'max' => __('Highest allowed: %s (the total of the chosen options).', 'nera-competitions'),
      'locked' => __('Choose add-on options above to set a Full bundle price.', 'nera-competitions'),
    ],
  ]);
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
  if (!nera_prize_addons_site_enabled() || !nera_prize_addons_is_lottery($product) || !function_exists('nera_render_component')) {
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
 * Read `nera_addon_years[<option_id>]` from $_POST for just the given ids,
 * sanitised to positive ints (docs/adr/0015). An id missing here, or posted
 * with an invalid value, is left out — nera_prize_addons_years_map() and
 * quote() both read a missing id as 1 year, so there is nothing to default
 * here; a value out of 1..max_term is still clamped by quote() itself.
 *
 * @param string[] $ids Option IDs already read from the same request.
 * @return array<string,int> id => years, only for ids present and > 0.
 */
function nera_prize_addons_years_from_request(array $ids): array
{
  // phpcs:ignore WordPress.Security.NonceVerification.Missing -- same unauthenticated request as the ids; prices are never read from it.
  $raw = isset($_POST['nera_addon_years']) && is_array($_POST['nera_addon_years']) ? wp_unslash($_POST['nera_addon_years']) : [];

  $years = [];
  foreach ($ids as $id) {
    if (isset($raw[$id]) && is_numeric($raw[$id]) && (int) $raw[$id] > 0) {
      $years[$id] = (int) $raw[$id];
    }
  }

  return $years;
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
  if (!nera_prize_addons_site_enabled() || empty($_POST['nera_addons_submitted'])) {
    return;
  }
  $raw = isset($_POST['nera_addon_ids']) ? (array) wp_unslash($_POST['nera_addon_ids']) : [];
  // phpcs:enable

  $ids = array_values(array_unique(array_filter(array_map('sanitize_key', array_map('strval', $raw)))));
  $years = nera_prize_addons_years_from_request($ids);
  nera_prize_addons_set_cart_selection((int) $product_id, $ids, $years);
}
add_action('nera_ajax_add_to_cart_success', 'nera_prize_addons_sync_from_request', 10, 2);

/**
 * Apply the add-ons chosen in the Lucky Dip dialogs when Lucky Dip tickets are added.
 *
 * Lottery for WooCommerce adds Lucky Dip tickets with its own AJAX handlers, not the
 * theme's, so nera_ajax_add_to_cart_success never fires for them. WooCommerce's own
 * woocommerce_add_to_cart does, right after the tickets are in the basket. The Lucky
 * Dip script (assets/js/lucky-dip-addons.js) adds nera_addons_submitted and
 * nera_addon_ids[] to exactly these requests, and only when the prize shows add-ons.
 *
 * @param string $cart_item_key Ticket cart item key.
 * @param int    $product_id    Product added.
 * @return void
 */
function nera_prize_addons_sync_from_lucky_dip($cart_item_key, $product_id): void
{
  static $done = false;

  // The add-on line is itself added to the basket: do not react to that.
  if ($done || nera_prize_addons_internal_add() || !wp_doing_ajax()) {
    return;
  }
  // phpcs:ignore WordPress.Security.NonceVerification.Missing -- LFW's handler verified its nonce before adding.
  $action = isset($_POST['action']) ? sanitize_key(wp_unslash($_POST['action'])) : '';
  if (!in_array($action, ['lty_process_lucky_dip', 'lty_regenerate_lucky_dip_add_to_cart'], true)) {
    return;
  }

  $done = true;
  nera_prize_addons_sync_from_request((int) $product_id, (string) $cart_item_key);
}
add_action('woocommerce_add_to_cart', 'nera_prize_addons_sync_from_lucky_dip', 20, 2);

/**
 * Save the add-ons ticked in a Lucky Dip popup once its tickets are already in the basket.
 *
 * After "add directly" the tickets are in the basket before the customer sees the popup,
 * so ticking there has no add-to-cart request to ride on: the script (lucky-dip-addons.js)
 * calls this instead. Same rules as everywhere else: the server prices and validates the
 * line, an empty selection removes it, and it needs tickets for the draw to be in the
 * basket.
 *
 * @return void
 */
function nera_prize_addons_ajax_save_selection(): void
{
  // phpcs:disable WordPress.Security.NonceVerification.Missing -- changes only the caller's own basket, like add to cart.
  $draw_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
  $raw = isset($_POST['nera_addon_ids']) ? (array) wp_unslash($_POST['nera_addon_ids']) : [];
  // phpcs:enable

  if (
    !$draw_id ||
    !nera_prize_addons_site_enabled() ||
    !function_exists('WC') ||
    !WC()->cart ||
    !nera_prize_addons_draw_has_tickets($draw_id)
  ) {
    wp_send_json(['ok' => false]);
  }

  $ids = array_values(array_unique(array_filter(array_map('sanitize_key', array_map('strval', $raw)))));
  $years = nera_prize_addons_years_from_request($ids);
  nera_prize_addons_set_cart_selection($draw_id, $ids, $years);
  WC()->cart->calculate_totals();
  if (WC()->session) {
    WC()->session->save_data();
  }

  $line = nera_prize_addons_find_cart_line($draw_id);
  wp_send_json([
    'ok' => true,
    'selected' => $line ? array_values((array) $line[1][NERA_PRIZE_ADDON_CART_KEY]['option_ids']) : [],
    // The years actually saved (clamped by quote() inside set_cart_selection()),
    // not merely what was posted — the Alpine UI reconciles against this.
    'years' => $line ? (object) ($line[1][NERA_PRIZE_ADDON_CART_KEY]['option_years'] ?? []) : (object) [],
  ]);
}
add_action('wp_ajax_nera_prize_addons_save', 'nera_prize_addons_ajax_save_selection');
add_action('wp_ajax_nopriv_nera_prize_addons_save', 'nera_prize_addons_ajax_save_selection');

/**
 * Make the cart's add-on line for a draw match a selection.
 *
 * One line per draw: an existing line is updated, an empty selection removes it.
 * The years actually stored are quote()'s own clamped values (docs/adr/0015),
 * never the raw posted years, so the cart can never hold an out-of-range term.
 *
 * @param int                 $draw_id Lottery product ID.
 * @param string[]            $ids     Chosen option IDs.
 * @param array<string,int>   $years   Years per ID (missing id => 1); unchanged
 *                                     callers passing none still work.
 * @return void
 */
function nera_prize_addons_set_cart_selection(int $draw_id, array $ids, array $years = []): void
{
  if (!function_exists('WC') || !WC()->cart) {
    return;
  }

  $line = nera_prize_addons_find_cart_line($draw_id);
  $config = nera_prize_addons_config($draw_id);
  $selection = nera_prize_addons_years_map($ids, $years);
  $quote = $config['enabled']
    ? nera_prize_addons_quote($draw_id, $selection, nera_prize_addons_current_user_purchased($draw_id))
    : ['options' => []];
  $valid_ids = array_keys($quote['options']);

  if (empty($valid_ids)) {
    if ($line) {
      WC()->cart->remove_cart_item($line[0]);
    }
    return;
  }

  $valid_years = [];
  foreach ($quote['options'] as $id => $option) {
    $valid_years[$id] = $option['years'];
  }

  if ($line) {
    WC()->cart->cart_contents[$line[0]][NERA_PRIZE_ADDON_CART_KEY]['option_ids'] = $valid_ids;
    WC()->cart->cart_contents[$line[0]][NERA_PRIZE_ADDON_CART_KEY]['option_years'] = $valid_years;
  } else {
    $addon_product_id = nera_prize_addons_ensure_product();
    if (!$addon_product_id) {
      return;
    }
    nera_prize_addons_internal_add(true);
    WC()->cart->add_to_cart($addon_product_id, 1, 0, [], [
      NERA_PRIZE_ADDON_CART_KEY => ['draw_id' => $draw_id, 'option_ids' => $valid_ids, 'option_years' => $valid_years],
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
    $keep_ids = (array) (WC()->cart->cart_contents[$keep][NERA_PRIZE_ADDON_CART_KEY]['option_ids'] ?? []);
    $keep_years = (array) (WC()->cart->cart_contents[$keep][NERA_PRIZE_ADDON_CART_KEY]['option_years'] ?? []);
    $item_ids = (array) ($item[NERA_PRIZE_ADDON_CART_KEY]['option_ids'] ?? []);
    $item_years = (array) ($item[NERA_PRIZE_ADDON_CART_KEY]['option_years'] ?? []);

    $merged = array_values(array_unique(array_merge($keep_ids, $item_ids)));
    // Same option on both lines (guest cart merged into a signed-in one, say):
    // keep whichever term is longer rather than silently shortening one the
    // customer already chose (docs/adr/0015).
    $merged_years = [];
    foreach ($merged as $id) {
      $merged_years[$id] = max((int) ($keep_years[$id] ?? 1), (int) ($item_years[$id] ?? 1));
    }

    WC()->cart->cart_contents[$keep][NERA_PRIZE_ADDON_CART_KEY]['option_ids'] = $merged;
    WC()->cart->cart_contents[$keep][NERA_PRIZE_ADDON_CART_KEY]['option_years'] = $merged_years;
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
      $selection = nera_prize_addons_years_map(
        (array) ($item[NERA_PRIZE_ADDON_CART_KEY]['option_ids'] ?? []),
        (array) ($item[NERA_PRIZE_ADDON_CART_KEY]['option_years'] ?? [])
      );
      $quote = nera_prize_addons_quote($draw_id, $selection, nera_prize_addons_current_user_purchased($draw_id));
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

    $stored_ids = (array) ($item[NERA_PRIZE_ADDON_CART_KEY]['option_ids'] ?? []);
    $stored_years = (array) ($item[NERA_PRIZE_ADDON_CART_KEY]['option_years'] ?? []);
    $quote = nera_prize_addons_quote(
      $draw_id,
      nera_prize_addons_years_map($stored_ids, $stored_years),
      nera_prize_addons_current_user_purchased($draw_id)
    );

    // A surviving option whose clamped year differs from what was stored means
    // the switch was turned off, or the Maximum term was lowered, since this
    // line was last saved (docs/adr/0015) — reprice and say so, same tone as
    // an option dropped outright.
    $terms_clamped = false;
    foreach ($quote['options'] as $option_id => $option) {
      if ((int) ($stored_years[$option_id] ?? 1) !== (int) $option['years']) {
        $terms_clamped = true;
        break;
      }
    }

    if (empty($quote['dropped']) && !$terms_clamped) {
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

    if ($terms_clamped) {
      wc_add_notice(sprintf(
        /* translators: %s: prize name */
        __('An add-on term for %s was adjusted to fit what is currently allowed, and your basket total has been updated.', 'nera-competitions'),
        $draw_name
      ), $type);
    }

    if (empty($quote['options'])) {
      WC()->cart->remove_cart_item($key);
    } else {
      $years_out = [];
      foreach ($quote['options'] as $option_id => $option) {
        $years_out[$option_id] = $option['years'];
      }
      WC()->cart->cart_contents[$key][NERA_PRIZE_ADDON_CART_KEY]['option_ids'] = array_keys($quote['options']);
      WC()->cart->cart_contents[$key][NERA_PRIZE_ADDON_CART_KEY]['option_years'] = $years_out;
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
 * Remove one option from a draw's add-on line (the × on an option in the basket).
 *
 * Mirrors WooCommerce's own `?remove_item=` link: a GET carrying the cart nonce,
 * handled on wp_loaded (after WooCommerce's handler at priority 20), then a redirect
 * back to the basket. Removing the last option removes the whole line. Dropping an
 * option from a Full bundle simply reprices the rest at their own prices.
 *
 * @return void
 */
function nera_prize_addons_handle_remove_option(): void
{
  // phpcs:disable WordPress.Security.NonceVerification.Recommended -- nonce checked below.
  if (empty($_GET['nera_remove_addon']) || empty($_GET['nera_addon_line']) || !function_exists('WC') || !WC()->cart) {
    return;
  }
  $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
  if (!wp_verify_nonce($nonce, 'woocommerce-cart')) {
    return;
  }
  $line_key = sanitize_text_field(wp_unslash($_GET['nera_addon_line']));
  $option_id = sanitize_key(wp_unslash($_GET['nera_remove_addon']));
  // phpcs:enable

  $cart = WC()->cart;
  $item = $cart->get_cart_item($line_key);
  if ($item && nera_prize_addons_is_addon_cart_item($item)) {
    $left = array_values(array_diff((array) ($item[NERA_PRIZE_ADDON_CART_KEY]['option_ids'] ?? []), [$option_id]));
    if (empty($left)) {
      $cart->remove_cart_item($line_key);
    } else {
      $years = (array) ($item[NERA_PRIZE_ADDON_CART_KEY]['option_years'] ?? []);
      unset($years[$option_id]);
      $cart->cart_contents[$line_key][NERA_PRIZE_ADDON_CART_KEY]['option_ids'] = $left;
      $cart->cart_contents[$line_key][NERA_PRIZE_ADDON_CART_KEY]['option_years'] = $years;
    }
    $cart->calculate_totals();
  }

  wp_safe_redirect(wc_get_cart_url());
  exit;
}
add_action('wp_loaded', 'nera_prize_addons_handle_remove_option', 25);

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
  $selection = nera_prize_addons_years_map(
    (array) ($cart_item[NERA_PRIZE_ADDON_CART_KEY]['option_ids'] ?? []),
    (array) ($cart_item[NERA_PRIZE_ADDON_CART_KEY]['option_years'] ?? [])
  );
  $quote = nera_prize_addons_quote($draw_id, $selection, nera_prize_addons_current_user_purchased($draw_id));

  // "Title (× N Years)" — only when a term is actually more than one year, so
  // a prize with Term choice off (every option 1 year) reads exactly as
  // before (docs/adr/0015).
  $option_labels = array_map(static function (array $option): string {
    return $option['years'] > 1
      ? sprintf('%1$s (× %2$d %3$s)', $option['title'], $option['years'], nera_prize_addons_label('years'))
      : $option['title'];
  }, $quote['options']);

  $item_data[] = [
    'key' => nera_prize_addons_label('meta_options'),
    'value' => implode(', ', $option_labels),
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
