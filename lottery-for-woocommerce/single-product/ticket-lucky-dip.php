<?php
/**
 * Inline Lucky Dip controls — Nera theme override.
 *
 * @package Nera_Competitions
 * @see lottery-for-woocommerce/templates/single-product/ticket-lucky-dip.php
 */

if (!defined('ABSPATH')) {
  exit;
}

do_action('lty_before_lottery_ticket_lucky_dip_container');
?>
<div class="lty-lottery-ticket-lucky-dip-container nera-lucky-dip-inline">
  <div class="nera-lucky-dip-inline__header">
    <span class="material-symbols-outlined nera-lucky-dip-inline__icon" aria-hidden="true">casino</span>
    <span class="nera-lucky-dip-inline__label"><?php esc_html_e('Lucky Dip', 'nera-competitions'); ?></span>
  </div>

  <div class="nera-lucky-dip-inline__controls">
    <div class="nera-lucky-dip-inline__qty">
      <?php woocommerce_quantity_input(lty_get_lucky_dip_quantity_input_arguments($product)); ?>
    </div>
    <button
      type="button"
      title="<?php echo esc_attr(lty_lucky_dip_question_answer_hover_message($product)); ?>"
      value="<?php echo esc_attr($product->get_id()); ?>"
      class="nera-lucky-dip-inline__btn <?php echo esc_attr(implode(' ', lty_get_lucky_dip_button_classes($product))); ?>">
      <span class="material-symbols-outlined" aria-hidden="true">shuffle</span>
      <?php echo wp_kses_post($product->get_lucky_dip_text()); ?>
    </button>
  </div>

  <p class="nera-lucky-dip-inline__error" hidden role="alert" aria-live="polite"></p>

  <input type="hidden" class="lty-ticket-product-id" value="<?php echo esc_attr($product->get_id()); ?>"/>
</div>
<?php
do_action('lty_after_lottery_ticket_lucky_dip_container');
