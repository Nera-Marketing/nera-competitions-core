<?php
/**
 * Lucky Dip success popup — Nera theme override.
 *
 * @package Nera_Competitions
 * @see lottery-for-woocommerce/templates/single-product/ticket-lucky-dip-popup.php
 */

if (!defined('ABSPATH')) {
  exit;
}

do_action('lty_before_lottery_ticket_lucky_dip_popup_info');

$ticket_count = count((array) $ticket_numbers);
$tickets_class = 'lty-lucky-dip-tickets nera-lucky-dip-popup__tickets';
if (1 === $ticket_count) {
  $tickets_class .= ' nera-lucky-dip-popup__tickets--single';
}
?>
<div class="lty-ticket-lucky-dip-popup-wrapper lty-lottery-ticket-lucky-dip-container nera-lucky-dip-popup">
  <div class="nera-lucky-dip-popup__header">
    <div class="nera-lucky-dip-popup__icon-wrap" aria-hidden="true">
      <span class="material-symbols-outlined">check_circle</span>
    </div>
    <h3 class="nera-lucky-dip-popup__title">
      <?php esc_html_e('Added to Cart', 'nera-competitions'); ?>
    </h3>
    <p class="nera-lucky-dip-popup__subtitle">
      <?php esc_html_e('Your lucky dip ticket(s) are ready', 'nera-competitions'); ?>
    </p>
  </div>

  <div class="nera-lucky-dip-popup__body">
    <div class="<?php echo esc_attr($tickets_class); ?>" aria-live="polite">
      <?php foreach ((array) $ticket_numbers as $ticket_number) : ?>
        <span class="nera-lucky-dip-popup__ticket-chip">
          <?php echo esc_html($ticket_number); ?>
        </span>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="nera-lucky-dip-popup__actions">
    <a href="#" class="nera-lucky-dip-popup__btn nera-lucky-dip-popup__btn--secondary lty-add-more-lucky-tip">
      <span class="material-symbols-outlined" aria-hidden="true">casino</span>
      <?php echo wp_kses_post(lty_get_single_product_add_more_lucky_dip_button_label()); ?>
    </a>
    <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="nera-lucky-dip-popup__btn nera-lucky-dip-popup__btn--primary lty-view-cart">
      <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
      <?php echo wp_kses_post(lty_get_single_product_lucky_dip_view_cart_button_label()); ?>
    </a>
  </div>

  <input type="hidden" class="lty-lucky-dip-quantity" value="<?php echo esc_attr($quantity); ?>"/>
</div>
<?php
do_action('lty_after_lottery_ticket_lucky_dip_popup_info');
