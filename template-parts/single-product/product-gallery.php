<?php
/**
 * Product Gallery Template Part
 *
 * Server-side rendered gallery using Swiper.js for carousels and Alpine.js for lightbox.
 *
 * @package Nera_Competitions
 */

if (!defined('ABSPATH')) {
  exit();
}

$images = $args['images'] ?? ($args['gallery_images'] ?? []);
$product = $args['product'] ?? null;
$badge_text = $args['badge_text'] ?? '';
$badge_color = $args['badge_color'] ?? 'red';
$video_url = $args['video_url'] ?? '';

if (!$product) {
  return;
}

if (empty($images)) {
  $images = [
    [
      'id' => 0,
      'full' => wc_placeholder_img_src('full'),
      'large' => wc_placeholder_img_src('large'),
      'thumbnail' => wc_placeholder_img_src('thumbnail'),
      'alt' => __('Product Image', 'nera-competitions'),
    ],
  ];
}

$badge_classes_map = [
  'red' => 'bg-red-500 text-white',
  'primary' => 'bg-primary text-white',
  'orange' => 'bg-orange-500 text-white',
  'green' => 'bg-green-500 text-white',
  'blue' => 'bg-blue-500 text-white',
];
$badge_classes = $badge_classes_map[$badge_color] ?? $badge_classes_map['red'];

$alpine_images = array_map(function ($img) {
  return [
    'full' => $img['full'],
    'alt' => $img['alt'],
  ];
}, $images);
?>

<div
  class="product-gallery"
  x-data="productGallery(<?php echo esc_attr(wp_json_encode($alpine_images)); ?>)"
  @swiper:slidechange.window="currentIndex = $event.detail.index"
>

  <div class="group">

  <div class="relative rounded-2xl overflow-hidden shadow-lg">

    <?php if ($badge_text): ?>
      <div class="absolute top-4 left-4 z-20">
        <span class="<?php echo esc_attr($badge_classes); ?> text-xs font-bold px-4 py-2 rounded-md uppercase tracking-wider shadow-lg">
          <?php echo esc_html($badge_text); ?>
        </span>
      </div>
    <?php endif; ?>

    <div class="swiper aspect-[4/3]" data-gallery-main>
      <div class="swiper-wrapper">
        <?php foreach ($images as $index => $image): ?>
          <div
            class="swiper-slide flex items-center justify-center cursor-zoom-in"
            @click="openLightbox(<?php echo esc_attr($index); ?>)"
          >
            <img
              src="<?php echo esc_url($image['large']); ?>"
              alt="<?php echo esc_attr($image['alt'] ?: $product->get_name()); ?>"
              class="w-full h-full object-contain"
              loading="<?php echo $index === 0 ? 'eager' : 'lazy'; ?>"
            />
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (count($images) > 1): ?>
      <button
        class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-surface/90 rounded-full shadow-lg flex items-center justify-center hover:bg-surface opacity-0 group-hover:opacity-100 transition-opacity z-10"
        data-gallery-prev
        aria-label="<?php esc_attr_e('Previous image', 'nera-competitions'); ?>"
      >
        <span class="material-symbols-outlined text-gray-700">chevron_left</span>
      </button>
      <button
        class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-surface/90 rounded-full shadow-lg flex items-center justify-center hover:bg-surface opacity-0 group-hover:opacity-100 transition-opacity z-10"
        data-gallery-next
        aria-label="<?php esc_attr_e('Next image', 'nera-competitions'); ?>"
      >
        <span class="material-symbols-outlined text-gray-700">chevron_right</span>
      </button>
    <?php endif; ?>
  </div>

  <?php if (count($images) > 1 || $video_url): ?>
    <div class="mt-4">
      <div class="swiper gallery-thumbs-swiper" data-gallery-thumbs>
        <div class="swiper-wrapper">
          <?php foreach ($images as $index => $image): ?>
            <div class="swiper-slide !w-20 sm:!w-24 cursor-pointer">
              <div class="thumb-border aspect-square rounded-lg overflow-hidden border-2 border-transparent transition-all hover:border-primary/50 bg-gray-100">
                <img
                  src="<?php echo esc_url($image['thumbnail']); ?>"
                  alt="<?php echo esc_attr(sprintf(__('Thumbnail %d', 'nera-competitions'), $index + 1)); ?>"
                  class="h-full w-full object-cover"
                  loading="lazy"
                />
              </div>
            </div>
          <?php endforeach; ?>

          <?php if ($video_url): ?>
            <div
              class="swiper-slide !w-20 sm:!w-24 cursor-pointer"
              data-video-thumb
              onclick="window.open('<?php echo esc_url($video_url); ?>', '_blank', 'noopener,noreferrer')"
            >
              <div class="aspect-square rounded-lg overflow-hidden border-2 border-transparent bg-gray-900 flex items-center justify-center transition-all hover:border-primary/50">
                <span class="material-symbols-outlined text-white text-2xl">play_arrow</span>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  </div>

  <div
    x-show="lightboxOpen"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/95 p-4"
    style="display: none;"
    @click.self="closeLightbox()"
  >
    <button
      class="absolute top-4 right-4 z-10 w-12 h-12 rounded-full bg-surface/10 hover:bg-surface/20 flex items-center justify-center text-white transition-colors"
      @click="closeLightbox()"
      aria-label="<?php esc_attr_e('Close lightbox', 'nera-competitions'); ?>"
    >
      <span class="material-symbols-outlined text-2xl">close</span>
    </button>

    <?php foreach ($images as $index => $image): ?>
      <div
        x-show="currentIndex === <?php echo esc_attr($index); ?>"
        class="w-full max-w-5xl h-[85vh] flex items-center justify-center"
      >
        <img
          src="<?php echo esc_url($image['full']); ?>"
          alt="<?php echo esc_attr($image['alt'] ?: $product->get_name()); ?>"
          :style="zoomed ? { transform: 'scale(2)', transformOrigin: zoomOrigin.x + '% ' + zoomOrigin.y + '%' } : {}"
          :class="zoomed ? 'cursor-zoom-out' : 'cursor-zoom-in'"
          class="max-w-full max-h-full object-contain transition-transform duration-300 select-none"
          @click="toggleZoom($event)"
          @mousemove="updateZoomOrigin($event)"
          draggable="false"
        />
      </div>
    <?php endforeach; ?>

    <?php if (count($images) > 1): ?>
      <button
        class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-black/50 rounded-full hover:bg-black/70 flex items-center justify-center text-white transition-colors z-20"
        @click="prevSlide()"
        :class="currentIndex === 0 ? 'opacity-30 pointer-events-none' : ''"
        aria-label="<?php esc_attr_e('Previous image', 'nera-competitions'); ?>"
      >
        <span class="material-symbols-outlined text-lg">chevron_left</span>
      </button>
      <button
        class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-black/50 rounded-full hover:bg-black/70 flex items-center justify-center text-white transition-colors z-20"
        @click="nextSlide()"
        :class="currentIndex === <?php echo count($images) - 1; ?> ? 'opacity-30 pointer-events-none' : ''"
        aria-label="<?php esc_attr_e('Next image', 'nera-competitions'); ?>"
      >
        <span class="material-symbols-outlined text-lg">chevron_right</span>
      </button>
    <?php endif; ?>

    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white/60 text-sm">
      <span x-text="currentIndex + 1"></span> / <?php echo count($images); ?>
    </div>
  </div>

</div>
