<?php
namespace Nera\Components\CompetitionCard;

if (!defined('ABSPATH')) exit;

function get_acf_layout(): array {
    return [
        'key'        => 'layout_CompetitionCard',
        'name'       => 'CompetitionCard',
        'label'      => __('Competition Card', 'nera-competitions-standard'),
        'display'    => 'block',
        'sub_fields' => [],
        'min'        => '',
        'max'        => '',
    ];
}
