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
 * Expected $args keys: cart_item_key, cart_item, product; optional readonly (bool)
 * and compact (bool): the checkout order review shows the same panel smaller, aligned with the
 * product title, without remove buttons or the qty field.
 */

if (!defined('ABSPATH')) {
  exit();
}

$cart_item_key = $args['cart_item_key'] ?? '';
$cart_item = $args['cart_item'] ?? [];
$readonly = !empty($args['readonly']);
$compact = !empty($args['compact']);

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

<div class="ncs-cart-item ncs-cart-item--addon<?php echo $compact ? ' ncs-cart-item--addon-compact' : ''; ?> relative"
  <?php if (!$readonly): ?>
    id="cart-item-<?php echo esc_attr($cart_item_key); ?>"
    data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
  <?php endif; ?>
  data-product-id="<?php echo esc_attr($draw_id); ?>">
  <div class="ncs-cart-addon<?php echo $compact ? ' ncs-cart-addon--compact' : ''; ?> flex flex-col">
    <?php
    /*
     * The header is a grid whose parts are direct children, so CSS decides where each goes
     * (frontend/src/main.css, ".ncs-cart-addon__head"). In a narrow panel (a phone, or the checkout
     * sidebar) it is two rows: icon + "Add-ons" (+ remove) first, then the Full bundle tag and the
     * price. In a wide one everything sits on a single line. The option chips are always below.
     */
    ?>
    <div class="ncs-cart-addon__head">
      <?php // The icon is the identity of the panel; the checkout's compact one is just smaller. ?>
      <span class="ncs-cart-addon__icon <?php echo $compact ? 'w-6 h-6 rounded-md' : 'w-8 h-8 rounded-lg'; ?> flex items-center justify-center flex-shrink-0" aria-hidden="true">
        <span class="material-symbols-outlined <?php echo $compact ? 'text-base' : 'text-lg'; ?>">playlist_add_check</span>
      </span>

      <?php
      /*
       * Just "Add-ons": the panel sits directly under its prize's tickets, so repeating the
       * prize name only made the title wrap over several lines on a phone. The full name is
       * still what the remove button announces, and the link still leads to the prize.
       */
      ?>
      <h3 class="ncs-cart-addon__title m-0 font-semibold text-sm text-text-primary leading-tight">
        <?php if ($draw): ?>
          <a href="<?php echo esc_url($draw->get_permalink()); ?>" class="hover:text-primary transition-colors"><?php echo esc_html(nera_prize_addons_label('line_short')); ?></a>
        <?php else: ?>
          <?php echo esc_html(nera_prize_addons_label('line_short')); ?>
        <?php endif; ?>
      </h3>

      <?php if ($quote['full_bundle']): ?>
        <span class="ncs-cart-addon__tag inline-flex items-center text-[10px] font-bold uppercase leading-none tracking-wider px-2 py-1 rounded-md whitespace-nowrap"><?php echo esc_html(nera_prize_addons_label('meta_full_bundle')); ?></span>
      <?php endif; ?>

      <span class="ncs-cart-addon__price inline-flex items-center text-base font-bold leading-none text-primary whitespace-nowrap tabular-nums">
        <?php if ($quote['full_bundle']): ?>
          <s class="text-xs font-medium text-gray-400 mr-1"><?php echo wp_kses_post(wc_price($quote['subtotal'])); ?></s>
        <?php endif; ?>
        <?php echo wp_kses_post(wc_price($subtotal)); ?>
      </span>

      <?php if (!$readonly): ?>
      <button type="button" class="ncs-cart-addon__remove inline-flex items-center justify-center p-1 text-gray-400 hover:text-danger hover:bg-danger-bg rounded-lg transition-all" aria-label="<?php echo esc_attr(
        sprintf(
          /* translators: %s: add-on line name */
          __('Remove %s', 'nera-competitions'),
          $line_name,
        ),
      ); ?>" onclick="NeraCart.removeItem('<?php echo esc_js($cart_item_key); ?>')">
        <span class="material-symbols-outlined text-lg leading-none">delete</span>
      </button>
      <?php endif; ?>
    </div>

    <?php if (!empty($quote['options'])): ?>
      <ul class="ncs-cart-addon__chips" role="list">
        <?php foreach ($quote['options'] as $option): ?>
          <li class="ncs-cart-addon__chip">
            <?php echo esc_html($option['title']); ?>
            <span class="tabular-nums"><?php echo wp_kses_post(wc_price($option['price'])); ?></span>
            <?php if (!$readonly): ?>
            <button type="button" class="ncs-cart-addon__chip-remove" aria-label="<?php echo esc_attr(
              sprintf(
                /* translators: %s: add-on option name */
                __('Remove %s', 'nera-competitions'),
                $option['title'],
              ),
            ); ?>" onclick="NeraCart.removeAddonOption('<?php echo esc_js($cart_item_key); ?>', '<?php echo esc_js($option['id']); ?>')">
              <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php // An add-on is bought once per draw, so there is no quantity to show; the field keeps the cart form's quantity at 1. ?>
    <?php if (!$readonly): ?>
      <input type="hidden" name="cart[<?php echo esc_attr($cart_item_key); ?>][qty]" value="1">
    <?php endif; ?>
  </div>
</div>
