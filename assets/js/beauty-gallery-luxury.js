(function () {
  'use strict';

  const page = document.querySelector('.beauty-gallery-page');
  if (!page) return;

  const grid = page.querySelector('[data-beauty-gallery-grid]');
  const filterButtons = Array.from(page.querySelectorAll('[data-beauty-filter]'));
  const loadMoreButton = page.querySelector('[data-beauty-load-more]');
  const showAllButton = page.querySelector('[data-beauty-show-all]');
  const modal = document.querySelector('[data-beauty-lightbox-modal]');
  const modalImage = modal ? modal.querySelector('img') : null;
  const closeButton = modal ? modal.querySelector('.beauty-gallery-lightbox__close') : null;
  const prevButton = modal ? modal.querySelector('.beauty-gallery-lightbox__prev') : null;
  const nextButton = modal ? modal.querySelector('.beauty-gallery-lightbox__next') : null;

  let activeFilter = 'all';
  let visibleCount = 8;
  let activeImages = [];
  let activeImageIndex = 0;

  function items() {
    return grid ? Array.from(grid.querySelectorAll('.beauty-gallery-grid__item')) : [];
  }

  function applyGalleryState() {
    const all = items();
    const matching = all.filter((item) => activeFilter === 'all' || item.dataset.beautyCategory === activeFilter);
    all.forEach((item) => {
      const index = matching.indexOf(item);
      item.classList.toggle('is-hidden', index === -1 || index >= visibleCount);
    });
    if (loadMoreButton) {
      const remaining = matching.length > visibleCount;
      loadMoreButton.hidden = !remaining;
    }
  }

  filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
      activeFilter = button.dataset.beautyFilter || 'all';
      visibleCount = 8;
      filterButtons.forEach((item) => item.classList.toggle('is-active', item === button));
      applyGalleryState();
    });
  });

  if (loadMoreButton) {
    loadMoreButton.addEventListener('click', () => {
      visibleCount += 8;
      applyGalleryState();
    });
  }

  if (showAllButton) {
    showAllButton.addEventListener('click', () => {
      const library = document.getElementById('beauty-gallery-library');
      activeFilter = 'all';
      visibleCount = items().length;
      filterButtons.forEach((button) => button.classList.toggle('is-active', button.dataset.beautyFilter === 'all'));
      applyGalleryState();
      if (library) library.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }

  function openLightbox(src, sourceButton) {
    if (!modal || !modalImage || !src) return;
    activeImages = Array.from(document.querySelectorAll('[data-beauty-lightbox]'))
      .filter((button) => button.offsetParent !== null)
      .map((button) => button.dataset.beautyLightbox)
      .filter(Boolean);
    activeImageIndex = Math.max(0, activeImages.indexOf(src));
    modalImage.src = src;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  function moveLightbox(direction) {
    if (!activeImages.length || !modalImage) return;
    activeImageIndex = (activeImageIndex + direction + activeImages.length) % activeImages.length;
    modalImage.src = activeImages[activeImageIndex];
  }

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-beauty-lightbox]');
    if (trigger) openLightbox(trigger.dataset.beautyLightbox, trigger);
  });

  if (closeButton) closeButton.addEventListener('click', closeLightbox);
  if (prevButton) prevButton.addEventListener('click', () => moveLightbox(-1));
  if (nextButton) nextButton.addEventListener('click', () => moveLightbox(1));
  if (modal) {
    modal.addEventListener('click', (event) => {
      if (event.target === modal) closeLightbox();
    });
  }
  document.addEventListener('keydown', (event) => {
    if (!modal || !modal.classList.contains('is-open')) return;
    if (event.key === 'Escape') closeLightbox();
    if (event.key === 'ArrowLeft') moveLightbox(-1);
    if (event.key === 'ArrowRight') moveLightbox(1);
  });

  applyGalleryState();
})();
