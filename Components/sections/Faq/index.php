<?php
namespace Nera\Components\Faq;

if (!defined('ABSPATH')) exit;

function get_data(array $args = []): array
{
    $default_faqs = [
        ['question' => __('Is it legal to enter UK?', 'nera-competitions'),        'answer' => __('Yes, our competitions are fully legal in the UK. We operate as a prize competition which requires entrants to demonstrate skill by answering a question correctly. This is fully compliant with UK gambling laws and we are registered with the relevant authorities.', 'nera-competitions')],
        ['question' => __('How do you draw winners?', 'nera-competitions'),        'answer' => __('All our draws are conducted live on Facebook and YouTube using a certified random number generator. The draw process is completely transparent and all entries are verified before the winner is announced. You can watch previous draws on our social media channels.', 'nera-competitions')],
        ['question' => __('When are the draws done?', 'nera-competitions'),        'answer' => __('Draws are typically conducted when all tickets have sold or when the competition end date is reached. We announce draw times in advance via email and social media so you never miss the excitement. Most draws happen weekly on Sunday evenings.', 'nera-competitions')],
        ['question' => __('How do I receive my prize?', 'nera-competitions'),      'answer' => __('For physical prizes, we arrange delivery to your door completely free of charge. Cash prizes are transferred directly to your bank account within 48 hours. For larger prizes like cars, we can either deliver to your address or arrange collection from a convenient location.', 'nera-competitions')],
    ];

    $list_raw = nera_component_field($args, 'list', 'faq_list', null);
    $faqs = (is_array($list_raw) && !empty($list_raw)) ? $list_raw : $default_faqs;

    return [
        'title'       => nera_component_field($args, 'title', 'faq_title', __('Frequently Asked Questions', 'nera-competitions')),
        'faqs'        => $faqs,
        'contact_url' => get_permalink(get_page_by_path('contact')) ?: '#',
    ];
}
