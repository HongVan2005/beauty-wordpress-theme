(function(){
'use strict';
var key='beauty_wishlist';
function getList(){try{return JSON.parse(localStorage.getItem(key)||'[]').map(String)}catch(e){return []}}
function save(a){try{localStorage.setItem(key,JSON.stringify(a))}catch(e){}}
function refresh(){var a=getList();document.querySelectorAll('.wishlist-count').forEach(function(n){n.textContent=a.length;n.classList.toggle('is-zero',a.length<1)});document.querySelectorAll('.header-wishlist').forEach(function(h){h.classList.toggle('has-items',a.length>0)});document.querySelectorAll('[data-wishlist-id]').forEach(function(b){var on=a.indexOf(String(b.dataset.wishlistId))>-1;b.classList.toggle('is-favorite',on);b.setAttribute('aria-pressed',on?'true':'false');b.setAttribute('aria-label',on?'Bỏ khỏi yêu thích':'Thêm vào yêu thích');b.textContent=on?'♥':'♡'})}
document.addEventListener('click',function(e){var b=e.target.closest('[data-wishlist-id]');if(!b)return;e.preventDefault();var a=getList(),id=String(b.dataset.wishlistId);a=a.indexOf(id)>-1?a.filter(function(x){return x!==id}):a.concat(id);save(a);refresh()});
document.addEventListener('DOMContentLoaded',function(){
refresh();
document.querySelectorAll('.beauty-album-image img,.bv-album img,.beauty-news-image img').forEach(function(img){img.addEventListener('error',function(){if(img.dataset.fallbackTried)return;img.dataset.fallbackTried='1';img.src=window.beautySpaAssets?window.beautySpaAssets+'/images/spa-album-room.jpg':img.src;},{once:true})});
var links=document.querySelectorAll('.beauty-news-image img');links.forEach(function(img){img.style.objectFit='cover'});
});
})();
