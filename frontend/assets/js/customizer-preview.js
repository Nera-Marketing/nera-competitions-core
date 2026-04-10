/**
 * Nera Competitions - Customizer Live Preview
 * Real-time preview of customizer changes
 */

(function ($) {
  'use strict';

  // Helper function to update CSS variable
  function updateCSSVariable(variable, value) {
    document.documentElement.style.setProperty(variable, value);
  }

  // =========================================================================
  // Brand Colors
  // =========================================================================

  wp.customize('nera_primary_color', function (value) {
    value.bind(function (newval) {
      updateCSSVariable('--nera-color-primary', newval);
    });
  });

  wp.customize('nera_secondary_color', function (value) {
    value.bind(function (newval) {
      updateCSSVariable('--nera-color-secondary', newval);
    });
  });

  wp.customize('nera_accent_color', function (value) {
    value.bind(function (newval) {
      updateCSSVariable('--nera-color-accent', newval);
    });
  });

  wp.customize('nera_success_color', function (value) {
    value.bind(function (newval) {
      updateCSSVariable('--nera-color-success', newval);
    });
  });

  wp.customize('nera_danger_color', function (value) {
    value.bind(function (newval) {
      updateCSSVariable('--nera-color-danger', newval);
    });
  });

  // =========================================================================
  // Header & Footer
  // =========================================================================

  wp.customize('nera_header_bg_color', function (value) {
    value.bind(function (newval) {
      updateCSSVariable('--nera-header-bg', newval);
      // Also update header element directly
      $('.site-header, .main-header-bar').css('background-color', newval);
    });
  });

  wp.customize('nera_footer_bg_color', function (value) {
    value.bind(function (newval) {
      updateCSSVariable('--nera-footer-bg', newval);
      // Also update footer element directly
      $('.site-footer, .ast-footer, footer').css('background-color', newval);
    });
  });

  wp.customize('nera_footer_text_color', function (value) {
    value.bind(function (newval) {
      updateCSSVariable('--nera-footer-text', newval);
      // Update footer text color
      $('.site-footer, .ast-footer, footer').css('color', newval);
      $('.site-footer a, .ast-footer a, footer a').css('color', newval);
    });
  });

  // =========================================================================
  // Typography (requires page reload for Google Fonts)
  // =========================================================================

  wp.customize('nera_heading_font', function (value) {
    value.bind(function (newval) {
      // Load font dynamically
      loadGoogleFont(newval);
      updateCSSVariable('--nera-font-heading', "'" + newval + "', sans-serif");
    });
  });

  wp.customize('nera_body_font', function (value) {
    value.bind(function (newval) {
      // Load font dynamically
      loadGoogleFont(newval);
      updateCSSVariable('--nera-font-body', "'" + newval + "', sans-serif");
    });
  });

  wp.customize('nera_heading_weight', function (value) {
    value.bind(function (newval) {
      updateCSSVariable('--nera-font-weight-heading', newval);
    });
  });

  // =========================================================================
  // Layout Options
  // =========================================================================

  wp.customize('nera_container_width', function (value) {
    value.bind(function (newval) {
      updateCSSVariable('--nera-container-xl', newval + 'px');
    });
  });

  wp.customize('nera_border_radius_style', function (value) {
    value.bind(function (newval) {
      var radiusMap = {
        sharp: '0',
        subtle: '0.25rem',
        rounded: '0.75rem',
        pill: '1.5rem',
      };
      updateCSSVariable('--nera-radius-lg', radiusMap[newval] || '0.75rem');
    });
  });

  wp.customize('nera_card_shadow', function (value) {
    value.bind(function (newval) {
      var shadowMap = {
        none: 'none',
        subtle: '0 2px 8px 0 rgba(0, 0, 0, 0.05)',
        medium: '0 4px 16px 0 rgba(0, 0, 0, 0.1)',
        strong: '0 10px 40px 0 rgba(0, 0, 0, 0.15)',
      };
      updateCSSVariable('--nera-shadow-card', shadowMap[newval] || shadowMap['medium']);
    });
  });

  // =========================================================================
  // Helper Functions
  // =========================================================================

  // Dynamically load Google Font
  function loadGoogleFont(fontName) {
    if (!fontName) return;

    var fontUrl =
      'https://fonts.googleapis.com/css2?family=' +
      encodeURIComponent(fontName.replace(/ /g, '+')) +
      ':wght@300;400;500;600;700;800&display=swap';

    // Check if font is already loaded
    var existingLinks = $('link[href*="' + encodeURIComponent(fontName) + '"]');
    if (existingLinks.length) return;

    // Create and append link element
    $('<link>').attr('rel', 'stylesheet').attr('href', fontUrl).appendTo('head');
  }

  // Update gradient variables when primary/accent changes
  function updateGradients() {
    var primary = getComputedStyle(document.documentElement)
      .getPropertyValue('--nera-color-primary')
      .trim();
    var accent = getComputedStyle(document.documentElement)
      .getPropertyValue('--nera-color-accent')
      .trim();
    var secondary = getComputedStyle(document.documentElement)
      .getPropertyValue('--nera-color-secondary')
      .trim();

    updateCSSVariable(
      '--nera-gradient-primary',
      'linear-gradient(135deg, ' + primary + ', ' + accent + ')'
    );
    updateCSSVariable(
      '--nera-gradient-secondary',
      'linear-gradient(135deg, ' + secondary + ', ' + primary + ')'
    );
  }

  // Debounce function for performance
  function debounce(func, wait) {
    var timeout;
    return function executedFunction() {
      var context = this;
      var args = arguments;
      clearTimeout(timeout);
      timeout = setTimeout(function () {
        func.apply(context, args);
      }, wait);
    };
  }

  // Update gradients when colors change (debounced)
  var debouncedGradientUpdate = debounce(updateGradients, 100);

  wp.customize('nera_primary_color', function (value) {
    value.bind(debouncedGradientUpdate);
  });

  wp.customize('nera_accent_color', function (value) {
    value.bind(debouncedGradientUpdate);
  });

  wp.customize('nera_secondary_color', function (value) {
    value.bind(debouncedGradientUpdate);
  });
})(jQuery);
