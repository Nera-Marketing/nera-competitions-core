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
$raw_video_files = $args['video_file'] ?? [];
if (is_array($raw_video_files) && isset($raw_video_files['url'])) {
  $raw_video_files = [$raw_video_files];
}
$video_files = [];
if (is_array($raw_video_files)) {
  foreach ($raw_video_files as $file) {
    if (!is_array($file)) {
      continue;
    }
    $file_mime = (string) ($file['mime_type'] ?? '');
    $file_url = (string) ($file['url'] ?? '');
    if ($file_url === '' || strpos($file_mime, 'video/') !== 0) {
      continue;
    }
    $poster = '';
    $attachment_id = (int) ($file['ID'] ?? 0);
    $poster_id = $attachment_id ? (int) get_post_thumbnail_id($attachment_id) : 0;
    if ($poster_id) {
      $poster = (string) wp_get_attachment_image_url($poster_id, 'large');
    }
    $video_files[] = [
      'url' => $file_url,
      'mime' => $file_mime,
      'poster' => $poster,
    ];
  }
}
$video_embed = '';
$video_thumb = '';
if ($video_url && preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/))([A-Za-z0-9_-]{6,})~', $video_url, $video_match)) {
  $video_embed = 'https://www.youtube.com/embed/' . $video_match[1] . '?autoplay=1&rel=0';
  $video_thumb = 'https://i.ytimg.com/vi/' . rawurlencode($video_match[1]) . '/hqdefault.jpg';
} elseif ($video_url && preg_match('~vimeo\.com/(?:video/)?(\d+)~', $video_url, $video_match)) {
  $video_embed = 'https://player.vimeo.com/video/' . $video_match[1] . '?autoplay=1';
  $video_thumb = get_transient('nera_vimeo_thumb_' . $video_match[1]);
  if ($video_thumb === false) {
    $video_thumb = '';
    $vimeo_response = wp_remote_get(
      'https://vimeo.com/api/oembed.json?url=' . rawurlencode('https://vimeo.com/' . $video_match[1]),
      ['timeout' => 3]
    );
    if (!is_wp_error($vimeo_response)) {
      $vimeo_data = json_decode(wp_remote_retrieve_body($vimeo_response), true);
      if (!empty($vimeo_data['thumbnail_url']) && is_string($vimeo_data['thumbnail_url'])) {
        $video_thumb = $vimeo_data['thumbnail_url'];
      }
    }
    set_transient('nera_vimeo_thumb_' . $video_match[1], $video_thumb, DAY_IN_SECONDS);
  }
}
$has_gallery_video = $video_files !== [] || $video_embed !== '';
$unified_mobile = !empty($args['unified_mobile']);

// Configurable main image aspect ratio (Theme Settings → WooCommerce); null = default 4/3
$aspect_ratio = function_exists('nera_get_single_product_image_aspect_ratio')
  ? nera_get_single_product_image_aspect_ratio()
  : null;

// Configurable main image max-height cap; null = no cap (keeps left column compact)
$max_height = function_exists('nera_get_single_product_image_max_height')
  ? nera_get_single_product_image_max_height()
  : null;

