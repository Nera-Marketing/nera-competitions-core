<?php
/**
 * Template Name: Nera Homepage
 * Template Post Type: page
 *
 * Premium competition/giveaway homepage template
 * Matches the Boutique Giveaways design
 *
 * @package Nera_Competitions
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit();
}

get_header();
?>

<main id="main" class="nera-homepage" role="main">

  <?php if (nera_render_page_components()): ?>
    <?php // page-components rendered via ACF Flexible Content — takes precedence over homepage_sections ?>
  <?php else: ?>
  <?php
  $homepage_section_map = [
    'winners' => 'template-parts/homepage/winners-section',
  ];

  $component_map = [
    'hero'                  => 'HomepageHero',
    'credibility'           => 'Credibility',
    'testimonials'          => 'Testimonials',
    'quick_guide'           => 'QuickGuide',
    'faq'                   => 'Faq',
    'promo_banner'          => 'PromoBanner',
    'featured_competitions' => 'FeaturedCompetitions',
    'categories'            => 'CategoriesCompetitions',
    'stats'                 => 'Stats',
    'about'                 => 'About',
  ];

  $sections = get_field('homepage_sections');

  if (is_array($sections) && !empty($sections)) {
    foreach ($sections as $row) {
      if (empty($row['show_section'])) {
        continue;
      }
      $slug = isset($row['section']) ? $row['section'] : '';

      if (isset($component_map[$slug])) {
        nera_render_component($component_map[$slug]);
        continue;
      }

      $part = isset($homepage_section_map[$slug]) ? $homepage_section_map[$slug] : null;
      if ($part) {
        get_template_part($part);
      }
    }
  } else {
    nera_render_component('HomepageHero');
    nera_render_component('Credibility');
    nera_render_component('FeaturedCompetitions');
    nera_render_component('PromoBanner');
    nera_render_component('Testimonials');
    get_template_part('template-parts/homepage/winners-section');
    nera_render_component('QuickGuide');
    nera_render_component('About');
    nera_render_component('CategoriesCompetitions');
    nera_render_component('Faq');
  }
  ?>
  <?php endif; ?>

</main>

<?php get_footer();
?>
