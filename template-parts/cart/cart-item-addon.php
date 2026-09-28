<?php
/**
 * Cart Item — Prize add-on line ("Add-ons for: …")
 *
 * One line per draw, sitting under that draw's tickets. Quantity is always 1
 * and the price comes from the server (inc/prize-addons.php). Removing it
 * keeps the tickets; removing the draw's tickets removes it.
 *
 * @package Nera_Competitions
 *
 * Expected $args keys: cart_item_key, cart_item, product.
 */

if (!defined('ABSPATH')) {
  exit();
}

$cart_item_key = $args['cart_item_key'] ?? '';
$cart_item = $args['cart_item'] ?? [];

if (!$cart_item_key || !nera_prize_addons_is_addon_cart_item($cart_item)) {
  return;
}

$draw_id = (int) $cart_item[NERA_PRIZE_ADDON_CART_KEY]['draw_id'];
$draw = wc_get_product($draw_id);
$line_name = sprintf(nera_prize_addons_label('line_name'), nera_prize_addons_draw_name($draw_id));
$quote = nera_prize_addons_quote(
  $draw_id,
  (array) ($cart_item[NERA_PRIZE_ADDON_CART_KEY]['option_ids'] ?? []),
  nera_prize_addons_current_user_purchased($draw_id)
);
$subtotal = (float) ($cart_item['line_subtotal'] ?? $quote['total']);
?>

<div class="ncs-cart-item ncs-cart-item--addon relative"
  id="cart-item-<?php echo esc_attr($cart_item_key); ?>"
  data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
  data-product-id="<?php echo esc_attr($draw_id); ?>">
  <div class="ncs-cart-addon flex gap-3 items-start py-3 md:py-2">
    <span class="ncs-cart-addon__icon w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" aria-hidden="true">
      <span class="material-symbols-outlined text-xl">playlist_add_check</span>
    </span>

    <div class="flex-1 min-w-0">
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <h3 class="font-semibold text-sm md:text-base text-text-primary leading-tight">
            <?php if ($draw): ?>
              <a href="<?php echo esc_url($draw->get_permalink()); ?>" class="hover:text-primary transition-colors"><?php echo esc_html($line_name); ?></a>
            <?php else: ?>
              <?php echo esc_html($line_name); ?>
            <?php endif; ?>
          </h3>
          <?php if ($quote['full_bundle']): ?>
            <span class="ncs-cart-addon__tag inline-block mt-1 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md"><?php echo esc_html(nera_prize_addons_label('meta_full_bundle')); ?></span>
          <?php endif; ?>
        </div>
        <button type="button" class="p-1.5 text-gray-400 hover:text-danger hover:bg-danger-bg rounded-lg transition-all flex-shrink-0" aria-label="<?php echo esc_attr(
          sprintf(
            /* translators: %s: add-on line name */
            __('Remove %s', 'nera-competitions'),
            $line_name,
          ),
        ); ?>" onclick="NeraCart.removeItem('<?php echo esc_js($cart_item_key); ?>')">
          <span class="material-symbols-outlined text-lg">delete</span>
        </button>
      </div>

      <?php if (!empty($quote['options'])): ?>
        <ul class="mt-1.5 flex flex-col gap-0.5 text-xs text-text-secondary" role="list">
          <?php foreach ($quote['options'] as $option): ?>
            <li class="flex justify-between gap-3">
              <span><?php echo esc_html($option['title']); ?></span>
              <span class="tabular-nums whitespace-nowrap"><?php echo wp_kses_post(wc_price($option['price'])); ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <div class="mt-2 flex items-center justify-between gap-3">
        <span class="inline-flex items-center gap-1.5 bg-gray-50 px-2 py-1 rounded text-xs">
          <span class="text-text-secondary"><?php esc_html_e('Qty:', 'nera-competitions'); ?></span>
          <span class="font-bold text-text-primary">1</span>
        </span>
        <input type="hidden" name="cart[<?php echo esc_attr($cart_item_key); ?>][qty]" value="1">
        <span class="text-base md:text-lg font-bold text-primary whitespace-nowrap tabular-nums">
          <?php if ($quote['full_bundle']): ?>
            <s class="text-xs font-medium text-gray-400 mr-1"><?php echo wp_kses_post(wc_price($quote['subtotal'])); ?></s>
          <?php endif; ?>
          <?php echo wp_kses_post(wc_price($subtotal)); ?>
        </span>
      </div>
    </div>
  </div>
</div>
