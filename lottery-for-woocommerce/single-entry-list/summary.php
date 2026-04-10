<?php
/**
 * Entry List Summary — Theme Override
 *
 * Overrides: lottery-for-woocommerce/templates/single-entry-list/summary.php
 *
 * Minimal layout: Download PDF only (no product title row, no lottery stats grid).
 * Ticket logs + table are rendered by single-entry-list/ticket-logs.php.
 *
 * If the PDF button does not appear, enable "Enable PDF Download for Entry List"
 * under WooCommerce → Giveaway/Lottery settings (option lty_settings_allow_entry_list_pdf_download).
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$pdf_download_button_url = isset($pdf_download_button_url) ? $pdf_download_button_url : '';

// Match LTY_Lottery_Page_Handler::render_entry_list_overview_content when URL was not passed.
if (
  $pdf_download_button_url === '' &&
  function_exists('lty_can_display_lottery_entry_list_pdf_download_button') &&
  lty_can_display_lottery_entry_list_pdf_download_button() &&
  isset($product) &&
  is_object($product)
) {
  $pdf_download_button_url = esc_url(
    add_query_arg(
      [
        'action'        => 'lty-download',
        'lty_pdf_nonce' => wp_create_nonce('lty-lottery-entry-list-pdf'),
        'lty_key'       => lty_encode(
          [
            'lty_lottery_id' => $product->get_id(),
          ],
          true
        ),
      ],
      get_site_url()
    )
  );
}

if ($pdf_download_button_url === '') {
  return;
}
?>

<div class="lty-entry-list-summary-content-wrapper mb-0">
  <div class="lty-entry-list-header-wrapper gap-3">
    <a href="<?php echo esc_url($pdf_download_button_url); ?>"
      class="lty-lottery-entry-list-pdf-download-button inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-text-primary no-underline transition-colors hover:bg-secondary">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12v4m0 0l-3-3m3 3l3-3M12 4v8" />
      </svg>
      <?php esc_html_e('Download PDF', 'lottery-for-woocommerce'); ?>
    </a>
  </div>
</div>
