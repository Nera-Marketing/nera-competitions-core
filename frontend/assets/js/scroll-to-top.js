/**
 * Nera Competitions - Scroll to Top
 * Handles the configured scroll to top button
 */

(function () {
  'use strict';

  function initScrollToTop() {
    const scrollBtn = document.getElementById('nera-scroll-top');
    if (!scrollBtn) return;

    // Show/Hide button based on scroll position
    function toggleScrollBtn() {
      if (window.scrollY > 300) {
        scrollBtn.classList.add('is-visible');
        scrollBtn.classList.remove('translate-y-20', 'opacity-0', 'invisible');
        scrollBtn.classList.add('translate-y-0', 'opacity-100', 'visible');
      } else {
        scrollBtn.classList.remove('is-visible');
        scrollBtn.classList.add('translate-y-20', 'opacity-0', 'invisible');
        scrollBtn.classList.remove('translate-y-0', 'opacity-100', 'visible');
      }
    }

    // Scroll to top on click
    scrollBtn.addEventListener('click', function (e) {
      e.preventDefault();
      window.scrollTo({
        top: 0,
        behavior: 'smooth',
      });
    });

    // Listen to scroll
    window.addEventListener('scroll', toggleScrollBtn, { passive: true });

    // Initial check
    toggleScrollBtn();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initScrollToTop);
  } else {
    initScrollToTop();
  }
})();
