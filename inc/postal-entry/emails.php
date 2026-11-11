<?php
/**
 * Postal entries: the three emails and their texts (see ../postal-entry.php for the overview).
 *
 *   - Confirmation: the entry was issued, with the entry number.
 *   - Not entered: the drop was full or closed before the entry could be made.
 *   - Registration invite: the person has no member account yet, so the entry waits until they register.
 *
 * They are WooCommerce emails (WooCommerce > Settings > Emails switches each on or off and gives them the
 * store's email layout). The subject and the text are edited in Theme Settings > WooCommerce > Postal
 * entries; these functions hold the neutral defaults.
 *
 * Placeholders: {first_name} {drop} {entry_numbers} {date} {register_url} {site_name}
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

/**
 * Neutral default subject and body of each email.
 *
 * @return array<string, array{subject: string, body: string}>
 */
function nera_postal_email_defaults(): array
{
  return [
    'confirmation' => [
      'subject' => 'Your free postal entry is in: {drop}',
      'body' => "Hi {first_name},\n\nYour free postal entry for {drop} has been received and entered.\n\nYour entry number: {entry_numbers}\n\nEntered on {date}.\n\nThank you,\n{site_name}",
    ],
    'not_entered' => [
      'subject' => 'Your postal entry for {drop} was not entered',
      'body' => "Hi {first_name},\n\nThank you for your postal entry for {drop}. Unfortunately the drop had already closed or was full when it arrived, so it could not be entered.\n\nThank you,\n{site_name}",
    ],
    'invite' => [
      'subject' => 'Register to complete your postal entry: {drop}',
      'body' => "Hi {first_name},\n\nWe received your postal entry for {drop}. To enter you we need you to register as a member with this email address.\n\nRegister here: {register_url}\n\nYour entry is added as soon as you have registered, as long as the drop is still open and not full.\n\nThank you,\n{site_name}",
    ],
  ];
}

/**
 * The saved or default text of an email.
 *
 * @param string $type  confirmation, not_entered or invite.
 * @param string $field subject or body.
 */
function nera_postal_email_text(string $type, string $field): string
{
  $defaults = nera_postal_email_defaults();
  $saved = trim((string) get_option('options_nera_postal_entry_' . $type . '_' . $field, ''));

  return '' !== $saved ? $saved : (string) ($defaults[$type][$field] ?? '');
}

/**
 * Theme Settings -> WooCommerce fields (merged in inc/acf/woocommerce/acf-woocommerce.php).
 *
 * @return array<int, array<string, mixed>>
 */
function nera_postal_settings_fields(): array
{
  $on = [[['field' => 'field_nera_postal_entry_enabled', 'operator' => '==', 'value' => '1']]];
  $defaults = nera_postal_email_defaults();
  $placeholders = 'Placeholders: {first_name} {drop} {entry_numbers} {date} {register_url} {site_name}. Leave empty to use the default text.';

  $fields = [
    [
      'key' => 'field_nera_postal_entry_accordion',
      'label' => 'Postal entries',
      'name' => '',
      'type' => 'accordion',
      'placement' => 'top',
      'open' => 0,
      'multi_expand' => 0,
      'endpoint' => 0,
    ],
    [
      'key' => 'field_nera_postal_entry_enabled',
      'label' => 'Enable postal entries',
      'name' => NERA_POSTAL_SWITCH,
      'type' => 'true_false',
      'instructions' => 'While on, WooCommerce > Postal entries lets an admin log a free postal entry against a drop (one entry per person per drop, GBP 0, issued the normal way and marked "postal" on the entry list), and three emails are sent: confirmation, not entered (drop full or closed) and an invitation to register. Turn off to hide the screen and stop the emails. Entries already logged stay.',
      'default_value' => 0,
      'ui' => 1,
      'ui_on_text' => 'On',
      'ui_off_text' => 'Off',
      'wrapper' => ['width' => '', 'class' => 'nera-acf-field--toggle', 'id' => ''],
    ],
  ];

  $labels = [
    'confirmation' => 'Confirmation email',
    'not_entered' => '"Not entered" email',
    'invite' => 'Registration invite email',
  ];
  foreach ($labels as $type => $label) {
    $fields[] = [
      'key' => 'field_nera_postal_entry_' . $type . '_subject',
      'label' => $label . ': subject',
      'name' => 'nera_postal_entry_' . $type . '_subject',
      'type' => 'text',
      'instructions' => $placeholders,
      'placeholder' => $defaults[$type]['subject'],
      'conditional_logic' => $on,
    ];
    $fields[] = [
      'key' => 'field_nera_postal_entry_' . $type . '_body',
      'label' => $label . ': text',
      'name' => 'nera_postal_entry_' . $type . '_body',
      'type' => 'textarea',
      'rows' => 8,
      'new_lines' => '',
      'instructions' => $placeholders,
      'placeholder' => $defaults[$type]['body'],
      'conditional_logic' => $on,
    ];
  }

  return $fields;
}

