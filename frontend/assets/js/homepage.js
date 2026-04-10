/**
 * Nera Competitions - Homepage Scripts
 * Interactive functionality for homepage sections
 *
 * @package Nera_Competitions
 */

(function () {
  'use strict';

  /**
   * Initialize Featured Carousel (Swiper)
   */
  function initFeaturedCarousel() {
    const featuredCarouselEl = document.getElementById('featuredCarousel');
    if (!featuredCarouselEl || typeof Swiper === 'undefined') return;

    new Swiper('#featuredCarousel', {
      slidesPerView: 1,
      spaceBetween: 30,
      loop: true,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      effect: 'coverflow',
      coverflowEffect: {
        rotate: 0,
        stretch: 0,
        depth: 100,
        modifier: 1,
        slideShadows: false,
      },
    });
  }

  /**
   * Initialize Winners Carousel (Swiper)
   */
  function initWinnersCarousel() {
    const winnersCarouselEl = document.getElementById('winnersCarousel');
    if (!winnersCarouselEl || typeof Swiper === 'undefined') return;

    new Swiper('#winnersCarousel', {
      slidesPerView: 1,
      spaceBetween: 24,
      loop: true,
      autoplay: {
        delay: 4000,
        disableOnInteraction: false,
      },
      pagination: {
        el: '#winnersCarousel .swiper-pagination',
        clickable: true,
      },
      breakpoints: {
        640: {
          slidesPerView: 2,
        },
        1024: {
          slidesPerView: 3,
        },
        1280: {
          slidesPerView: 4,
        },
      },
    });
  }

  /**
   * Initialize Testimonials Carousel (Swiper)
   */
  function initTestimonialsCarousel() {
    const testimonialsCarouselEl = document.getElementById('testimonialsCarousel');
    if (!testimonialsCarouselEl || typeof Swiper === 'undefined') return;

    new Swiper('#testimonialsCarousel', {
      slidesPerView: 1,
      spaceBetween: 24,
      loop: true,
      autoplay: {
        delay: 6000,
        disableOnInteraction: false,
      },
      pagination: {
        el: '#testimonialsCarousel .swiper-pagination',
        clickable: true,
      },
      breakpoints: {
        640: {
          slidesPerView: 2,
        },
        1024: {
          slidesPerView: 3,
        },
      },
    });
  }

  /**
   * Initialize Ending Soon Carousel Navigation
   */
  function initEndingSoonCarousel() {
    const carousel = document.getElementById('endingSoonCarousel');
    const prevBtn = document.getElementById('endingSoonPrev');
    const nextBtn = document.getElementById('endingSoonNext');

    if (!carousel || !prevBtn || !nextBtn) return;

    // Calculate scroll amount dynamically
    const getScrollAmount = () => {
      const item = carousel.querySelector('article') || carousel.firstElementChild;
      if (!item) return 370; // Fallback

      const itemWidth = item.offsetWidth;
      const style = window.getComputedStyle(carousel);
      const gap = parseFloat(style.columnGap || style.gap) || 24; // Tailwind gap-6 is 24px

      return itemWidth + gap;
    };

    prevBtn.addEventListener('click', function () {
      carousel.scrollBy({
        left: -getScrollAmount(),
        behavior: 'smooth',
      });
    });

    nextBtn.addEventListener('click', function () {
      carousel.scrollBy({
        left: getScrollAmount(),
        behavior: 'smooth',
      });
    });

    // Optional: Update button states based on scroll position
    function updateButtonStates() {
      const isAtStart = carousel.scrollLeft <= 10;
      const isAtEnd = carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth - 10;

      prevBtn.classList.toggle('opacity-50', isAtStart);
      prevBtn.classList.toggle('cursor-not-allowed', isAtStart);
      nextBtn.classList.toggle('opacity-50', isAtEnd);
      nextBtn.classList.toggle('cursor-not-allowed', isAtEnd);
    }

    carousel.addEventListener('scroll', updateButtonStates, { passive: true });
    updateButtonStates();
  }

  /**
   * Initialize Competition Filters
   */
  function initCompetitionFilters() {
    const filterTabs = document.querySelectorAll('.filter-tab');
    const competitionItems = document.querySelectorAll('.competition-item');

    if (!filterTabs.length || !competitionItems.length) return;

    filterTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        const filter = this.dataset.filter;

        // Update active tab
        filterTabs.forEach(function (t) {
          t.classList.remove('active');
        });
        this.classList.add('active');

        // Filter items
        competitionItems.forEach(function (item) {
          if (filter === 'all' || item.classList.contains(filter)) {
            item.style.display = '';
            item.style.animation = 'fadeInUp 0.4s ease forwards';
          } else {
            item.style.display = 'none';
          }
        });
      });
    });
  }

  /**
   * Initialize View Toggle (Grid/List)
   */
  function initViewToggle() {
    const viewToggles = document.querySelectorAll('.view-toggle');
    const competitionsGrid = document.getElementById('competitionsGrid');

    if (!viewToggles.length || !competitionsGrid) return;

    viewToggles.forEach(function (toggle) {
      toggle.addEventListener('click', function () {
        const view = this.dataset.view;

        // Update active toggle
        viewToggles.forEach(function (t) {
          t.classList.remove('active');
        });
        this.classList.add('active');

        // Update grid class
        if (view === 'list') {
          competitionsGrid.classList.add('list-view');
        } else {
          competitionsGrid.classList.remove('list-view');
        }
      });
    });
  }

  /**
   * Initialize Newsletter Form
   */
  function initNewsletterForm() {
    const newsletterForm = document.getElementById('newsletterForm');
    const successModal = document.getElementById('newsletterSuccess');

    if (!newsletterForm) return;

    newsletterForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const submitButton = this.querySelector('button[type="submit"]');
      const originalText = submitButton.innerHTML;

      // Show loading state
      submitButton.disabled = true;
      submitButton.innerHTML = '<span class="animate-spin">↻</span> Subscribing...';

      // Simulate API call (replace with actual endpoint)
      setTimeout(function () {
        // Reset button
        submitButton.disabled = false;
        submitButton.innerHTML = originalText;

        // Show success message
        if (successModal) {
          successModal.style.display = 'flex';

          // Close on click outside
          successModal.addEventListener('click', function (e) {
            if (e.target === successModal) {
              successModal.style.display = 'none';
            }
          });

          // Auto close after 3 seconds
          setTimeout(function () {
            successModal.style.display = 'none';
          }, 3000);
        }

        // Reset form
        newsletterForm.reset();
      }, 1500);
    });
  }

  /**
   * Initialize Smooth Scroll
   */
  function initSmoothScroll() {
    const scrollLinks = document.querySelectorAll('a[href^="#"]');

    scrollLinks.forEach(function (link) {
      link.addEventListener('click', function (e) {
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;

        const targetElement = document.querySelector(targetId);
        if (!targetElement) return;

        e.preventDefault();

        const headerOffset = 100;
        const elementPosition = targetElement.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

        window.scrollTo({
          top: offsetPosition,
          behavior: 'smooth',
        });
      });
    });
  }

  /**
   * Initialize Parallax Background
   */
  function initHeroParallax() {
    const hero = document.querySelector('.hero-section');
    if (!hero) return;

    window.addEventListener(
      'scroll',
      function () {
        const scrolled = window.pageYOffset;
        const background = hero.querySelector('.hero-section__background');

        if (background && scrolled < hero.offsetHeight) {
          background.style.transform = 'translateY(' + scrolled * 0.3 + 'px)';
        }
      },
      { passive: true }
    );
  }

  /**
   * Initialize Animated Stats Counters
   */
  function initStatsCounters() {
    const counters = document.querySelectorAll('[data-counter]');
    if (!counters.length) return;

    const observerOptions = {
      threshold: 0.5,
    };

    const observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, observerOptions);

    counters.forEach(function (counter) {
      observer.observe(counter);
    });
  }

  /**
   * Animate a counter element
   */
  function animateCounter(element) {
    const target = parseFloat(element.dataset.counter.replace(/,/g, ''));
    const duration = 2000;
    const start = 0;
    const startTime = performance.now();

    // Check if target has decimal (like 4.9)
    const hasDecimal = target % 1 !== 0;

    function update(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);

      // Easing
      const eased = 1 - Math.pow(1 - progress, 3);

      let current = start + (target - start) * eased;

      if (hasDecimal) {
        current = current.toFixed(1);
      } else {
        current = Math.floor(current).toLocaleString();
      }

      element.textContent = current;

      if (progress < 1) {
        requestAnimationFrame(update);
      } else {
        element.textContent = hasDecimal ? target.toFixed(1) : target.toLocaleString();
      }
    }

    requestAnimationFrame(update);
  }

  /**
   * Initialize Video Play Button
   */
  function initVideoPlay() {
    const playButton = document.getElementById('playLatestDraw');
    if (!playButton) return;

    playButton.addEventListener('click', function () {
      const placeholder = document.querySelector('.live-draw-video-placeholder');
      if (!placeholder) return;

      // Replace with YouTube embed (example)
      // Replace VIDEO_ID with actual video ID
      placeholder.innerHTML = `
        <iframe
          width="100%"
          height="100%"
          src="https://www.youtube.com/embed/VIDEO_ID?autoplay=1"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
        ></iframe>
      `;
    });
  }

  /**
   * Initialize How It Works Section Animations
   * Handles scroll-triggered animations for step cards and path
   */
  function initHowItWorksAnimations() {
    const section = document.querySelector('.how-it-works-section');
    if (!section) return;

    const header = section.querySelector('.how-it-works-header');
    const steps = section.querySelectorAll('.how-it-works-step');
    const ctaSection = section.querySelector('[data-delay="600"]');

    // Create intersection observer for the section
    const sectionObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            sectionObserver.unobserve(entry.target);
          }
        });
      },
      {
        threshold: 0.2,
        rootMargin: '0px 0px -100px 0px',
      }
    );

    // Observe the main section for path animation
    sectionObserver.observe(section);

    // Create observer for individual elements
    const elementObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            const delay = entry.target.dataset.delay || 0;
            setTimeout(function () {
              entry.target.classList.add('is-visible');
            }, parseInt(delay));
            elementObserver.unobserve(entry.target);
          }
        });
      },
      {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px',
      }
    );

    // Observe header
    if (header) {
      elementObserver.observe(header);
    }

    // Observe each step with staggered delay
    steps.forEach(function (step) {
      elementObserver.observe(step);
    });

    // Observe CTA section
    if (ctaSection) {
      elementObserver.observe(ctaSection);
    }

    // Optional: Add 3D tilt effect on mouse move (desktop only)
    if (window.matchMedia('(min-width: 1024px)').matches) {
      steps.forEach(function (step) {
        const card = step.querySelector('.how-it-works-card');
        if (!card) return;

        step.addEventListener('mousemove', function (e) {
          const rect = card.getBoundingClientRect();
          const x = e.clientX - rect.left;
          const y = e.clientY - rect.top;
          const centerX = rect.width / 2;
          const centerY = rect.height / 2;

          const rotateX = (y - centerY) / 20;
          const rotateY = (centerX - x) / 20;

          card.style.transform =
            'perspective(1000px) rotateX(' +
            rotateX +
            'deg) rotateY(' +
            rotateY +
            'deg) translateZ(10px)';
        });

        step.addEventListener('mouseleave', function () {
          card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateZ(0)';
          card.style.transition = 'transform 0.5s ease';
        });

        step.addEventListener('mouseenter', function () {
          card.style.transition = 'transform 0.1s ease';
        });
      });
    }
  }

  /**
   * Initialize FAQ Accordion
   */
  function initFaqAccordion() {
    const faqItems = document.querySelectorAll('.faq-item');
    if (!faqItems.length) return;

    faqItems.forEach(function (item) {
      const question = item.querySelector('.faq-item__question');
      const answer = item.querySelector('.faq-item__answer');
      const icon = item.querySelector('.faq-item__icon');

      if (!question || !answer) return;

      question.addEventListener('click', function () {
        const isExpanded = this.getAttribute('aria-expanded') === 'true';

        // Close all other items
        faqItems.forEach(function (otherItem) {
          const otherQuestion = otherItem.querySelector('.faq-item__question');
          const otherAnswer = otherItem.querySelector('.faq-item__answer');
          const otherIcon = otherItem.querySelector('.faq-item__icon');
          if (otherQuestion && otherAnswer && otherItem !== item) {
            otherQuestion.setAttribute('aria-expanded', 'false');
            otherAnswer.style.maxHeight = '0';
            otherAnswer.style.opacity = '0';
            if (otherIcon) {
              otherIcon.style.transform = 'rotate(0deg)';
            }
          }
        });

        // Toggle current item
        this.setAttribute('aria-expanded', !isExpanded);

        if (!isExpanded) {
          // Opening: set max-height to scrollHeight for smooth animation
          answer.style.maxHeight = answer.scrollHeight + 'px';
          answer.style.opacity = '1';
          if (icon) {
            icon.style.transform = 'rotate(180deg)';
          }
        } else {
          // Closing
          answer.style.maxHeight = '0';
          answer.style.opacity = '0';
          if (icon) {
            icon.style.transform = 'rotate(0deg)';
          }
        }
      });
    });
  }

  /**
   * Initialize animations for all homepage sections
   */
  function initAOS() {
    // Wait for AOS library to be loaded
    const checkAOS = setInterval(function () {
      if (typeof AOS !== 'undefined') {
        clearInterval(checkAOS);
        try {
          AOS.init({
            duration: 800,
            easing: 'ease-out-cubic',
            once: true,
            offset: 100,
            delay: 0,
            disable: false,
            startEvent: 'DOMContentLoaded',
          });
          console.log('AOS initialized successfully');
        } catch (error) {
          console.error('Error initializing AOS:', error);
        }
      }
    }, 100);

    // Timeout after 5 seconds
    setTimeout(function () {
      clearInterval(checkAOS);
      if (typeof AOS === 'undefined') {
        console.warn('AOS library failed to load, sections may not be visible');
      }
    }, 5000);
  }

  /**
   * Initialize all homepage functionality
   */
  function init() {
    initFeaturedCarousel();
    initWinnersCarousel();
    initTestimonialsCarousel();
    initEndingSoonCarousel();
    initCompetitionFilters();
    initViewToggle();
    initNewsletterForm();
    initSmoothScroll();
    initHeroParallax();
    initStatsCounters();
    initVideoPlay();
    // initFaqAccordion(); // Replaced by AlpineJS
    initHowItWorksAnimations();
    initAOS();
  }

  // Initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Expose for external use
  window.neraHomepage = {
    init: init,
    initCarousels: function () {
      initFeaturedCarousel();
      initWinnersCarousel();
      initTestimonialsCarousel();
    },
  };
})();
