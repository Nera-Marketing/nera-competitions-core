<?php
/**
 * Product Tabs Template Part
 *
 * Tabbed content area for Description, Rules, and FAQ.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

$product = isset($args['product']) ? $args['product'] : null;

if (!$product) {
  return;
}

$product_id = $product->get_id();

// Get content
$description = $product->get_description();
$rules = get_field('competition_rules', $product_id);
$faqs = get_field('product_faqs', $product_id);

// Build tabs array
$tabs = [];

if (!empty($description)) {
  $tabs['description'] = [
    'label' => __('Description', 'nera-competitions'),
    'icon' => 'description',
  ];
}

if (!empty($rules)) {
  $tabs['rules'] = [
    'label' => __('Rules', 'nera-competitions'),
    'icon' => 'gavel',
  ];
}

if (!empty($faqs)) {
  $tabs['faq'] = [
    'label' => __('FAQ', 'nera-competitions'),
    'icon' => 'help',
  ];
}

if (empty($tabs)) {
  return;
}

$first_tab = array_key_first($tabs);
?>

<div class="product-tabs" data-product-tabs>
  <!-- Tab Navigation -->
  <div class="tabs-nav border-b border-gray-200">
    <div class="flex gap-1 overflow-x-auto -mb-px">
      <?php foreach ($tabs as $tab_id => $tab): ?>
        <button
          type="button"
          class="tab-btn flex items-center gap-2 px-6 py-4 text-sm font-semibold border-b-2 transition-all whitespace-nowrap <?php echo $tab_id ===
          $first_tab
            ? 'border-primary text-primary'
            : 'border-transparent text-text-secondary hover:text-text-primary hover:border-gray-300'; ?>"
          data-tab="<?php echo esc_attr($tab_id); ?>"
          aria-selected="<?php echo $tab_id === $first_tab ? 'true' : 'false'; ?>"
          role="tab"
        >
          <span class="material-symbols-outlined text-lg"><?php echo esc_html(
            $tab['icon'],
          ); ?></span>
          <?php echo esc_html($tab['label']); ?>
        </button>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Tab Panels -->
  <div class="tabs-content pt-8">

    <!-- Description Tab -->
    <?php if (isset($tabs['description'])): ?>
      <div
        class="tab-panel <?php echo $first_tab !== 'description' ? 'hidden' : ''; ?>"
        data-tab-panel="description"
        role="tabpanel"
      >
        <div class="prose prose-lg max-w-none">
          <?php echo apply_filters('the_content', $description); ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Rules Tab -->
    <?php if (isset($tabs['rules'])): ?>
      <div
        class="tab-panel <?php echo $first_tab !== 'rules' ? 'hidden' : ''; ?>"
        data-tab-panel="rules"
        role="tabpanel"
      >
        <div class="bg-gray-50 rounded-2xl p-6 lg:p-8">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
              <span class="material-symbols-outlined text-primary text-2xl">gavel</span>
            </div>
            <div>
              <h3 class="text-lg font-bold text-text-primary"><?php _e(
                'Competition Rules',
                'nera-competitions',
              ); ?></h3>
              <p class="text-sm text-text-secondary"><?php _e(
                'Please read carefully before entering',
                'nera-competitions',
              ); ?></p>
            </div>
          </div>
          <div class="prose prose-sm max-w-none">
            <?php echo wp_kses_post($rules); ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- FAQ Tab -->
    <?php if (isset($tabs['faq'])): ?>
      <div
        class="tab-panel <?php echo $first_tab !== 'faq' ? 'hidden' : ''; ?>"
        data-tab-panel="faq"
        role="tabpanel"
      >
        <div class="space-y-4" data-faq-accordion>
          <?php foreach ($faqs as $index => $faq): ?>
            <?php if (!empty($faq['question']) && !empty($faq['answer'])): ?>
              <div class="faq-item bg-gray-50 rounded-2xl overflow-hidden">
                <button
                  type="button"
                  class="faq-toggle w-full flex items-center justify-between p-5 text-left"
                  aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                >
                  <span class="font-semibold text-text-primary pr-4">
                    <?php echo esc_html($faq['question']); ?>
                  </span>
                  <span class="faq-icon flex-shrink-0 w-8 h-8 rounded-full bg-surface shadow flex items-center justify-center transition-transform <?php echo $index ===
                  0
                    ? 'rotate-180'
                    : ''; ?>">
                    <span class="material-symbols-outlined text-text-secondary">expand_more</span>
                  </span>
                </button>
                <div class="faq-content <?php echo $index !== 0 ? 'hidden' : ''; ?>">
                  <div class="px-5 pb-5 text-text-secondary">
                    <?php echo wp_kses_post($faq['answer']); ?>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>
