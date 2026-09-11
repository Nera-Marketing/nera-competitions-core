<?php
/**
 * Re-generate Lucky Dip popup — Nera theme override.
 *
 * Two shapes:
 *  - No skill question: unchanged from before, a single column.
 *  - Skill question: two columns — the question on the left, the Lucky Dip
 *    content on the right — so the answer is collected before the tickets
 *    reach the cart. Without it LFW posts an empty `answer`, the order stores
 *    none, and its tickets are cancelled after checkout.
 *
 * Both Lucky Dip methods land here once a question is configured; see
 * nera_lucky_dip_force_answer_flow(). They differ only in what the left column
 * offers: "add directly" gets an Answer button that also adds to cart, while
 * "only display" leaves the existing Add to Cart button to do it, and only that
 * method may re-generate.
 *
 * @package Nera_Competitions
 * @see lottery-for-woocommerce/templates/single-product/regenerate-lucky-dip-popup.php
 */

defined('ABSPATH') || exit;

$nera_qa = function_exists('nera_lucky_dip_question_data')
  ? nera_lucky_dip_question_data($product)
  : ['can_display' => false, 'question_text' => '', 'answers' => [], 'cart_answer_id' => ''];

$nera_has_qa = !empty($nera_qa['can_display']);

$nera_is_direct = function_exists('nera_lucky_dip_is_direct_method')
  && nera_lucky_dip_is_direct_method($product);

// Re-generating is hidden only for the orders we rerouted here — "add
// directly" products that had to be given a popup so the question could be
// asked. Offering a re-roll there would contradict the configured method.
// Anything else, including every product without a question, keeps the button
// exactly as before.
$nera_rerouted = $nera_has_qa && $nera_is_direct;
$nera_can_regenerate = 'regenerate' === $action && !$nera_rerouted;

// Quantity is locked in lockstep with Add More Lucky Dip: before the question
// is answered nothing can be added, so a live quantity field would invite the
// customer to set a number that has nothing to apply to.
$nera_qty_locked = $nera_rerouted && 'regenerate' === $action;
$nera_qa_frozen = $nera_has_qa && 'add_to_cart' === $action;

// Shown above the quantity label on every step — see nera_lucky_dip_gate_note()
// for why it must not disappear once the answer is committed.
$nera_gate_note = ($nera_has_qa && function_exists('nera_lucky_dip_gate_note'))
  ? nera_lucky_dip_gate_note($product, $nera_qa_frozen)
  : '';
