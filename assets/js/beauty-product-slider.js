/**
 * Beauty – Section "Sản phẩm Beauty": Swiper slider 1 hàng + lọc danh mục tại chỗ (không tải lại trang).
 *
 * - Tất cả sản phẩm được render sẵn trong HTML (PHP), mỗi slide có data-cats="slug-1 slug-2".
 * - Bấm pill -> JS giữ lại danh sách node gốc, gỡ hết slide rồi nạp lại các slide khớp danh mục,
 *   sau đó cập nhật Swiper (số slide, dots, nút prev/next) và về slide đầu.
 * - Không clone/render lại thẻ nên wishlist, thêm giỏ hàng (AJAX) vẫn hoạt động bình thường.
 * - Nếu Swiper không tải được (CDN lỗi): vẫn lọc bằng cách ẩn/hiện và vuốt ngang bằng CSS.
 */
(function () {
  'use strict';

  var FADE_MS = 180;
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function matches(slide, slug) {
    if (slug === 'all') return true;
    return (' ' + (slide.getAttribute('data-cats') || '') + ' ').indexOf(' ' + slug + ' ') > -1;
  }

  function initBlock(block) {
    var root = block.querySelector('[data-bv-pslider]');
    var bar = block.querySelector('[data-bv-pfilter]');
    if (!root) return;

    var swiperEl = root.querySelector('.bv-pslider-swiper');
    var wrapper = root.querySelector('.swiper-wrapper');
    var emptyMsg = root.querySelector('.bv-pslider-empty');
    if (!swiperEl || !wrapper) return;

    // Danh sách node gốc – nguồn dữ liệu duy nhất cho việc lọc.
    var allSlides = Array.prototype.slice.call(wrapper.children);
    var current = 'all';
    var token = 0;
    var swiper = null;

    /* ---------- Khởi tạo Swiper ---------- */
    if (typeof window.Swiper === 'function') {
      root.classList.add('is-swiper'); // phải có TRƯỚC khi Swiper đo kích thước
      try {
        swiper = new window.Swiper(swiperEl, {
          slidesPerView: 1.15,
          slidesPerGroup: 1,
          spaceBetween: 16,
          speed: 550,
          grabCursor: true,
          watchOverflow: true,        // đủ chỗ hiển thị thì tự ẩn nút + dots
          centerInsufficientSlides: true, // lọc ra < 4 sản phẩm thì căn giữa thay vì lệch trái
          keyboard: { enabled: true, onlyInViewport: true },
          navigation: {
            prevEl: root.querySelector('.bv-pslider-prev'),
            nextEl: root.querySelector('.bv-pslider-next')
          },
          pagination: {
            el: root.querySelector('.bv-pslider-dots'),
            clickable: true
          },
          a11y: {
            prevSlideMessage: 'Sản phẩm trước',
            nextSlideMessage: 'Sản phẩm tiếp theo',
            paginationBulletMessage: 'Đến trang {{index}}'
          },
          breakpoints: {
            480:  { slidesPerView: 2, slidesPerGroup: 2, spaceBetween: 16 },
            768:  { slidesPerView: 3, slidesPerGroup: 3, spaceBetween: 20 },
            1024: { slidesPerView: 4, slidesPerGroup: 4, spaceBetween: 22 } // 4 sản phẩm / slide
          }
        });
      } catch (err) {
        swiper = null;
        root.classList.remove('is-swiper');
        if (window.console) console.warn('[beauty-product-slider] Swiper lỗi, dùng chế độ dự phòng.', err);
      }
    }

    /* ---------- Áp dụng bộ lọc ---------- */
    function refreshSwiper() {
      swiper.update();
      swiper.slideTo(0, 0);
      if (swiper.pagination) { swiper.pagination.render(); swiper.pagination.update(); }
      if (swiper.navigation) swiper.navigation.update();
      swiper.updateSlidesClasses();
    }

    function render(list) {
      if (emptyMsg) emptyMsg.hidden = list.length > 0;

      if (swiper) {
        swiper.removeAllSlides();
        if (list.length) swiper.appendSlide(list);
        refreshSwiper();
      } else {
        // Chế độ dự phòng: ẩn/hiện
        allSlides.forEach(function (s) { s.hidden = list.indexOf(s) === -1; });
        wrapper.scrollLeft = 0;
      }
    }

    function applyFilter(slug) {
      var list = allSlides.filter(function (s) { return matches(s, slug); });
      var myToken = ++token; // chống lỗi khi bấm liên tục

      if (reduceMotion || !swiper) { render(list); return; }

      swiperEl.classList.add('is-filtering');
      window.setTimeout(function () {
        if (myToken !== token) return; // đã có lần bấm mới hơn
        render(list);
        // đợi layout ổn định rồi mới hiện lại
        window.requestAnimationFrame(function () {
          if (myToken !== token) return;
          swiper.update();
          swiperEl.classList.remove('is-filtering');
        });
      }, FADE_MS);
    }

    /* ---------- Click pill (button, KHÔNG phải <a href>) ---------- */
    if (bar) {
      bar.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('button[data-filter]') : null;
        if (!btn || !bar.contains(btn)) return;
        e.preventDefault();

        var slug = btn.getAttribute('data-filter') || 'all';
        if (slug === current) return;
        current = slug;

        Array.prototype.forEach.call(bar.querySelectorAll('button[data-filter]'), function (b) {
          var on = b === btn;
          b.classList.toggle('active', on);
          b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });

        applyFilter(slug);
      });
    }
  }

  function boot() {
    Array.prototype.forEach.call(document.querySelectorAll('[data-bv-products]'), initBlock);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
