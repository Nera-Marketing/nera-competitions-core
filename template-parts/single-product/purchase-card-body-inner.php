<?php
/**
 * Purchase Card — Body Inner partial
 * Renders: TicketPrice + TicketBundles + QuantitySelector + Alpine form + Enter By Post + TrustBadges
 * Used by both section=body (details_first layout) and section=full (default layout).
 * Skips entirely when product is sold out.
 *
 * @package Nera_Competitions
 *
 * Expected $args keys (forwarded from purchase-card.php):
 *   product, price, lottery_data, remaining, has_qa, questions, qa_can_display,
 *   cart_answer_id, is_expired, is_sold_out, is_manual_ticket, seam_pt
 */

if (!defined('ABSPATH')) {
  exit();
}

$product        = $args['product'] ?? null;
$price          = $args['price'] ?? 0;
$lottery_data   = $args['lottery_data'] ?? [];
$remaining      = $args['remaining'] ?? 0;
$has_qa         = $args['has_qa'] ?? false;
$questions      = $args['questions'] ?? [];
$qa_can_display = $args['qa_can_display'] ?? false;
$cart_answer_id = $args['cart_answer_id'] ?? '';
$is_expired     = $args['is_expired'] ?? false;
$is_sold_out    = $args['is_sold_out'] ?? false;
$is_manual_ticket = $args['is_manual_ticket'] ?? false;
$seam_pt        = $args['seam_pt'] ?? 'pt-6';

if (!$product) {
  return;
}

$product_id = $product->get_id();

if ($is_sold_out) {
  return;
}

$bundles_on = !$is_manual_ticket
  && method_exists($product, 'is_predefined_button_enabled')
  && method_exists($product, 'can_display_predefined_buttons')
  && $product->is_predefined_button_enabled()
  && $product->can_display_predefined_buttons();

$bundles_with_qty = $bundles_on
  && method_exists($product, 'can_display_predefined_with_quantity_selector')
  && $product->can_display_predefined_with_quantity_selector();

$bundles_exclusive = $bundles_on && !$bundles_with_qty;

$qty_min = (int) ($lottery_data['minPerOrder'] ?? 1);
$qty_max = (int) ($lottery_data['maxPerOrder'] ?: $remaining);
if ($qty_max < 1) {
  $qty_max = max($qty_min, 1);
}

$bundle_select_message = get_option(
  'lty_settings_predefined_buttons_alert_error_message',
  __('Please select an option', 'nera-competitions')
);
if (!is_string($bundle_select_message) || $bundle_select_message === '') {
  $bundle_select_message = __('Please select a ticket pack', 'nera-competitions');
}

$currency_symbol   = get_woocommerce_currency_symbol();
$price_decimals    = wc_get_price_decimals();
$currency_position = get_option('woocommerce_currency_pos', 'left');
$thousand_sep      = wc_get_price_thousand_separator();
$decimal_sep       = wc_get_price_decimal_separator();
?>

