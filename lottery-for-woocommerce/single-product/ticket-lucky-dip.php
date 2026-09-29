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

  <?php
  /*
   * "Add directly": the click already puts tickets in the basket, so this is the one moment
   * to choose add-ons for them. With "only display" they are chosen in the popup instead.
   */
  if (function_exists('nera_lucky_dip_is_direct_method') && nera_lucky_dip_is_direct_method($product)) {
    get_template_part('template-parts/single-product/lucky-dip-safety', null, ['product' => $product]);
    get_template_part('template-parts/single-product/lucky-dip-addons', null, ['product' => $product]);
  }
  ?>

  <p class="nera-lucky-dip-inline__error" hidden role="alert" aria-live="polite"></p>

  <input type="hidden" class="lty-ticket-product-id" value="<?php echo esc_attr($product->get_id()); ?>"/>
</div>
<?php
do_action('lty_after_lottery_ticket_lucky_dip_container');
