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

<div>
  <div class="bg-surface rounded-2xl shadow-xl border border-gray-100 overflow-hidden">

    <?php if ($is_sold_out): ?>

      <!-- Sold out: title + Tickets Sold progress only -->
      <div class="p-6 pb-4">
        <div class="flex flex-wrap items-center gap-2 mb-1">
          <h1 class="text-2xl lg:text-3xl font-bold text-text-primary leading-tight">
            <?php printf(__('Win a %s', 'nera-competitions'), esc_html($product->get_name())); ?>
          </h1>
          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[0.65rem] font-extrabold uppercase tracking-widest bg-gray-200 text-text-secondary">
            <?php esc_html_e('Sold Out', 'nera-competitions'); ?>
          </span>
        </div>
      </div>

      <div class="px-6 pb-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-semibold text-text-primary">
            <?php _e('Tickets Sold', 'nera-competitions'); ?>
          </span>
          <span class="text-sm font-semibold text-text-primary">
            <?php printf('%s / %s', number_format($sold_tickets), number_format($max_tickets)); ?>
          </span>
        </div>
        <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
          <div class="h-full rounded-full transition-all duration-1000 ease-out bg-primary" style="width: 0%;"
            data-progress="<?php echo esc_attr($progress); ?>"></div>
        </div>
      </div>

    <?php else: ?>

    <!-- Product Title -->
    <div class="p-6 pb-4">
      <h1 class="text-2xl lg:text-3xl font-bold text-text-primary leading-tight">
        <?php printf(__('Win a %s', 'nera-competitions'), esc_html($product->get_name())); ?>
      </h1>
    </div>

    <!-- Countdown Section -->
    <div class="px-6 pb-6">
      <div class="flex items-center gap-2 mb-3">
        <?php if ($is_expired): ?>
          <span class="material-symbols-outlined text-gray-400 text-lg">lock</span>
          <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
            <?php _e('Competition Closed', 'nera-competitions'); ?>
          </span>
        <?php else: ?>
          <span class="material-symbols-outlined text-red-500 text-lg">schedule</span>
          <span class="text-xs font-bold text-red-500 uppercase tracking-wider">
            <?php _e('Closes In', 'nera-competitions'); ?>
          </span>
        <?php endif; ?>
      </div>

      <?php
      // Get countdown date directly from product method for JavaScript
      $countdown_date_for_js = '';
      if (method_exists($product, 'get_countdown_timer_enddate')) {
        $countdown_date_for_js = $product->get_countdown_timer_enddate();
      }
      ?>

      <?php if (!empty($countdown_date_for_js) && !$is_expired): ?>
        <!-- Use lottery plugin's countdown format - JS will update these values -->
        <div class="lty-lottery-countdown-timer competition-countdown grid grid-cols-4 gap-3"
          data-time="<?php echo esc_attr($countdown_date_for_js); ?>">
          <div class="text-center">
            <div class="bg-primary rounded-xl p-3 lg:p-6">
              <span class="lty-lottery-timer-content block text-2xl lg:text-3xl font-bold !text-white"
                id="lty_lottery_days">
                <?php echo esc_html(str_pad($countdown['days'] ?? 0, 2, '0', STR_PAD_LEFT)); ?>
              </span>
            </div>
            <span class="block mt-2 text-xs font-semibold text-text-secondary uppercase tracking-wide">
              <?php _e('Days', 'nera-competitions'); ?>
            </span>
          </div>
          <div class="text-center">
            <div class="bg-primary rounded-xl p-3 lg:p-6">
              <span class="lty-lottery-timer-content block text-2xl lg:text-3xl font-bold !text-white"
                id="lty_lottery_hours">
                <?php echo esc_html(str_pad($countdown['hours'] ?? 0, 2, '0', STR_PAD_LEFT)); ?>
              </span>
            </div>
            <span class="block mt-2 text-xs font-semibold text-text-secondary uppercase tracking-wide">
              <?php _e('Hours', 'nera-competitions'); ?>
            </span>
          </div>
          <div class="text-center">
            <div class="bg-primary rounded-xl p-3 lg:p-6">
              <span class="lty-lottery-timer-content block text-2xl lg:text-3xl font-bold !text-white"
                id="lty_lottery_minutes">
                <?php echo esc_html(str_pad($countdown['minutes'] ?? 0, 2, '0', STR_PAD_LEFT)); ?>
              </span>
            </div>
            <span class="block mt-2 text-xs font-semibold text-text-secondary uppercase tracking-wide">
              <?php _e('Mins', 'nera-competitions'); ?>
            </span>
          </div>
          <div class="text-center">
            <div class="bg-primary rounded-xl p-3 lg:p-6">
              <span class="lty-lottery-timer-content block text-2xl lg:text-3xl font-bold !text-white"
                id="lty_lottery_seconds">
                <?php echo esc_html(str_pad($countdown['seconds'] ?? 0, 2, '0', STR_PAD_LEFT)); ?>
              </span>
            </div>
            <span class="block mt-2 text-xs font-semibold text-text-secondary uppercase tracking-wide">
              <?php _e('Secs', 'nera-competitions'); ?>
            </span>
          </div>
        </div>
      <?php elseif ($is_expired): ?>
        <!-- Competition closed - static display, no plugin classes or data attributes -->
        <div class="grid grid-cols-4 gap-3">
          <div class="text-center">
            <div class="bg-primary/40 rounded-xl p-3 lg:p-6">
              <span class="block text-2xl lg:text-3xl font-bold text-white">00</span>
            </div>
            <span class="block mt-2 text-xs font-semibold text-text-secondary uppercase tracking-wide">
              <?php _e('Days', 'nera-competitions'); ?>
            </span>
          </div>
          <div class="text-center">
            <div class="bg-primary/40 rounded-xl p-3 lg:p-6">
              <span class="block text-2xl lg:text-3xl font-bold text-white">00</span>
            </div>
            <span class="block mt-2 text-xs font-semibold text-text-secondary uppercase tracking-wide">
              <?php _e('Hours', 'nera-competitions'); ?>
            </span>
          </div>
          <div class="text-center">
            <div class="bg-primary/40 rounded-xl p-3 lg:p-6">
              <span class="block text-2xl lg:text-3xl font-bold text-white">00</span>
            </div>
            <span class="block mt-2 text-xs font-semibold text-text-secondary uppercase tracking-wide">
              <?php _e('Mins', 'nera-competitions'); ?>
            </span>
          </div>
          <div class="text-center">
            <div class="bg-primary/40 rounded-xl p-3 lg:p-6">
              <span class="block text-2xl lg:text-3xl font-bold text-white">00</span>
            </div>
            <span class="block mt-2 text-xs font-semibold text-text-secondary uppercase tracking-wide">
              <?php _e('Secs', 'nera-competitions'); ?>
            </span>
          </div>
        </div>
      <?php else: ?>
        <!-- Fallback if no countdown available -->
        <div class="grid grid-cols-4 gap-3">
          <div class="text-center">
            <div class="bg-primary rounded-xl p-3 lg:p-4">
              <span class="block text-2xl lg:text-3xl font-bold text-white">--</span>
            </div>
            <span class="block mt-2 text-xs font-semibold text-text-secondary uppercase tracking-wide">
              <?php _e('Days', 'nera-competitions'); ?>
            </span>
          </div>
          <div class="text-center">
            <div class="bg-primary rounded-xl p-3 lg:p-4">
              <span class="block text-2xl lg:text-3xl font-bold text-white">--</span>
            </div>
            <span class="block mt-2 text-xs font-semibold text-text-secondary uppercase tracking-wide">
              <?php _e('Hours', 'nera-competitions'); ?>
            </span>
          </div>
          <div class="text-center">
            <div class="bg-primary rounded-xl p-3 lg:p-4">
              <span class="block text-2xl lg:text-3xl font-bold text-white">--</span>
            </div>
            <span class="block mt-2 text-xs font-semibold text-text-secondary uppercase tracking-wide">
              <?php _e('Mins', 'nera-competitions'); ?>
            </span>
          </div>
          <div class="text-center">
            <div class="bg-primary rounded-xl p-3 lg:p-4">
              <span class="block text-2xl lg:text-3xl font-bold text-white">--</span>
            </div>
            <span class="block mt-2 text-xs font-semibold text-text-secondary uppercase tracking-wide">
              <?php _e('Secs', 'nera-competitions'); ?>
            </span>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Ticket Progress Section -->
    <div class="px-6 pb-6">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-semibold text-text-primary">
          <?php _e('Tickets Sold', 'nera-competitions'); ?>
        </span>
        <span class="text-sm font-semibold text-text-primary">
          <?php printf('%s / %s', number_format($sold_tickets), number_format($max_tickets)); ?>
        </span>
      </div>

      <!-- Progress Bar -->
      <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
        <div class="h-full rounded-full transition-all duration-1000 ease-out bg-primary" style="width: 0%;"
          data-progress="<?php echo esc_attr($progress); ?>"></div>
      </div>

      <!-- Low Stock Warning -->
      <?php if ($is_low_stock): ?>
        <div class="mt-2 flex items-center gap-1 text-orange-500 text-sm">
          <span class="material-symbols-outlined text-base">local_fire_department</span>
          <span>
            <?php printf(
              __('Selling fast! Only %s tickets remaining.', 'nera-competitions'),
              '<strong>' . number_format($remaining) . '</strong>',
            ); ?>
          </span>
        </div>
      <?php endif; ?>
    </div>

    <!-- Ticket Price & Quantity -->
    <div class="px-6 pb-6">
      <div class="flex items-center justify-between mb-4">
        <span class="text-sm font-semibold text-text-primary">
          <?php _e('Ticket Price', 'nera-competitions'); ?>
        </span>
        <span
          class="inline-flex items-center gap-1 bg-primary/10 text-primary font-bold text-sm px-3 py-1 rounded-full">
          <?php echo wc_price($price); ?>
          <span class="font-normal text-primary/70">
            <?php _e('each', 'nera-competitions'); ?>
          </span>
        </span>
      </div>

      <?php if ($is_manual_ticket): ?>
        <!-- Ticket Picker (User Chooses mode) -->
        <?php do_action('woocommerce_before_add_to_cart_button'); ?>
      <?php else: ?>
        <!-- Quantity Selector (Auto-assign mode) -->
        <div class="flex items-center gap-3">
          <!-- Main Quantity Control -->
          <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
            <button type="button"
              class="w-10 h-10 flex items-center justify-center text-text-secondary hover:text-primary hover:bg-gray-50 transition-colors"
              data-quantity-minus>
              <span class="material-symbols-outlined text-xl">remove</span>
            </button>
            <input type="number" name="quantity" value="1"
              min="<?php echo esc_attr($lottery_data['minPerOrder'] ?? 1); ?>"
              max="<?php echo esc_attr($lottery_data['maxPerOrder'] ?: $remaining); ?>"
              class="qty w-12 h-10 text-center font-semibold text-text-primary border-x border-gray-200 focus:outline-none"
              data-quantity-input />
            <button type="button"
              class="w-10 h-10 flex items-center justify-center text-text-secondary hover:text-primary hover:bg-gray-50 transition-colors"
              data-quantity-plus>
              <span class="material-symbols-outlined text-xl">add</span>
            </button>
          </div>

          <!-- Quick Add Buttons -->
          <div class="flex items-center gap-2">
            <button type="button"
              class="px-4 h-10 bg-primary text-white font-semibold text-sm rounded-lg hover:bg-primary-dark transition-colors"
              data-quantity-add="5">
              +5
            </button>
            <button type="button"
              class="px-4 h-10 border border-gray-200 text-text-secondary font-semibold text-sm rounded-lg hover:border-primary hover:text-primary transition-colors"
              data-quantity-add="10">
              +10
            </button>
            <button type="button"
              class="px-4 h-10 border border-gray-200 text-text-secondary font-semibold text-sm rounded-lg hover:border-primary hover:text-primary transition-colors"
              data-quantity-add="20">
              +20
            </button>
          </div>
        </div>
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

          <?php if ($has_qa && $qa_can_display && !empty($questions)): ?>
            <!-- Skill Challenge Section -->
            <!-- Q&A configured and can display -->
            <div class="bg-slate-50 rounded-xl p-6 skill-challenge-wrapper border border-slate-100">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                  <span class="material-symbols-outlined text-xl">psychology</span>
                </div>
                <h4 class="font-bold text-slate-800 uppercase text-sm tracking-wide">
                  <?php _e('Question Answers', 'nera-competitions'); ?>
                </h4>
              </div>

              <?php // Manual rendering of Q&A section
            // Manual rendering of Q&A section
            $question = $questions[0]; ?>
              <div class="nera-contest-qa-container mb-5">
                <div class="nera-contest-question font-medium text-slate-900 text-lg mb-4">
                  <?php echo esc_html($question['question']); ?>
                </div>

                <ul class="nera-contest-answers flex flex-col gap-3">
                  <?php foreach ($question['answers'] as $key => $answer): ?>
                    <li class="relative group cursor-pointer" @click="selectAnswer('<?php echo esc_js(
                      $key,
                    ); ?>')">

                      <div class="flex items-center p-4 bg-surface border-2 rounded-xl transition-all duration-200"
                        :class="selectedAnswer === '<?php echo esc_js(
                          $key,
                        ); ?>' ? 'border-primary ring-1 ring-primary/20 shadow-sm' : 'border-slate-200 hover:border-primary/50'">
                        <!-- Radio Circle -->
                        <div
                          class="w-6 h-6 rounded-full border-2 mr-4 flex items-center justify-center flex-shrink-0 transition-colors"
                          :class="selectedAnswer === '<?php echo esc_js(
                            $key,
                          ); ?>' ? 'border-primary bg-primary' : 'border-slate-300 group-hover:border-primary/50 bg-surface'">
                          <div class="w-2.5 h-2.5 rounded-full bg-surface transition-opacity duration-200"
                            :class="selectedAnswer === '<?php echo esc_js(
                              $key,
                            ); ?>' ? 'opacity-100' : 'opacity-0'">
                          </div>
                        </div>

                        <!-- Label -->
                        <span class="text-base font-medium"
                          :class="selectedAnswer === '<?php echo esc_js(
                            $key,
                          ); ?>' ? 'text-slate-900' : 'text-slate-600 group-hover:text-slate-800'">
                          <?php echo esc_html($answer['label']); ?>
                        </span>
                      </div>

                      <!-- Keep submitted key aligned with lottery plugin request parsing -->
                      <input type="radio" name="lty_question_answer_id" value="<?php echo esc_attr(
                        $key,
                      ); ?>"
                        :checked="selectedAnswer === '<?php echo esc_js($key); ?>'" class="sr-only"
                        style="display:none !important;" />
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php elseif ($has_qa && !$qa_can_display): ?>
              <!-- Q&A configured but competition not yet started -->
              <div class="border border-gray-200 rounded-xl p-5 mb-6 skill-challenge-wrapper">
                <div class="flex items-center gap-2 mb-4">
                  <span class="material-symbols-outlined text-primary text-lg">psychology</span>
                  <h4 class="font-bold text-text-primary uppercase text-sm tracking-wide">
                    <?php _e('Question Answers', 'nera-competitions'); ?>
                  </h4>
                </div>
                <p class="text-sm text-text-secondary">
                  <?php _e(
                    'The question answers will be available once the competition starts.',
                    'nera-competitions',
                  ); ?>
                </p>
              </div>
            <?php endif; ?>

            <?php if (!$is_manual_ticket): ?>
            <input type="hidden" name="quantity" value="1" data-quantity-hidden />
            <?php endif; ?>
            <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product_id); ?>"
              class="w-full bg-primary text-white text-lg py-4 rounded-xl font-bold shadow-lg shadow-primary/20 hover:bg-primary-dark hover:shadow-xl hover:shadow-primary/30 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed lty-participate-now-button"
              :class="{'opacity-75 cursor-wait': isSubmitting}"
              :disabled="isSubmitting || <?php echo $is_expired
                ? 'true'
                : 'false'; ?>" <?php echo $is_expired ? 'disabled' : ''; ?>>

              <template x-if="isSubmitting">
                <span class="material-symbols-outlined animate-spin text-xl">progress_activity</span>
              </template>

              <template x-if="!isSubmitting">
                <span>
                  <?php if ($is_expired): ?>
                    <?php _e('Competition Ended', 'nera-competitions'); ?>
                  <?php else: ?>
                    <?php _e('Enter Now', 'nera-competitions'); ?>
                    <span class="material-symbols-outlined relative top-1">arrow_forward</span>
                  <?php endif; ?>
                </span>
              </template>
            </button>
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
      <div class="flex items-center justify-center gap-6">
        <div class="flex items-center gap-1.5 text-sm text-text-secondary">
          <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
          <?php _e('Guaranteed Draw', 'nera-competitions'); ?>
        </div>
        <div class="flex items-center gap-1.5 text-sm text-text-secondary">
          <span class="material-symbols-outlined text-green-500 text-lg">lock</span>
          <?php _e('Secure Payment', 'nera-competitions'); ?>
        </div>
      </div>
    </div>

    <?php endif; ?>

  </div>
</div>