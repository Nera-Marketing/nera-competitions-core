<?php
/**
 * Purchase Card template part for Single Competition
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$product = $args['product'] ?? null;
$countdown = $args['countdown'] ?? [];
$sold_tickets = $args['sold_tickets'] ?? 0;
$max_tickets = $args['max_tickets'] ?? 0;
$remaining = $args['remaining'] ?? 0;
$progress = $args['progress'] ?? 0;
$is_low_stock = $args['is_low_stock'] ?? false;
$price = $args['price'] ?? 0;
$lottery_data = $args['lottery_data'] ?? [];
$has_qa = $args['has_qa'] ?? false;
$questions = $args['questions'] ?? [];
$qa_can_display = $args['qa_can_display'] ?? false;
$cart_answer_id = $args['cart_answer_id'] ?? '';
$is_expired = $args['is_expired'] ?? false;

if (!$product) {
  return;
}

$product_id = $product->get_id();
$is_sold_out = function_exists('nera_lottery_product_is_sold_out')
  ? nera_lottery_product_is_sold_out($product, $lottery_data)
  : false;
$is_manual_ticket = method_exists($product, 'is_manual_ticket') ? $product->is_manual_ticket() : false;
?>

<div class="bg-surface rounded-2xl shadow-xl border border-gray-100 overflow-hidden">

  <?php if ($is_sold_out): ?>

    <!-- Sold out: title + Tickets Sold progress only -->
    <div class="p-6 pb-4">
      <?php if (function_exists('nera_render_component')) { nera_render_component('ProductTitle', ['name' => $product->get_name(), 'is_sold_out' => true]); } ?>
    </div>

    <div class="px-6 pb-6">
      <?php if (function_exists('nera_render_component')) { nera_render_component('TicketsProgress', [
        'sold'         => $sold_tickets,
        'max'          => $max_tickets,
        'progress'     => $progress,
        'remaining'    => $remaining,
        'is_low_stock' => $is_low_stock,
      ]); } ?>
    </div>

  <?php else: ?>

    <!-- Product Title -->
    <div class="p-6 pb-4">
      <?php if (function_exists('nera_render_component')) { nera_render_component('ProductTitle', ['name' => $product->get_name(), 'is_sold_out' => false]); } ?>
    </div>

    <!-- Countdown Section -->
    <div class="px-6 pb-6">
      <?php
      // Get countdown date directly from product method for JavaScript
      $countdown_date_for_js = '';
      if (method_exists($product, 'get_countdown_timer_enddate')) {
        $countdown_date_for_js = $product->get_countdown_timer_enddate();
      }
      ?>
      <?php if (function_exists('nera_render_component')) { nera_render_component('CountdownTimer', [
        'countdown_date' => $countdown_date_for_js,
        'countdown'      => $countdown,
        'is_expired'     => $is_expired,
      ]); } ?>
    </div>

    <!-- Ticket Progress Section -->
    <div class="px-6 pb-6">
      <?php if (function_exists('nera_render_component')) { nera_render_component('TicketsProgress', [
        'sold'         => $sold_tickets,
        'max'          => $max_tickets,
        'progress'     => $progress,
        'remaining'    => $remaining,
        'is_low_stock' => $is_low_stock,
      ]); } ?>
    </div>

    <!-- Ticket Price & Quantity -->
    <div class="px-6 pb-6">
      <?php if (function_exists('nera_render_component')) { nera_render_component('TicketPrice', ['price' => $price]); } ?>
      <?php if ($is_manual_ticket): ?>
        <?php do_action('woocommerce_before_add_to_cart_button'); ?>
      <?php else:
        $quantity_layout = nera_get_quantity_selector_layout($product_id);
        ?>
        <?php if (function_exists('nera_render_component')) { nera_render_component('QuantitySelector', [
          'min' => $lottery_data['minPerOrder'] ?? 1,
          'max' => $lottery_data['maxPerOrder'] ?: $remaining,
          'layout' => $quantity_layout,
        ]); } ?>
      <?php endif; ?>
    </div>

    <!-- Enter Now Form (includes Skill Challenge Q&A) -->
    <div class="px-6 pb-6">
      <script defer>
        document.addEventListener('alpine:init', () => {
          Alpine.data('purchaseCard', (config) => ({
            selectedAnswer: config.selectedAnswer,
            isSubmitting: false,
            quantity: 1,

            parseTicketCount(val) {
              if (!val || !val.trim()) return 0;
              // Plugin may store as JSON array ["571","572",...] or pipe/comma-separated
              try {
                const parsed = JSON.parse(val);
                if (Array.isArray(parsed)) return parsed.filter(Boolean).length;
                if (typeof parsed === 'object') return Object.keys(parsed).length;
              } catch (e) {
                // Not JSON — split by pipe or comma
              }
              return val.split(/[|,]/).filter((s) => s.trim().length > 0).length;
            },

            init() {
              if (config.isManualTicket) {
                // Wire up live selected-ticket counter.
                // The plugin's jQuery updates .lty-lottery-ticket-numbers directly,
                // so we poll on ticket clicks (MutationObserver won't fire on .value changes).
                const updateCount = () => {
                  const field = document.querySelector('.lty-lottery-ticket-numbers');
                  const count = this.parseTicketCount(field ? field.value : '');
                  // Update all badge instances (trigger button, dialog header, confirm button)
                  document.querySelectorAll('[data-selected-ticket-count]').forEach((el) => {
                    el.textContent = count;
                  });
                  document.querySelectorAll('[data-count-badge]').forEach((el) => {
                    el.dataset.hasSelection = count > 0 ? 'true' : 'false';
                  });
                };

                document.addEventListener('click', (e) => {
                  if (e.target.closest('.lty-ticket, .lty-selected-ticket')) {
                    setTimeout(updateCount, 50);
                  }
                });
              } else {
                // Sync external quantity inputs (auto-assign mode)
                const qtyInput = document.querySelector('[data-quantity-input]');
                if (qtyInput) {
                  this.quantity = qtyInput.value;
                  qtyInput.addEventListener('change', (e) => this.quantity = e.target.value);

                  const observer = new MutationObserver(() => {
                    this.quantity = qtyInput.value;
                  });
                  observer.observe(qtyInput, {
                    attributes: true
                  });
                }
              }
            },

            selectAnswer(id) {
              this.selectedAnswer = id;
            },

            updateCartFragments(fragments) {
              if (!fragments) {
                return;
              }

              Object.keys(fragments).forEach((selector) => {
                const elements = document.querySelectorAll(selector);
                elements.forEach((element) => {
                  element.outerHTML = fragments[selector];
                });
              });
            },

            async submitForm(e) {
              // Validate ticket selection for manual-ticket mode
              if (config.isManualTicket) {
                const field = document.querySelector('.lty-lottery-ticket-numbers');
                if (!field || this.parseTicketCount(field.value) === 0) {
                  Alpine.store('toast').error(config.i18n.selectTickets);
                  return;
                }
              }

              if (config.hasQa && !this.selectedAnswer) {
                Alpine.store('toast').error(config.i18n.selectAnswer);
                return;
              }

              this.isSubmitting = true;

              try {
                // We'll use the custom AJAX handler defined in functions.php
                const ajaxData = new FormData();
                ajaxData.append('action', 'woocommerce_ajax_add_to_cart');
                ajaxData.append('product_id', config.productId);

                if (config.isManualTicket) {
                  const ticketNumbers = document.querySelector('.lty-lottery-ticket-numbers')?.value;
                  const ticketQty = document.querySelector('.lty-lottery-ticket-quantity')?.value || '1';
                  ajaxData.append('lty_lottery_ticket_numbers', ticketNumbers);
                  ajaxData.append('quantity', ticketQty);
                } else {
                  ajaxData.append('quantity', document.querySelector('[data-quantity-input]').value);
                }

                if (config.hasQa) {
                  // Lottery plugin validates/reads this request key during add-to-cart.
                  ajaxData.append('lty_question_answer_id', this.selectedAnswer);
                }

                const ajaxResponse = await fetch(config.ajaxUrl, {
                  method: 'POST',
                  body: ajaxData
                });

                const result = await ajaxResponse.json();

                if (result.error) {
                  Alpine.store('toast').error(result.message || config.i18n.error);
                } else {
                  Alpine.store('toast').success(config.i18n.success, {
                    label: config.i18n.viewCart,
                    callback: () => window.location.href = config.cartUrl
                  });

                  document.dispatchEvent(new CustomEvent('nera:cart:updated', {
                    detail: { productId: config.productId }
                  }));

                  // Update cart fragments if provided
                  if (result.fragments) {
                    this.updateCartFragments(result.fragments);

                    if (window.jQuery) {
                      window.jQuery(document.body).trigger('wc_fragments_refreshed');
                      window.jQuery(document.body).trigger('wc_fragment_refresh');
                    }
                  }
                }

              } catch (err) {
                console.error(err);
                Alpine.store('toast').error(config.i18n.generalError);
              } finally {
                this.isSubmitting = false;
              }
            }
          }));
        });
      </script>
      <div x-data="purchaseCard({
        selectedAnswer: '<?php echo esc_js($cart_answer_id); ?>',
        productId: '<?php echo esc_js($product_id); ?>',
        hasQa: <?php echo $has_qa && $qa_can_display ? 'true' : 'false'; ?>,
        isManualTicket: <?php echo $is_manual_ticket ? 'true' : 'false'; ?>,
        ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
        cartUrl: '<?php echo wc_get_cart_url(); ?>',
        i18n: {
              selectTickets: '<?php _e('Please select at least one ticket', 'nera-competitions'); ?>',
              selectAnswer: '<?php _e(
                'Please select an answer to the question',
                'nera-competitions',
              ); ?>',
              error: '<?php _e('Could not add to cart', 'nera-competitions'); ?>',
              success: '<?php echo esc_js(get_field('add_to_cart_success_message', 'option') ?: __('Tickets added to cart!', 'nera-competitions')); ?>',
              viewCart: '<?php _e('View Cart', 'nera-competitions'); ?>',
              generalError: '<?php _e(
                'An error occurred. Please try again.',
                'nera-competitions',
              ); ?>'
        }
      })">
        <form class="cart" @submit.prevent="submitForm"
          action="<?php echo esc_url(
            apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink()),
          ); ?>"
          method="post" enctype='multipart/form-data'>

          <?php if ($has_qa): ?>
            <?php if (function_exists('nera_render_component')) { nera_render_component('SkillQuestionAnswer', [
              'question_text'  => $questions[0]['question'] ?? '',
              'answers'        => $questions[0]['answers'] ?? [],
              'cart_answer_id' => $cart_answer_id,
              'qa_can_display' => $qa_can_display,
            ]); } ?>
          <?php endif; ?>

          <?php if (!$is_manual_ticket): ?>
            <input type="hidden" name="quantity" value="1" data-quantity-hidden />
          <?php endif; ?>

          <?php if (function_exists('nera_render_component')) { nera_render_component('AddToCartButton', [
            'product_id'       => $product_id,
            'is_expired'       => $is_expired,
            'is_manual_ticket' => $is_manual_ticket,
          ]); } ?>

        </form>
      </div>
    </div>

    <!-- Enter By Post Section -->
    <div class="px-6 pb-6" x-data>
      <div class="border-t border-gray-200">
        <p class="text-center text-sm text-text-secondary pt-6">
          <?php _e('Or', 'nera-competitions'); ?>
          <button type="button" @click="$store.postDialog.show = true" class="text-primary font-semibold hover:text-primary-dark transition-colors">
            <?php _e('ENTER BY POST', 'nera-competitions'); ?>
          </button>
        </p>
      </div>
    </div>

    <!-- Trust Badges -->
    <div class="px-6 pb-6">
      <?php if (function_exists('nera_render_component')) { nera_render_component('TrustBadges', []); } ?>
    </div>

  <?php endif; ?>

</div>
