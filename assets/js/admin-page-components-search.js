(function () {
    'use strict';

    var POPUP_SELECTOR  = '.acf-fc-popup';
    var ITEM_SELECTOR   = '[data-layout]';
    var INPUT_ID        = 'nera-fc-search';

    /**
     * Inject a search <input> at the top of the ACF Flexible Content popup,
     * focus it, and wire up live filtering of layout items by label text.
     *
     * @param {HTMLElement} popup
     */
    function attachSearch(popup) {
        // Guard: already injected
        if (popup.querySelector('#' + INPUT_ID)) {
            return;
        }

        var wrap = document.createElement('div');
        wrap.className = 'nera-fc-search-wrap';
        wrap.style.cssText = [
            'padding:8px 10px',
            'border-bottom:1px solid #dcdcde',
            'background:#f6f7f7',
            'box-sizing:border-box',
        ].join(';');

        var input = document.createElement('input');
        input.type         = 'search';
        input.id           = INPUT_ID;
        input.placeholder  = 'Search components…';
        input.autocomplete = 'off';

        // Minimal inline styles — keeps this fully self-contained without a
        // stylesheet dependency.  Static-value rule does not apply here because
        // this file is outside the Tailwind/Vite build pipeline.
        input.style.cssText = [
            'display:block',
            'width:100%',
            'padding:6px 10px',
            'font-size:13px',
            'border:1px solid #8c8f94',
            'border-radius:3px',
            'box-sizing:border-box',
        ].join(';');

        wrap.appendChild(input);
        popup.insertBefore(wrap, popup.firstChild);

        // Stop ACF's outside-click handler from closing the popup when
        // interacting with the search field.
        ['mousedown', 'click', 'focusin', 'touchstart'].forEach(function (evt) {
            wrap.addEventListener(evt, function (e) { e.stopPropagation(); });
        });

        // Auto-focus (small delay lets ACF finish its own show animation)
        setTimeout(function () { input.focus(); }, 50);

        // Live filter
        input.addEventListener('input', function () {
            filterItems(popup, input.value);
        });

        // Keyboard handling
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                if (input.value !== '') {
                    e.stopPropagation();
                    input.value = '';
                    filterItems(popup, '');
                }
                // If already empty, do nothing special — ACF's own handler fires
                return;
            }
            // Stop typing keys from being interpreted by ACF popup handlers
            // (item navigation, etc.)
            e.stopPropagation();
        });
    }

    /**
     * Show/hide layout items whose label text matches the query.
     *
     * @param {HTMLElement} popup
     * @param {string}      query
     */
    function filterItems(popup, query) {
        var term  = query.trim().toLowerCase();
        var items = popup.querySelectorAll(ITEM_SELECTOR);

        items.forEach(function (item) {
            var label  = (item.textContent || '').toLowerCase();
            var target = item.closest('li') || item;
            target.style.display = (!term || label.indexOf(term) !== -1) ? '' : 'none';
        });
    }

    /**
     * Reset visibility of all items and remove the injected input when the
     * popup is hidden, so the next open gets a clean state.
     *
     * @param {HTMLElement} popup
     */
    function teardownSearch(popup) {
        var items = popup.querySelectorAll(ITEM_SELECTOR);
        items.forEach(function (item) {
            var target = item.closest('li') || item;
            target.style.display = '';
        });

        var input = popup.querySelector('#' + INPUT_ID);
        if (input) {
            var wrap = input.parentNode;
            // Remove the wrapping div if it's our injected wrapper, else just the input
            if (wrap && wrap.classList && wrap.classList.contains('nera-fc-search-wrap')) {
                wrap.parentNode.removeChild(wrap);
            } else {
                input.parentNode.removeChild(input);
            }
        }
    }

    /**
     * MutationObserver watching for ACF popup open / close transitions.
     */
    function init() {
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                // Attribute changes on existing popup elements
                if (mutation.type === 'attributes') {
                    var target = mutation.target;
                    if (!target.matches(POPUP_SELECTOR)) { return; }

                    var visible = (target.style.display !== 'none') &&
                                  !target.classList.contains('hidden');
                    if (visible) {
                        attachSearch(target);
                    } else {
                        teardownSearch(target);
                    }
                }

                // Newly added popup nodes
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function (node) {
                        if (node.nodeType !== 1) { return; }
                        if (node.matches && node.matches(POPUP_SELECTOR)) {
                            attachSearch(node);
                        }
                        // Also search descendants
                        var nested = node.querySelectorAll
                            ? node.querySelectorAll(POPUP_SELECTOR)
                            : [];
                        nested.forEach(function (el) { attachSearch(el); });
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList:  true,
            subtree:    true,
            attributes: true,
            attributeFilter: ['style', 'class'],
        });
    }

    // Auto-init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Public API (allows manual re-init if needed)
    window.NeraFCSearch = { init: init };
})();
