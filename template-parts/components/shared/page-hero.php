<?php
/**
 * Shared Page Hero — centered title, optional eyebrow pill, description
 *
 * Pass props via `get_template_part( ..., null, $args )` where `$args` contains:
 * - title              (string, required)
 * - description        (string, optional)
 * - variant            (string) 'default' | 'compact' — spacing, container padding, title scale
 * - eyebrow_label      (string, optional) translated label for pill
 * - eyebrow_icon       (string, optional) Material Symbols ligature name e.g. groups, emoji_events
 * - description_class  (string, optional) extra classes for the description paragraph
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$hero_args = wp_parse_args(
  isset($args) && is_array($args) ? $args : [],
  [
    'title' => '',
    'description' => '',
    'variant' => 'default',
    'eyebrow_label' => '',
    'eyebrow_icon' => '',
    'description_class' => '',
  ],
);

$title = (string) $hero_args['title'];
$description = (string) $hero_args['description'];
$variant = $hero_args['variant'] === 'compact' ? 'compact' : 'default';
$eyebrow_label = (string) $hero_args['eyebrow_label'];
$eyebrow_icon = (string) $hero_args['eyebrow_icon'];
$description_extra_class = trim((string) $hero_args['description_class']);

$has_eyebrow = $eyebrow_label !== '';

$section_class =
  $variant === 'compact'
    ? 'py-10 sm:py-16 md:py-20 bg-gradient-to-br from-primary via-primary to-primary-dark'
    : 'py-16 md:py-20 bg-gradient-to-br from-primary via-primary to-primary-dark';

$container_class =
  $variant === 'compact'
    ? 'max-w-[1200px] mx-auto px-3 sm:px-4 lg:px-8'
    : 'max-w-[1200px] mx-auto px-4 lg:px-8';

$h1_class =
  $variant === 'compact'
    ? 'text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-on-primary tracking-tight mb-4'
    : 'text-4xl md:text-5xl lg:text-6xl font-extrabold text-on-primary tracking-tight mb-4';

$p_classes = trim(
  'text-lg md:text-xl font-medium ' .
    ($description_extra_class !== '' ? $description_extra_class : 'text-on-primary/80'),
);
?>

<section class="<?php echo esc_attr($section_class); ?>">
  <div class="<?php echo esc_attr($container_class); ?>">
    <div class="text-center max-w-2xl mx-auto">
      <?php if ($has_eyebrow) : ?>
        <div class="flex justify-center mb-6" data-aos="fade-up">
          <span
            class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-on-primary/15 text-on-primary border border-on-primary/25 text-sm font-semibold">
            <?php if ($eyebrow_icon !== '') : ?>
              <span class="material-symbols-outlined text-base"
                style="font-variation-settings:'FILL' 1"><?php echo esc_html($eyebrow_icon); ?></span>
            <?php endif; ?>
            <?php echo esc_html($eyebrow_label); ?>
          </span>
        </div>
      <?php endif; ?>

      <h1
        class="<?php echo esc_attr($h1_class); ?>"
        data-aos="fade-up"
        <?php echo $has_eyebrow ? ' data-aos-delay="100"' : ''; ?>>
        <?php echo esc_html($title); ?>
      </h1>

      <?php if ($description !== '') : ?>
        <p
          class="<?php echo esc_attr($p_classes); ?>"
          data-aos="fade-up"
          data-aos-delay="<?php echo esc_attr($has_eyebrow ? '200' : '100'); ?>">
          <?php echo esc_html($description); ?>
        </p>
      <?php endif; ?>
    </div>
  </div>
</section>
