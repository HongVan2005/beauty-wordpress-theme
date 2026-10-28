/* [FIX 3] Album ảnh + lightbox (không phụ thuộc thư viện) */
(function () {
  'use strict';
  var cards = document.querySelectorAll('.bva-card');
  if (!cards.length) return;
  var box, stage, img, cap, titleEl, countEl, thumbs, closeBtn, prevBtn, nextBtn;
  var list = [], idx = 0, lastFocus = null, startX = null;

  function build() {
    if (box) return;
    box = document.createElement('div');
    box.className = 'bva-lb'; box.hidden = true;
    box.setAttribute('role', 'dialog'); box.setAttribute('aria-modal', 'true'); box.setAttribute('aria-label', 'Xem album ảnh');
    box.innerHTML =
      '<div class="bva-lb-head"><div class="bva-lb-title"><span class="bva-lb-name"></span><span class="bva-lb-count" aria-live="polite"></span></div>' +
      '<button type="button" class="bva-lb-close" aria-label="Đóng">×</button></div>' +
      '<div class="bva-lb-stage"><button type="button" class="bva-lb-prev" aria-label="Ảnh trước">‹</button><img alt=""><button type="button" class="bva-lb-next" aria-label="Ảnh sau">›</button></div>' +
      '<div class="bva-lb-cap"></div><div class="bva-lb-thumbs"></div>';
    document.body.appendChild(box);
    stage = box.querySelector('.bva-lb-stage'); img = stage.querySelector('img');
    cap = box.querySelector('.bva-lb-cap'); titleEl = box.querySelector('.bva-lb-name'); countEl = box.querySelector('.bva-lb-count');
    thumbs = box.querySelector('.bva-lb-thumbs');
    closeBtn = box.querySelector('.bva-lb-close'); prevBtn = box.querySelector('.bva-lb-prev'); nextBtn = box.querySelector('.bva-lb-next');
    closeBtn.addEventListener('click', close);
    prevBtn.addEventListener('click', function () { step(-1); });
    nextBtn.addEventListener('click', function () { step(1); });
    box.addEventListener('click', function (e) { if (e.target === box || e.target === stage) close(); });
    box.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
    box.addEventListener('touchend', function (e) {
      if (startX === null) return;
      var dx = e.changedTouches[0].clientX - startX; startX = null;
      if (Math.abs(dx) > 50) step(dx < 0 ? 1 : -1);
    });
    document.addEventListener('keydown', function (e) {
      if (box.hidden) return;
      if (e.key === 'Escape') close();
      else if (e.key === 'ArrowLeft') step(-1);
      else if (e.key === 'ArrowRight') step(1);
      else if (e.key === 'Tab') {
        var f = box.querySelectorAll('button:not([hidden])'); if (!f.length) return;
        var first = f[0], last = f[f.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });
  }
  function show(i) {
    idx = (i + list.length) % list.length;
    var it = list[idx];
    img.src = it.full; img.alt = it.alt || it.caption || '';
    cap.textContent = it.caption || '';
    countEl.textContent = '  ' + (idx + 1) + ' / ' + list.length;
    Array.prototype.forEach.call(thumbs.children, function (b, k) { b.setAttribute('aria-current', k === idx ? 'true' : 'false'); });
    var nx = list[(idx + 1) % list.length]; if (nx) { var p = new Image(); p.src = nx.full; }
  }
  function step(d) { if (list.length > 1) show(idx + d); }
  function open(card) {
    var data; try { data = JSON.parse(card.getAttribute('data-images') || '[]'); } catch (e) { data = []; }
    if (!data.length) { window.location.href = card.href; return; }
    build(); list = data; lastFocus = document.activeElement;
    titleEl.textContent = card.getAttribute('data-title') || 'Album';
    thumbs.innerHTML = '';
    list.forEach(function (it, k) {
      var b = document.createElement('button'); b.type = 'button'; b.setAttribute('aria-label', 'Xem ảnh ' + (k + 1));
      var t = document.createElement('img'); t.src = it.thumb || it.full; t.alt = ''; b.appendChild(t);
      b.addEventListener('click', function () { show(k); });
      thumbs.appendChild(b);
    });
    box.classList.toggle('is-single', list.length < 2);
    box.hidden = false; document.body.classList.add('bva-lock');
    show(0); closeBtn.focus();
  }
  function close() {
    box.hidden = true; document.body.classList.remove('bva-lock'); img.removeAttribute('src');
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }
  Array.prototype.forEach.call(cards, function (c) {
    c.addEventListener('click', function (e) { e.preventDefault(); open(c); });
  });
})();
