(function () {
  var audioCtx = null;

  function getAudioContext() {
    if (!audioCtx) {
      audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    return audioCtx;
  }

  function playAddToCartSound() {
    try {
      var ctx = getAudioContext();

      // Resume context if suspended (browser autoplay policy)
      var play = function () {
        [[523, 0], [659, 0.15]].forEach(function (note) {
          var freq = note[0];
          var delay = note[1];

          var osc = ctx.createOscillator();
          var gain = ctx.createGain();

          osc.connect(gain);
          gain.connect(ctx.destination);

          osc.frequency.value = freq;
          osc.type = 'sine';

          gain.gain.setValueAtTime(0.3, ctx.currentTime + delay);
          gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + delay + 0.4);

          osc.start(ctx.currentTime + delay);
          osc.stop(ctx.currentTime + delay + 0.4);
        });
      };

      if (ctx.state === 'suspended') {
        ctx.resume().then(play);
      } else {
        play();
      }
    } catch (e) {
      // Web Audio API not supported — fail silently
    }
  }

  document.addEventListener('nera:cart:updated', playAddToCartSound);
})();