<!-- Ticket Price & Quantity -->
<div class="px-6 pb-6 <?php echo esc_attr($seam_pt); ?>">
  <?php if (function_exists('nera_render_component')) { nera_render_component('TicketPrice', ['price' => $price]); } ?>
  <?php if ($is_manual_ticket): ?>
    <?php do_action('woocommerce_before_add_to_cart_button'); ?>
  <?php else:
    $quantity_layout = nera_get_quantity_selector_layout($product_id);
    if ($bundles_on && function_exists('nera_render_component')) {
      nera_render_component('TicketBundles', ['product' => $product]);
    }
    if (!$bundles_exclusive && function_exists('nera_render_component')) {
      $qty_args = [
        'min'    => $qty_min,
        'max'    => $qty_max,
        'layout' => $quantity_layout,
      ];

      // LFW "Display Discount Tag for Range Slider" → markers on Nera slider layout.
      if (
        $quantity_layout === 'slider'
        && method_exists($product, 'is_predefined_button_enabled')
        && method_exists($product, 'can_display_range_slider_predefined_buttons_discount_tag')
        && $product->is_predefined_button_enabled()
        && $product->can_display_range_slider_predefined_buttons_discount_tag()
        && method_exists($product, 'get_predefined_buttons_rule')
      ) {
        $range_label = method_exists($product, 'get_lty_range_slider_predefined_discount_label')
          ? (string) $product->get_lty_range_slider_predefined_discount_label()
          : '';
        if ($range_label !== '') {
          $markers = [];
          $span = max(1, $qty_max - $qty_min);
          foreach ($product->get_predefined_buttons_rule() as $button_id => $button_data) {
            if (
              method_exists($product, 'is_valid_to_display_predefined_button')
              && !$product->is_valid_to_display_predefined_button($button_id)
            ) {
              continue;
            }
            $marker_qty = method_exists($product, 'get_predefined_buttons_ticket_quantity')
              ? (int) $product->get_predefined_buttons_ticket_quantity($button_id)
              : 0;
            if ($marker_qty < 1 || $marker_qty < $qty_min || $marker_qty > $qty_max) {
              continue;
            }
            $label_html = method_exists($product, 'get_range_slider_predefined_discount_label')
              ? $product->get_range_slider_predefined_discount_label($button_id, $marker_qty)
              : '';
            if ($label_html === '') {
              continue;
            }
            $markers[] = [
              'qty'        => $marker_qty,
              'label_html' => $label_html,
              'percent'    => round(($marker_qty - $qty_min) / $span * 100, 2),
            ];
          }
          if (!empty($markers)) {
            $qty_args['discount_markers'] = $markers;
          }
        }
      }

      nera_render_component('QuantitySelector', $qty_args);
    } elseif ($bundles_exclusive) {
      // Exclusive packs: qty is locked to the selected pack; keep a hidden input for AJAX.
      ?>
      <input
        type="hidden"
        name="quantity"
        value="<?php echo esc_attr($qty_min); ?>"
        min="<?php echo esc_attr($qty_min); ?>"
        max="<?php echo esc_attr($qty_max); ?>"
        data-quantity-input
        data-quantity-exclusive
      />
      <?php
    }
    ?>
  <?php endif; ?>
</div>

