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
 *   cart_answer_id: string,                             // required, default '' — pre-selected answer key (not consumed by twig)
 *   qa_can_display: bool,                               // required, default false — gates active vs. not-started variant
 * }
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
    ];
}
