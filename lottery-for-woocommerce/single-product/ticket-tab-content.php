<?php
/**
 * Ticket Tab Content - Theme Override
 *
 * Displays individual ticket numbers in a grid.
 * Grid layout is enforced via nera-ticket-grid__list in main.css (not Tailwind utilities),
 * so it works regardless of build state.
 *
 * Overrides lottery-for-woocommerce/templates/single-product/ticket-tab-content.php
 *
 * @package Nera_Competitions
 * @var object $product           WC_Product_Lottery instance
 * @var array  $sold_tickets      Sold ticket numbers
 * @var array  $cart_tickets      Tickets currently in cart
 * @var array  $reserved_tickets  Reserved ticket numbers
 * @var int    $index             Current tab index
 * @var array  $ticket_numbers    Ticket numbers for this tab
 * @var int    $view_more         Number of tickets shown before "view more"
 */

defined('ABSPATH') || exit;
?>
<div class="lty-ticket-number-wrapper nera-ticket-grid">
  <div class="lty-ticket-number-content nera-ticket-grid__content">
    <ul class="nera-ticket-grid__list">
      <?php
      $tickets_count = 1;
      $step = 0;

      foreach ($ticket_numbers as $ticket_number):
        $formatted = $product->format_ticket_number($ticket_number, $index);

        if ($product->is_valid_to_display_ticket_number($formatted)):
          continue;
        endif;

        $class = 'lty-ticket nera-ticket-grid__cell';
        $title = '';

        if ($view_more && $tickets_count >= $view_more):
          $step = (0 === ($tickets_count - 1) % $view_more) ? $step + 1 : $step;
        endif;

        $class .= $step ? ' lty-hidden-ticket lty-step-' . $step : '';

        if (in_array($formatted, $cart_tickets)):
          $class .= ' lty-processing-ticket';
          $title = __('In Cart', 'lottery-for-woocommerce');
        elseif (in_array($formatted, $sold_tickets)):
          $class .= ' lty-booked-ticket';
          $title = __('Sold', 'lottery-for-woocommerce');
        elseif (in_array($formatted, $reserved_tickets)):
          $class .= ' lty-reserved-ticket';
          $title = __('Reserved', 'lottery-for-woocommerce');
        endif;
        ?>
        <li
          class="<?php echo esc_attr($class); ?>"
          data-ticket="<?php echo esc_attr($formatted); ?>"
          title="<?php echo esc_attr($title); ?>">
          <?php echo esc_html($formatted); ?>
        </li>
        <?php
        $tickets_count++;
      endforeach;
      ?>
    </ul>
  </div>

  <?php if ($view_more): ?>
    <div class="lty-toggle-view-button">
      <a href="#" class="lty-toggle-lottery-tickets" data-action="view_more" data-step="1">
        <?php esc_html_e('Show more tickets', 'nera-competitions'); ?>
      </a>
    </div>
  <?php endif; ?>
</div>
