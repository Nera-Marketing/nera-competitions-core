<?php
/**
 * Competition Detail Hybrid Premium Template
 *
 * Premium hybrid layout for lottery/competition products.
 * Based on Stitch "Competition Detail Hybrid Premium" design.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

get_header();

global $product;

if (!$product || !is_a($product, 'WC_Product')) {
  $product = wc_get_product(get_the_ID());
}

if (!$product) {
  echo '<p>' . esc_html__('Product not found.', 'nera-competitions') . '</p>';
  get_footer();
  return;
}

$product_id = $product->get_id();

// Get lottery data
$lottery_data = nera_get_lottery_product_data($product);
$gallery_images = nera_get_product_gallery_images($product);

// Get end date for countdown calculations
$end_date_gmt = '';
if (method_exists($product, 'get_lty_end_date_gmt')) {
  $end_date_gmt = $product->get_lty_end_date_gmt();
}
if (empty($end_date_gmt)) {
  $end_date_gmt = get_post_meta($product_id, '_lty_end_date_gmt', true);
}
$countdown = nera_get_countdown_parts($end_date_gmt);

// ACF fields
$specifications = function_exists('get_field')
  ? get_field('product_specifications', $product_id)
  : [];

// Calculate ticket data
$max_tickets = $lottery_data['maxTickets'] ?? 0;
$sold_tickets = $lottery_data['soldTickets'] ?? 0;
$remaining = $lottery_data['remainingTickets'] ?? 0;
$progress = $lottery_data['progress'] ?? 0;
$price = $product->get_price();
$currency = get_woocommerce_currency_symbol();

// Status flags
$is_low_stock = $remaining <= 1500 && $remaining > 0;
$is_expired = isset($countdown['expired']) && $countdown['expired'];

// Check if product is started and not closed
$is_started = method_exists($product, 'is_started') ? $product->is_started() : true;
$is_closed = method_exists($product, 'is_closed') ? $product->is_closed() : false;

// Check if Q&A should be displayed and get Q&A data
$has_qa = false;
$qa_can_display = false;
$questions = [];
$cart_answer_id = '';

// Check if lottery plugin functions exist
if (function_exists('lty_is_lottery_product') && lty_is_lottery_product($product)) {
  // Check if Q&A is valid (handles both global and product-level)
  if (method_exists($product, 'is_valid_question_answer') && $product->is_valid_question_answer()) {
    $has_qa = true;

    // Get questions (method handles global vs product-level automatically)
    $questions = $product->get_question_answers();

    // Check if competition is started (not required for Q&A to show configuration)
    $is_started_for_qa = method_exists($product, 'is_started') ? $product->is_started() : true;
    $is_closed_for_qa = method_exists($product, 'is_closed') ? $product->is_closed() : false;

    // Q&A can display if: has valid questions AND started AND not closed
    $qa_can_display =
      !empty($questions) &&
      isset($questions[0]['answers']) &&
      $is_started_for_qa &&
      !$is_closed_for_qa;

    // Get current cart answer if exists
    if ($qa_can_display && WC()->cart) {
      $cart_contents = WC()->cart->get_cart();
      foreach ($cart_contents as $cart_item) {
        if (isset($cart_item['product_id']) && $cart_item['product_id'] == $product_id) {
          $cart_answer_id = isset($cart_item['lty_lottery']['answers'])
            ? $cart_item['lty_lottery']['answers']
            : '';
          break;
        }
      }
    }
  }
}

// Thumbnail display settings
$visible_thumbs = 4;
$extra_images = max(0, count($gallery_images) - $visible_thumbs);
?>

<main id="primary" class="site-main bg-gray-50 min-h-screen">


  <?php do_action('woocommerce_before_single_product'); ?>

  <div id="product-<?php echo esc_attr($product_id); ?>" <?php wc_product_class('', $product); ?>>

    <!-- Breadcrumb Navigation -->
    <?php get_template_part('template-parts/single-product/breadcrumb'); ?>

    <!-- Main Content Section -->
    <section class="py-8 lg:py-10">
      <div class="max-w-7xl mx-auto flex w-full min-w-0 flex-col px-4 lg:px-8">
        <div class="grid w-full min-w-0 grid-cols-1 gap-8 lg:grid-cols-12 lg:gap-10">

          <!-- Left Column: Gallery + Tabs (desktop) / Unwrapped for mobile ordering -->
          <div class="max-lg:contents lg:flex lg:flex-col lg:gap-8 lg:col-span-7">
            <!-- 1. Product Gallery (mobile: top) -->
            <div class="order-1">
              <?php get_template_part('template-parts/single-product/product-gallery', null, [
                'images' => $gallery_images,
                'product' => $product,
                'badge_text' => nera_product_has_featured_tag($product)
                  ? __('Featured Prize', 'nera-competitions')
                  : '',
                'badge_color' => 'red',
              ]); ?>
            </div>

            <!-- 3. Tabs Section (mobile: bottom) -->
            <div class="order-3 lg:order-2">
              <?php get_template_part('template-parts/single-product/tabs', null, [
                'product' => $product,
                'specifications' => $specifications,
                'end_date_gmt' => $end_date_gmt,
              ]); ?>
            </div>
          </div>

          <!-- 2. Product Details / Purchase Card (mobile: middle) -->
          <div class="order-2 lg:col-span-5">
            <?php get_template_part('template-parts/single-product/purchase-card', null, [
              'product' => $product,
              'countdown' => $countdown,
              'sold_tickets' => $sold_tickets,
              'max_tickets' => $max_tickets,
              'remaining' => $remaining,
              'progress' => $progress,
              'is_low_stock' => $is_low_stock,
              'price' => $price,
              'lottery_data' => $lottery_data,
              'has_qa' => $has_qa,
              'questions' => $questions,
              'qa_can_display' => $qa_can_display,
              'cart_answer_id' => $cart_answer_id,
              'is_expired' => $is_expired,
            ]); ?>
          </div>

        </div>

      </div>
    </section>

    <!-- Instant Win prizes: own section below hero grid (avoids WC flex/float fighting inner flex-col) -->
    <section class="pb-8 lg:pb-10" aria-label="<?php esc_attr_e('Instant win prizes', 'nera-competitions'); ?>">
      <div class="max-w-7xl mx-auto w-full min-w-0 px-4 lg:px-8">
        <?php get_template_part('template-parts/single-product/instant-wins-section', null, [
          'product' => $product,
        ]); ?>
      </div>
    </section>

    <?php do_action('nera_before_related_competitions', $product); ?>

    <!-- Related Competitions Section -->
    <?php
    $related_ids = nera_get_related_lottery_products($product_id, 4);
    if (!empty($related_ids)): ?>
      <section class="py-12 lg:py-16 bg-surface border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
          <?php get_template_part('template-parts/single-product/related-competitions', null, [
            'product' => $product,
            'related_ids' => $related_ids,
          ]); ?>
        </div>
      </section>
    <?php endif;
    ?>

  </div>

  <?php do_action('woocommerce_after_single_product'); ?>

</main>

<!-- Enter By Post Modal - Rendered at body level to fix z-index stacking context -->
<div x-data 
     x-show="$store.postDialog.show" 
     x-cloak
     @keydown.escape.window="$store.postDialog.show = false"
     data-post-modal
     class="fixed inset-0 z-[999999] flex items-center justify-center p-4"
     style="z-index: 999999 !important; position: fixed !important;">
  <!-- Backdrop -->
  <div x-show="$store.postDialog.show"
       x-transition:enter="transition-opacity ease-out duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition-opacity ease-in duration-200"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       @click="$store.postDialog.show = false"
       class="fixed inset-0 bg-black/60 backdrop-blur-sm"
       style="z-index: 999998 !important;"></div>

  <!-- Modal Content -->
  <div x-show="$store.postDialog.show"
       x-transition:enter="transition-all ease-out duration-300"
       x-transition:enter-start="opacity-0 scale-95 translate-y-4"
       x-transition:enter-end="opacity-100 scale-100 translate-y-0"
       x-transition:leave="transition-all ease-in duration-200"
       x-transition:leave-start="opacity-100 scale-100 translate-y-0"
       x-transition:leave-end="opacity-0 scale-95 translate-y-4"
       class="relative bg-primary rounded-2xl shadow-2xl max-w-lg w-full p-8 text-white"
       style="z-index: 999999 !important; position: relative !important;">
    
    <!-- Close Button -->
    <button type="button"
            @click="$store.postDialog.show = false"
            class="absolute top-4 right-4 text-white hover:text-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-white/50 rounded-full p-1">
      <span class="material-symbols-outlined text-2xl">close</span>
    </button>

    <!-- Modal Content -->
    <?php
    $postal_instruction = get_field('postal_instruction_text', 'option');
    $postal_company = get_field('postal_company_name', 'option');
    $postal_address = get_field('postal_address_line_1', 'option');
    $postal_town = get_field('postal_town_city', 'option');
    $postal_postcode = get_field('postal_postcode', 'option');
    $postal_items = get_field('postal_required_items', 'option');
    $postal_terms_text = get_field('postal_terms_text', 'option');
    $postal_terms_url = get_field('postal_terms_url', 'option');

    if (!$postal_instruction) {
      $postal_instruction =
        'To enter this competition by post, send an unenclosed postcard with sufficient postage (1st or 2nd class stamp) to:';
    }
    if (!$postal_terms_text) {
      $postal_terms_text = 'All postal entries are subject to our';
    }
    if (!$postal_terms_url) {
      $postal_terms_url = home_url('/terms-and-conditions/');
    }
    ?>
    <div class="space-y-6">
      <!-- Instruction Text -->
      <p class="text-lg leading-relaxed">
        <?php echo esc_html($postal_instruction); ?>
      </p>

      <!-- Postal Address -->
      <div class="space-y-1">
        <?php if ($postal_company): ?><p><?php echo esc_html($postal_company); ?></p><?php endif; ?>
        <?php if ($postal_address): ?><p><?php echo esc_html($postal_address); ?></p><?php endif; ?>
        <?php if ($postal_town): ?><p><?php echo esc_html($postal_town); ?></p><?php endif; ?>
        <?php if ($postal_postcode): ?><p><?php echo esc_html(
  $postal_postcode,
); ?></p><?php endif; ?>
      </div>

      <!-- Divider -->
      <div class="border-t border-white/20"></div>

      <!-- Required Information -->
      <div>
        <p class="font-semibold mb-3">
          <?php _e('Include the following:', 'nera-competitions'); ?>
        </p>
        <ul class="space-y-2 list-disc list-inside">
          <?php if ($postal_items): ?>
            <?php foreach ($postal_items as $item): ?>
              <li><?php echo esc_html($item['item_label']); ?></li>
            <?php endforeach; ?>
          <?php else: ?>
            <li><?php _e('Competition name or product title', 'nera-competitions'); ?></li>
            <li><?php _e('Full name', 'nera-competitions'); ?></li>
            <li><?php _e('Postal address', 'nera-competitions'); ?></li>
            <li><?php _e('Telephone number', 'nera-competitions'); ?></li>
            <li><?php _e('Email address', 'nera-competitions'); ?></li>
            <li><?php _e('Date of birth', 'nera-competitions'); ?></li>
            <li><?php _e('Answer to the competition question', 'nera-competitions'); ?></li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Divider -->
      <div class="border-t border-white/20"></div>

      <!-- Terms & Conditions Link -->
      <div class="text-sm">
        <p>
          <?php echo esc_html($postal_terms_text); ?>
          <a href="<?php echo esc_url($postal_terms_url); ?>"
             target="_blank"
             class="font-bold !underline hover:text-gray-200 transition-colors">
            <?php _e('TERMS AND CONDITIONS', 'nera-competitions'); ?>
          </a>
        </p>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Animate progress bar on load
    const progressBars = document.querySelectorAll('[data-progress]');
    progressBars.forEach(function (bar) {
      const progress = bar.dataset.progress;
      setTimeout(function () {
        bar.style.width = progress + '%';
      }, 300);
    });

    // Tab switching
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        const tabId = this.dataset.tab;

        // Update button states
        tabBtns.forEach(function (b) {
          b.classList.remove('active', 'text-primary', 'border-primary');
          b.classList.add('text-text-secondary');
        });
        this.classList.add('active', 'text-primary', 'border-primary');
        this.classList.remove('text-text-secondary');

        // Show/hide content
        tabContents.forEach(function (content) {
          if (content.dataset.tabContent === tabId) {
            content.classList.remove('hidden');
          } else {
            content.classList.add('hidden');
          }
        });
      });
    });

    // Quantity controls
    const quantityInput = document.querySelector('[data-quantity-input]');
    const quantityHidden = document.querySelector('[data-quantity-hidden]');
    const minusBtn = document.querySelector('[data-quantity-minus]');
    const plusBtn = document.querySelector('[data-quantity-plus]');
    const addBtns = document.querySelectorAll('[data-quantity-add]');

    function updateQuantity(newValue) {
      const max = parseInt(quantityInput.max, 10) || 999;
      const min = parseInt(quantityInput.min, 10) || 1;
      const value = Math.max(min, Math.min(max, newValue));
      quantityInput.value = value;
      if (quantityHidden) quantityHidden.value = value;
    }

    if (minusBtn && quantityInput) {
      minusBtn.addEventListener('click', function () {
        updateQuantity(parseInt(quantityInput.value, 10) - 1);
      });
    }

    if (plusBtn && quantityInput) {
      plusBtn.addEventListener('click', function () {
        updateQuantity(parseInt(quantityInput.value, 10) + 1);
      });
    }

    addBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        const addAmount = parseInt(this.dataset.quantityAdd, 10);
        updateQuantity(parseInt(quantityInput.value, 10) + addAmount);
      });
    });

    if (quantityInput) {
      quantityInput.addEventListener('change', function () {
        updateQuantity(parseInt(this.value, 10) || 1);
      });
    }
  });
</script>

<?php get_footer();
?>
