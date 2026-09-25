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

  function syncVideo(swiper) {
    Array.prototype.forEach.call(swiper.slides, function (slide, index) {
      var active = index === swiper.activeIndex;
      var frame = slide.querySelector('[data-gallery-video-frame]');
      var player = slide.querySelector('[data-gallery-video-player]');

      if (frame) {
        if (active && !frame.getAttribute('src')) {
          frame.setAttribute('src', frame.getAttribute('data-src'));
        } else if (!active) {
          frame.removeAttribute('src');
        }
      }

      if (player) {
        if (active) {
          var playAttempt = player.play();
          if (playAttempt && playAttempt.catch) {
            playAttempt.catch(function () {});
          }
        } else {
          player.pause();
          player.currentTime = 0;
        }
      }
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
        syncVideo(this);
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
