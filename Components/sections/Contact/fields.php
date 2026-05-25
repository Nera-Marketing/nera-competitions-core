<?php
namespace Nera\Components\Contact;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array {
    return [
        'key'        => 'layout_Contact',
        'name'       => 'Contact',
        'label'      => 'Contact',
        'display'    => 'block',
        'sub_fields' => [
            // Tab: Hero
            ['key' => 'field_pc_contact_tab_hero', 'label' => 'Hero', 'name' => 'tab_hero', 'type' => 'tab'],
            ['key' => 'field_pc_contact_title', 'label' => 'Heading', 'name' => 'title', 'type' => 'text', 'default_value' => 'Contact Us'],
            ['key' => 'field_pc_contact_description', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'default_value' => "We'd love to hear from you regarding the competition. Our team is ready to answer any questions."],
            // Tab: Contact Info
            ['key' => 'field_pc_contact_tab_info', 'label' => 'Contact Info', 'name' => 'tab_info', 'type' => 'tab'],
            ['key' => 'field_pc_contact_get_in_touch_heading', 'label' => 'Get in Touch Heading', 'name' => 'get_in_touch_heading', 'type' => 'text', 'default_value' => 'Get in Touch'],
            ['key' => 'field_pc_contact_get_in_touch_description', 'label' => 'Get in Touch Description', 'name' => 'get_in_touch_description', 'type' => 'textarea', 'default_value' => "Have questions about our competitions? We're here to help."],
            ['key' => 'field_pc_contact_show_contact_cards', 'label' => 'Show Contact Cards', 'name' => 'show_contact_cards', 'type' => 'true_false', 'default_value' => 1, 'ui' => 1],
            [
                'key' => 'field_pc_contact_contact_address', 'label' => 'Address', 'name' => 'contact_address',
                'type' => 'textarea', 'default_value' => '123 Innovation Blvd, Tech City, TC 12345',
                'conditional_logic' => [[['field' => 'field_pc_contact_show_contact_cards', 'operator' => '==', 'value' => '1']]],
            ],
            [
                'key' => 'field_pc_contact_contact_email', 'label' => 'Email', 'name' => 'contact_email',
                'type' => 'email', 'default_value' => 'support@competition.com',
                'conditional_logic' => [[['field' => 'field_pc_contact_show_contact_cards', 'operator' => '==', 'value' => '1']]],
            ],
            [
                'key' => 'field_pc_contact_contact_phone', 'label' => 'Phone', 'name' => 'contact_phone',
                'type' => 'text', 'default_value' => '+1 (555) 012-3456',
                'conditional_logic' => [[['field' => 'field_pc_contact_show_contact_cards', 'operator' => '==', 'value' => '1']]],
            ],
            // Tab: Form
            ['key' => 'field_pc_contact_tab_form', 'label' => 'Form', 'name' => 'tab_form', 'type' => 'tab'],
            ['key' => 'field_pc_contact_form_heading', 'label' => 'Form Heading', 'name' => 'form_heading', 'type' => 'text', 'default_value' => 'Send Us a Message'],
            ['key' => 'field_pc_contact_form_description', 'label' => 'Form Description', 'name' => 'form_description', 'type' => 'textarea', 'default_value' => ''],
            ['key' => 'field_pc_contact_fluent_form_id', 'label' => 'Fluent Form ID', 'name' => 'fluent_form_id', 'type' => 'number', 'default_value' => 0, 'min' => 0],
        ],
    ];
}
