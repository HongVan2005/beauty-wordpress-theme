/* [FIX 6] Lọc album hình ảnh theo danh mục, không phụ thuộc thư viện ngoài. */
(function () {
  'use strict';
  document.querySelectorAll('.beauty-image-categories').forEach(function (section) {
    var buttons = Array.prototype.slice.call(section.querySelectorAll('.bic-filter'));
    var cards = Array.prototype.slice.call(section.querySelectorAll('.bic-card'));
    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        var filter = button.getAttribute('data-filter') || 'all';
        buttons.forEach(function (other) {
          var active = other === button;
          other.classList.toggle('is-active', active);
          other.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        cards.forEach(function (card) {
          var show = filter === 'all' || card.getAttribute('data-filter-category') === filter;
          card.hidden = !show;
        });
      });
    });
  });
})();
