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
        <div class="grid w-full min-w-0 grid-cols-1 gap-8 lg:grid-cols-12 lg:gap-x-10 lg:gap-y-0">

          <!-- Left column: gallery + tabs (contents on mobile, flex-col on desktop) -->
          <div class="contents lg:flex lg:flex-col lg:col-span-7 lg:row-start-1 lg:self-start">

            <!-- Product Gallery (mobile order 1) -->
            <div class="order-1 bg-surface p-6 rounded-2xl lg:rounded-b-none lg:pb-4">
              <?php get_template_part('template-parts/single-product/product-gallery', null, [
                'images' => $gallery_images,
                'product' => $product,
                'badge_text' => nera_product_has_featured_tag($product)
                  ? __('Featured Prize', 'nera-competitions')
                  : '',
                'badge_color' => 'red',
              ]); ?>
            </div>

            <!-- Tabs Section (mobile order 3) -->
            <div class="order-3 bg-surface p-6 rounded-2xl lg:rounded-t-none lg:pt-4">
              <?php get_template_part('template-parts/single-product/tabs', null, [
                'product' => $product,
                'specifications' => $specifications,
                'end_date_gmt' => $end_date_gmt,
              ]); ?>
            </div>

          </div>

          <!-- Product Details / Purchase Card (mobile order 2 | desktop col 8-12) -->
          <div class="order-2 lg:order-none lg:col-start-8 lg:col-span-5 lg:row-start-1 lg:self-start">
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

    <?php nera_competitions_render_instant_win_prizes_section($product); ?>

    <?php nera_competitions_render_spin_to_win_prizes_section($product); ?>

    <?php do_action('nera_before_related_competitions', $product); ?>

    <!-- Related Competitions Section -->
    <?php
    $related_ids = nera_get_related_lottery_products($product_id, 4);
    if (!empty($related_ids)): ?>
      <section class="py-12 lg:py-16 bg-surface border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 lg:px-0">
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
