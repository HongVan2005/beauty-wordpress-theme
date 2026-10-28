/* [FIX ẢNH] Tự cứu ảnh lỗi: ảnh gốc -> ảnh dự phòng (data-fallback) -> ảnh nhúng sẵn theo tên tệp/tên sản phẩm -> khung placeholder.
   Không bao giờ để lộ chữ alt / khung vỡ. */
(function () {
  'use strict';
  var cfg = window.BEAUTY_IMG_CFG || {};
  var KW = [['tay te bao chet','sq-tay-te-bao-chet.jpg'],['tay trang','sq-tay-trang.jpg'],['gel rua mat','sq-gel-rua-mat.jpg'],['sua rua mat','sq-sua-rua-mat.jpg'],['face wash','sq-sua-rua-mat.jpg'],['nuoc can bang','sq-toner.jpg'],['toner','sq-toner.jpg'],['serum','sq-serum.jpg'],['tinh chat','sq-tinh-chat.jpg'],['chong nang','sq-chong-nang.jpg'],['kem duong ban dem','sq-kem-dem.jpg'],['kem dem','sq-kem-dem.jpg'],['kem duong tay','sq-kem-tay.jpg'],['kem tay','sq-kem-tay.jpg'],['kem duong','sq-kem-duong.jpg'],['mat na','sq-mat-na.jpg'],['son duong','sq-son-duong.jpg'],['bo cham soc','sq-bo-cham-soc.jpg'],['sua duong the','sq-sua-duong-the.jpg'],['sua tam','sq-sua-tam.jpg'],['dau goi','sq-dau-goi.jpg'],['dau xa','sq-dau-xa.jpg']];
  var PH = 'data:image/svg+xml;utf8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400"><rect width="400" height="400" fill="#f5ebe0"/><circle cx="200" cy="170" r="46" fill="none" stroke="#d4a373" stroke-width="6"/><path d="M120 300q80-70 160 0" fill="none" stroke="#d4a373" stroke-width="6" stroke-linecap="round"/></svg>');
  var loading = false, queue = [];
  function base(u) { return String(u || '').split('#')[0].split('?')[0].split('/').pop(); }
  function plain(s) { return String(s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd'); }
  function load(cb) {
    if (window.BEAUTY_IMG_DATA) return cb();
    queue.push(cb);
    if (loading) return; loading = true;
    var s = document.createElement('script');
    s.src = cfg.dataUrl || ''; s.async = true;
    s.onload = s.onerror = function () { var q = queue.splice(0); q.forEach(function (f) { f(); }); };
    if (!s.src) { s.onerror(); return; }
    document.head.appendChild(s);
  }
  function pick(img) {
    var D = window.BEAUTY_IMG_DATA || {};
    var names = [base(img.getAttribute('src')), base(img.getAttribute('data-fallback'))];
    for (var i = 0; i < names.length; i++) { if (names[i] && D[names[i]]) return D[names[i]]; }
    var a = plain(img.alt);
    for (var k = 0; k < KW.length; k++) { if (a.indexOf(KW[k][0]) > -1 && D[KW[k][1]]) return D[KW[k][1]]; }
    return PH;
  }
  function fix(img) {
    if (!img || img.tagName !== 'IMG') return;
    var tag = img.getAttribute('data-bfixed');
    if (tag === '2' || (img.getAttribute('src') || '').indexOf('data:') === 0) return;
    load(function () {
      var D = window.BEAUTY_IMG_DATA || {}, own = D[base(img.getAttribute('src'))], fb = img.getAttribute('data-fallback');
      img.removeAttribute('srcset');
      if (own) { img.setAttribute('data-bfixed', '2'); img.src = own; return; }
      if (fb && !tag && fb !== img.getAttribute('src')) { img.setAttribute('data-bfixed', '1'); img.src = fb; return; }
      img.setAttribute('data-bfixed', '2'); img.src = pick(img);
    });
  }
  window.addEventListener('error', function (e) { if (e.target && e.target.tagName === 'IMG') fix(e.target); }, true);
  function sweep() {
    var list = document.images;
    for (var i = 0; i < list.length; i++) { var im = list[i]; if (im.complete && im.naturalWidth === 0 && im.getAttribute('src')) fix(im); }
  }
  document.addEventListener('DOMContentLoaded', sweep);
  window.addEventListener('load', function () { sweep(); setTimeout(sweep, 1500); });
})();
