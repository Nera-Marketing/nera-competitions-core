<?php
/**
 * Winners Grid Template Part (PHP + AlpineJS)
 *
 * @package Nera_Competitions
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
  exit();
}

$per_page = (int) (get_field('winners_per_page') ?: 12);
$show_filters = (bool) get_field('winners_show_filters');
$show_quotes = (bool) get_field('winners_show_quotes')  ;
$dataset = nera_winners_get_page_dataset(get_the_ID());
$winners = $dataset['winners'];
$filter_items = $dataset['filter_items'];

$empty_heading = function_exists('get_field') ? get_field('winners_empty_heading') : '';
$empty_description = function_exists('get_field') ? get_field('winners_empty_description') : '';
$empty_heading = is_string($empty_heading) ? trim($empty_heading) : '';
$empty_description = is_string($empty_description) ? trim($empty_description) : '';
if ($empty_heading === '') {
  $empty_heading = __('No Winners Yet', 'nera-competitions');
}
if ($empty_description === '') {
  $empty_description = __('Check back soon to see our lucky winners!', 'nera-competitions');
}

if (empty($winners)) { ?>
  <section class="py-16 px-5 sm:px-6 bg-surface">
      <div class="container mx-auto max-w-7xl">
          <div class="text-center py-16">
              <div class="max-w-md mx-auto">
                  <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                      <path d="M12 15l3.5-3.5L12 8" />
                      <circle cx="12" cy="12" r="10" />
                  </svg>
                  <h3 class="text-xl font-bold text-text-primary mb-2">
                      <?php echo esc_html($empty_heading); ?>
                  </h3>
                  <p class="text-text-secondary">
                      <?php echo esc_html($empty_description); ?>
                  </p>
              </div>
          </div>
      </div>
  </section>
  <?php return;
}

$alpine_items = array_map(function ($winner) {
  return [
    'category' => $winner['category'],
  ];
}, $winners);

$alpine_config = [
  'items' => $alpine_items,
  'perPage' => $per_page > 0 ? $per_page : 12,
];
?>

<section class="py-16 px-5 sm:px-6 bg-surface">
    <div
      class="container mx-auto max-w-7xl"
      x-data="winnersPage(<?php echo esc_attr(wp_json_encode($alpine_config)); ?>)"
    >
        <div role="status" aria-live="polite" aria-atomic="true" class="sr-only">
            <span x-text="`Showing ${visibleFilteredCount} of ${filteredCount} winners`"></span>
        </div>

        <?php if ($show_filters): ?>
            <div class="flex justify-center mb-12">
                <div class="relative w-full md:hidden">
                    <div class="absolute right-0 top-0 bottom-0 w-10 z-10 bg-gradient-to-l from-surface to-transparent pointer-events-none"></div>
                    <div x-ref="mobileScroll" class="flex gap-2 overflow-x-auto hide-scrollbar px-4 py-2">
                        <?php foreach ($filter_items as $item): ?>
                          <button
                            type="button"
                            data-filter-chip="<?php echo esc_attr($item['value']); ?>"
                            class="flex-shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-full font-semibold text-sm transition-all duration-300 whitespace-nowrap"
                            :class="activeFilter === '<?php echo esc_attr($item['value']); ?>'
                              ? 'bg-gradient-to-br from-primary to-primary/80 text-white shadow-[0_4px_12px_rgba(19,19,236,0.3)]'
                              : 'bg-gray-100 text-text-secondary active:bg-gray-200'"
                            @click="setFilter('<?php echo esc_attr($item['value']); ?>')"
                          >
                              <span><?php echo esc_html($item['label']); ?></span>
                              <span
                                class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-bold"
                                :class="activeFilter === '<?php echo esc_attr($item['value']); ?>' ? 'bg-surface/20 text-white' : 'bg-gray-200 text-text-secondary'"
                              >
                                  <?php echo esc_html((string) $item['count']); ?>
                              </span>
                          </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="hidden md:block">
                    <div x-ref="desktopTabs" class="relative inline-flex bg-gray-100 rounded-xl p-1.5 gap-1">
                        <div
                          class="absolute h-[calc(100%-12px)] rounded-lg bg-gradient-to-br from-primary to-primary/80 shadow-lg transition-all duration-500 ease-out"
                          :style="indicatorStyle"
                        ></div>
                        <?php foreach ($filter_items as $item): ?>
                          <button
                            type="button"
                            data-filter-item="<?php echo esc_attr($item['value']); ?>"
                            class="relative z-10 px-6 py-3 rounded-lg font-semibold text-sm transition-all duration-300"
                            :class="activeFilter === '<?php echo esc_attr($item['value']); ?>'
                              ? 'text-white'
                              : 'text-text-secondary hover:text-text-primary hover:-translate-y-0.5'"
                            @click="setFilter('<?php echo esc_attr($item['value']); ?>')"
                          >
                              <span class="relative z-10"><?php echo esc_html($item['label']); ?></span>
                              <span
                                class="relative z-10 ml-2 inline-flex items-center justify-center min-w-[24px] h-6 px-2 rounded-full text-xs font-bold transition-all duration-300"
                                :class="activeFilter === '<?php echo esc_attr($item['value']); ?>' ? 'bg-surface/20 text-white' : 'bg-gray-200 text-text-secondary hover:bg-gray-300'"
                              >
                                  <?php echo esc_html((string) $item['count']); ?>
                              </span>
                          </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
            <?php foreach ($winners as $index => $winner): ?>
                <div class="h-full" x-show="visible(<?php echo (int) $index; ?>)" x-cloak>
                    <?php get_template_part('template-parts/winners/winner-card', null, [
                      'winner' => $winner,
                      'show_quotes' => $show_quotes,
                    ]); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div x-show="filteredCount === 0" x-cloak class="text-center py-20">
            <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center">
                <span class="material-symbols-outlined text-4xl text-text-secondary">search_off</span>
            </div>
            <h3 class="text-xl font-bold text-text-primary mb-2"><?php esc_html_e('No winners found', 'nera-competitions'); ?></h3>
            <p class="text-sm text-text-secondary"><?php esc_html_e('Try selecting a different filter', 'nera-competitions'); ?></p>
        </div>

        <div class="mt-8 md:mt-12 text-center" x-show="visibleCount < filteredCount" x-cloak>
            <p class="text-sm text-text-secondary mb-4 font-medium">
                <?php esc_html_e('Showing', 'nera-competitions'); ?>
                <span class="font-bold text-primary" x-text="visibleFilteredCount"></span>
                <?php esc_html_e('of', 'nera-competitions'); ?>
                <span class="font-bold text-text-primary" x-text="filteredCount"></span>
                <?php esc_html_e('winners', 'nera-competitions'); ?>
            </p>
            <button
                type="button"
                @click="loadMore()"
                class="group relative px-5 py-2.5 md:px-8 md:py-4 bg-gradient-to-br from-primary to-primary/80 text-white rounded-xl font-bold text-sm md:text-base shadow-lg hover:shadow-2xl hover:shadow-primary/30 hover:-translate-y-1 transition-all duration-500 overflow-hidden"
            >
                <span class="relative z-10 flex items-center justify-center gap-2 md:gap-3">
                    <span class="material-symbols-outlined transition-transform duration-300 group-hover:translate-y-1">expand_more</span>
                    <span><?php esc_html_e('Load More Winners', 'nera-competitions'); ?></span>
                </span>
            </button>
        </div>

        <noscript>
            <div class="text-center mt-8 p-4 bg-gray-100 rounded-lg">
                <p class="text-text-secondary">
                    <?php esc_html_e('JavaScript is optional. All winners are shown without filtering.', 'nera-competitions'); ?>
                </p>
            </div>
        </noscript>
    </div>
</section>
