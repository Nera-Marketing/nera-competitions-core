<?php
namespace Nera\Components\SkillQuestionAnswer;

if (!defined('ABSPATH')) {
    exit;
}

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
