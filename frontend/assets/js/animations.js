/**
 * Nera Competitions - Animations
 * Page animations using AOS-like scroll reveal and micro-interactions
 *
 * @package Nera_Competitions
 */

(function () {
  'use strict';

  // Animation configuration (can be overridden by theme settings)
  const config = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px',
    once: true,
    defaultAnimation: 'fade-up',
    defaultDuration: 400,
    defaultDelay: 0,
  };

  /**
   * Initialize scroll animations
   */
  function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('[data-aos]');

    if (!animatedElements.length) return;

    // Check if IntersectionObserver is supported
    if (!('IntersectionObserver' in window)) {
      // Fallback: show all elements immediately
      animatedElements.forEach(function (el) {
        el.classList.add('aos-animate');
      });
      return;
    }

    const observer = new IntersectionObserver(handleIntersection, {
      threshold: config.threshold,
      rootMargin: config.rootMargin,
    });

    animatedElements.forEach(function (element) {
      // Set initial state
      element.style.opacity = '0';
      element.style.transform = getInitialTransform(element.dataset.aos);

      // Add transition
      const duration = element.dataset.aosDuration || config.defaultDuration;
      const delay = element.dataset.aosDelay || config.defaultDelay;
      element.style.transition = `opacity ${duration}ms ease, transform ${duration}ms ease`;
      element.style.transitionDelay = `${delay}ms`;

      observer.observe(element);
    });
  }

  /**
   * Get initial transform based on animation type
   */
  function getInitialTransform(animation) {
    const transforms = {
      'fade-up': 'translateY(30px)',
      'fade-down': 'translateY(-30px)',
      'fade-left': 'translateX(30px)',
      'fade-right': 'translateX(-30px)',
      'fade-in': 'scale(0.95)',
      'zoom-in': 'scale(0.8)',
      'zoom-out': 'scale(1.1)',
      'slide-up': 'translateY(100px)',
      'slide-down': 'translateY(-100px)',
      'slide-left': 'translateX(100px)',
      'slide-right': 'translateX(-100px)',
      'flip-up': 'perspective(2500px) rotateX(-100deg)',
      'flip-down': 'perspective(2500px) rotateX(100deg)',
    };

    return transforms[animation] || transforms['fade-up'];
  }

  /**
   * Handle intersection observer callback
   */
  function handleIntersection(entries, observer) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        const element = entry.target;

        // Animate in
        element.style.opacity = '1';
        element.style.transform = 'translateY(0) translateX(0) scale(1) rotateX(0)';

        element.classList.add('aos-animate');

        // Unobserve if animation should only run once
        if (config.once) {
          observer.unobserve(element);
        }
      }
    });
  }

  /**
   * Initialize hover effects
   */
  function initHoverEffects() {
    // Competition card hover effects
    const cards = document.querySelectorAll('.ncs-product-card');

    cards.forEach(function (card) {
      card.addEventListener('mouseenter', handleCardMouseEnter);
      card.addEventListener('mouseleave', handleCardMouseLeave);
      card.addEventListener('mousemove', handleCardMouseMove);
    });

    // Button ripple effect
    const buttons = document.querySelectorAll('.btn');

    buttons.forEach(function (button) {
      button.addEventListener('click', createRipple);
    });
  }

  /**
   * Card mouse enter handler
   */
  function handleCardMouseEnter(e) {
    const card = e.currentTarget;
    card.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
  }

  /**
   * Card mouse leave handler
   */
  function handleCardMouseLeave(e) {
    const card = e.currentTarget;
    card.style.transform = '';
  }

  /**
   * Card mouse move handler for 3D tilt effect
   */
  function handleCardMouseMove(e) {
    const card = e.currentTarget;
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const centerX = rect.width / 2;
    const centerY = rect.height / 2;

    const rotateX = (y - centerY) / 20;
    const rotateY = (centerX - x) / 20;

    // Subtle 3D effect
    card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px)`;
  }

  /**
   * Create ripple effect on button click
   */
  function createRipple(e) {
    const button = e.currentTarget;
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();

    const size = Math.max(rect.width, rect.height);
    const x = e.clientX - rect.left - size / 2;
    const y = e.clientY - rect.top - size / 2;

    ripple.style.cssText = `
      position: absolute;
      width: ${size}px;
      height: ${size}px;
      left: ${x}px;
      top: ${y}px;
      background: rgba(255, 255, 255, 0.3);
      border-radius: 50%;
      transform: scale(0);
      animation: ripple 0.6s ease-out;
      pointer-events: none;
    `;

    button.style.position = 'relative';
    button.style.overflow = 'hidden';
    button.appendChild(ripple);

    setTimeout(function () {
      ripple.remove();
    }, 600);
  }

  /**
   * Initialize parallax effects
   */
  function initParallax() {
    const parallaxElements = document.querySelectorAll('[data-parallax]');

    if (!parallaxElements.length) return;

    window.addEventListener(
      'scroll',
      function () {
        const scrolled = window.pageYOffset;

        parallaxElements.forEach(function (element) {
          const speed = parseFloat(element.dataset.parallax) || 0.5;
          const yPos = -(scrolled * speed);
          element.style.transform = `translateY(${yPos}px)`;
        });
      },
      { passive: true }
    );
  }

  /**
   * Initialize counter animations
   */
  function initCounterAnimations() {
    const counters = document.querySelectorAll('[data-counter]');

    if (!counters.length) return;

    const observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            animateCounter(entry.target);
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.5 }
    );

    counters.forEach(function (counter) {
      observer.observe(counter);
    });
  }

  /**
   * Animate a counter element
   */
  function animateCounter(element) {
    const target = parseInt(element.dataset.counter, 10);
    const duration = parseInt(element.dataset.counterDuration, 10) || 2000;
    const start = 0;
    const startTime = performance.now();

    function updateCounter(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);

      // Easing function (ease-out)
      const easedProgress = 1 - Math.pow(1 - progress, 3);

      const current = Math.floor(start + (target - start) * easedProgress);
      element.textContent = current.toLocaleString();

      if (progress < 1) {
        requestAnimationFrame(updateCounter);
      } else {
        element.textContent = target.toLocaleString();
      }
    }

    requestAnimationFrame(updateCounter);
  }

  /**
   * Add ripple keyframes to document
   */
  function addRippleStyles() {
    if (document.getElementById('nera-ripple-styles')) return;

    const style = document.createElement('style');
    style.id = 'nera-ripple-styles';
    style.textContent = `
      @keyframes ripple {
        to {
          transform: scale(4);
          opacity: 0;
        }
      }
    `;
    document.head.appendChild(style);
  }

  /**
   * Initialize all animations
   */
  function init() {
    addRippleStyles();
    initScrollAnimations();
    initHoverEffects();
    initParallax();
    initCounterAnimations();
  }

  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Re-initialize for dynamically loaded content
  document.addEventListener('nera:content:loaded', initScrollAnimations);

  // Expose for external use
  window.neraAnimations = {
    init: init,
    refresh: initScrollAnimations,
  };
})();
