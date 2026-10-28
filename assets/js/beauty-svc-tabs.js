/* [FIX 5] Tab Danh mục dịch vụ (có hỗ trợ bàn phím: ← → Home End) */
(function () {
  'use strict';
  document.querySelectorAll('.bsc-wrap').forEach(function (wrap) {
    var tabs = Array.prototype.slice.call(wrap.querySelectorAll('.bsc-tab'));
    var panels = Array.prototype.slice.call(wrap.querySelectorAll('.bsc-panel'));
    function activate(i, focus) {
      tabs.forEach(function (t, k) {
        var on = k === i;
        t.classList.toggle('is-active', on);
        t.setAttribute('aria-selected', on ? 'true' : 'false');
        t.tabIndex = on ? 0 : -1;
        if (panels[k]) panels[k].hidden = !on;
      });
      if (focus) tabs[i].focus();
    }
    tabs.forEach(function (t, i) {
      t.addEventListener('click', function () { activate(i, false); });
      t.addEventListener('keydown', function (e) {
        var n = tabs.length, k = null;
        if (e.key === 'ArrowRight') k = (i + 1) % n;
        else if (e.key === 'ArrowLeft') k = (i - 1 + n) % n;
        else if (e.key === 'Home') k = 0;
        else if (e.key === 'End') k = n - 1;
        if (k !== null) { e.preventDefault(); activate(k, true); }
      });
    });
  });
})();
