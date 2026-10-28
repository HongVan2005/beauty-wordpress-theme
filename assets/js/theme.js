(function () {
  'use strict';
  var init = function () {
    if (window.__beautyInit) return; window.__beautyInit = true;
    var reduceMotion = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

    /* ---------- Menu mobile + submenu ---------- */
    var btn = document.querySelector('.menu-toggle'), nav = document.querySelector('.main-nav');
    if (btn && nav) {
      btn.addEventListener('click', function () {
        var open = nav.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    }
    document.querySelectorAll('.submenu-toggle').forEach(function (toggle) {
      toggle.addEventListener('click', function (e) {
        e.preventDefault();
        var item = toggle.closest('.beauty-dropdown');
        if (!item) return;
        var open = item.classList.toggle('is-submenu-open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    });
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
      a.addEventListener('click', function () { if (nav) nav.classList.remove('is-open'); });
    });

    /* ---------- Tìm kiếm ---------- */
    var searchBtn = document.querySelector('.header-search-toggle'), panel = document.getElementById('site-search-panel');
    if (searchBtn && panel) {
      var setSearch = function (open) {
        panel.hidden = !open;
        searchBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) { var input = panel.querySelector('input[type="search"]'); if (input) input.focus(); }
      };
      searchBtn.addEventListener('click', function () { setSearch(panel.hidden); });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.hidden) { setSearch(false); searchBtn.focus(); }
      });
    }

    /* ---------- Số lượng giỏ hàng (giỏ tích hợp, đọc cookie để đúng cả khi trang bị cache) ---------- */
    var builtinCart = document.querySelector('.header-cart[data-builtin]');
    if (builtinCart) {
      var cm = document.cookie.match(/(?:^|; )beauty_cart=([^;]*)/), total = 0;
      if (cm) {
        try { decodeURIComponent(cm[1]).split(',').forEach(function (p) { var q = parseInt(p.split(':')[1], 10); if (q > 0) total += q; }); } catch (err) { total = 0; }
      }
      var badge = builtinCart.querySelector('.cart-count'); if (badge) { badge.textContent = total; badge.classList.toggle('is-zero', total < 1); }
    }

    /* ---------- Hero slider ---------- */
    var hero = document.querySelector('.hero-slider');
    if (hero) {
      var slides = Array.prototype.slice.call(hero.querySelectorAll('.hero-slide'));
      var dots = Array.prototype.slice.call(hero.querySelectorAll('.slider-dots button'));
      var prev = hero.querySelector('.slider-arrow.prev'), next = hero.querySelector('.slider-arrow.next');
      var delay = Math.min(8000, Math.max(3000, parseInt(hero.getAttribute('data-autoplay'), 10) || 5000));
      var current = 0, timer = null;
      var show = function (i) {
        current = (i + slides.length) % slides.length;
        slides.forEach(function (s, n) {
          s.classList.toggle('active', n === current);
          s.setAttribute('aria-hidden', n === current ? 'false' : 'true');
        });
        dots.forEach(function (d, n) {
          d.classList.toggle('active', n === current);
          if (n === current) d.setAttribute('aria-current', 'true'); else d.removeAttribute('aria-current');
        });
      };
      var stop = function () { if (timer) { clearInterval(timer); timer = null; } };
      var play = function () {
        stop();
        if (slides.length < 2 || document.hidden) return;
        timer = setInterval(function () { show(current + 1); }, delay);
      };
      if (prev) prev.addEventListener('click', function () { show(current - 1); play(); });
      if (next) next.addEventListener('click', function () { show(current + 1); play(); });
      dots.forEach(function (d, i) { d.addEventListener('click', function () { show(i); play(); }); });
      document.addEventListener('visibilitychange', play);
      var startX = null;
      hero.addEventListener('touchstart', function (e) { startX = e.changedTouches[0].clientX; }, { passive: true });
      hero.addEventListener('touchend', function (e) {
        if (startX === null) return;
        var dx = e.changedTouches[0].clientX - startX; startX = null;
        if (Math.abs(dx) > 50) { show(current + (dx < 0 ? 1 : -1)); play(); }
      }, { passive: true });
      show(0); play();
    }

    /* ---------- Album ảnh: lọc + lightbox ---------- */
    var grid = document.querySelector('.beauty-album-grid');
    if (grid) {
      var cards = Array.prototype.slice.call(grid.querySelectorAll('.beauty-album-card'));
      var filterBtns = Array.prototype.slice.call(document.querySelectorAll('.bv-albums [data-filter]'));
      var titleEl = document.getElementById('album-title');
      var applyFilter = function (f, label, push) {
        filterBtns.forEach(function (x) {
          var on = x.getAttribute('data-filter') === f; x.classList.toggle('active', on);
          if (on) x.setAttribute('aria-current', 'true'); else x.removeAttribute('aria-current');
        });
        cards.forEach(function (c) { c.hidden = !(f === 'all' || c.getAttribute('data-album') === f); });
        if (titleEl) titleEl.textContent = label || 'Tất cả ảnh';
        if (push && window.history && history.replaceState) {
          var u = new URL(window.location.href);
          if (f === 'all') u.searchParams.delete('album'); else u.searchParams.set('album', f);
          history.replaceState(null, '', u.pathname + u.search + '#album-grid');
        }
      };
      filterBtns.forEach(function (b) {
        b.addEventListener('click', function (e) {
          e.preventDefault();
          applyFilter(b.getAttribute('data-filter'), b.getAttribute('data-label'), true);
          var target = document.getElementById('album-grid');
          if (target && target.scrollIntoView) target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
        });
      });

      var box = null, img, cap, counter, items = [], idx = 0, lastFocus = null, touchX = null;
      var build = function () {
        box = document.createElement('div');
        box.className = 'beauty-lightbox'; box.hidden = true;
        box.setAttribute('role', 'dialog'); box.setAttribute('aria-modal', 'true'); box.setAttribute('aria-label', 'Xem ảnh lớn');
        box.innerHTML = '<button type="button" class="lb-close" aria-label="Đóng">×</button>' +
          '<button type="button" class="lb-prev" aria-label="Ảnh trước">‹</button>' +
          '<button type="button" class="lb-next" aria-label="Ảnh tiếp theo">›</button>' +
          '<figure class="beauty-lightbox-figure"><img alt=""><figcaption><span class="lb-caption"></span><span class="beauty-lightbox-counter" aria-live="polite"></span></figcaption></figure>';
        document.body.appendChild(box);
        img = box.querySelector('img'); cap = box.querySelector('.lb-caption'); counter = box.querySelector('.beauty-lightbox-counter');
        box.querySelector('.lb-close').addEventListener('click', close);
        box.querySelector('.lb-prev').addEventListener('click', function () { step(-1); });
        box.querySelector('.lb-next').addEventListener('click', function () { step(1); });
        box.addEventListener('click', function (e) { if (e.target === box) close(); });
        box.addEventListener('touchstart', function (e) { touchX = e.changedTouches[0].clientX; }, { passive: true });
        box.addEventListener('touchend', function (e) {
          if (touchX === null) return;
          var dx = e.changedTouches[0].clientX - touchX; touchX = null;
          if (Math.abs(dx) > 50) step(dx < 0 ? 1 : -1);
        }, { passive: true });
      };
      var render = function () {
        var a = items[idx], thumb = a.querySelector('img');
        img.src = a.getAttribute('href');
        img.alt = thumb ? thumb.getAttribute('alt') || '' : '';
        cap.textContent = a.getAttribute('data-caption') || '';
        counter.textContent = (idx + 1) + '/' + items.length;
      };
      var step = function (d) { idx = (idx + d + items.length) % items.length; render(); };
      var open = function (i) {
        if (!box) build();
        idx = i; render();
        lastFocus = document.activeElement;
        box.hidden = false; document.documentElement.classList.add('lb-open');
        box.querySelector('.lb-close').focus();
      };
      var close = function () {
        if (!box || box.hidden) return;
        box.hidden = true; document.documentElement.classList.remove('lb-open'); img.removeAttribute('src');
        if (lastFocus && lastFocus.focus) lastFocus.focus();
      };
      grid.addEventListener('click', function (e) {
        var a = e.target.closest('.beauty-album-image');
        if (!a) return;
        e.preventDefault();
        items = Array.prototype.slice.call(grid.querySelectorAll('.beauty-album-card:not([hidden]) .beauty-album-image'));
        open(items.indexOf(a));
      });
      document.addEventListener('keydown', function (e) {
        if (!box || box.hidden) return;
        if (e.key === 'Escape') { close(); }
        else if (e.key === 'ArrowLeft') { step(-1); }
        else if (e.key === 'ArrowRight') { step(1); }
        else if (e.key === 'Tab') {
          var f = box.querySelectorAll('button'); if (!f.length) return;
          var first = f[0], last = f[f.length - 1];
          if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
          else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
      });
    }
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