$nera_root_class = 'lty-regenerate-ticket-lucky-dip-popup-wrapper lty-lottery-ticket-lucky-dip-container nera-lucky-dip-regenerate';
if ($nera_has_qa) {
  $nera_root_class .= ' nera-lucky-dip-regenerate--with-qa';
}
?>
<div class="<?php echo esc_attr($nera_root_class); ?>"
  <?php
  /*
   * State is declared, not inferred. lottery-lucky-dip-sync.js used to tell a
   * preview popup from a post-add one by looking for a View Cart button, which
   * only the post-add step used to have. The pre-answer popup now carries one
   * too, so that guess started firing the added-to-cart sound before anything
   * had been added.
   */
  ?>
  data-nera-lucky-dip-state="<?php echo 'add_to_cart' === $action ? 'added' : 'preview'; ?>"
  <?php if ($nera_has_qa) : ?>
    data-nera-qa-popup
    data-nera-qa-direct="<?php echo $nera_is_direct ? 'yes' : 'no'; ?>"
  <?php endif; ?>>
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

  <div class="nera-lucky-dip-regenerate__layout">

    <?php if ($nera_has_qa) : ?>
      <?php
      /*
       * Rendered Alpine-free on purpose. jQuery .modal() injects this markup
       * into the page after Alpine has started, and Alpine does not pick it
       * up: x-cloak was never removed, so the answer list rendered but stayed
       * display:none, and the @click bindings never attached. The question
       * showed, the answers did not.
       *
       * lottery-lucky-dip-qa.js owns selection here. It writes the bridge
       * input on the product page, which is both what LFW reads at add-to-cart
       * and what keeps the page behind the popup in sync.
       */
      ?>
      <div class="nera-lucky-dip-regenerate__qa" data-nera-qa-column
        <?php if ($nera_qa_frozen) : ?>data-nera-qa-frozen="yes"<?php endif; ?>>
        <?php if (function_exists('nera_render_component')) {
          nera_render_component('SkillQuestionAnswer', [
            'question_text'  => $nera_qa['question_text'],
            'answers'        => $nera_qa['answers'],
            'cart_answer_id' => $nera_qa['cart_answer_id'],
            'qa_can_display' => true,
            'interactive'    => false,
          ]);
        } ?>


        <?php
        /*
         * Where the plugin's answer errors land — "you selected an incorrect
         * answer" belongs under the question it is about, not under the
         * quantity field in the other column. Carries the plugin's own error
         * class as well so lottery-alertable.js clears it with the rest.
         */
        ?>
        <p class="nera-lucky-dip-regenerate__qa-message nera-lucky-dip-inline__error"
          data-nera-qa-message hidden role="alert" aria-live="polite"></p>

        <?php if ($nera_is_direct && $nera_qa_frozen) : ?>
      <?php
      /*
       * After a successful add the answer is committed to the cart and cannot be
       * changed from here — LFW stores it on the cart item, and editing the
       * radios would only desynchronise what is shown from what was bought.
       * The column is frozen, showing the answer that passed, and the Answer
       * button stays in place disabled so the step does not appear to vanish.
       */
      ?>
          <button
            type="button"
            class="nera-lucky-dip-popup__btn nera-lucky-dip-popup__btn--primary nera-lucky-dip-regenerate__answer"
            disabled
            aria-disabled="true">
            <span class="material-symbols-outlined" aria-hidden="true">check_circle</span>
            <?php esc_html_e('Answered', 'nera-competitions'); ?>
          </button>
        <?php endif; ?>

        <?php if ($nera_is_direct && 'regenerate' === $action) : ?>
          <button
            type="button"
            value="<?php echo esc_attr($product->get_id()); ?>"
            class="nera-lucky-dip-popup__btn nera-lucky-dip-popup__btn--primary nera-lucky-dip-regenerate__answer lty-regenerate-lucky-dip-add-to-cart-button"
            data-tickets="<?php echo esc_attr(implode(',', (array) $ticket_numbers)); ?>">
            <span class="material-symbols-outlined" aria-hidden="true">add_shopping_cart</span>
            <?php
            /*
             * "Answer" alone undersold this: the button validates the answer AND
             * puts the tickets in the cart. Nothing else on this popup does, so
             * a label that only mentions answering left people looking for an
             * add-to-cart button that was never coming.
             */
            esc_html_e('Answer & Add to Cart', 'nera-competitions');
            ?>
          </button>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="nera-lucky-dip-regenerate__main">
      <?php
      /*
       * One home for the ticket list, whichever step this is. It used to be
       * duplicated — a titled section before the answer, and an untitled copy
       * inside the button group after it — which left the post-add step with no
       * heading and its tickets dragged to the bottom of the column alongside
       * the buttons.
       */
      ?>
      <?php if ('regenerate' === $action || $nera_has_qa) : ?>
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

      <?php
      /*
       * Quantity sits directly above the button that consumes it. At the top of
       * the column it read as a stray field with no obvious effect; next to
       * "Add More Lucky Dip" it reads as "how many to take next".
       */
      ?>
      <div class="nera-lucky-dip-regenerate__quantity<?php echo $nera_qty_locked ? ' nera-lucky-dip-regenerate__quantity--locked' : ''; ?>">
        <?php if ('' !== $nera_gate_note) : ?>
          <p class="nera-lucky-dip-regenerate__gate-note">
            <span class="material-symbols-outlined" aria-hidden="true">lock</span>
            <?php echo esc_html($nera_gate_note); ?>
          </p>
        <?php endif; ?>

        <label class="nera-lucky-dip-regenerate__qty-label">
          <?php echo wp_kses_post(lty_get_single_product_lucky_dip_quantity_label()); ?>
        </label>
        <div class="nera-lucky-dip-regenerate__qty-row">
          <?php if ($nera_qty_locked) : ?>
            <?php
            /*
             * woocommerce_quantity_input() accepts `readonly` but not
             * `disabled`, and readonly still takes focus and looks editable. A
             * disabled fieldset disables every control inside it natively —
             * pointer, keyboard and assistive tech alike — without rewriting
             * the plugin's markup. Rendered only when locked so products
             * without a question keep their original DOM.
             */
            ?>
            <fieldset class="nera-lucky-dip-regenerate__qty-lock" disabled>
              <div class="nera-lucky-dip-inline__qty">
                <?php woocommerce_quantity_input($quantity_args, $product); ?>
              </div>
            </fieldset>
          <?php else : ?>
            <div class="nera-lucky-dip-inline__qty">
              <?php woocommerce_quantity_input($quantity_args, $product); ?>
            </div>
          <?php endif; ?>
          <?php if ('add_to_cart' === $action && !$nera_rerouted) : ?>
            <?php
            /*
             * Hidden for rerouted "add directly" products. This button carries
             * lty_get_lucky_dip_button_classes(), which our own filter has
             * rewritten to lty-regenerate-lucky-dip-button — so it would reopen
             * the pre-answer popup and hand the customer a re-roll their
             * configured method never offered. The quantity field stays, since
             * Add More Lucky Dip reads it.
             */
            ?>
            <button
              type="button"
              title="<?php echo esc_attr(lty_lucky_dip_question_answer_hover_message($product)); ?>"
              value="<?php echo esc_attr($product->get_id()); ?>"
              class="nera-lucky-dip-inline__btn nera-lucky-dip-regenerate__generate <?php echo esc_attr(implode(' ', lty_get_lucky_dip_button_classes($product))); ?>">
              <span class="material-symbols-outlined" aria-hidden="true">shuffle</span>
              <?php echo wp_kses_post(lty_get_single_product_generate_lucky_dip_button_label()); ?>
            </button>
          <?php endif; ?>

          <?php if ($nera_rerouted) : ?>
            <?php
            /*
             * Add More shares the quantity row with the field it consumes, the
             * same shape the Generate button uses above. Before the question is
             * answered it is disabled in lockstep with that field — a disabled
             * <button> emits no click, so LFW's delegated handler cannot fire.
             * After the answer it is live; LFW re-renders the whole popup on a
             * successful add, so "enabling" happens by re-render.
             */
            ?>
            <?php if ('regenerate' === $action) : ?>
              <button
                type="button"
                class="nera-lucky-dip-popup__btn nera-lucky-dip-popup__btn--secondary nera-lucky-dip-regenerate__add-more lty-add-more-lucky-tip"
                disabled
                aria-disabled="true"
                title="<?php esc_attr_e('Answer the question first', 'nera-competitions'); ?>">
                <span class="material-symbols-outlined" aria-hidden="true">casino</span>
                <?php echo wp_kses_post(lty_get_single_product_add_more_lucky_dip_button_label()); ?>
              </button>
            <?php else : ?>
              <a href="#" class="nera-lucky-dip-popup__btn nera-lucky-dip-popup__btn--secondary nera-lucky-dip-regenerate__add-more lty-add-more-lucky-tip">
                <span class="material-symbols-outlined" aria-hidden="true">casino</span>
                <?php echo wp_kses_post(lty_get_single_product_add_more_lucky_dip_button_label()); ?>
              </a>
            <?php endif; ?>
          <?php endif; ?>

          <?php if ($nera_can_regenerate) : ?>
            <?php /* Same row as the field it re-rolls, matching Generate and Add More. */ ?>
            <button
              type="button"
              title="<?php echo esc_attr(lty_lucky_dip_question_answer_hover_message($product)); ?>"
              value="<?php echo esc_attr($product->get_id()); ?>"
              class="nera-lucky-dip-popup__btn nera-lucky-dip-popup__btn--secondary nera-lucky-dip-regenerate__add-more lty-regenerate-lucky-dip-button">
              <span class="material-symbols-outlined" aria-hidden="true">refresh</span>
              <?php echo wp_kses_post(lty_get_single_product_regenerate_lucky_dip_button_label()); ?>
            </button>
          <?php endif; ?>
        </div>
        <p class="nera-lucky-dip-inline__error" hidden role="alert" aria-live="polite"></p>
      </div>

      <div class="nera-lucky-dip-regenerate__actions">
        <?php if ('add_to_cart' === $action) : ?>
          <?php if (!$nera_has_qa) : ?>
            <?php /* Untitled list inside the buttons — the original shape, kept for products with no question. */ ?>
            <div class="lty-regenerate-lucky-dip-tickets nera-lucky-dip-popup__tickets">
              <?php foreach ((array) $ticket_numbers as $ticket_number) : ?>
                <span class="nera-lucky-dip-popup__ticket-chip"><?php echo esc_html($ticket_number); ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="nera-lucky-dip-popup__btn nera-lucky-dip-popup__btn--primary lty-view-cart">
            <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
            <?php echo wp_kses_post(lty_get_single_product_lucky_dip_view_cart_button_label()); ?>
          </a>
        <?php endif; ?>

        <?php if ('regenerate' === $action) : ?>
          <?php if ($nera_rerouted) : ?>
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="nera-lucky-dip-popup__btn nera-lucky-dip-popup__btn--primary lty-view-cart">
              <span class="material-symbols-outlined" aria-hidden="true">shopping_cart</span>
              <?php echo wp_kses_post(lty_get_single_product_lucky_dip_view_cart_button_label()); ?>
            </a>
          <?php else : ?>
            <button
              type="button"
              value="<?php echo esc_attr($product->get_id()); ?>"
              class="nera-lucky-dip-popup__btn nera-lucky-dip-popup__btn--primary lty-regenerate-lucky-dip-add-to-cart-button"
              data-tickets="<?php echo esc_attr(implode(',', (array) $ticket_numbers)); ?>">
              <span class="material-symbols-outlined" aria-hidden="true">add_shopping_cart</span>
              <?php echo wp_kses_post(lty_get_single_product_lucky_dip_add_to_cart_button_label()); ?>
            </button>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>