/**
 * Register the emails with WooCommerce, only while the switch is on.
 *
 * @param array<string, object> $emails Email classes.
 * @return array<string, object>
 */
function nera_postal_register_emails($emails)
{
  if (!nera_postal_enabled() || !class_exists('WC_Email')) {
    return $emails;
  }

  if (!class_exists('Nera_Postal_Entry_Email_Base')) {
    /**
     * Shared behaviour of the three postal entry emails.
     */
    abstract class Nera_Postal_Entry_Email_Base extends WC_Email
    {
      /** @var string confirmation, not_entered or invite. */
      protected $type = '';

      /** @var array<string, mixed> Values for the placeholders of the email being sent. */
      public $vars = [];

      public function __construct()
      {
        $this->id = 'nera_postal_entry_' . $this->type;
        $this->customer_email = true;
        parent::__construct();
      }

      public function init_form_fields()
      {
        $this->form_fields = [
          'enabled' => [
            'title' => __('Enable/Disable', 'woocommerce'),
            'type' => 'checkbox',
            'label' => __('Enable this email notification', 'woocommerce'),
            'default' => 'yes',
          ],
          'email_type' => [
            'title' => __('Email type', 'woocommerce'),
            'type' => 'select',
            'description' => __('The subject and text are edited in Theme Settings > WooCommerce > Postal entries.', 'nera-competitions'),
            'default' => 'html',
            'class' => 'email_type wc-enhanced-select',
            'options' => $this->get_email_type_options(),
            'desc_tip' => true,
          ],
        ];
      }

      public function get_default_subject()
      {
        return nera_postal_email_defaults()[$this->type]['subject'];
      }

      public function get_default_heading()
      {
        return $this->get_subject();
      }

      public function get_subject()
      {
        return $this->format_string(nera_postal_email_text($this->type, 'subject'));
      }

      public function get_heading()
      {
        return $this->get_subject();
      }

      /** @return string The text with the placeholders filled in (plain, not yet HTML). */
      protected function body_text(): string
      {
        return $this->format_string(nera_postal_email_text($this->type, 'body'));
      }

      public function get_content_html()
      {
        ob_start();
        wc_get_template('emails/email-header.php', ['email_heading' => $this->get_heading(), 'email' => $this]);
        echo wp_kses_post(wpautop(make_clickable(esc_html($this->body_text()))));
        wc_get_template('emails/email-footer.php', ['email' => $this]);

        return (string) ob_get_clean();
      }

      public function get_content_plain()
      {
        return wp_strip_all_tags($this->get_heading()) . "\n\n" . $this->body_text() . "\n";
      }

      /**
       * Send this email.
       *
       * @param string               $to   Recipient.
       * @param array<string, mixed> $vars first_name, drop, entry_numbers, date, register_url.
       */
      public function send_postal(string $to, array $vars): bool
      {
        if (!$this->is_enabled() || !is_email($to)) {
          return false;
        }

        $this->vars = $vars;
        $this->recipient = $to;
        $this->placeholders = [
          '{first_name}' => (string) ($vars['first_name'] ?? ''),
          '{drop}' => (string) ($vars['drop'] ?? ''),
          '{entry_numbers}' => (string) ($vars['entry_numbers'] ?? ''),
          '{date}' => (string) ($vars['date'] ?? ''),
          '{register_url}' => (string) ($vars['register_url'] ?? ''),
          '{site_name}' => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
        ];

        $this->setup_locale();
        $sent = $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
        $this->restore_locale();

        return (bool) $sent;
      }
    }

    class Nera_Postal_Entry_Confirmation_Email extends Nera_Postal_Entry_Email_Base
    {
      protected $type = 'confirmation';

      public function __construct()
      {
        $this->title = __('Postal entry confirmation', 'nera-competitions');
        $this->description = __('Sent to the entrant when an admin has logged their free postal entry, with the entry number.', 'nera-competitions');
        parent::__construct();
      }
    }

    class Nera_Postal_Entry_Not_Entered_Email extends Nera_Postal_Entry_Email_Base
    {
      protected $type = 'not_entered';

      public function __construct()
      {
        $this->title = __('Postal entry not entered', 'nera-competitions');
        $this->description = __('Sent to the entrant when the drop was full or closed before their postal entry could be made.', 'nera-competitions');
        parent::__construct();
      }
    }

    class Nera_Postal_Entry_Invite_Email extends Nera_Postal_Entry_Email_Base
    {
      protected $type = 'invite';

      public function __construct()
      {
        $this->title = __('Postal entry registration invite', 'nera-competitions');
        $this->description = __('Sent when a postal entry is logged for an email that has no member account yet, asking them to register.', 'nera-competitions');
        parent::__construct();
      }
    }
  }

  $emails['Nera_Postal_Entry_Confirmation_Email'] = new Nera_Postal_Entry_Confirmation_Email();
  $emails['Nera_Postal_Entry_Not_Entered_Email'] = new Nera_Postal_Entry_Not_Entered_Email();
  $emails['Nera_Postal_Entry_Invite_Email'] = new Nera_Postal_Entry_Invite_Email();

  return $emails;
}
add_filter('woocommerce_email_classes', 'nera_postal_register_emails');

