<?php
/**
 * Ticket Logs Rows — Theme Override
 * Masks usernames for data protection.
 *
 * Overrides: lottery-for-woocommerce/templates/single-product/tabs/ticket-logs.php
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

foreach ($ticket_ids as $ticket_id):
  $ticket = lty_get_lottery_ticket($ticket_id);
  ?>
  <tr class="transition-colors hover:bg-secondary">
    <?php foreach ($_columns as $key => $val): ?>
      <td class="px-4 py-3 text-sm text-text-primary" data-title="<?php echo esc_html($val); ?>">
        <?php
        switch ($key) {
          case 'ticket_number':
            echo '<span class="font-mono font-bold text-primary">' . esc_html($ticket->get_lottery_ticket_number()) . '</span>';
            break;
          case 'user_name':
            echo esc_html(nera_mask_username($ticket->display_user_name_by()));
            break;
          case 'date':
            echo esc_html($ticket->get_formatted_created_date());
            break;
          case 'answer':
            echo esc_html($ticket->get_answer());
            break;
          default:
            do_action('lty_lottery_ticket_log_' . $key, $ticket_id, $ticket);
            break;
        }
        ?>
      </td>
    <?php endforeach; ?>
  </tr>
<?php endforeach;
