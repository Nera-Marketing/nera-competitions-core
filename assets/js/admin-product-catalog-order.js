/**
 * Drag-to-reorder products on the Products list.
 * On drop: table overlay + spinner → AJAX save → reload into Catalog order.
 * On failure: revert rows, remove overlay, show error.
 */
(function ($) {
  'use strict';

  function reloadIntoCatalogOrder() {
    var url = new URL(window.location.href);
    url.searchParams.set('post_type', 'product');
    url.searchParams.set('orderby', 'menu_order');
    url.searchParams.set('order', 'asc');
    window.location.href = url.toString();
  }

  $(function () {
    var $list = $('#the-list');
    if (!$list.length || typeof neraCatalogOrder === 'undefined') {
      return;
    }

    var $table = $list.closest('table.wp-list-table');
    if (!$table.length) {
      $table = $list.parent();
    }

    var $wrap = $table.parent();
    if (!$wrap.hasClass('nera-catalog-order-table-wrap')) {
      $table.wrap('<div class="nera-catalog-order-table-wrap"></div>');
      $wrap = $table.parent();
    }

    $list.addClass('nera-catalog-sortable');

    var snapshot = [];
    var $overlay = null;
    var $error = null;

    function ensureOverlay() {
      if ($overlay && $overlay.length) {
        return $overlay;
      }
      $overlay = $(
        '<div class="nera-catalog-order-overlay" role="status" aria-live="polite">' +
          '<span class="spinner is-active"></span>' +
          '<span class="nera-catalog-order-overlay__label"></span>' +
          '</div>'
      );
      $overlay.find('.nera-catalog-order-overlay__label').text(neraCatalogOrder.i18n.saving);
      $wrap.append($overlay);
      return $overlay;
    }

    function showOverlay() {
      var $el = ensureOverlay();
      $el.find('.nera-catalog-order-overlay__label').text(neraCatalogOrder.i18n.saving);
      $wrap.addClass('nera-catalog-order-busy');
      $el.show();
    }

    function hideOverlay() {
      if ($overlay) {
        $overlay.hide();
      }
      $wrap.removeClass('nera-catalog-order-busy');
    }

    function showError(message) {
      if ($error) {
        $error.remove();
      }
      $error = $(
        '<div class="notice notice-error inline nera-catalog-order-error"><p></p></div>'
      );
      $error.find('p').text(message || neraCatalogOrder.i18n.error);
      $wrap.before($error);
    }

    function clearError() {
      if ($error) {
        $error.remove();
        $error = null;
      }
    }

    function captureSnapshot() {
      snapshot = $list
        .children('tr')
        .map(function () {
          return {
            id: this.id,
            menuOrder: $(this).find('.column-menu_order').text(),
          };
        })
        .get();
    }

    function restoreSnapshot() {
      snapshot.forEach(function (row) {
        if (!row.id) {
          return;
        }
        var $tr = $list.children('#' + row.id);
        if (!$tr.length) {
          $tr = $('#' + row.id);
        }
        if ($tr.length) {
          $list.append($tr);
          $tr.find('.column-menu_order').text(row.menuOrder);
        }
      });
    }

    function failAndRevert() {
      hideOverlay();
      restoreSnapshot();
      $list.sortable('enable');
      showError(neraCatalogOrder.i18n.error);
    }

    $list.sortable({
      items: 'tr:not(.no-items)',
      cursor: 'move',
      axis: 'y',
      opacity: 0.7,
      distance: 6,
      cancel:
        'a, button, input, select, textarea, label, .row-actions, .row-actions *, ' +
        '.inline-edit-row, .inline-edit-row *, .check-column, .check-column *, ' +
        '.column-featured, .column-featured *, .wc-featured, .tips, .woocommerce-help-tip',
      helper: function (e, ui) {
        ui.children().each(function () {
          $(this).width($(this).width());
        });
        return ui;
      },
      start: function () {
        captureSnapshot();
        clearError();
      },
      update: function () {
        var offset = parseInt(neraCatalogOrder.offset, 10) || 0;
        var order = [];

        $list.children('tr').each(function (index) {
          var id = this.id ? String(this.id).replace(/^post-/, '') : '';
          if (!id) {
            return;
          }
          var menuOrder = offset + index;
          order.push({ id: id, menu_order: menuOrder });
          $(this).find('.column-menu_order').text(String(menuOrder));
        });

        if (!order.length) {
          return;
        }

        showOverlay();
        $list.sortable('disable');

        $.post(ajaxurl, {
          action: 'nera_save_product_catalog_order',
          nonce: neraCatalogOrder.nonce,
          order: order,
        })
          .done(function (res) {
            if (res && res.success) {
              reloadIntoCatalogOrder();
              return;
            }
            failAndRevert();
          })
          .fail(function () {
            failAndRevert();
          });
      },
    });
  });
})(jQuery);