/**
 * Registration page link for the invite email.
 *
 * @param string $email Email the invite is for (added as a hint the register form can pick up).
 */
function nera_postal_register_url(string $email): string
{
  $base = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');

  return add_query_arg('postal_email', rawurlencode($email), $base);
}

/**
 * Fill the email field of the register form from the invite link.
 */
function nera_postal_prefill_register_email(): void
{
  if (!nera_postal_enabled() || empty($_GET['postal_email']) || is_user_logged_in() || !function_exists('is_account_page') || !is_account_page()) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    return;
  }

  $email = sanitize_email(rawurldecode((string) wp_unslash($_GET['postal_email']))); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
  if (!is_email($email)) {
    return;
  }

  printf(
    '<script>document.addEventListener("DOMContentLoaded",function(){var f=document.getElementById("reg_email");if(f&&!f.value){f.value=%s;}var t=document.getElementById("tab-register");if(t&&f){t.click();}});</script>',
    wp_json_encode($email)
  );
}
add_action('wp_footer', 'nera_postal_prefill_register_email', 50);

/**
 * Send one of the three emails for a log row.
 *
 * @param string               $type confirmation, not_entered or invite.
 * @param array<string, mixed> $row  Log row.
 */
function nera_postal_send(string $type, array $row): bool
{
  if (!function_exists('WC')) {
    return false;
  }

  $mailer = WC()->mailer();
  $email = $mailer->emails['Nera_Postal_Entry_' . implode('_', array_map('ucfirst', explode('_', $type))) . '_Email'] ?? null;
  if (!$email instanceof Nera_Postal_Entry_Email_Base) {
    return false;
  }

  $name = trim((string) ($row['name'] ?? ''));
  $first = '' !== $name ? (string) preg_split('/\s+/', $name)[0] : '';

  return $email->send_postal((string) ($row['email'] ?? ''), [
    'first_name' => $first,
    'drop' => (string) ($row['product_name'] ?? ''),
    'entry_numbers' => implode(', ', (array) ($row['numbers'] ?? [])),
    'date' => wp_date(get_option('date_format') . ' ' . get_option('time_format'), (int) ($row['updated'] ?? time())),
    'register_url' => nera_postal_register_url((string) ($row['email'] ?? '')),
  ]);
}
