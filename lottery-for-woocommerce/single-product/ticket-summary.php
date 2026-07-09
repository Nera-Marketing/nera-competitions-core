<?php
/**
 * Ticket Summary - Theme Override (Dialog Version)
 *
 * Renders a "Browse & Choose Tickets" trigger button that opens a bottom-sheet
 * modal on mobile and a centered dialog on desktop.
 *
 * Overrides lottery-for-woocommerce/templates/single-product/ticket-summary.php
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit;
}

do_action('lty_before_lottery_ticket_container');

$tabs = lty_get_ticket_tabs($product);
$has_multiple_tabs = count($tabs) > 1;

// Max tickets per order for dialog subtitle
$max_per_order = null;
if (method_exists($product, 'get_lty_maximum_tickets_per_order')) {
  $max_per_order = $product->get_lty_maximum_tickets_per_order();
}
?>

<div
  x-data="{
    ticketOpen: false,
    init() {
      const unlock = () => document.body.classList.remove('nera-ticket-dialog-open');
      this.$watch('ticketOpen', open => {
        document.body.classList.toggle('nera-ticket-dialog-open', open);
      });
      return unlock;
    }
  }"
  class="nera-ticket-picker-wrap">

  <!-- ── Trigger Button ───────────────────────────────────────────── -->
  <button
    type="button"
    @click="ticketOpen = true"
    class="nera-ticket-trigger group w-full">
    <div class="flex items-center gap-2">
      <span class="material-symbols-outlined nera-ticket-trigger__icon">confirmation_number</span>
      <span class="font-semibold text-sm"><?php esc_html_e('Browse & Choose Tickets', 'nera-competitions'); ?></span>
    </div>
    <div class="flex items-center gap-2">
      <span class="nera-ticket-picker__count-badge" data-count-badge>
        <span class="material-symbols-outlined" style="font-size:13px;">check_circle</span>
        <span data-selected-ticket-count>0</span>
        <?php esc_html_e('selected', 'nera-competitions'); ?>
      </span>
      <span class="material-symbols-outlined nera-ticket-trigger__arrow">arrow_forward</span>
    </div>
  </button>

  <!-- ── Dialog ──────────────────────────────────────────────────── -->
  <div
    x-show="ticketOpen"
    x-cloak
    @keydown.escape.window="ticketOpen = false"
    class="fixed inset-0 flex items-end sm:items-center justify-center overflow-hidden overscroll-none p-0 sm:p-4"
    style="z-index: 999990 !important; position: fixed !important;">

    <!-- Backdrop -->
    <div
      x-show="ticketOpen"
      x-transition:enter="transition-opacity ease-out duration-250"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition-opacity ease-in duration-200"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
      @click="ticketOpen = false"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm"
      style="z-index: 999990 !important;"></div>

    <!-- Dialog Box (bottom-sheet on mobile, centered modal on desktop) -->
    <div
      x-show="ticketOpen"
      x-transition:enter="transition-all ease-out duration-300"
      x-transition:enter-start="opacity-0 translate-y-12 sm:translate-y-0 sm:scale-95"
      x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
      x-transition:leave="transition-all ease-in duration-200"
      x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
      x-transition:leave-end="opacity-0 translate-y-12 sm:translate-y-0 sm:scale-95"
      class="nera-ticket-dialog relative w-full sm:max-w-lg"
      style="z-index: 999991 !important;">

      <!-- Dialog Header -->
      <div class="nera-ticket-dialog__header">
        <div class="flex items-center gap-3">
          <div class="nera-ticket-dialog__header-icon">
            <span class="material-symbols-outlined">confirmation_number</span>
          </div>
          <div>
            <h3 class="font-bold text-text-primary text-[15px] leading-tight">
              <?php esc_html_e('Choose Your Tickets', 'nera-competitions'); ?>
            </h3>
            <p class="text-xs text-text-secondary mt-0.5">
              <?php if ($max_per_order): ?>
                <?php printf(esc_html__('Select up to %d tickets', 'nera-competitions'), intval($max_per_order)); ?>
              <?php else: ?>
                <?php esc_html_e('Tap a number to select it', 'nera-competitions'); ?>
              <?php endif; ?>
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <div class="nera-ticket-picker__count-badge" data-count-badge>
            <span class="material-symbols-outlined" style="font-size:13px;">check_circle</span>
            <span data-selected-ticket-count>0</span>
            <?php esc_html_e('selected', 'nera-competitions'); ?>
          </div>
          <button
            type="button"
            @click="ticketOpen = false"
            class="nera-ticket-dialog__close">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
      </div>

      <!-- Dialog Body -->
      <div class="nera-ticket-dialog__body">

        <?php do_action('lty_before_lottery_ticket_panel'); ?>

        <!-- Plugin hooks (lucky dip / search) -->
        <?php do_action('lty_before_lottery_ticket'); ?>

        <div class="lty-lottery-ticket-container lty-lottery-ticket-panel">
          <div class="lty-lottery-ticket-wrapper nera-ticket-dialog__inner">

            <?php if ($has_multiple_tabs):
              $tab_count = count($tabs);
              $first_label = array_values($tabs)[0];
            ?>
              <!-- Hidden tab buttons — plugin JS needs these in DOM to fire AJAX -->
              <div class="lty-lottery-ticket-tab-wrapper" style="display:none !important;" aria-hidden="true">
                <?php
                $index = 0;
                foreach ($tabs as $tab_key => $label):
                ?>
                  <button
                    class="lty-lottery-ticket-tab<?php echo $index === 0 ? ' lty-active-tab' : ''; ?>"
                    data-index="<?php echo esc_attr($index++); ?>"
                    data-tab="<?php echo esc_attr($tab_key); ?>"
                    type="button"
                    tabindex="-1">
                    <?php echo esc_html($label); ?>
                  </button>
                <?php endforeach; ?>
              </div>

              <!-- Range select dropdown -->
              <div class="nera-range-select-wrap" data-tab-nav>
                <span class="material-symbols-outlined nera-range-select__icon">confirmation_number</span>
                <select
                  class="nera-range-select"
                  data-tab-select
                  aria-label="<?php esc_attr_e('Select ticket range', 'nera-competitions'); ?>">
                  <?php
                  $si = 0;
                  foreach ($tabs as $tab_key => $label):
                  ?>
                    <option value="<?php echo esc_attr($si++); ?>"><?php echo esc_html($label); ?></option>
                  <?php endforeach; ?>
                </select>
                <span class="material-symbols-outlined nera-range-select__chevron" aria-hidden="true">chevron_right</span>
              </div>
            <?php endif; ?>

            <!-- Ticket Grid (plugin populates via AJAX on tab click) -->
            <div class="lty-lottery-ticket-tab-content nera-ticket-picker__dialog-grid">
              <?php do_action('lty_lottery_ticket_tab_content', $product); ?>
            </div>

          </div>

          <?php do_action('lty_after_lottery_ticket'); ?>

          <!-- Plugin hidden fields MUST be inside .lty-lottery-ticket-container
               so plugin JS (.closest('.lty-lottery-ticket-container').find(...)) can find them -->
          <input type="hidden" name="quantity" class="lty-lottery-ticket-quantity">
          <input type="hidden" name="lty_lottery_ticket_numbers" class="lty-lottery-ticket-numbers">
          <input type="hidden" class="lty-ticket-product-id" value="<?php echo esc_attr($product->get_id()); ?>">
        </div>

        <!-- Legend -->
        <div class="nera-ticket-picker__legend">
          <span class="nera-ticket-picker__legend-item nera-ticket-picker__legend-item--available">
            <span class="nera-ticket-picker__legend-dot"></span>
            <?php esc_html_e('Available', 'nera-competitions'); ?>
          </span>
          <span class="nera-ticket-picker__legend-item nera-ticket-picker__legend-item--selected">
            <span class="nera-ticket-picker__legend-dot"></span>
            <?php esc_html_e('Selected', 'nera-competitions'); ?>
          </span>
          <span class="nera-ticket-picker__legend-item nera-ticket-picker__legend-item--sold">
            <span class="nera-ticket-picker__legend-dot"></span>
            <?php esc_html_e('Sold', 'nera-competitions'); ?>
          </span>
          <span class="nera-ticket-picker__legend-item nera-ticket-picker__legend-item--cart">
            <span class="nera-ticket-picker__legend-dot"></span>
            <?php esc_html_e('In Cart', 'nera-competitions'); ?>
          </span>
        </div>

      </div>

      <!-- Dialog Footer: Confirm -->
      <div class="nera-ticket-dialog__footer">
        <button
          type="button"
          @click="ticketOpen = false"
          class="nera-ticket-dialog__confirm-btn">
          <span class="material-symbols-outlined text-xl">check_circle</span>
          <?php esc_html_e('Confirm Selection', 'nera-competitions'); ?>
          <span class="nera-ticket-dialog__confirm-count">
            <span data-selected-ticket-count>0</span>
            <?php esc_html_e('tickets', 'nera-competitions'); ?>
          </span>
        </button>
      </div>

    </div>
  </div>


  <script>
  (function () {
    function initTabNav() {
      var nav = document.querySelector('[data-tab-nav]');
      if (!nav) return;

      var tabWrapper = nav.closest('.lty-lottery-ticket-wrapper')
                         .querySelector('.lty-lottery-ticket-tab-wrapper');
      var tabs = tabWrapper ? Array.prototype.slice.call(
        tabWrapper.querySelectorAll('.lty-lottery-ticket-tab')
      ) : [];
      if (tabs.length === 0) return;

      var selectEl = nav.querySelector('[data-tab-select]');
      if (!selectEl) return;

      selectEl.addEventListener('change', function () {
        var idx = parseInt(this.value, 10);
        if (tabs[idx]) tabs[idx].click();
      });
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initTabNav);
    } else {
      initTabNav();
    }
  })();
  </script>
</div>

<?php do_action('lty_after_lottery_ticket_container'); ?>
