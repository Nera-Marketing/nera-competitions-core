<?php
/**
 * Order Item Details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details-item.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Nera_Competitions
 * @version 5.2.0
 */

if (!defined('ABSPATH')) {
  exit();
}

if (!apply_filters('woocommerce_order_item_visible', true, $item)) {
  return;
}

$product = isset($product) ? $product : null;
$is_visible = $product && $product->is_visible();
$product_permalink = apply_filters(
  'woocommerce_order_item_permalink',
  $is_visible ? $product->get_permalink($item) : '',
  $item,
  $order,
);
$qty = $item->get_quantity();
$refunded_qty = $order->get_qty_refunded_for_item($item_id);
$qty_display = $refunded_qty
  ? '<del>' . esc_html($qty) . '</del> <ins>' . esc_html($qty - $refunded_qty * -1) . '</ins>'
  : esc_html($qty);
$item_class = apply_filters(
  'woocommerce_order_item_class',
  'woocommerce-table__line-item order_item',
  $item,
  $order,
);
?>

<div class="<?php echo esc_attr(
  $item_class,
); ?> flex flex-col sm:flex-row sm:items-center gap-4 p-4 bg-gray-50 rounded-xl">
  <?php if ($product && $product->exists() && $product->get_image_id()): ?>
    <div class="w-16 h-16 flex-shrink-0 bg-white rounded-lg overflow-hidden border border-gray-200">
      <?php echo wp_kses_post($product->get_image('thumbnail')); ?>
    </div>
  <?php endif; ?>

  <div class="flex-1 min-w-0">
    <h3 class="font-semibold text-gray-900 mb-1">
      <?php echo wp_kses_post(
        apply_filters(
          'woocommerce_order_item_name',
          $product_permalink
            ? sprintf(
              '<a href="%s" class="text-primary hover:underline">%s</a>',
              esc_url($product_permalink),
              $item->get_name(),
            )
            : $item->get_name(),
          $item,
          $is_visible,
        ),
      ); ?>
      <span class="product-quantity font-normal text-gray-600"> &times; <?php echo wp_kses_post(
        $qty_display,
      ); ?></span>
    </h3>

    <?php
    do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, false);

    ob_start();
    wc_display_item_meta($item);
    $item_meta = ob_get_clean();
    if ($item_meta): ?>
      <div class="mt-2 text-sm text-gray-600 [&_.variation]:flex [&_.variation]:flex-wrap [&_.variation]:gap-x-2 [&_.variation]:gap-y-1 [&_.variation_dt]:font-medium [&_.variation_dt]:text-gray-700 [&_.variation_dd]:m-0">
        <?php echo wp_kses_post($item_meta); ?>
      </div>
    <?php endif;

    do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, false);
    ?>
  </div>

  <div class="sm:text-right flex-shrink-0">
    <p class="font-bold text-gray-900 text-lg"><?php echo wp_kses_post(
      $order->get_formatted_line_subtotal($item),
    ); ?></p>
  </div>
</div>

<?php if (!empty($show_purchase_note) && !empty($purchase_note)): ?>
  <div class="woocommerce-table__product-purchase-note product-purchase-note p-4 bg-blue-50 rounded-xl border border-blue-100 mt-2">
    <?php echo wpautop(do_shortcode(wp_kses_post($purchase_note))); ?>
  </div>
<?php endif; ?>
