/**
 * Basket Hold expiry sound (cart + checkout).
 *
 * Standalone (not Vite-bundled) so it still runs after reload when the countdown
 * script is no longer enqueued because no holds remain.
 */
(function () {
  'use strict';

  var audioCtx = null;

  function getAudioContext() {
    if (!audioCtx) {
      audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    return audioCtx;
  }

  function playBasketHoldExpiredSound() {
    try {
      var ctx = getAudioContext();
      var notes = [
        [659, 0, 0.35],
        [523, 0.14, 0.35],
        [392, 0.28, 0.45],
      ];

      var play = function () {
        notes.forEach(function (note) {
          var osc = ctx.createOscillator();
          var gain = ctx.createGain();
          osc.connect(gain);
          gain.connect(ctx.destination);
          osc.frequency.value = note[0];
          osc.type = 'sine';
          gain.gain.setValueAtTime(0.28, ctx.currentTime + note[1]);
          gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + note[1] + note[2]);
          osc.start(ctx.currentTime + note[1]);
          osc.stop(ctx.currentTime + note[1] + note[2]);
        });
      };

      if (ctx.state === 'suspended') {
        ctx.resume().then(play);
      } else {
        play();
      }
    } catch (e) {
      // Web Audio unavailable — fail silently
    }
  }

  function consumePending() {
    try {
      if (sessionStorage.getItem('nera_basket_hold_sound') !== '1') {
        return;
      }
      sessionStorage.removeItem('nera_basket_hold_sound');
      playBasketHoldExpiredSound();
    } catch (e) {
      // ignore
    }
  }

  window.neraBasketHoldSound = {
    play: playBasketHoldExpiredSound,
  };

  document.addEventListener('nera:basket-hold:expired', playBasketHoldExpiredSound);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', consumePending);
  } else {
    consumePending();
  }
})();
