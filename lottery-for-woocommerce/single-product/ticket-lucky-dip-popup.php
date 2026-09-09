<?php
/**
 * Lucky Dip success popup — Nera theme override.
 *
 * Returned by lty_process_lucky_dip, which is what "Add More Lucky Dip" calls.
 * It is a different endpoint from the one behind the Answer button, so without
 * the two-column shell below the popup visibly snapped back to the old
 * single-column layout the moment a customer added a second batch.
 *
 * The question column is repeated here on purpose. It is already answered, so
 * it reads as a reminder of what was chosen rather than a prompt — and keeping
 * it means the popup does not change shape between adds.
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

// LFW hands this template only the ticket numbers and quantity — no $product —
// so it has to be resolved from the request that rendered it.
$nera_product = function_exists('nera_lucky_dip_resolve_product')
  ? nera_lucky_dip_resolve_product($product ?? null)
  : ($product ?? null);

$nera_qa = ($nera_product && function_exists('nera_lucky_dip_question_data'))
  ? nera_lucky_dip_question_data($nera_product)
  : ['can_display' => false, 'question_text' => '', 'answers' => [], 'cart_answer_id' => ''];

// Only the products this theme reroutes get the two-column shell; every other
// Lucky Dip keeps the popup it has always had.
$nera_show_qa = !empty($nera_qa['can_display'])
  && function_exists('nera_lucky_dip_is_direct_method')
  && nera_lucky_dip_is_direct_method($nera_product);

$nera_root_class = 'lty-ticket-lucky-dip-popup-wrapper lty-lottery-ticket-lucky-dip-container nera-lucky-dip-popup';
if ($nera_show_qa) {
  $nera_root_class .= ' nera-lucky-dip-popup--with-qa';
}
?>
<div class="<?php echo esc_attr($nera_root_class); ?>" data-nera-lucky-dip-state="added">
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

  <div class="nera-lucky-dip-regenerate__layout">
    <?php if ($nera_show_qa) : ?>
      <div class="nera-lucky-dip-regenerate__qa" data-nera-qa-column>
        <?php if (function_exists('nera_render_component')) {
          nera_render_component('SkillQuestionAnswer', [
            'question_text'  => $nera_qa['question_text'],
            'answers'        => $nera_qa['answers'],
            'cart_answer_id' => $nera_qa['cart_answer_id'],
            'qa_can_display' => true,
            'interactive'    => false,
          ]);
        } ?>
      </div>
    <?php endif; ?>

    <div class="nera-lucky-dip-regenerate__main">
      <div class="nera-lucky-dip-popup__body">
        <?php if ($nera_show_qa) : ?>
          <?php
          /*
           * Only in the two-column shell. Without it the right column opens on
           * a bare row of chips while the left opens on a heading, so the two
           * halves start on different baselines.
           */
          ?>
          <h4 class="nera-lucky-dip-regenerate__tickets-title">
            <?php echo wp_kses_post(lty_get_single_product_generated_lucky_dip_tickets_label()); ?>
          </h4>
        <?php endif; ?>
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
    </div>
  </div>

  <input type="hidden" class="lty-lucky-dip-quantity" value="<?php echo esc_attr($quantity); ?>"/>
</div>
<?php
do_action('lty_after_lottery_ticket_lucky_dip_popup_info');
