/* [FIX 3] NÚT GIỎ HÀNG - thêm vào giỏ bằng AJAX cho giỏ hàng tích hợp của theme.
 * Hỗ trợ 2 kiểu nút:
 *   1) <a class="ajax_add_to_cart" data-product_id="ID" data-quantity="1" href="?add-to-cart=ID">
 *   2) <form class="bv-addcart"> (trang chi tiết sản phẩm, nút "Thêm vào giỏ")
 * Nếu AJAX lỗi, tự quay về cách cũ (tải trang) nên khách luôn thêm được hàng.
 */
(function () {
  'use strict';
  var cfg = window.beautyCart;
  if (!cfg || !cfg.ajaxUrl || !window.fetch || !window.URLSearchParams) return;

  function setCount(n) {
    document.querySelectorAll('.cart-count').forEach(function (el) {
      el.textContent = n;
      el.classList.toggle('is-zero', n < 1);
    });
  }

  function toast(message, data) {
    var old = document.querySelector('.bv-toast[data-js]');
    if (old) old.remove();
    var box = document.createElement('div');
    box.className = 'bv-toast';
    box.setAttribute('data-js', '1');
    box.setAttribute('role', 'status');
    var text = document.createElement('span');
    text.textContent = message;
    box.appendChild(text);
    if (data && data.cart_url) {
      var a1 = document.createElement('a');
      a1.href = data.cart_url;
      a1.textContent = 'Xem giỏ hàng';
      box.appendChild(document.createTextNode(' '));
      box.appendChild(a1);
      if (data.checkout_url) {
        var a2 = document.createElement('a');
        a2.href = data.checkout_url;
        a2.textContent = 'Thanh toán';
        box.appendChild(document.createTextNode(' '));
        box.appendChild(a2);
      }
    }
    document.body.appendChild(box);
    setTimeout(function () { if (box.parentNode) box.remove(); }, 6000);
  }

  /** Gửi yêu cầu thêm giỏ. onFail() được gọi khi lỗi mạng/máy chủ để dùng cách dự phòng. */
  function addToCart(productId, quantity, button, onFail) {
    var label = button ? button.textContent : '';
    if (button) { button.classList.add('loading'); button.setAttribute('aria-busy', 'true'); }

    var body = new URLSearchParams();
    body.set('action', 'beauty_add_to_cart');
    body.set('product_id', productId);
    body.set('quantity', quantity || 1);

    fetch(cfg.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString()
    })
      .then(function (r) { if (!r.ok) throw new Error('http ' + r.status); return r.json(); })
      .then(function (res) {
        if (button) { button.classList.remove('loading'); button.removeAttribute('aria-busy'); }
        if (res && res.success) {
          setCount(res.data.count);
          toast(res.data.message, res.data);
          if (button) {
            button.classList.add('added');
            button.textContent = 'Đã thêm ✓';
            setTimeout(function () { button.classList.remove('added'); button.textContent = label; }, 2000);
          }
        } else {
          toast((res && res.data && res.data.message) || 'Chưa thêm được sản phẩm. Vui lòng thử lại.');
        }
      })
      .catch(function () {
        if (button) { button.classList.remove('loading'); button.removeAttribute('aria-busy'); }
        if (typeof onFail === 'function') onFail();
      });
  }

  // Kiểu 1: thẻ <a class="ajax_add_to_cart">
  document.addEventListener('click', function (e) {
    var a = e.target.closest ? e.target.closest('a.ajax_add_to_cart[data-product_id]') : null;
    if (!a) return;
    if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    e.preventDefault();
    if (a.classList.contains('loading')) return;
    addToCart(a.getAttribute('data-product_id'), a.getAttribute('data-quantity') || 1, a, function () {
      window.location.href = a.href; // dự phòng: ?add-to-cart=ID
    });
  });

  // Kiểu 2: form ở trang chi tiết (chỉ chặn nút "Thêm vào giỏ", để "Mua ngay" gửi form như cũ)
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form || !form.classList || !form.classList.contains('bv-addcart')) return;
    var submitter = e.submitter || null;
    var op = submitter && submitter.value ? submitter.value : (form.querySelector('button[name="op"]') ? '' : 'add');
    if (op !== 'add') return; // buynow hoặc không xác định được -> gửi form bình thường
    var idField = form.querySelector('[name="product_id"]');
    var qtyField = form.querySelector('[name="qty"]');
    if (!idField) return;
    e.preventDefault();
    var btn = submitter || form.querySelector('button[type="submit"]');
    if (btn && btn.classList.contains('loading')) return;
    addToCart(idField.value, qtyField ? qtyField.value : 1, btn, function () {
      var hidden = document.createElement('input');
      hidden.type = 'hidden'; hidden.name = 'op'; hidden.value = 'add';
      form.appendChild(hidden);
      form.submit(); // dự phòng: gửi form như cũ
    });
  });
})();
