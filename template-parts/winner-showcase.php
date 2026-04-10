<?php
/**
 * Winner Showcase Template Part
 *
 * Displays a winner card or grid of winners
 *
 * @package Nera_Competitions
 *
 * Available variables:
 * $args['winner']      - Single winner data array OR
 * $args['winners']     - Array of winner data
 * $args['title']       - Section title (for grid view)
 * $args['show_quote']  - Show winner quote (default: true)
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

// Get arguments
$winners = isset($args['winners']) ? $args['winners'] : [];
$single_winner = isset($args['winner']) ? $args['winner'] : null;
$title = isset($args['title']) ? $args['title'] : __('Recent Winners', 'nera-competitions');
$show_quote = isset($args['show_quote']) ? $args['show_quote'] : true;

// If single winner provided, wrap in array
if ($single_winner) {
  $winners = [$single_winner];
}

// Demo winners if none provided
if (empty($winners)) {
  $winners = [
    [
      'name' => 'Sarah M.',
      'prize' => 'BMW M3 Competition',
      'date' => date('F j, Y', strtotime('-3 days')),
      'image' => '',
      'quote' => 'I couldn\'t believe it when I won! This has changed my life!',
    ],
    [
      'name' => 'James T.',
      'prize' => '£10,000 Cash Prize',
      'date' => date('F j, Y', strtotime('-7 days')),
      'image' => '',
      'quote' => 'Third competition I entered and I won big! Absolutely buzzing!',
    ],
    [
      'name' => 'Emma R.',
      'prize' => 'PlayStation 5 Bundle',
      'date' => date('F j, Y', strtotime('-14 days')),
      'image' => '',
      'quote' => 'So easy to enter and so happy I won. Highly recommend!',
    ],
  ];
}

// Display grid view if multiple winners
$is_grid = count($winners) > 1;
?>

<?php if ($is_grid): ?>
  <section class="winner-showcase-section section" data-aos="fade-up">
    <div class="nera-container">

      <div class="section-header">
        <h2 class="section-header__title">
          <?php echo esc_html($title); ?>
        </h2>
        <p class="section-header__subtitle">
          <?php _e('Join our growing list of happy winners!', 'nera-competitions'); ?>
        </p>
      </div>

      <div class="nera-grid nera-grid--3">
        <?php foreach ($winners as $index => $winner): ?>
          <?php nera_render_winner_card($winner, $show_quote, $index); ?>
        <?php endforeach; ?>
      </div>

      <div class="text-center mt-10">
        <a href="<?php echo esc_url(home_url('/winners')); ?>" class="btn btn--outline">
          <?php _e('View All Winners', 'nera-competitions'); ?>
        </a>
      </div>

    </div>
  </section>
<?php
  // Single winner card

  else: ?>
  <?php nera_render_winner_card($winners[0], $show_quote, 0); ?>
<?php endif; ?>

<?php
/**
 * Render a single winner card
 */
function nera_render_winner_card($winner, $show_quote = true, $index = 0)
 {
   $name = isset($winner['name']) ? $winner['name'] : '';
   $prize = isset($winner['prize']) ? $winner['prize'] : '';
   $date = isset($winner['date']) ? $winner['date'] : '';
   $image = isset($winner['image']) ? $winner['image'] : '';
   $quote = isset($winner['quote']) ? $winner['quote'] : '';
   ?>
  <div class="winner-card" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">

    <?php if ($image): ?>
      <img class="winner-card__image" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr(
  $name,
); ?>">
    <?php else: ?>
        <div class="winner-card__image bg-gradient-primary flex items-center justify-center text-[60px]">
        🏆
      </div>
    <?php endif; ?>

    <div class="winner-card__content">
      <h4 class="winner-card__name">
        <?php echo esc_html($name); ?>
      </h4>
      <p class="winner-card__prize">
        <?php echo esc_html($prize); ?>
      </p>
      <p class="winner-card__date">
        <?php echo esc_html($date); ?>
      </p>

      <?php if ($show_quote && $quote): ?>
        <blockquote class="winner-card__quote">
          "
          <?php echo esc_html($quote); ?>"
        </blockquote>
      <?php endif; ?>
    </div>

  </div>
  <?php
 } ?>
