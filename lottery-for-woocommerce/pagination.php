<?php
/**
 * Pagination — Theme Override
 *
 * Overrides: lottery-for-woocommerce/templates/pagination.php
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}
?>

<nav class="lty-pagination-nav flex items-center justify-end gap-1 flex-wrap pt-2">

  <?php if ($prev_arrows): ?>
    <a href="#" class="lty-pagination lty-first-pagination lty-page-btn" data-page="1" aria-label="First page">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/></svg>
    </a>
    <a href="#" class="lty-pagination lty-prev-pagination lty-page-btn" data-page="<?php echo esc_attr($prev_page_count); ?>" aria-label="Previous page">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
  <?php endif; ?>

  <a href="#" class="lty-page-btn <?php echo in_array('current', lty_get_pagination_classes(1, $current_page)) ? 'lty-page-btn--active' : ''; ?> <?php echo esc_attr(implode(' ', lty_get_pagination_classes(1, $current_page))); ?>" data-page="1">1</a>

  <?php if ($prev_dot): ?>
    <span class="lty-page-dot">…</span>
  <?php endif; ?>

  <?php for ($p = $start_page; $p <= $end_page; $p++):
    $page_no = lty_get_pagination_number($p, $page_count, $current_page);
    if ($page_no):
      $classes = lty_get_pagination_classes($p, $current_page);
      $is_active = in_array('current', $classes);
  ?>
    <a href="#"
      class="lty-page-btn <?php echo $is_active ? 'lty-page-btn--active' : ''; ?> <?php echo esc_attr(implode(' ', $classes)); ?>"
      data-page="<?php echo esc_attr($page_no); ?>">
      <?php echo esc_html($page_no); ?>
    </a>
  <?php endif; endfor; ?>

  <?php if ($next_dot): ?>
    <span class="lty-page-dot">…</span>
  <?php endif; ?>

  <?php if ($next_arrows): ?>
    <?php if (($page_count - 1) != $current_page): ?>
      <a href="#"
        class="lty-page-btn <?php echo esc_attr(implode(' ', lty_get_pagination_classes($page_count, $current_page))); ?>"
        data-page="<?php echo esc_attr($page_count); ?>">
        <?php echo esc_html($page_count); ?>
      </a>
    <?php endif; ?>
    <a href="#" class="lty-pagination lty-next-pagination lty-page-btn" data-page="<?php echo esc_attr($next_page_count); ?>" aria-label="Next page">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
    <a href="#" class="lty-pagination lty-last-pagination lty-page-btn" data-page="<?php echo esc_attr($page_count); ?>" aria-label="Last page">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M6 5l7 7-7 7"/></svg>
    </a>
  <?php endif; ?>

</nav>