// Combined inline style for the main gallery box: aspect-ratio sets the shape,
// max-height clamps it so the gallery column does not push the buy controls down.
$gallery_styles = [];
if ($aspect_ratio) {
  $gallery_styles[] = 'aspect-ratio: ' . $aspect_ratio;
}
if ($max_height) {
  $gallery_styles[] = 'max-height: ' . $max_height;
}
$gallery_main_class = 'swiper' . ($aspect_ratio ? '' : ' aspect-[4/3]');
$gallery_main_style = $gallery_styles
  ? ' style="' . esc_attr(implode('; ', $gallery_styles)) . ';"'
  : '';

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
  'red' => 'bg-danger text-white',
  'primary' => 'bg-primary text-white',
  'orange' => 'bg-warning text-white',
  'green' => 'bg-success text-white',
  'blue' => 'bg-primary text-white',
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

  <div class="relative rounded-2xl overflow-hidden shadow-lg product-gallery-main-frame<?php echo $unified_mobile ? ' max-lg:shadow-none' : ''; ?>">

    <?php if ($badge_text): ?>
      <div class="absolute top-4 left-4 z-20">
        <span class="<?php echo esc_attr($badge_classes); ?> text-xs font-bold px-4 py-2 rounded-md uppercase tracking-wider shadow-lg">
          <?php echo esc_html($badge_text); ?>
        </span>
      </div>
    <?php endif; ?>

    <div class="<?php echo esc_attr($gallery_main_class); ?>"<?php echo $gallery_main_style; ?> data-gallery-main>
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

        <?php foreach ($video_files as $video_file): ?>
          <div class="swiper-slide flex items-center justify-center bg-black" data-gallery-video-slide>
            <video
              class="h-full w-full"
              data-gallery-video-player
              controls
              playsinline
              preload="metadata"
              <?php echo $video_file['poster'] ? 'poster="' . esc_url($video_file['poster']) . '"' : ''; ?>
            >
              <source src="<?php echo esc_url($video_file['url']); ?>" type="<?php echo esc_attr($video_file['mime']); ?>" />
            </video>
          </div>
        <?php endforeach; ?>

        <?php if ($video_embed): ?>
          <div class="swiper-slide flex items-center justify-center bg-black" data-gallery-video-slide>
            <iframe
              class="h-full w-full"
              data-gallery-video-frame
              data-src="<?php echo esc_url($video_embed); ?>"
              title="<?php esc_attr_e('Product video', 'nera-competitions'); ?>"
              allow="autoplay; encrypted-media; picture-in-picture"
              allowfullscreen
            ></iframe>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (count($images) > 1 || $has_gallery_video): ?>
      <button
        class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-surface/90 rounded-full shadow-lg flex items-center justify-center hover:bg-surface opacity-0 group-hover:opacity-100 transition-opacity z-10"
        data-gallery-prev
        aria-label="<?php esc_attr_e('Previous image', 'nera-competitions'); ?>"
      >
        <span class="material-symbols-outlined text-text-secondary">chevron_left</span>
      </button>
      <button
        class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-surface/90 rounded-full shadow-lg flex items-center justify-center hover:bg-surface opacity-0 group-hover:opacity-100 transition-opacity z-10"
        data-gallery-next
        aria-label="<?php esc_attr_e('Next image', 'nera-competitions'); ?>"
      >
        <span class="material-symbols-outlined text-text-secondary">chevron_right</span>
      </button>
    <?php endif; ?>
  </div>

  <?php if (count($images) > 1 || $has_gallery_video): ?>
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

          <?php foreach ($video_files as $video_index => $video_file): ?>
            <div
              class="swiper-slide !w-20 sm:!w-24 cursor-pointer"
              data-video-thumb
              role="button"
              aria-label="<?php echo esc_attr(sprintf(__('Play uploaded video %d', 'nera-competitions'), $video_index + 1)); ?>"
            >
              <div class="thumb-border relative aspect-square rounded-lg overflow-hidden border-2 border-transparent bg-background-dark transition-all hover:border-primary/50">
                <?php if ($video_file['poster']): ?>
                  <img
                    src="<?php echo esc_url($video_file['poster']); ?>"
                    alt="<?php echo esc_attr(sprintf(__('Uploaded product video %d', 'nera-competitions'), $video_index + 1)); ?>"
                    class="h-full w-full object-cover"
                    loading="lazy"
                  />
                <?php endif; ?>
                <span class="absolute inset-0 flex items-center justify-center" style="background: rgba(0, 0, 0, 0.4);">
                  <span class="material-symbols-outlined text-white text-2xl">play_arrow</span>
                </span>
              </div>
            </div>
          <?php endforeach; ?>

          <?php if ($video_embed): ?>
            <div
              class="swiper-slide !w-20 sm:!w-24 cursor-pointer"
              data-video-thumb
              role="button"
              aria-label="<?php esc_attr_e('Play product video', 'nera-competitions'); ?>"
            >
              <div class="thumb-border relative aspect-square rounded-lg overflow-hidden border-2 border-transparent bg-background-dark transition-all hover:border-primary/50">
                <?php if ($video_thumb): ?>
                  <img
                    src="<?php echo esc_url($video_thumb); ?>"
                    alt="<?php esc_attr_e('Product video', 'nera-competitions'); ?>"
                    class="h-full w-full object-cover"
                    loading="lazy"
                  />
                <?php endif; ?>
                <span class="absolute inset-0 flex items-center justify-center" style="background: rgba(0, 0, 0, 0.4);">
                  <span class="material-symbols-outlined text-white text-2xl">play_arrow</span>
                </span>
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
