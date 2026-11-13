/**
 * Postal entries (main menu): member search, "Log postal entry" and the per-drop table.
 * Every call is an admin-ajax request with a nonce (see inc/postal-entry/admin.php).
 */
(function ($) {
  'use strict';

  var cfg = window.neraPostal || {};
  var i18n = cfg.i18n || {};
  var $form = $('#nera-postal-form');
  var $status = $('#nera-postal-status');
  var $rows = $('#nera-postal-rows');
  var $drop = $('#nera-postal-drop');
  var $hits = $('#nera-postal-hits');
  var timer = null;

  function call(action, data) {
    return $.post(cfg.ajax, $.extend({ action: action, nonce: cfg.nonce }, data));
  }

  function say(text, cls) {
    $status.removeClass('is-working is-ok is-err').addClass(cls || '').text(text || '');
  }

  function loadRows() {
    var id = $drop.val();
    if (!id) {
      $rows.html('');
      return;
    }
    call('nera_postal_rows', { product_id: id }).done(function (res) {
      if (res && res.success) {
        $rows.html(res.data.rows);
      }
    });
  }

  function clearMember() {
    $('#nera-postal-user').val('');
    $('#nera-postal-chosen').prop('hidden', true);
    $('#nera-postal-email').prop('readonly', false).val('');
  }

  function chooseMember(u) {
    $('#nera-postal-user').val(u.id);
    $('#nera-postal-chosen-text').text(u.name + ' <' + u.email + '>');
    $('#nera-postal-chosen').prop('hidden', false);
    $('#nera-postal-email').val(u.email).prop('readonly', true);
    $('#nera-postal-name').val(u.name);
    $('#nera-postal-address').val(u.address_1);
    $('#nera-postal-city').val(u.city);
    $('#nera-postal-postcode').val(u.postcode);
    $hits.prop('hidden', true).empty();
    $('#nera-postal-search').val('');
  }

  $('#nera-postal-search').on('input', function () {
    var term = $.trim($(this).val());
    window.clearTimeout(timer);
    if (term.length < 2) {
      $hits.prop('hidden', true).empty();
      return;
    }
    timer = window.setTimeout(function () {
      $hits.prop('hidden', false).html('<li>' + i18n.searching + '</li>');
      call('nera_postal_user_search', { term: term }).done(function (res) {
        var users = (res && res.success && res.data.users) || [];
        $hits.empty();
        if (!users.length) {
          $hits.append($('<li></li>').text(i18n.none));
          return;
        }
        $.each(users, function (_, u) {
          $('<li></li>')
            .text(u.name + ' · ' + u.email + ' · ' + u.login)
            .on('click', function () {
              chooseMember(u);
            })
            .appendTo($hits);
        });
      });
    }, 250);
  });

  $('#nera-postal-clear').on('click', function (e) {
    e.preventDefault();
    clearMember();
  });

  $drop.on('change', loadRows);

  $form.on('submit', function (e) {
    e.preventDefault();
    if (!$drop.val()) {
      say(i18n.chooseDrop, 'is-err');
      return;
    }
    var $btn = $('#nera-postal-submit').prop('disabled', true);
    say(i18n.working, 'is-working');
    call('nera_postal_log', $form.serializeArray().reduce(function (o, f) {
      o[f.name] = f.value;
      return o;
    }, {}))
      .done(function (res) {
        var d = (res && res.data) || {};
        if (d.rows) {
          $rows.html(d.rows);
        }
        if (res && res.success) {
          say(d.message, d.status === 'entered' ? 'is-ok' : 'is-info');
          $form.find('input[type=text],input[type=email]').not('#nera-postal-search').val('');
          $('#nera-postal-quantity').val(1);
          clearMember();
        } else {
          say(d.message || i18n.failed, 'is-err');
        }
      })
      .fail(function (xhr) {
        var msg = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message;
        say(msg || i18n.failed, 'is-err');
      })
      .always(function () {
        $btn.prop('disabled', false);
      });
  });

  $rows.on('click', '.nera-postal-action', function () {
    var $b = $(this).prop('disabled', true);
    say(i18n.working, 'is-working');
    call('nera_postal_row_action', { row: $b.data('row'), do: $b.data('do') })
      .done(function (res) {
        var d = (res && res.data) || {};
        say(d.message || '', res && res.success ? 'is-ok' : 'is-err');
        if (d.rows) {
          $rows.html(d.rows);
        }
      })
      .fail(function () {
        say(i18n.failed, 'is-err');
        $b.prop('disabled', false);
      });
  });
})(jQuery);
