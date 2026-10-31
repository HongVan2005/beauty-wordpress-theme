document.addEventListener('DOMContentLoaded', function () {
    const carousels = document.querySelectorAll('[data-bv-carousel]');

    carousels.forEach(function (carousel) {
        const viewport = carousel.querySelector('.bv-product-carousel-viewport');
        const track = carousel.querySelector('.bv-product-carousel-track');
        const prevButton = carousel.querySelector('.bv-carousel-prev');
        const nextButton = carousel.querySelector('.bv-carousel-next');

        if (!viewport || !track || !prevButton || !nextButton) {
            return;
        }

        let currentIndex = 0;
        let resizeTimer = null;

        function getVisibleCount() {
            if (window.innerWidth <= 600) return 1;
            if (window.innerWidth <= 900) return 2;
            return 4;
        }

        function getGap() {
            return parseFloat(window.getComputedStyle(track).gap) || 0;
        }

        function getMaxIndex() {
            return Math.max(0, track.children.length - getVisibleCount());
        }

        function updateCarousel() {
            const cards = track.children;
            const visibleCount = getVisibleCount();

            if (!cards.length) {
                prevButton.disabled = true;
                nextButton.disabled = true;
                prevButton.style.visibility = 'hidden';
                nextButton.style.visibility = 'hidden';
                track.style.transform = 'translateX(0)';
                return;
            }

            currentIndex = Math.max(0, Math.min(currentIndex, getMaxIndex()));

            const cardWidth = cards[0].getBoundingClientRect().width;
            const offset = currentIndex * (cardWidth + getGap());
            const canNavigate = cards.length > visibleCount;

            track.style.transform = 'translateX(-' + offset + 'px)';

            prevButton.disabled = !canNavigate || currentIndex === 0;
            nextButton.disabled = !canNavigate || currentIndex >= getMaxIndex();

            prevButton.style.visibility = canNavigate ? 'visible' : 'hidden';
            nextButton.style.visibility = canNavigate ? 'visible' : 'hidden';

            prevButton.setAttribute('aria-disabled', String(prevButton.disabled));
            nextButton.setAttribute('aria-disabled', String(nextButton.disabled));
        }

        // Mỗi lần bấm chỉ dịch một sản phẩm.
        prevButton.addEventListener('click', function () {
            currentIndex = Math.max(0, currentIndex - 1);
            updateCarousel();
        });

        nextButton.addEventListener('click', function () {
            currentIndex = Math.min(getMaxIndex(), currentIndex + 1);
            updateCarousel();
        });

        window.addEventListener('resize', function () {
            window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(updateCarousel, 100);
        });

        updateCarousel();
    });
});
