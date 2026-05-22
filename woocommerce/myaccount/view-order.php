<?php
/**
 * View Order
 *
 * Shows the details of a particular order on the account page.
 *
 * @package Nera Competitions Standard
 */

defined('ABSPATH') || exit();

$notes = $order->get_customer_order_notes();
?>

<div class="nera-view-order">
  
  <!-- Back Navigation -->
  <a href="<?php echo esc_url(wc_get_endpoint_url('orders')); ?>" 
     class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-primary transition-colors mb-6">
    <span class="material-symbols-outlined text-base mr-1">arrow_back</span>
    <?php esc_html_e('Back to orders', 'nera-competitions-standard'); ?>
  </a>

  <!-- Order Header Card -->
  <div class="bg-gradient-to-br from-primary via-primary to-primary-dark rounded-2xl shadow-xl p-5 sm:p-8 mb-8 relative overflow-hidden">
    <!-- Decorative background -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute top-0 right-0 w-64 h-64 bg-surface rounded-full blur-3xl transform translate-x-1/2 -translate-y-1/2"></div>
    </div>
    
    <div class="relative z-10">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <p class="text-secondary text-sm font-semibold mb-2">
            <?php esc_html_e('Order Details', 'nera-competitions-standard'); ?>
          </p>
          <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2">
           <?php printf(
             esc_html__('Order #%1$s', 'woocommerce'),
             '<span class="text-white">' . esc_html($order->get_order_number()) . '</span>',
           ); ?>
          </h1>
          <p class="text-secondary">
            <?php
            /* translators: 1: order date */
            printf(
              esc_html__('Placed on %1$s', 'woocommerce'),
              '<time datetime="' .
                esc_attr($order->get_date_created()->date('c')) .
                '">' .
                esc_html(wc_format_datetime($order->get_date_created())) .
                '</time>',
            );
            ?>
          </p>
        </div>
        
        <div class="flex flex-col gap-2">
          <?php
          $status = $order->get_status();
          $status_colors = [
            'completed' => 'bg-success text-white',
            'processing' => 'bg-primary text-white',
            'pending' => 'bg-warning text-white',
            'on-hold' => 'bg-warning text-white',
            'cancelled' => 'bg-danger text-white',
            'refunded' => 'bg-gray-500 text-white',
            'failed' => 'bg-danger text-white',
          ];
          $status_class = isset($status_colors[$status])
            ? $status_colors[$status]
            : 'bg-gray-500 text-white';
          ?>
          <span class="ncs-order-status ncs-order-status--<?php echo esc_attr($status); ?> px-4 py-2 rounded-xl font-bold <?php echo esc_attr(
            $status_class,
          ); ?> inline-flex items-center gap-2 justify-center md:justify-start">
            <span class="material-symbols-outlined text-xl">info</span>
            <?php echo esc_html(wc_get_order_status_name($status)); ?>
          </span>
          
          <?php if ($order->needs_payment()): ?>
            <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" 
               class="px-4 py-2 bg-surface text-primary rounded-xl font-bold hover:bg-secondary transition-all inline-flex items-center gap-2 justify-center">
              <span class="material-symbols-outlined text-xl">payment</span>
              <?php esc_html_e('Pay now', 'woocommerce'); ?>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Main Content Column -->
    <div class="lg:col-span-2 space-y-6">
      
      <!-- Order Items Card -->
      <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2 mb-6">
          <span class="material-symbols-outlined text-primary">shopping_bag</span>
          <?php esc_html_e('Order items', 'woocommerce'); ?>
        </h2>

        <div class="space-y-4">
          <?php
          do_action('woocommerce_order_details_before_order_table', $order);

          foreach ($order->get_items() as $item_id => $item) {

            $product = $item->get_product();
            if (!$product) {
              continue;
            }

            $product_name = $item->get_name();
            $quantity = $item->get_quantity();
            $total = $order->get_formatted_line_subtotal($item);
            $thumbnail = $product->get_image('thumbnail');
            ?>
            <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl">
              <div class="w-16 h-16 flex-shrink-0 bg-surface rounded-lg overflow-hidden border border-gray-200">
                <?php echo wp_kses_post($thumbnail); ?>
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-gray-900 mb-1"><?php echo esc_html(
                  $product_name,
                ); ?></h3>
                <p class="text-sm text-gray-600">
                  <?php esc_html_e('Quantity:', 'woocommerce'); ?> <?php echo esc_html(
   $quantity,
 ); ?>
                </p>
                <?php
                $lty_tickets = $item->get_meta('_lty_lottery_tickets');
                if (!is_array($lty_tickets)) {
                    $lty_tickets = [];
                }
                if (!empty($lty_tickets)):
                    $visible_tickets   = array_slice($lty_tickets, 0, 10);
                    $remaining_tickets = array_slice($lty_tickets, 10);
                ?>
                <div class="mt-2 flex flex-wrap items-center gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <?php esc_html_e('Tickets', 'nera-competitions-standard'); ?>:
                    </span>
                    <?php foreach ($visible_tickets as $tn): ?>
                        <span class="notranslate inline-flex items-center px-2 py-0.5 rounded-md bg-primary/10 text-primary text-xs font-mono font-semibold border border-primary/20">
                            #<?php echo esc_html($tn); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($remaining_tickets)): ?>
                <details class="ncs-ticket-expand mt-1">
                    <summary class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 text-xs font-semibold border border-gray-200 cursor-pointer hover:bg-gray-200 transition-colors select-none list-none w-fit">
                        <span class="ncs-ticket-expand__label"><?php printf(esc_html__('+%d more tickets', 'nera-competitions-standard'), count($remaining_tickets)); ?></span>
                        <span class="ncs-ticket-expand__chevron">&#x25BE;</span>
                    </summary>
                    <div class="flex flex-wrap gap-1 mt-1">
                        <?php foreach ($remaining_tickets as $tn): ?>
                            <span class="notranslate inline-flex items-center px-2 py-0.5 rounded-md bg-primary/10 text-primary text-xs font-mono font-semibold border border-primary/20">
                                #<?php echo esc_html($tn); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </details>
                <?php endif; ?>
                <?php endif; ?>
              </div>
              <div class="text-right flex-shrink-0">
                <p class="font-bold text-gray-900 text-lg"><?php echo wp_kses_post($total); ?></p>
              </div>
            </div>
          <?php
          }
          ?>
        </div>

        <!-- Order Totals -->
        <div class="mt-6 pt-6 border-t border-gray-200 space-y-3">
          <?php foreach ($order->get_order_item_totals() as $key => $total) {
            $is_total = $key === 'order_total'; ?>
            <div class="flex justify-between items-center <?php echo $is_total
              ? 'text-lg font-bold text-gray-900'
              : 'text-gray-700'; ?>">
              <span><?php echo esc_html($total['label']); ?></span>
              <span><?php echo wp_kses_post($total['value']); ?></span>
            </div>
          <?php
          } ?>
        </div>

        <?php do_action('woocommerce_order_details_after_order_table', $order); ?>
      </div>

      <!-- Customer Notes -->
      <?php if ($notes): ?>
      <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2 mb-6">
          <span class="material-symbols-outlined text-primary">chat</span>
          <?php esc_html_e('Order updates', 'woocommerce'); ?>
        </h2>

        <ol class="space-y-4">
          <?php foreach ($notes as $note): ?>
          <li class="flex gap-4 p-4 bg-info-bg rounded-xl border border-info-bg">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 bg-info-bg rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-primary">comment</span>
              </div>
            </div>
            <div class="flex-1">
              <div class="text-sm text-gray-900"><?php echo wpautop(
                wptexturize($note->comment_content),
              ); ?></div>
              <time class="text-xs text-gray-500" datetime="<?php echo esc_attr(
                $note->comment_date,
              ); ?>">
                <?php echo esc_html(
                  date_i18n(
                    esc_html__('l jS \o\f F Y, h:ia', 'woocommerce'),
                    strtotime($note->comment_date),
                  ),
                ); ?>
              </time>
            </div>
          </li>
          <?php endforeach; ?>
        </ol>
      </div>
      <?php endif; ?>

    </div>

    <!-- Sidebar Column -->
    <div class="space-y-6">
      
      <!-- Billing Address Card -->
      <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 mb-4">
          <span class="material-symbols-outlined text-primary">receipt_long</span>
          <?php esc_html_e('Billing address', 'woocommerce'); ?>
        </h3>
        <address class="not-italic text-gray-700 text-sm space-y-1">
          <?php echo wp_kses_post(
            $order->get_formatted_billing_address(esc_html__('N/A', 'woocommerce')),
          ); ?>
        </address>
        <?php if ($order->get_billing_phone()): ?>
          <p class="mt-4 pt-4 border-t border-gray-200">
            <span class="text-sm font-semibold text-gray-600"><?php esc_html_e(
              'Phone:',
              'woocommerce',
            ); ?></span>
            <span class="text-sm text-gray-900 ml-2"><?php echo esc_html(
              $order->get_billing_phone(),
            ); ?></span>
          </p>
        <?php endif; ?>
        <?php if ($order->get_billing_email()): ?>
          <p class="mt-2">
            <span class="text-sm font-semibold text-gray-600"><?php esc_html_e(
              'Email:',
              'woocommerce',
            ); ?></span>
            <span class="text-sm text-gray-900 ml-2 break-all"><?php echo esc_html(
              $order->get_billing_email(),
            ); ?></span>
          </p>
        <?php endif; ?>
      </div>

      <!-- Shipping Address Card -->
      <?php if (!wc_ship_to_billing_address_only() && $order->needs_shipping_address()): ?>
      <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 mb-4">
          <span class="material-symbols-outlined text-primary">local_shipping</span>
          <?php esc_html_e('Shipping address', 'woocommerce'); ?>
        </h3>
        <address class="not-italic text-gray-700 text-sm space-y-1">
          <?php echo wp_kses_post(
            $order->get_formatted_shipping_address(esc_html__('N/A', 'woocommerce')),
          ); ?>
        </address>
        <?php if ($order->get_shipping_phone()): ?>
          <p class="mt-4 pt-4 border-t border-gray-200">
            <span class="text-sm font-semibold text-gray-600"><?php esc_html_e(
              'Phone:',
              'woocommerce',
            ); ?></span>
            <span class="text-sm text-gray-900 ml-2"><?php echo esc_html(
              $order->get_shipping_phone(),
            ); ?></span>
          </p>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- Payment Method Card -->
      <div class="bg-surface rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 mb-4">
          <span class="material-symbols-outlined text-primary">payment</span>
          <?php esc_html_e('Payment method', 'woocommerce'); ?>
        </h3>
        <p class="text-gray-700"><?php echo wp_kses_post($order->get_payment_method_title()); ?></p>
      </div>

    </div>

  </div>

</div>

<?php
/**
 * Action hook fired after the order details.
 *
 * @since 4.4.0
 * @param int $order_id Order ID.
 */
do_action('woocommerce_after_order_details', $order->get_id());
?>
