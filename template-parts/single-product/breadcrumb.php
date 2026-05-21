<?php
/**
 * Breadcrumb template part for Single Competition
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

global $product;

if (!$product) {
  return;
}
?>

<!-- Breadcrumb Navigation -->
<nav class="bg-surface border-b border-gray-100 rounded-lg">
  <div class="px-4 lg:px-8 py-4">
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
      <li>
        <a href="<?php echo esc_url(home_url('/')); ?>"
          class="text-text-secondary hover:text-primary transition-colors whitespace-nowrap">
          <?php _e('Home', 'nera-competitions'); ?>
        </a>
      </li>
      <li class="text-gray-300">/</li>
      <li>
        <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>"
          class="text-primary hover:text-primary-dark transition-colors whitespace-nowrap">
          <?php echo esc_html(get_the_title(wc_get_page_id('shop'))); ?>
        </a>
      </li>
      <li class="text-gray-300">/</li>
      <li class="text-text-primary font-medium min-w-0">
        <?php echo esc_html($product->get_name()); ?>
      </li>
    </ol>
  </div>
</nav>