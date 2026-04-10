<?php
/**
 * Order details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Nera_Competitions
 * @version 10.1.0
 *
 * @var int  $order_id       Order ID.
 * @var bool $show_downloads Controls whether the downloads table should be rendered.
 */

defined('ABSPATH') || exit();

$order = wc_get_order($order_id);

if (!$order) {
  return;
}

$order_items = $order->get_items(
  apply_filters('woocommerce_purchase_order_item_types', 'line_item'),
);
$show_purchase_note = $order->has_status(
  apply_filters('woocommerce_purchase_note_order_statuses', ['completed', 'processing']),
);
$downloads = $order->get_downloadable_items();
$actions = array_filter(
  wc_get_account_orders_actions($order),
  function ($key) {
    return 'view' !== $key;
  },
  ARRAY_FILTER_USE_KEY,
);

$show_customer_details = $order->get_user_id() === get_current_user_id();

if ($show_downloads) {
  wc_get_template('order/order-downloads.php', [
    'downloads' => $downloads,
    'show_title' => true,
  ]);
}

ob_start();
do_action('woocommerce_after_order_details', $order);
$after_order_details_html = trim((string) ob_get_clean());
?>

<?php if ($after_order_details_html !== ''): ?>
  <div class="w-full mb-6 rounded-2xl border border-primary/25 bg-gradient-to-r from-primary/[0.07] via-indigo-500/[0.06] to-emerald-500/[0.08] p-4 sm:p-6 shadow-[0_20px_45px_-28px_rgba(19,19,236,0.55)] [&>*]:w-full [&>*]:max-w-none [&_a]:inline-flex [&_a]:items-center [&_a]:justify-center [&_a]:gap-2 [&_a]:w-full [&_a]:rounded-xl [&_a]:border [&_a]:border-primary/20 [&_a]:bg-white/95 [&_a]:px-5 [&_a]:py-3.5 [&_a]:text-[1.05rem] [&_a]:font-extrabold [&_a]:leading-tight [&_a]:text-primary [&_a]:shadow-[0_14px_35px_-24px_rgba(19,19,236,0.75)] [&_a]:transition-all [&_a]:duration-200 [&_a:hover]:-translate-y-0.5 [&_a:hover]:bg-primary [&_a:hover]:text-white [&_a:hover]:shadow-[0_20px_45px_-25px_rgba(19,19,236,0.8)] [&_a:focus-visible]:outline-none [&_a:focus-visible]:ring-2 [&_a:focus-visible]:ring-primary/45 [&_a:focus-visible]:ring-offset-2 sm:[&_a]:w-auto [&_h2]:mb-2 [&_h3]:mb-2 [&_h4]:mb-2 [&_h2]:text-base [&_h3]:text-base [&_h4]:text-base [&_h2]:font-bold [&_h3]:font-bold [&_h4]:font-bold [&_h2]:text-text-primary [&_h3]:text-text-primary [&_h4]:text-text-primary">
    <?php echo $after_order_details_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
  </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Order Details Card -->
  <section class="woocommerce-order-details lg:col-span-2">
    <?php do_action('woocommerce_order_details_before_order_table', $order); ?>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
      <h2 class="woocommerce-order-details__title text-xl font-bold text-gray-900 flex items-center gap-2 mb-6">
        <span class="material-symbols-outlined text-primary">shopping_bag</span>
        <?php esc_html_e('Order details', 'woocommerce'); ?>
      </h2>

      <div class="space-y-4">
        <?php
        do_action('woocommerce_order_details_before_order_table_items', $order);

        foreach ($order_items as $item_id => $item) {
          $product = $item->get_product();

          wc_get_template('order/order-details-item.php', [
            'order' => $order,
            'item_id' => $item_id,
            'item' => $item,
            'show_purchase_note' => $show_purchase_note,
            'purchase_note' => $product ? $product->get_purchase_note() : '',
            'product' => $product,
          ]);
        }

        do_action('woocommerce_order_details_after_order_table_items', $order);
        ?>
      </div>

      <!-- Order Totals -->
      <div class="mt-6 pt-6 border-t border-gray-200 space-y-3">
        <?php foreach ($order->get_order_item_totals() as $key => $total) {
          $is_total = 'order_total' === $key; ?>
          <div class="flex justify-between items-center <?php echo $is_total
            ? 'text-lg font-bold text-gray-900'
            : 'text-gray-700'; ?>">
            <span><?php echo esc_html($total['label']); ?></span>
            <span><?php echo wp_kses_post($total['value']); ?></span>
          </div>
        <?php
        } ?>
      </div>

      <?php if ($order->get_customer_note()): ?>
        <div class="mt-6 pt-6 border-t border-gray-200">
          <p class="text-sm font-semibold text-gray-700 mb-1"><?php esc_html_e(
            'Note:',
            'woocommerce',
          ); ?></p>
          <?php
          $customer_note = wc_wptexturize_order_note($order->get_customer_note());
          echo '<p class="text-gray-600 text-sm">' .
            wp_kses(nl2br($customer_note), ['br' => []]) .
            '</p>';
          ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($actions)): ?>
        <div class="mt-6 pt-6 border-t border-gray-200">
          <p class="text-sm font-semibold text-gray-700 mb-3"><?php esc_html_e(
            'Actions',
            'woocommerce',
          ); ?>:</p>
          <div class="flex flex-wrap gap-3">
            <?php
            $wp_button_class = wc_wp_theme_get_element_class_name('button')
              ? ' ' . wc_wp_theme_get_element_class_name('button')
              : '';
            foreach ($actions as $key => $action) {

              $action_aria_label = !empty($action['aria-label'])
                ? $action['aria-label']
                : sprintf(
                  __('%1$s order number %2$s', 'woocommerce'),
                  $action['name'],
                  $order->get_order_number(),
                );
              $btn_class =
                'inline-flex items-center gap-2 px-4 py-2 rounded-xl font-medium transition-all text-sm';
              $btn_class .=
                'pay' === $key
                  ? ' bg-gradient-to-r from-primary to-indigo-600 text-white hover:opacity-90 shadow-sm'
                  : ' bg-gray-100 text-gray-700 hover:bg-gray-200';
              ?>
              <a href="<?php echo esc_url($action['url']); ?>"
                 class="<?php echo esc_attr($btn_class); ?> woocommerce-button<?php echo esc_attr(
   $wp_button_class,
 ); ?> button <?php echo esc_attr(sanitize_html_class($key)); ?> order-actions-button"
                 aria-label="<?php echo esc_attr($action_aria_label); ?>">
                <?php echo esc_html($action['name']); ?>
              </a>
            <?php
            }
            ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <?php do_action('woocommerce_order_details_after_order_table', $order); ?>
  </section>

  <?php
  if ($show_customer_details) {
    wc_get_template('order/order-details-customer.php', ['order' => $order]);
  }
  ?>
</div>
