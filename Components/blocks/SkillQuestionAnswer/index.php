<?php
namespace Nera\Components\SkillQuestionAnswer;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param array $args
 * @return array{
 *   question_text: string,                              // required, default '' — question prompt rendered above the answer list
 *   answers: list<array{key: string, label: string}>,  // required, default [] — normalized answer options
 *   cart_answer_id: string,                             // required, default '' — pre-selected answer key
 *   qa_can_display: bool,                               // required, default false — gates active vs. not-started variant
 *   interactive: bool,                                  // required, default true — false emits Alpine-free markup
 * }
 *
 * `interactive` exists for the Lucky Dip popup. That markup is injected into
 * the page by jQuery .modal(), where Alpine does not initialise — x-cloak was
 * never removed, so the answers rendered but stayed display:none, and the
 * @click handlers never bound. In that context the component emits plain
 * markup driven by data attributes, and lottery-lucky-dip-qa.js owns the
 * selection instead.
 */
function get_data(array $args = []): array
{
    $raw_answers = $args['answers'] ?? [];

    // Normalize from [$key => ['label' => ...]] to [['key' => ..., 'label' => ...]]
    $answers = [];
    foreach ($raw_answers as $key => $answer) {
        $answers[] = [
            'key'   => $key,
            'label' => $answer['label'] ?? '',
        ];
    }

    return [
        'question_text'  => (string) ($args['question_text'] ?? ''),
        'answers'        => $answers,
        'cart_answer_id' => (string) ($args['cart_answer_id'] ?? ''),
        'qa_can_display' => (bool) ($args['qa_can_display'] ?? false),
        'interactive'    => (bool) ($args['interactive'] ?? true),
    ];
}
