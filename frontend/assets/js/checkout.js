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
        this.initPaypalTermsGuard();
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
              'checkout-loading-overlay fixed inset-0 bg-surface/70 z-50 flex items-center justify-center';
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
        $body.on('checkout_error', function (event, errorMessage) {
          self.processing = false;
          self.setButtonProcessing(false);

          // Auto-scroll to first error
          var firstError = document.querySelector(
            '.woocommerce-NoticeGroup, .woocommerce-error, .woocommerce-invalid'
          );
          if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }

          // Both the normal Place Order submit and the PayPal Smart Button's own
          // early validation fire this same event with the notice HTML as $2 — neither
          // touches #terms itself (it isn't part of any AJAX-refreshed fragment), so
          // without this the checkbox never shows an error state to match.
          var mentionsTerms =
            typeof errorMessage === 'string' && /terms and conditions/i.test(errorMessage);
          self.setTermsInvalid(mentionsTerms);
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
        this.updatePaypalGuard();

        // Ticking the box clears its own error state immediately, same as the email
        // field's border-danger toggling on blur — don't wait for another submit attempt.
        if (this.termsAccepted) {
          this.setTermsInvalid(false);
        }
      },

      /**
       * Disable the PayPal Smart Button until the terms checkbox is ticked, and
       * re-enable it the moment it is.
       *
       * WHY THE THEME HAS TO DO THIS
       * The plugin owns a disabled state for this button, but the classic checkout
       * bootstrap decides it with `shouldRender() && !button.is_disabled` — the terms
       * checkbox is not part of that test. So #place_order went disabled while PayPal
       * stayed live beside it, which is the one button that skips the form submit
       * entirely and hands off to paypal.com.
       *
       * HOW
       * The button is a cross-origin paypal.com iframe: no `disabled` attribute, and no
       * click of ours to intercept. The plugin's own gateway.css already solves this for
       * its internal use —
       *     .ppcp-disabled { cursor: not-allowed; filter: grayscale(100%) }
       *     .ppcp-disabled * { pointer-events: none }
       * — so we drive that class instead of stacking an overlay on top. pointer-events
       * on the ancestor stops the iframe receiving clicks at all, with none of the
       * z-index guesswork an overlay needs against a third-party iframe.
       *
       * It is our own class, `ncs-ppcp-blocked`, that carries those rules (with
       * !important, to beat the inline pointer-events the plugin sets) rather than
       * `ppcp-disabled` itself. The plugin reads `ppcp-disabled` as "I disabled this":
       * setting it ourselves would have its ButtonsDisabler call actions.enable() and
       * strip the class again on its next evaluation, leaving the two of us fighting
       * over one attribute. Ours is a class it never looks at.
       */
      initPaypalTermsGuard() {
        var self = this;

        /**
         * The plugin localizes the exact wrapper selector it renders into, so read it
         * from there rather than hard-coding an ID that moves between SDK versions
         * (ppcp-sdk-v6 renders #ppc-button-ppcp-gateway-v6, for one).
         */
        function wrapperSelector() {
          var data = window.PayPalCommerceGateway;
          var selector = data && data.button ? data.button.wrapper : '';

          return typeof selector === 'string' && selector !== ''
            ? selector
            : '#ppc-button-ppcp-gateway';
        }

        function findContainer() {
          var btn = document.querySelector(wrapperSelector());
          if (!btn) return null;

          // .ppc-button-wrapper also holds Pay Later messaging and the funding-source
          // buttons, so guarding it covers every clickable the SDK renders.
          return btn.closest('.ppc-button-wrapper') || btn;
        }

        function sync() {
          var container = findContainer();
          if (!container) return;

          if (!container.dataset.ncsGuarded) {
            container.dataset.ncsGuarded = '1';

            // Descendants have pointer-events: none while blocked, so the click lands
            // here — say why instead of doing nothing.
            container.addEventListener('click', function (event) {
              if (self.termsAccepted) return;

              event.preventDefault();
              event.stopPropagation();
              self.setTermsInvalid(true);

              var termsCheckbox = self.$el.querySelector('#terms');
              if (termsCheckbox && typeof termsCheckbox.scrollIntoView === 'function') {
                termsCheckbox.scrollIntoView({ block: 'center', behavior: 'smooth' });
              }
            });
          }

          self._ppcpGuardEl = container;
          self.updatePaypalGuard();
        }

        sync();

        if (typeof jQuery !== 'undefined') {
          // updated_checkout replaces the review-order fragment this button sits in, so
          // the guarded node is gone and a fresh one needs picking up.
          jQuery(document.body).on('updated_checkout', sync);

          // The SDK renders asynchronously and re-renders on its own schedule, so take
          // the plugin's own "buttons exist / buttons changed" signals rather than
          // guessing at a delay.
          jQuery(document).on(
            'ppcp-smart-buttons-init ppcp-paypal-loaded ppcp-enabled ppcp-shown ppcp_buttons_enabled_changed',
            sync,
          );
        }

        // Last line of defence for a button that appears without any of the above (a
        // funding-source re-render, a gateway switch). Cheap: it only reads one selector
        // and returns unless an unguarded container showed up.
        if (typeof MutationObserver !== 'undefined') {
          var pending = false;
          new MutationObserver(function () {
            if (pending) return;
            pending = true;
            setTimeout(function () {
              pending = false;
              var container = findContainer();
              if (container && container !== self._ppcpGuardEl) sync();
            }, 100);
          }).observe(document.body, { childList: true, subtree: true });
        }
      },

      /**
       * Sync the PayPal button's disabled state with the current terms acceptance.
       */
      updatePaypalGuard() {
        var container = this._ppcpGuardEl;
        if (!container) return;

        var blocked = !this.termsAccepted;

        container.classList.toggle('ncs-ppcp-blocked', blocked);
        container.setAttribute('aria-disabled', blocked ? 'true' : 'false');
      },

      /**
       * Toggle the same "invalid field" treatment other checkout inputs use
       * (border-danger) on the terms checkbox, since it sits outside any
       * WooCommerce-rendered field group that would normally get it for free.
       */
      setTermsInvalid(isInvalid) {
        var termsCheckbox = this.$el.querySelector('#terms');
        if (!termsCheckbox) return;

        var wrapper = termsCheckbox.closest('.woocommerce-terms-and-conditions-wrapper') || termsCheckbox;
        // border-danger mirrors the other invalid fields' convention, but native (non
        // appearance:none) checkboxes largely ignore border-color visually in Chromium —
        // ring-danger is what actually shows up around the box itself.
        termsCheckbox.classList.toggle('border-danger', isInvalid);
        termsCheckbox.classList.toggle('ring-2', isInvalid);
        termsCheckbox.classList.toggle('ring-danger', isInvalid);
        termsCheckbox.classList.toggle('ring-offset-1', isInvalid);
        wrapper.classList.toggle('ncs-terms-invalid', isInvalid);
        termsCheckbox.setAttribute('aria-invalid', isInvalid ? 'true' : 'false');
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
