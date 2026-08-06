/**
 * Giveaway view screen (wp-admin) — strip email addresses, leaving the username.
 *
 * See inc/admin-giveaway-ticket-privacy.php for why this runs on the client rather
 * than through a PHP filter.
 *
 * Three cell shapes are produced by Lottery for WooCommerce, handled by structure
 * rather than by any translated string:
 *
 *   Giveaway Tickets    <td class="… column-user_details">
 *                         <div class="tips" data-tip="Billing name: Tom Oakey">
 *                           oakey1993 (oakey1993@example.co.uk)
 *                       — name and address share one text node; the `data-tip`
 *                         attribute is untouched, so the tooltip is preserved.
 *
 *   Instant Win Prizes  <td class="… column-user_details">
 *                         <span class="lty-instant-winner-name">oakey1993</span>
 *                         <br>(oakey1993@example.co.uk)
 *                       — address is its own text node; the <br> that introduced it
 *                         has to go too, or the cell keeps an empty second line.
 *
 *   Giveaway Winners    <td data-title="User Name">oakey1993 (oakey1993@example.co.uk)</td>
 *                       — no class to target and `data-title` is translated, so this
 *                         one is matched positionally instead.
 *
 * This file is enqueued in <head>, so the guard class below is in place before the
 * tables are parsed and no address is briefly painted.
 */
(function () {
  'use strict';

  var GUARD_CLASS = 'nera-redacting-ticket-emails';

  /* Cells whose text may contain an address. `column-user_details` covers both the
     Giveaway Tickets and Instant Win Prizes tables; the winners table has no class. */
  var CELL_SELECTOR = [
    'td.column-user_details',
    '#lty-view-winner-log tbody td:nth-child(2)',
  ].join(', ');

  /* A parenthesised email plus any surrounding whitespace. The empty alternative also
     catches the bare "()" left behind on manual or guest tickets with no stored
     address. Requiring an "@" inside means legitimate parentheses in a display name
     — "oakey (the second)" — are left alone. */
  var EMAIL_IN_PARENS = /\s*\(\s*(?:[^()\s@]+@[^()\s@]+|)\s*\)\s*/g;

  /* Hide the affected cells for as long as this class is set. Added here, while the
     <head> is parsing; removed in reveal() below. If this script never loads, the
     class is never added and the plugin's own output stands — failing open. */
  document.documentElement.classList.add(GUARD_CLASS);

  function reveal() {
    document.documentElement.classList.remove(GUARD_CLASS);
  }

  function collectTextNodes(cell) {
    var walker = document.createTreeWalker(cell, NodeFilter.SHOW_TEXT, null, false);
    var nodes = [];
    var node;

    /* Collected up front: the loop below mutates and removes nodes, which would
       otherwise invalidate the walker mid-traversal. */
    while ((node = walker.nextNode())) {
      nodes.push(node);
    }

    return nodes;
  }

  function redactCell(cell) {
    collectTextNodes(cell).forEach(function (textNode) {
      var original = textNode.nodeValue;

      if (original.indexOf('(') === -1) {
        return;
      }

      var cleaned = original.replace(EMAIL_IN_PARENS, ' ').replace(/[ \t]+/g, ' ');

      if (cleaned === original) {
        return;
      }

      if (cleaned.trim() === '') {
        /* The node held nothing but the address (Instant Win Prizes shape). Drop it
           along with the <br> that put it on its own line, so the cell does not keep
           a trailing blank row. */
        var previous = textNode.previousSibling;

        textNode.parentNode.removeChild(textNode);

        if (previous && previous.nodeType === Node.ELEMENT_NODE && 'BR' === previous.tagName) {
          previous.parentNode.removeChild(previous);
        }

        return;
      }

      textNode.nodeValue = cleaned.trim();
    });
  }

  function redact() {
    try {
      var cells = document.querySelectorAll(CELL_SELECTOR);
      var i;

      for (i = 0; i < cells.length; i++) {
        redactCell(cells[i]);
      }
    } finally {
      /* Always reveal, even if a cell throws — a half-redacted table is a far better
         outcome than a screen of permanently invisible usernames. */
      reveal();
    }
  }

  /* Backstop: if DOMContentLoaded never arrives (a script error higher up the page
     can prevent it), un-hide the cells anyway rather than leaving them blank. */
  window.setTimeout(reveal, 4000);

  if ('loading' === document.readyState) {
    document.addEventListener('DOMContentLoaded', redact);
  } else {
    redact();
  }
})();
