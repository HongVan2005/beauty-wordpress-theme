/* Trang Giới thiệu — hiệu ứng fade-up khi cuộn (JS nhỏ, không thư viện) */
(function () {
  'use strict';
  var items = document.querySelectorAll('.ba-fade');
  if (!items.length) return;

  /* Trình duyệt cũ hoặc người dùng tắt chuyển động: hiện luôn, không ẩn nội dung */
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!('IntersectionObserver' in window) || reduce) {
    for (var i = 0; i < items.length; i++) items[i].classList.add('is-in');
    return;
  }

  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) {
        e.target.classList.add('is-in');
        io.unobserve(e.target); /* chỉ chạy 1 lần */
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });

  for (var j = 0; j < items.length; j++) io.observe(items[j]);
})();
