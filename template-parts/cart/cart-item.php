<?php
/**
 * Cart Item Template Part
 *
 * Compact layout on mobile; original layout on desktop (md+).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$cart_item_key = $args['cart_item_key'] ?? '';
$cart_item = $args['cart_item'] ?? [];
$product = $args['product'] ?? null;

if (!$product || !$cart_item_key) {
  return;
}

$product_id = $product->get_id();
$product_name = $product->get_name();
$product_price = $product->get_price(); // Unit price
$item_subtotal = $cart_item['line_subtotal'];
$quantity = $cart_item['quantity'];

// Check if product is lottery type
$is_lottery = $product->is_type('lottery');

// Get lottery data if available
$end_date = get_post_meta($product_id, '_lty_end_date_gmt', true);

// Determine if quantity can be edited (only for lottery products)
$can_edit_qty = false;
$min_per_order = 1;
$max_per_order = 100;

if ($is_lottery) {
  // Check for manual tickets or fixed quantity products
  $is_manual_ticket = method_exists($product, 'is_manual_ticket') && $product->is_manual_ticket();
  $is_predefined =
    method_exists($product, 'is_predefined_button_enabled') &&
    $product->is_predefined_button_enabled();
  $can_display_with_selector =
    !$is_predefined ||
    (method_exists($product, 'can_display_predefined_with_quantity_selector') &&
      $product->can_display_predefined_with_quantity_selector());

  // Use product methods to get min/max (handles guest mode, stock, user limits)
  $min_per_order = method_exists($product, 'get_min_purchase_quantity')
    ? $product->get_min_purchase_quantity()
    : 1;
  $max_per_order = method_exists($product, 'get_max_purchase_quantity')
    ? $product->get_max_purchase_quantity()
    : 100;

  // LFW can return empty max — cast so Alpine x-data never gets `max:  ` (invalid JS).
  $min_per_order = max(1, (int) $min_per_order);
  $max_per_order = (int) $max_per_order;
  if ($max_per_order < 1) {
    $stock_qty = $product->get_stock_quantity();
    $max_per_order = $stock_qty !== null && (int) $stock_qty > 0 ? (int) $stock_qty : 9999;
  }
  if ($max_per_order < $min_per_order) {
    $max_per_order = $min_per_order;
  }

  // Allow editing if: not manual ticket, allows quantity selector, and min != max
  $can_edit_qty =
    !$is_manual_ticket && $can_display_with_selector && $min_per_order !== $max_per_order;
}

// Thumbnail
$thumbnail = $product->get_image([280, 280], ['class' => 'w-full h-full object-contain']);
$answers = isset($cart_item['lty_lottery']['answers']) ? $cart_item['lty_lottery']['answers'] : '';
?>

<div class="ncs-cart-item relative"
  id="cart-item-<?php echo esc_attr($cart_item_key); ?>"
  data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
  data-product-id="<?php echo esc_attr($product_id); ?>">

  <!-- Mobile: compact 2-row layout -->
  <div class="cart-item-mobile flex flex-col gap-3 py-3 md:hidden">
    <div class="flex gap-3 items-center">
      <a href="<?php echo esc_url($product->get_permalink()); ?>"
        class="w-16 h-16 rounded-lg overflow-hidden bg-gray-50 flex-shrink-0 relative group block">
        <?php echo $thumbnail; ?>
        <span class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity"></span>
      </a>
      <div class="flex-1 min-w-0">
        <h3 class="font-semibold text-base text-text-primary leading-tight truncate">
          <a href="<?php echo esc_url(
            $product->get_permalink(),
          ); ?>" class="hover:text-primary transition-colors">
            <?php echo wp_kses_post($product_name); ?>
          </a>
        </h3>
        <div class="flex items-center gap-2 text-xs text-text-secondary flex-wrap mt-0.5">
          <span class="inline-flex items-center gap-1 bg-gray-50 px-2 py-0.5 rounded"><?php echo wc_price(
            $product_price,
          ); ?> / ticket</span>
          <?php if ($end_date): ?>
            <span class="flex items-center gap-1 <?php echo strtotime($end_date) < time()
              ? 'text-danger'
              : ''; ?>">
              <span class="material-symbols-outlined text-xs">schedule</span>
              <?php echo date_i18n(get_option('date_format'), strtotime($end_date)); ?>
            </span>
          <?php endif; ?>
          <?php if ($answers): ?>
            <span class="text-text-secondary"><?php echo esc_html__(
              'Answer:',
              'nera-competitions',
            ); ?> <?php echo esc_html($answers); ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="flex items-center justify-between gap-2">
      <?php if ($can_edit_qty): ?>
        <div class="flex items-center gap-2" x-data="{ qty: <?php echo esc_js(
          $quantity,
        ); ?>, min: <?php echo esc_js($min_per_order); ?>, max: <?php echo esc_js(
  $max_per_order,
); ?> }">
          <div class="nera-cart-quantity h-9">
            <button type="button" class="w-7 h-full flex items-center justify-center text-text-secondary hover:text-primary hover:bg-gray-50 transition-colors" @click="if(qty > min) { qty--; }">
              <span class="material-symbols-outlined text-sm">remove</span>
            </button>
            <input type="number" name="cart[<?php echo esc_attr(
              $cart_item_key,
            ); ?>][qty]" value="<?php echo esc_attr($quantity); ?>" min="<?php echo esc_attr(
  $min_per_order,
); ?>" max="<?php echo esc_attr(
  $max_per_order,
); ?>" class="w-10 h-full text-center border-x border-gray-200 text-sm font-semibold focus:outline-none qty" x-model="qty">
            <button type="button" class="w-7 h-full flex items-center justify-center text-text-secondary hover:text-primary hover:bg-gray-50 transition-colors" @click="if(qty < max) { qty++; }">
              <span class="material-symbols-outlined text-sm">add</span>
            </button>
          </div>
        </div>
      <?php else: ?>
        <div class="inline-flex items-center gap-1.5 bg-gray-50 px-2 py-1 rounded text-xs">
          <span class="text-text-secondary"><?php _e('Qty:', 'nera-competitions'); ?></span>
          <span class="font-bold text-text-primary"><?php echo esc_html($quantity); ?></span>
        </div>
        <input type="hidden" name="cart[<?php echo esc_attr(
          $cart_item_key,
        ); ?>][qty]" value="<?php echo esc_attr($quantity); ?>">
      <?php endif; ?>
      <span class="text-base font-bold text-primary whitespace-nowrap"><?php echo WC()->cart->get_product_subtotal(
        $product,
        $quantity,
      ); ?></span>
      <button type="button" class="p-1.5 text-gray-400 hover:text-danger hover:bg-danger-bg rounded-lg transition-all" aria-label="<?php esc_attr_e(
        'Remove item',
        'nera-competitions',
      ); ?>" onclick="NeraCart.removeItem('<?php echo esc_js($cart_item_key); ?>')">
        <span class="material-symbols-outlined text-lg">delete</span>
      </button>
    </div>
  </div>

  <!-- Desktop: original layout with large image and card styling -->
  <div class="cart-item-desktop hidden md:flex flex-col md:flex-row gap-4 md:items-center">
    <div class="w-36 h-36 rounded-xl overflow-hidden bg-gray-50 flex-shrink-0 relative group">
      <?php echo $thumbnail; ?>
      <a href="<?php echo esc_url(
        $product->get_permalink(),
      ); ?>" class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity"></a>
    </div>
    <div class="flex-grow">
      <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-2">
        <div>
          <h3 class="font-bold text-lg text-text-primary leading-tight mb-1">
            <a href="<?php echo esc_url(
              $product->get_permalink(),
            ); ?>" class="hover:text-primary transition-colors">
              <?php echo wp_kses_post($product_name); ?>
            </a>
          </h3>
          <div class="flex flex-wrap items-center gap-3 text-sm text-text-secondary">
            <span class="inline-flex items-center gap-1 bg-gray-50 px-2 py-0.5 rounded text-xs">
              <?php echo wc_price($product_price); ?> / ticket
            </span>
            <?php if ($end_date): ?>
              <span class="flex items-center gap-1 text-xs <?php echo strtotime($end_date) < time()
                ? 'text-danger'
                : 'text-text-secondary'; ?>">
                <span class="material-symbols-outlined text-sm">schedule</span>
                <?php echo date_i18n(get_option('date_format'), strtotime($end_date)); ?>
              </span>
            <?php endif; ?>
          </div>
          <?php if ($answers): ?>
            <div class="text-xs text-text-secondary mt-1"><span class="font-medium">Selected Answer:</span> <?php echo esc_html(
              $answers,
            ); ?></div>
          <?php endif; ?>
        </div>
        <button type="button" class="md:ml-auto p-2 text-gray-400 hover:text-danger hover:bg-danger-bg rounded-lg transition-all" aria-label="<?php esc_attr_e(
          'Remove item',
          'nera-competitions',
        ); ?>" onclick="NeraCart.removeItem('<?php echo esc_js($cart_item_key); ?>')">
          <span class="material-symbols-outlined text-xl">delete</span>
        </button>
      </div>
      <div class="mt-4 flex flex-col md:flex-row items-center justify-between gap-4">
        <?php if ($can_edit_qty): ?>
          <div class="flex items-center gap-3 w-full md:w-auto" x-data="{ qty: <?php echo esc_js(
            $quantity,
          ); ?>, min: <?php echo esc_js($min_per_order); ?>, max: <?php echo esc_js(
  $max_per_order,
); ?> }">
            <div class="nera-cart-quantity h-10">
              <button type="button" class="w-8 h-full flex items-center justify-center text-text-secondary hover:text-primary hover:bg-gray-50 transition-colors" @click="if(qty > min) { qty--; }">
                <span class="material-symbols-outlined text-sm">remove</span>
              </button>
              <input type="number" name="cart[<?php echo esc_attr(
                $cart_item_key,
              ); ?>][qty]" value="<?php echo esc_attr($quantity); ?>" min="<?php echo esc_attr(
  $min_per_order,
); ?>" max="<?php echo esc_attr(
  $max_per_order,
); ?>" class="w-12 h-full text-center border-x border-gray-200 text-sm font-semibold focus:outline-none qty" x-model="qty">
              <button type="button" class="w-8 h-full flex items-center justify-center text-text-secondary hover:text-primary hover:bg-gray-50 transition-colors" @click="if(qty < max) { qty++; }">
                <span class="material-symbols-outlined text-sm">add</span>
              </button>
            </div>
            <div class="flex items-center gap-1">
              <button type="button" class="px-2 py-1 text-xs font-semibold text-primary bg-primary/5 hover:bg-primary/10 rounded transition-colors" @click="let newQty = Math.min(qty + 5, max); qty = newQty;">+5</button>
              <button type="button" class="px-2 py-1 text-xs font-semibold text-primary bg-primary/5 hover:bg-primary/10 rounded transition-colors" @click="let newQty = Math.min(qty + 10, max); qty = newQty;">+10</button>
            </div>
          </div>
        <?php else: ?>
          <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="inline-flex items-center gap-2 bg-gray-50 px-4 py-2 rounded-lg">
              <span class="text-sm text-text-secondary"><?php _e(
                'Quantity:',
                'nera-competitions',
              ); ?></span>
              <span class="text-sm font-bold text-text-primary"><?php echo esc_html(
                $quantity,
              ); ?></span>
            </div>
            <input type="hidden" name="cart[<?php echo esc_attr(
              $cart_item_key,
            ); ?>][qty]" value="<?php echo esc_attr($quantity); ?>">
          </div>
        <?php endif; ?>
        <div class="text-right w-full md:w-auto">
          <span class="block text-xs text-text-secondary md:hidden mb-1"><?php _e(
            'Total',
            'nera-competitions',
          ); ?></span>
          <span class="text-xl font-bold text-primary"><?php echo WC()->cart->get_product_subtotal(
            $product,
            $quantity,
          ); ?></span>
        </div>
      </div>
    </div>
  </div>
</div>