<!-- Enter Now Form (includes Skill Challenge Q&A) -->
<div class="px-6 pb-6">
  <?php if (!defined('NERA_PURCHASE_CARD_ALPINE_LOADED')): ?>
    <?php define('NERA_PURCHASE_CARD_ALPINE_LOADED', true); ?>
    <script defer>
      document.addEventListener('alpine:init', () => {
        Alpine.data('purchaseCard', (config) => ({
          selectedAnswer: config.selectedAnswer,
          isSubmitting: false,
          quantity: 1,
          _syncingFromBundle: false,

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

          formatMoney(amount) {
            const n = Number(amount);
            const fixed = (Number.isFinite(n) ? n : 0).toFixed(config.priceDecimals);
            const parts = fixed.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, config.thousandSep);
            const number = config.priceDecimals > 0
              ? parts[0] + config.decimalSep + parts[1]
              : parts[0];
            const symbol = config.currencySymbol || '';
            switch (config.currencyPosition) {
              case 'left_space':
                return symbol + ' ' + number;
              case 'right':
                return number + symbol;
              case 'right_space':
                return number + ' ' + symbol;
              case 'left':
              default:
                return symbol + number;
            }
          },

          getQtyInput() {
            return document.querySelector('[data-quantity-input]');
          },

          getBundleRoot() {
            return document.querySelector('[data-ticket-bundles]');
          },

          getSelectedBundleButton() {
            const root = this.getBundleRoot();
            return root ? root.querySelector('.ncs-ticket-bundles__button--selected') : null;
          },

          setQuantityValue(qty) {
            const qtyInput = this.getQtyInput();
            if (!qtyInput) {
              return;
            }
            const value = String(qty);
            qtyInput.value = value;
            const hidden = document.querySelector('[data-quantity-hidden]');
            if (hidden) {
              hidden.value = value;
            }
            // Drive QuantitySelector UI when present (buttons / slider layout).
            qtyInput.dispatchEvent(new Event('input', { bubbles: true }));
            this.quantity = value;
          },

          clearBundleSelection() {
            const root = this.getBundleRoot();
            if (!root) {
              return;
            }
            root.querySelectorAll('.ncs-ticket-bundles__button').forEach((btn) => {
              btn.classList.remove('ncs-ticket-bundles__button--selected');
              btn.setAttribute('aria-pressed', 'false');
            });
            const idInput = root.querySelector('[data-bundle-id-input]');
            const amountInput = root.querySelector('[data-per-ticket-amount-input]');
            if (idInput) {
              idInput.value = '';
            }
            if (amountInput) {
              amountInput.value = '';
            }
          },

          applyBundleSelection(btn, options = {}) {
            const root = this.getBundleRoot();
            if (!root || !btn) {
              return;
            }
            const skipQty = options.skipQty === true;
            const id = btn.dataset.bundleId || '';
            const qty = parseInt(btn.dataset.ticketQuantity, 10) || 1;
            const perTicket = btn.dataset.perTicketAmount || '';

            root.querySelectorAll('.ncs-ticket-bundles__button').forEach((el) => {
              const selected = el === btn;
              el.classList.toggle('ncs-ticket-bundles__button--selected', selected);
              el.setAttribute('aria-pressed', selected ? 'true' : 'false');
            });

            const idInput = root.querySelector('[data-bundle-id-input]');
            const amountInput = root.querySelector('[data-per-ticket-amount-input]');
            if (idInput) {
              idInput.value = id;
            }
            if (amountInput) {
              amountInput.value = perTicket;
            }

            if (!skipQty) {
              this._syncingFromBundle = true;
              this.setQuantityValue(qty);
              this._syncingFromBundle = false;
            }

            this.updateTicketPrice();
          },

          selectBundle(btn) {
            if (!btn) {
              return;
            }
            const alreadySelected = btn.classList.contains('ncs-ticket-bundles__button--selected');
            // Combined mode: allow toggle off. Exclusive: keep a pack selected (re-click keeps it).
            if (alreadySelected && !config.bundlesExclusive) {
              this.clearBundleSelection();
              this.updateTicketPrice();
              return;
            }
            this.applyBundleSelection(btn);
          },

          syncBundleFromQuantity(qty) {
            if (this._syncingFromBundle || !config.bundlesEnabled || config.bundlesExclusive) {
              return;
            }
            const root = this.getBundleRoot();
            if (!root) {
              return;
            }
            const match = root.querySelector('[data-ticket-quantity="' + String(qty) + '"]');
            if (match) {
              this.applyBundleSelection(match, { skipQty: true });
            } else {
              this.clearBundleSelection();
              this.updateTicketPrice();
            }
          },

          getUnitPrice() {
            const selected = this.getSelectedBundleButton();
            if (selected && selected.dataset.perTicketAmount) {
              const packPrice = parseFloat(selected.dataset.perTicketAmount);
              if (Number.isFinite(packPrice)) {
                return packPrice;
              }
            }
            return Number(config.basePrice) || 0;
          },

          updateTicketPrice() {
            const priceRoot = document.querySelector('[data-ticket-price]');
            if (!priceRoot) {
              return;
            }
            const qtyInput = this.getQtyInput();
            const qty = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;
            const unit = this.getUnitPrice();
            const total = unit * qty;

            const unitEl = priceRoot.querySelector('[data-ticket-price-unit]');
            if (unitEl) {
              unitEl.textContent = this.formatMoney(unit);
            }

            const totalRow = priceRoot.querySelector('[data-ticket-price-total-row]');
            const totalEl = priceRoot.querySelector('[data-ticket-price-total]');
            if (totalRow && totalEl) {
              if (qty > 1 || this.getSelectedBundleButton()) {
                totalEl.textContent = this.formatMoney(total);
                totalRow.hidden = false;
              } else {
                totalRow.hidden = true;
              }
            }
          },

          initBundles() {
            if (!config.bundlesEnabled) {
              return;
            }
            const root = this.getBundleRoot();
            if (!root) {
              return;
            }

            root.querySelectorAll('[data-bundle-id]').forEach((btn) => {
              btn.addEventListener('click', () => this.selectBundle(btn));
            });

            if (!config.bundlesExclusive) {
              document.addEventListener('nera:quantity:change', (e) => {
                const qty = e && e.detail ? e.detail.quantity : null;
                if (qty == null) {
                  return;
                }
                this.quantity = qty;
                this.syncBundleFromQuantity(qty);
              });
            }
          },

          init() {
            if (config.isManualTicket) {
              const updateCount = () => {
                const field = document.querySelector('.lty-lottery-ticket-numbers');
                const count = this.parseTicketCount(field ? field.value : '');
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
              const qtyInput = this.getQtyInput();
              if (qtyInput) {
                this.quantity = qtyInput.value;
                qtyInput.addEventListener('change', (e) => {
                  this.quantity = e.target.value;
                  if (!config.bundlesExclusive) {
                    this.syncBundleFromQuantity(parseInt(e.target.value, 10) || 0);
                  }
                  this.updateTicketPrice();
                });

                const observer = new MutationObserver(() => {
                  this.quantity = qtyInput.value;
                });
                observer.observe(qtyInput, {
                  attributes: true
                });
              }

              this.initBundles();
              this.updateTicketPrice();
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

          resetManualTicketSelection() {
            if (!config.isManualTicket || !window.jQuery) {
              return;
            }

            const $ = window.jQuery;

            // Unselect via plugin handlers — clears closure ticket_numbers[]
            $('.lty-selected-ticket').trigger('click');

            $('.lty-lottery-ticket-numbers').val('');
            $('.lty-lottery-ticket-quantity').val(0);

            document.querySelectorAll('[data-selected-ticket-count]').forEach((el) => {
              el.textContent = '0';
            });
            document.querySelectorAll('[data-count-badge]').forEach((el) => {
              el.dataset.hasSelection = 'false';
            });

            const $active = $('.nera-ticket-dialog .lty-lottery-ticket-tab.lty-active-tab');
            if ($active.length) {
              $active.trigger('click');
            } else {
              $('.nera-ticket-dialog .lty-lottery-ticket-tab').first().trigger('click');
            }
          },

          async submitForm(e) {
            if (config.isManualTicket) {
              const field = document.querySelector('.lty-lottery-ticket-numbers');
              if (!field || this.parseTicketCount(field.value) === 0) {
                Alpine.store('toast').error(config.i18n.selectTickets);
                return;
              }
            }

            if (config.bundlesExclusive) {
              const idInput = document.querySelector('[data-bundle-id-input]');
              if (!idInput || !idInput.value) {
                Alpine.store('toast').error(config.i18n.selectPack);
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
                const qtyInput = this.getQtyInput();
                ajaxData.append('quantity', qtyInput ? qtyInput.value : '1');

                const bundleIdInput = document.querySelector('[data-bundle-id-input]');
                const perTicketInput = document.querySelector('[data-per-ticket-amount-input]');
                if (bundleIdInput && bundleIdInput.value !== '') {
                  ajaxData.append('lty_predefined_button_id', bundleIdInput.value);
                }
                if (perTicketInput && perTicketInput.value !== '') {
                  ajaxData.append('lty_per_ticket_amount', perTicketInput.value);
                }
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

                if (config.isManualTicket) {
                  this.resetManualTicketSelection();
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
  <?php endif; ?>

  <div x-data="purchaseCard({
    selectedAnswer: '<?php echo esc_js($cart_answer_id); ?>',
    productId: '<?php echo esc_js($product_id); ?>',
    hasQa: <?php echo $has_qa && $qa_can_display ? 'true' : 'false'; ?>,
    isManualTicket: <?php echo $is_manual_ticket ? 'true' : 'false'; ?>,
    bundlesEnabled: <?php echo $bundles_on ? 'true' : 'false'; ?>,
    bundlesExclusive: <?php echo $bundles_exclusive ? 'true' : 'false'; ?>,
    basePrice: <?php echo esc_js((float) $price); ?>,
    currencySymbol: '<?php echo esc_js($currency_symbol); ?>',
    currencyPosition: '<?php echo esc_js($currency_position); ?>',
    priceDecimals: <?php echo (int) $price_decimals; ?>,
    thousandSep: '<?php echo esc_js($thousand_sep); ?>',
    decimalSep: '<?php echo esc_js($decimal_sep); ?>',
    ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
    cartUrl: '<?php echo wc_get_cart_url(); ?>',
    i18n: {
          selectTickets: '<?php echo esc_js(__('Please select at least one ticket', 'nera-competitions')); ?>',
          selectAnswer: '<?php echo esc_js(__(
            'Please select an answer to the question',
            'nera-competitions',
          )); ?>',
          selectPack: '<?php echo esc_js($bundle_select_message); ?>',
          error: '<?php echo esc_js(__('Could not add to cart', 'nera-competitions')); ?>',
          success: '<?php echo esc_js(get_field('add_to_cart_success_message', 'option') ?: __('Tickets added to cart!', 'nera-competitions')); ?>',
          viewCart: '<?php echo esc_js(__('View Cart', 'nera-competitions')); ?>',
          generalError: '<?php echo esc_js(__(
            'An error occurred. Please try again.',
            'nera-competitions',
          )); ?>'
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
