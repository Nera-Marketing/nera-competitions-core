/**
 * Stats Counter — CountUp animation triggered on viewport-enter via IntersectionObserver.
 * Looks for elements with .count-up + data-target="<numeric value>" inside #stats-section.
 */
(function () {
  'use strict';

  function animateCountUp(el) {
    const target = parseFloat(el.dataset.target);
    if (isNaN(target)) return;
    const duration = 2000;
    const startTime = performance.now();
    const easeOutQuart = (t) => 1 - Math.pow(1 - t, 4);

    function updateCount(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const eased = easeOutQuart(progress);
      const value = target * eased;
      el.textContent = target % 1 !== 0 ? value.toFixed(1) : Math.floor(value);
      if (progress < 1) {
        requestAnimationFrame(updateCount);
      } else {
        el.textContent = target;
      }
    }

    requestAnimationFrame(updateCount);
  }

  function init() {
    const section = document.getElementById('stats-section');
    if (!section) return;

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.querySelectorAll('.count-up').forEach((el) => {
          if (el.classList.contains('counted')) return;
          el.classList.add('counted');
          animateCountUp(el);
        });
      });
    }, { threshold: 0.3 });

    observer.observe(section);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
