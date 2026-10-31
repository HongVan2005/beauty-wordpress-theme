/* Beauty Spa – trang Cửa hàng: tự gửi form sắp xếp và thanh kéo khoảng giá (cải tiến dần dần, không có JS vẫn dùng được). */
(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  function fmt(n) {
    try { return Number(n).toLocaleString('vi-VN') + ' đ'; } catch (e) { return n + ' đ'; }
  }

  ready(function () {
    /* 1) Sắp xếp: chọn xong là áp dụng */
    var sort = document.querySelector('.bv-shop .bvs-sort');
    if (sort) {
      var sel = sort.querySelector('select');
      var btn = sort.querySelector('.bvs-sort__submit');
      if (btn) btn.hidden = true;
      if (sel) sel.addEventListener('change', function () { sort.submit(); });
    }

    /* 2) Lọc giá: hai thanh kéo đồng bộ với hai ô số */
    var form = document.querySelector('.bv-shop .bvs-pricef');
    if (!form) return;
    var lo = parseInt(form.getAttribute('data-min'), 10) || 0;
    var hi = parseInt(form.getAttribute('data-max'), 10) || 0;
    var step = parseInt(form.getAttribute('data-step'), 10) || 10000;
    var box = form.querySelector('.bvs-range');
    var inMin = form.querySelector('input[name="gia_tu"]');
    var inMax = form.querySelector('input[name="gia_den"]');
    if (!box || !inMin || !inMax || hi <= lo) return;

    function clamp(v, a, b) { return Math.min(b, Math.max(a, v)); }
    function num(input, fallback) {
      var v = parseInt(input.value, 10);
      return isNaN(v) ? fallback : clamp(v, lo, hi);
    }

    var rMin = document.createElement('input');
    var rMax = document.createElement('input');
    [rMin, rMax].forEach(function (r, i) {
      r.type = 'range'; r.min = lo; r.max = hi; r.step = step;
      r.setAttribute('aria-label', i === 0 ? 'Giá thấp nhất' : 'Giá cao nhất');
      box.appendChild(r);
    });
    var labels = document.createElement('p');
    labels.className = 'bvs-range__labels';
    labels.setAttribute('aria-hidden', 'true');
    labels.style.cssText = 'display:flex;justify-content:space-between;margin:16px 0 0;font-size:13px;color:#7A685C';
    var lMin = document.createElement('span');
    var lMax = document.createElement('span');
    labels.appendChild(lMin); labels.appendChild(lMax);
    box.parentNode.insertBefore(labels, box.nextSibling);

    function paint() {
      var a = Number(rMin.value), b = Number(rMax.value);
      box.style.setProperty('--from', ((a - lo) / (hi - lo) * 100) + '%');
      box.style.setProperty('--to', ((b - lo) / (hi - lo) * 100) + '%');
      lMin.textContent = fmt(a);
      lMax.textContent = fmt(b);
    }

    rMin.value = num(inMin, lo);
    rMax.value = num(inMax, hi);
    if (Number(rMin.value) > Number(rMax.value)) rMin.value = rMax.value;
    paint();

    rMin.addEventListener('input', function () {
      if (Number(rMin.value) > Number(rMax.value)) rMin.value = rMax.value;
      inMin.value = rMin.value; paint();
    });
    rMax.addEventListener('input', function () {
      if (Number(rMax.value) < Number(rMin.value)) rMax.value = rMin.value;
      inMax.value = rMax.value; paint();
    });
    inMin.addEventListener('change', function () { rMin.value = num(inMin, lo); if (+rMin.value > +rMax.value) rMin.value = rMax.value; paint(); });
    inMax.addEventListener('change', function () { rMax.value = num(inMax, hi); if (+rMax.value < +rMin.value) rMax.value = rMin.value; paint(); });

    /* Không gửi tham số thừa khi khoảng giá chưa thu hẹp (URL gọn, phân trang sạch) */
    form.addEventListener('submit', function () {
      if (inMin.value === '' || Number(inMin.value) <= lo) inMin.disabled = true;
      if (inMax.value === '' || Number(inMax.value) >= hi) inMax.disabled = true;
    });
    window.addEventListener('pageshow', function (e) {
      if (e.persisted) { inMin.disabled = false; inMax.disabled = false; }
    });
  });
})();
