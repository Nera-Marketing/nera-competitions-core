<?php
/**
 * Re-generate Lucky Dip popup — Nera theme override.
 *
 * @package Nera_Competitions
 * @see lottery-for-woocommerce/templates/single-product/regenerate-lucky-dip-popup.php
 */

defined('ABSPATH') || exit;
?>
<div class="lty-regenerate-ticket-lucky-dip-popup-wrapper lty-lottery-ticket-lucky-dip-container nera-lucky-dip-regenerate">
  <input type="hidden" class="lty-lucky-dip-fixed-quantity" value="<?php echo esc_attr(isset($quantity_args['readonly']) && $quantity_args['readonly'] ? 'yes' : 'no'); ?>"/>
  <input type="hidden" class="lty-lucky-dip-quantity" value="<?php echo esc_attr($quantity_args['input_value']); ?>"/>

  <div class="nera-lucky-dip-regenerate__header">
    <div class="nera-lucky-dip-regenerate__icon-wrap" aria-hidden="true">
      <span class="material-symbols-outlined">casino</span>
    </div>
    <h3 class="nera-lucky-dip-regenerate__title">
      <?php echo wp_kses_post(lty_get_single_product_lucky_dip_title_label()); ?>
    </h3>
  </div>

  <div class="nera-lucky-dip-regenerate__quantity">
    <label class="nera-lucky-dip-regenerate__qty-label">
      <?php echo wp_kses_post(lty_get_single_product_lucky_dip_quantity_label()); ?>
    </label>
    <div class="nera-lucky-dip-regenerate__qty-row">
      <div class="nera-lucky-dip-inline__qty">
        <?php woocommerce_quantity_input($quantity_args, $product); ?>
      </div>
      <?php if ('add_to_cart' === $action) : ?>
        <button
          type="button"
          title="<?php echo esc_attr(lty_lucky_dip_question_answer_hover_message($product)); ?>"
          value="<?php echo esc_attr($product->get_id()); ?>"
          class="nera-lucky-dip-inline__btn nera-lucky-dip-regenerate__generate <?php echo esc_attr(implode(' ', lty_get_lucky_dip_button_classes($product))); ?>">
          <span class="material-symbols-outlined" aria-hidden="true">shuffle</span>
          <?php echo wp_kses_post(lty_get_single_product_generate_lucky_dip_button_label()); ?>
        </button>
      <?php endif; ?>
    </div>
    <p class="nera-lucky-dip-inline__error" hidden role="alert" aria-live="polite"></p>
  </div>

  <?php if ('regenerate' === $action) : ?>
    <div class="nera-lucky-dip-regenerate__tickets-section">
      <h4 class="nera-lucky-dip-regenerate__tickets-title">
        <?php echo wp_kses_post(lty_get_single_product_generated_lucky_dip_tickets_label()); ?>
      </h4>
      <div class="lty-regenerate-lucky-dip-tickets nera-lucky-dip-popup__tickets">
        <?php foreach ((array) $ticket_numbers as $ticket_number) : ?>
          <span class="nera-lucky-dip-popup__ticket-chip"><?php echo esc_html($ticket_number); ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="nera-lucky-dip-regenerate__actions">
    <?php if ('add_to_cart' === $action) : ?>
      <div class="lty-regenerate-lucky-dip-tickets nera-lucky-dip-popup__tickets">
        <?php foreach ((array) $ticket_numbers as $ticket_number) : ?>
          <span class="nera-lucky-dip-popup__ticket-chip"><?php echo esc_html($ticket_number); ?></span>
        <?php endforeach; ?>
      </div>
      <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="nera-lucky-dip-popup__btn nera-lucky-dip-popup__btn--primary lty-view-cart">
        <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
        <?php echo wp_kses_post(lty_get_single_product_lucky_dip_view_cart_button_label()); ?>
      </a>
    <?php endif; ?>

    <?php if ('regenerate' === $action) : ?>
      <button
        type="button"
        title="<?php echo esc_attr(lty_lucky_dip_question_answer_hover_message($product)); ?>"
        value="<?php echo esc_attr($product->get_id()); ?>"
        class="nera-lucky-dip-popup__btn nera-lucky-dip-popup__btn--secondary lty-regenerate-lucky-dip-button">
        <span class="material-symbols-outlined" aria-hidden="true">refresh</span>
        <?php echo wp_kses_post(lty_get_single_product_regenerate_lucky_dip_button_label()); ?>
      </button>
      <button
        type="button"
        value="<?php echo esc_attr($product->get_id()); ?>"
        class="nera-lucky-dip-popup__btn nera-lucky-dip-popup__btn--primary lty-regenerate-lucky-dip-add-to-cart-button"
        data-tickets="<?php echo esc_attr(implode(',', $ticket_numbers)); ?>">
        <span class="material-symbols-outlined" aria-hidden="true">add_shopping_cart</span>
        <?php echo wp_kses_post(lty_get_single_product_lucky_dip_add_to_cart_button_label()); ?>
      </button>
    <?php endif; ?>
  </div>
</div>
