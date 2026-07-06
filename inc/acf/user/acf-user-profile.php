<?php
/**
 * ACF User Profile Fields
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

if (function_exists('acf_add_local_field_group')) {
  acf_add_local_field_group([
    'key' => 'group_nera_user_profile',
    'title' => __('User Profile', 'nera-competitions'),
    'fields' => [
      [
        'key' => 'field_user_author_profile_picture',
        'label' => __('Author Profile Picture', 'nera-competitions'),
        'name' => 'author_profile_picture',
        'type' => 'image',
        'instructions' => __(
          'Upload a profile photo shown as the author avatar on blog posts.',
          'nera-competitions',
        ),
        'required' => 0,
        'return_format' => 'array',
        'preview_size' => 'thumbnail',
        'library' => 'all',
        'mime_types' => 'jpg,jpeg,png,webp',
      ],
    ],
    'location' => [
      [
        [
          'param' => 'user_form',
          'operator' => '==',
          'value' => 'all',
        ],
      ],
    ],
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'active' => true,
  ]);
}
