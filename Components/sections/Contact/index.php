<?php
namespace Nera\Components\Contact;

if (!defined('ABSPATH')) exit;

/**
 * @param array $args
 * @return array{
 *   title: string,                    // required, default 'Contact Us' — hero heading
 *   description: string,              // required, default "We'd love to hear…" — hero subheading
 *   get_in_touch_heading: string,     // required, default 'Get in Touch' — left column heading
 *   get_in_touch_description: string, // required, default "Have questions…" — left column intro
 *   show_contact_cards: bool,         // required, default true — whether to render contact info column
 *   contact_address: string,          // required, default placeholder address — raw address text
 *   contact_email: string,            // required, default 'support@competition.com' — email
 *   contact_phone: string,            // required, default '+1 (555) 012-3456' — phone display string
 *   form_heading: string,             // required, default 'Send Us a Message' — form column heading
 *   form_description: string,         // required, default '' — optional form intro text
 *   fluent_form_id: int,              // required, default 0 — Fluent Forms ID (0 = not set)
 *   facebook_url: string,             // required, default '' — from get_theme_mod
 *   twitter_url: string,              // required, default '' — from get_theme_mod
 *   instagram_url: string,            // required, default '' — from get_theme_mod
 *   linkedin_url: string,             // required, default '' — from get_theme_mod
 *   address_html: string,             // required — nl2br/esc_html of contact_address
 *   phone_digits: string,             // required — digits-only version of contact_phone for tel: href
 *   can_edit: bool,                   // required — current_user_can('edit_pages')
 *   edit_link: string,                // required — get_edit_post_link() or ''
 *   form_html: string,                // required, default '' — rendered shortcode output or ''
 *   form_plugin_missing: bool,        // required — true when fluent_form_id > 0 but plugin inactive
 * }
 */
function get_data(array $args = []): array
{
    $title = nera_component_field($args, 'title', 'contact_heading', 'Contact Us');
    $description = nera_component_field(
        $args,
        'description',
        'contact_description',
        "We'd love to hear from you regarding the competition. Our team is ready to answer any questions."
    );
    $get_in_touch_heading = nera_component_field($args, 'get_in_touch_heading', 'get_in_touch_heading', 'Get in Touch');
    $get_in_touch_description = nera_component_field(
        $args,
        'get_in_touch_description',
        'get_in_touch_description',
        "Have questions about our competitions? We're here to help."
    );
    $show_contact_cards = nera_component_field($args, 'show_contact_cards', 'show_contact_cards', true);
    $contact_address = nera_component_field($args, 'contact_address', 'contact_address', '123 Innovation Blvd, Tech City, TC 12345');
    $contact_email = nera_component_field($args, 'contact_email', 'contact_email', 'support@competition.com');
    $contact_phone = nera_component_field($args, 'contact_phone', 'contact_phone', '+1 (555) 012-3456');
    $form_heading = nera_component_field($args, 'form_heading', 'form_heading', 'Send Us a Message');
    $form_description = nera_component_field($args, 'form_description', 'form_description', '');
    $fluent_form_id = nera_component_field($args, 'fluent_form_id', 'fluent_form_id', 0);

    // Computed values (not via nera_component_field)
    $facebook_url  = get_theme_mod('nera_facebook_url', '');
    $twitter_url   = get_theme_mod('nera_twitter_url', '');
    $instagram_url = get_theme_mod('nera_instagram_url', '');
    $linkedin_url  = get_theme_mod('nera_linkedin_url', '');

    $address_html  = nl2br(esc_html((string) $contact_address));
    $phone_digits  = preg_replace('/[^0-9+]/', '', (string) $contact_phone);
    $can_edit      = current_user_can('edit_pages');
    $edit_link     = (string) get_edit_post_link();

    $fluent_form_id = (int) $fluent_form_id;

    if ($fluent_form_id > 0 && shortcode_exists('fluentform')) {
        $form_html = do_shortcode('[fluentform id="' . absint($fluent_form_id) . '"]');
    } else {
        $form_html = '';
    }

    $form_plugin_missing = $fluent_form_id > 0 && !shortcode_exists('fluentform');

    return compact(
        'title',
        'description',
        'get_in_touch_heading',
        'get_in_touch_description',
        'show_contact_cards',
        'contact_address',
        'contact_email',
        'contact_phone',
        'form_heading',
        'form_description',
        'fluent_form_id',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'linkedin_url',
        'address_html',
        'phone_digits',
        'can_edit',
        'edit_link',
        'form_html',
        'form_plugin_missing'
    );
}
