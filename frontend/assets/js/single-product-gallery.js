(function () {
  var mainSwiperEl = document.querySelector('[data-gallery-main]');
  var thumbsSwiperEl = document.querySelector('[data-gallery-thumbs]');

  if (!mainSwiperEl) return;

  var thumbsSwiper = null;

  if (thumbsSwiperEl) {
    thumbsSwiper = new Swiper(thumbsSwiperEl, {
      slidesPerView: 'auto',
      spaceBetween: 12,
      watchSlidesProgress: true,
    });
  }

  new Swiper(mainSwiperEl, {
    spaceBetween: 10,
    thumbs: thumbsSwiper ? { swiper: thumbsSwiper } : undefined,
    navigation: {
      prevEl: '[data-gallery-prev]',
      nextEl: '[data-gallery-next]',
    },
    on: {
      slideChange: function () {
        mainSwiperEl.dispatchEvent(
          new CustomEvent('swiper:slidechange', {
            detail: { index: this.activeIndex },
            bubbles: true,
          }),
        );
      },
    },
  });
})();
