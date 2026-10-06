<?php
/**
 * Entry-list PDF, written in chunks.
 *
 * The Lottery plugin passes the whole entry list to a single mPDF WriteHTML()
 * call, which throws once the HTML passes pcre.backtrack_limit (1 MB, roughly
 * 2,800 tickets). This renders the plugin's pdf/entry-list.php template with the
 * first chunk of tickets, then writes the remaining tickets as further tables,
 * one WriteHTML() call per chunk. Header, footer, mPDF settings and file name
 * match LTY_Generate_PDF_Handler.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Whether the plugin pieces the entry-list PDF needs are loaded.
 *
 * @return bool
 */
function nera_entry_list_pdf_available()
{
  return class_exists('\Mpdf\Mpdf') &&
    function_exists('lty_prepare_entry_list_pdf_arguments') &&
    function_exists('lty_get_template_html') &&
    function_exists('lty_get_lottery_entry_list_pdf_header_details') &&
    function_exists('lty_get_lottery_entry_list_pdf_footer_details');
}

/**
 * Stream the entry-list PDF for a lottery product as a download.
 *
 * @param WC_Product $product    Lottery product.
 * @param int        $chunk_size Tickets per WriteHTML() call.
 * @return void
 */
function nera_output_entry_list_pdf($product, $chunk_size = 1000)
{
  wp_raise_memory_limit();

  $args = lty_prepare_entry_list_pdf_arguments($product);
  $ticket_ids = isset($args['ticket_ids']) && is_array($args['ticket_ids'])
    ? array_values($args['ticket_ids'])
    : [];
  $chunks = array_chunk($ticket_ids, max(1, (int) $chunk_size));
  $args['ticket_ids'] = $chunks ? array_shift($chunks) : [];

  $pdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'orientation' => 'P',
    'format' => 'a4',
    'setAutoTopMargin' => 'stretch',
    'setAutoBottomMargin' => 'stretch',
  ]);

  $pdf->SetHTMLHeader(
    '<div class="lty-lottery-entry-list-pdf-header" style="background: ' .
      get_option('lty_settings_entry_list_pdf_header_bg_color') .
      '; color: ' .
      get_option('lty_settings_entry_list_pdf_header_font_color') .
      '; padding: 10px; display: flex; vertical-align: middle;">' .
      lty_get_lottery_entry_list_pdf_header_details($product) .
      '</div>',
  );
  $pdf->SetHTMLFooter(
    '<div class="lty-lottery-entry-list-pdf-footer" style="background: ' .
      get_option('lty_settings_entry_list_pdf_footer_bg_color') .
      '; color: ' .
      get_option('lty_settings_entry_list_pdf_footer_font_color') .
      '; padding: 10px; font-size: 10px;">' .
      lty_get_lottery_entry_list_pdf_footer_details($product) .
      '</div>',
  );

  // Summary, winner log and the first chunk of tickets, as the plugin renders them.
  $pdf->WriteHTML(lty_get_template_html('pdf/entry-list.php', $args));

  // Remaining tickets: same table markup as pdf/entry-list.php, one table per chunk.
  if ($chunks) {
    $head = '';
    foreach ($args['ticket_log_columns'] as $column_name) {
      $head .= '<th>' . esc_html($column_name) . '</th>';
    }

    foreach ($chunks as $chunk) {
      $pdf->WriteHTML(
        '<div class="lty-entry-list-ticket-logs-wrapper">' .
          '<table class="lty-frontend-table lty-ticket-logs-table" style="table-layout: fixed;">' .
          '<thead><tr>' .
          $head .
          '</tr></thead><tbody>' .
          lty_get_template_html('single-product/tabs/ticket-logs.php', [
            '_columns' => $args['ticket_log_columns'],
            'ticket_ids' => $chunk,
          ]) .
          '</tbody></table></div>',
      );
    }
  }

  $file_name = str_replace(
    ['{product_name}', '{date}'],
    [$product->get_product_name(), gmdate('Ymd')],
    get_option(
      'lty_settings_entry_list_pdf_file_name',
      __('Entry list for {product_name}', 'lottery-for-woocommerce'),
    ),
  );

  $pdf->Output($file_name . '.pdf', 'D');
}
