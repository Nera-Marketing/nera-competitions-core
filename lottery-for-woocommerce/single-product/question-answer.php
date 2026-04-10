<?php
/**
 * Question & Answer Template Override
 *
 * Custom styled Q&A skill question for lottery products.
 * This template overrides the Lottery for WooCommerce plugin's default Q&A template.
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

global $product;

if (!$product || !is_a($product, 'WC_Product')) {
  return;
}

$product_id = $product->get_id();

// Get question data from the lottery plugin
// The plugin typically stores these in post meta
$question = get_post_meta($product_id, '_lty_question', true);
$answers = get_post_meta($product_id, '_lty_answers', true);
$correct_answer = get_post_meta($product_id, '_lty_correct_answer', true);

// If no question is set, don't render anything
if (empty($question) || empty($answers)) {
  return;
}

// Parse answers if it's a string (comma-separated)
if (is_string($answers)) {
  $answers = array_map('trim', explode(',', $answers));
}

// Ensure we have valid answers array
if (!is_array($answers) || empty($answers)) {
  return;
}
?>

<div class="competition-question" data-question-answer>
  <!-- Question Header -->
  <div class="question-header mb-4">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
        <span class="material-symbols-outlined text-amber-600">quiz</span>
      </div>
      <div>
        <span class="text-xs font-bold text-amber-600 uppercase tracking-wide">
          <?php _e('Skill Question', 'nera-competitions'); ?>
        </span>
        <p class="text-sm text-text-secondary">
          <?php _e('Answer correctly to enter', 'nera-competitions'); ?>
        </p>
      </div>
    </div>
  </div>

  <!-- Question Text -->
  <div class="question-text bg-gray-50 rounded-2xl p-5 mb-4">
    <p class="text-lg font-semibold text-text-primary">
      <?php echo esc_html($question); ?>
    </p>
  </div>

  <!-- Answer Options -->
  <div class="answer-options">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      <?php foreach ($answers as $index => $answer): ?>
        <?php
        $answer = trim($answer);
        $answer_id = 'answer_' . $product_id . '_' . $index;
        $answer_value = sanitize_title($answer);
        ?>
        <label
          class="answer-option relative cursor-pointer"
          for="<?php echo esc_attr($answer_id); ?>"
        >
          <input
            type="radio"
            id="<?php echo esc_attr($answer_id); ?>"
            name="lottery_answer"
            value="<?php echo esc_attr($answer); ?>"
            class="answer-input sr-only"
            required
          />
          <div class="answer-card flex items-center gap-3 p-4 rounded-xl border-2 border-gray-200 bg-white transition-all hover:border-primary/50 hover:bg-primary/5 peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:ring-2 peer-checked:ring-primary/20">
            <!-- Answer Letter -->
            <span class="answer-letter flex-shrink-0 w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-sm font-bold text-text-secondary transition-colors">
              <?php echo esc_html(chr(65 + $index)); ?>
            </span>
            <!-- Answer Text -->
            <span class="answer-text text-text-primary font-medium">
              <?php echo esc_html($answer); ?>
            </span>
            <!-- Check Icon (shown when selected) -->
            <span class="answer-check ml-auto hidden text-primary">
              <span class="material-symbols-outlined">check_circle</span>
            </span>
          </div>
        </label>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Validation Message -->
  <div class="question-validation hidden mt-3 p-3 rounded-xl bg-red-50 text-red-600 text-sm" data-validation-message>
    <span class="flex items-center gap-2">
      <span class="material-symbols-outlined text-lg">error</span>
      <?php _e('Please select an answer to continue.', 'nera-competitions'); ?>
    </span>
  </div>
</div>
