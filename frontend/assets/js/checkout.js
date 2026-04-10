/**
 * Nera Checkout Module
 * AlpineJS component for checkout form interactions
 *
 * @package Nera_Competitions
 */
(function () {
  'use strict';

  /**
   * AlpineJS checkout component
   */
  window.neraCheckout = function () {
    return {
      processing: false,
      selectedPayment: '',
      termsAccepted: true,

      init() {
        this.initPaymentMethods();
        this.initEmailValidation();
        this.initLoadingStates();
        this.preventDoubleSubmit();
        this.bindCheckoutEvents();
        this.initCouponUpdates();
        this.initCouponRemoval();
        this.initTermsGuard();
      },

      /**
       * Initialize payment method selection UI
       */
      initPaymentMethods() {
        var self = this;
        var form = this.$el;

        // Set initial active state
        var checked = form.querySelector('input[name="payment_method"]:checked');
        if (checked) {
          this.selectedPayment = checked.value;
          this.updatePaymentUI();
        }

        // Listen for payment method changes
        form.addEventListener('change', function (e) {
          if (e.target.name === 'payment_method') {
            self.selectedPayment = e.target.value;
            self.updatePaymentUI();
          }
        });

        // Make entire payment card clickable
        form.addEventListener('click', function (e) {
          var card = e.target.closest('.wc_payment_method');
          if (!card) return;
          var radio = card.querySelector('input[name="payment_method"]');
          if (!radio) return;
          if (e.target.closest('label[for^="payment_method"]')) return;
          if (e.target.closest('a, input, select, textarea, button')) return;
          radio.click();
        });
      },

      /**
       * Toggle active class on payment method containers
       */
      updatePaymentUI() {
        var methods = this.$el.querySelectorAll('.wc_payment_method');
        var selected = this.selectedPayment;

        methods.forEach(function (el) {
          var radio = el.querySelector('input[type="radio"]');
          if (radio && radio.value === selected) {
            el.classList.add('active');
          } else {
            el.classList.remove('active');
          }
        });
      },

      /**
       * Email field validation on blur
       */
      initEmailValidation() {
        var emailField = document.getElementById('billing_email');
        if (emailField) {
          emailField.addEventListener('blur', function () {
            if (this.value && !this.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
              this.classList.add('border-danger');
            } else {
              this.classList.remove('border-danger');
            }
          });
        }
      },

      /**
       * Show loading overlay during checkout updates
       */
      initLoadingStates() {
        if (typeof jQuery === 'undefined') return;

        var $body = jQuery(document.body);

        $body.on('update_checkout', function () {
          // Show loading overlay
          var form = document.querySelector('.woocommerce-checkout');
          if (form && !form.querySelector('.checkout-loading-overlay')) {
            var overlay = document.createElement('div');
            overlay.className =
              'checkout-loading-overlay fixed inset-0 bg-white/70 z-50 flex items-center justify-center';
            overlay.innerHTML =
              '<span class="material-symbols-outlined animate-spin text-4xl text-primary">progress_activity</span>';
            form.appendChild(overlay);
          }
        });

        $body.on('updated_checkout', function () {
          // Hide loading overlay
          var overlay = document.querySelector('.checkout-loading-overlay');
          if (overlay) overlay.remove();
        });
      },

      /**
       * Prevent double form submission
       */
      preventDoubleSubmit() {
        var form = this.$el;
        var isSubmitting = false;

        form.addEventListener('submit', function (e) {
          if (isSubmitting) {
            e.preventDefault();
            return false;
          }
          isSubmitting = true;

          // Reset after 30 seconds (safety net)
          setTimeout(function () {
            isSubmitting = false;
          }, 30000);
        });
      },

      /**
       * Bind WooCommerce checkout events
       */
      bindCheckoutEvents() {
        var self = this;

        if (typeof jQuery === 'undefined') return;

        var $body = jQuery(document.body);

        // Processing state on checkout submit
        $body.on('checkout_place_order', function () {
          self.processing = true;
          self.setButtonProcessing(true);
          return true;
        });

        // Reset on error
        $body.on('checkout_error', function () {
          self.processing = false;
          self.setButtonProcessing(false);

          // Auto-scroll to first error
          var firstError = document.querySelector(
            '.woocommerce-NoticeGroup, .woocommerce-error, .woocommerce-invalid'
          );
          if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
        });

        // Re-init payment UI after checkout update
        $body.on('updated_checkout', function () {
          self.initPaymentMethods();
        });
      },

      /**
       * Handle applied coupon display updates
       */
      initCouponUpdates() {
        if (typeof jQuery === 'undefined') return;

        var $body = jQuery(document.body);
        var self = this;

        // Listen for coupon applied event
        $body.on('applied_coupon_in_checkout', function (event, couponCode) {
          self.refreshAppliedCoupons();
        });

        // Listen for coupon removed event
        $body.on('removed_coupon_in_checkout', function (event, couponCode) {
          self.refreshAppliedCoupons();
        });

        // Also refresh after checkout update completes
        $body.on('updated_checkout', function () {
          self.refreshAppliedCoupons();
        });
      },

      /**
       * Fetch and update applied coupons display via AJAX
       */
      refreshAppliedCoupons() {
        // Find the container or its parent if it's been removed
        var container = document.getElementById('checkout-applied-coupons');
        var parentContainer = document.querySelector('.checkout_coupon');
        
        if (!container && !parentContainer) return;

        // Get AJAX URL from localized settings
        var ajaxUrl =
          typeof neraSettings !== 'undefined' && neraSettings.ajaxUrl
            ? neraSettings.ajaxUrl
            : '/wp-admin/admin-ajax.php';

        // Make AJAX request
        fetch(ajaxUrl + '?action=get_applied_coupons', {
          method: 'POST',
          credentials: 'same-origin',
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              if (data.data.has_coupons) {
                if (container) {
                  // Replace existing container
                  container.outerHTML = data.data.html;
                } else if (parentContainer) {
                  // Insert new container if it doesn't exist
                  parentContainer.insertAdjacentHTML('beforeend', data.data.html);
                }
              } else {
                // Remove the container if no coupons
                if (container) {
                  container.remove();
                }
              }
            }
          })
          .catch((error) => {
            console.error('Error refreshing applied coupons:', error);
          });
      },

      /**
       * Initialize coupon removal via AJAX
       */
      initCouponRemoval() {
        var self = this;

        // Use event delegation to handle dynamically added remove buttons
        document.addEventListener('click', function (e) {
          var removeLink = e.target.closest('a.remove-coupon');
          if (!removeLink) return;

          e.preventDefault();

          var couponCode = removeLink.getAttribute('data-coupon');
          if (!couponCode) return;

          self.removeCoupon(couponCode);
        });
      },

      /**
       * Ensure terms checkbox is accepted before placing order
       */
      initTermsGuard() {
        var self = this;
        var form = this.$el;

        this.updateTermsState();

        form.addEventListener('change', function (e) {
          if (e.target && e.target.id === 'terms') {
            self.updateTermsState();
          }
        });

        if (typeof jQuery !== 'undefined') {
          jQuery(document.body).on('updated_checkout', function () {
            self.updateTermsState();
          });
        }
      },

      /**
       * Track whether terms acceptance is currently satisfied
       */
      updateTermsState() {
        var termsCheckbox = this.$el.querySelector('#terms');
        var termsFieldMarker = this.$el.querySelector('input[name="terms-field"]');
        var isTermsRequired = !!(termsCheckbox && termsFieldMarker);

        this.termsAccepted = !isTermsRequired || termsCheckbox.checked;
        this.updatePlaceOrderAvailability();
      },

      /**
       * Keep place order button state in sync with checkout requirements
       */
      updatePlaceOrderAvailability() {
        var btn = document.getElementById('place_order');
        if (!btn) return;

        if (this.processing) {
          btn.disabled = true;
        } else {
          btn.disabled = !this.termsAccepted;
        }

        btn.setAttribute('aria-disabled', btn.disabled ? 'true' : 'false');
      },

      /**
       * Remove coupon via AJAX
       */
      removeCoupon(couponCode) {
        if (typeof jQuery === 'undefined') return;

        var self = this;
        var $body = jQuery(document.body);

        // Get nonce from WooCommerce
        var nonce = typeof wc_checkout_params !== 'undefined'
          ? wc_checkout_params.remove_coupon_nonce
          : '';

        if (!nonce) {
          console.error('Missing remove_coupon_nonce');
          return;
        }

        // Construct WooCommerce AJAX URL using query parameter format
        // WooCommerce AJAX endpoints use /?wc-ajax=ACTION_NAME format
        var siteUrl = window.location.origin;
        var wcAjaxUrl = siteUrl + '/?wc-ajax=remove_coupon';

        console.log('Removing coupon:', {
          coupon: couponCode,
          nonce: nonce,
          url: wcAjaxUrl
        });

        // Show loading state
        var container = document.querySelector('.checkout_coupon');
        if (container) {
          container.style.opacity = '0.6';
          container.style.pointerEvents = 'none';
        }

        // Make AJAX request
        jQuery.ajax({
          type: 'POST',
          url: wcAjaxUrl,
          data: {
            security: nonce,
            coupon: couponCode
          },
          success: function (response) {
            // WooCommerce remove_coupon returns HTML with notices
            // Trigger WooCommerce events to update checkout
            $body.trigger('removed_coupon_in_checkout', [couponCode]);
            $body.trigger('update_checkout', { update_shipping_method: false });

            // Show success message via toast if available
            if (window.Alpine && Alpine.store('toast')) {
              Alpine.store('toast').success('Coupon removed');
            }
          },
          error: function (xhr, status, error) {
            console.error('Error removing coupon:', { xhr, status, error });

            // Parse error message if available
            var errorMessage = 'Failed to remove coupon';
            if (xhr.responseText) {
              try {
                var $response = jQuery(xhr.responseText);
                var notice = $response.find('.woocommerce-error').text();
                if (notice) {
                  errorMessage = notice.trim();
                }
              } catch (e) {
                // Use default error message
              }
            }

            // Show error message
            if (window.Alpine && Alpine.store('toast')) {
              Alpine.store('toast').error(errorMessage);
            }

            // Trigger checkout update to refresh state
            $body.trigger('update_checkout', { update_shipping_method: false });
          },
          complete: function () {
            // Remove loading state
            if (container) {
              container.style.opacity = '1';
              container.style.pointerEvents = 'auto';
            }
          }
        });
      },

      /**
       * Toggle Place Order button processing state
       */
      setButtonProcessing(isProcessing) {
        var btn = document.getElementById('place_order');
        if (!btn) return;

        if (isProcessing) {
          btn.disabled = true;
          btn.innerHTML =
            '<span class="material-symbols-outlined animate-spin text-xl">progress_activity</span>' +
            '<span>Processing\u2026</span>';
        } else {
          btn.innerHTML =
            '<span class="material-symbols-outlined">lock</span>' + btn.getAttribute('data-value');
          this.updatePlaceOrderAvailability();
        }
      },
    };
  };
})();
