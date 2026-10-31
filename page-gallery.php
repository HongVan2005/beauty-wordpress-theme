<?php
/**
 * Template Name: Beauty Spa - Thư viện hình ảnh
 * Description: Trang thư viện ảnh Luxury Spa cho theme Beauty WordPress.
 *
 * Cách dùng:
 * 1. Đưa file này vào thư mục theme đang dùng.
 * 2. Tạo trang WordPress tên "Hình ảnh".
 * 3. Trong Thuộc tính trang > Template, chọn "Beauty Spa - Thư viện hình ảnh".
 * 4. Thay các ID ảnh trong mảng $beauty_gallery_items bằng ID ảnh trong Media Library.
 * 5. Chỉnh link đặt lịch ở biến $booking_url.
 */

defined('ABSPATH') || exit;

// Nạp tài nguyên trước get_header() để WordPress in CSS/JS đúng vị trí.
$theme_uri = get_stylesheet_directory_uri();
$theme_dir = get_stylesheet_directory();
$css_file = $theme_dir . '/assets/css/beauty-gallery-luxury.css';
$js_file  = $theme_dir . '/assets/js/beauty-gallery-luxury.js';
wp_enqueue_style('beauty-gallery-luxury', $theme_uri . '/assets/css/beauty-gallery-luxury.css', array(), file_exists($css_file) ? filemtime($css_file) : '1.0.0');
wp_enqueue_script('beauty-gallery-luxury', $theme_uri . '/assets/js/beauty-gallery-luxury.js', array(), file_exists($js_file) ? filemtime($js_file) : '1.0.0', true);

get_header();

$booking_url = home_url('/lien-he/');

/**
 * Mỗi ảnh gồm:
 * - id: ID ảnh trong Media Library.
 * - category: slug bộ lọc.
 * - label: tên hiển thị trên ảnh.
 * - description: mô tả ngắn.
 *
 * Thay ID 0 bằng ID ảnh thật trong Media Library.
 * Nếu chưa thay ID, template sẽ tự lấy ảnh mới nhất làm ảnh dự phòng.
 */
$beauty_gallery_items = array(
    array('id' => 0, 'category' => 'cham-soc-da', 'label' => 'Chăm sóc da', 'description' => 'Làn da khỏe đẹp từ bên trong'),
    array('id' => 0, 'category' => 'massage', 'label' => 'Massage thư giãn', 'description' => 'Giảm căng thẳng, tái tạo năng lượng'),
    array('id' => 0, 'category' => 'goi-dau', 'label' => 'Gội đầu dưỡng sinh', 'description' => 'Thư giãn tâm trí, nuôi dưỡng tóc'),
    array('id' => 0, 'category' => 'khong-gian', 'label' => 'Không gian Spa', 'description' => 'Tinh tế trong từng chi tiết'),
    array('id' => 0, 'category' => 'san-pham', 'label' => 'Sản phẩm Beauty', 'description' => 'Chăm sóc toàn diện, an toàn'),
    array('id' => 0, 'category' => 'lieu-trinh', 'label' => 'Liệu trình nổi bật', 'description' => 'Đẹp hơn mỗi ngày'),
);

$beauty_fallback_images = get_posts(array(
    'post_type'      => 'attachment',
    'post_mime_type' => 'image',
    'post_status'    => 'inherit',
    'posts_per_page' => 16,
    'orderby'        => 'date',
    'order'          => 'DESC',
));

$beauty_get_image = function($attachment_id, $fallback_index = 0) use ($beauty_fallback_images) {
    if ($attachment_id && wp_attachment_is_image($attachment_id)) {
        $src = wp_get_attachment_image_url($attachment_id, 'large');
        $full = wp_get_attachment_image_url($attachment_id, 'full');
        $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        return array(
            'src' => $src,
            'full' => $full ? $full : $src,
            'alt' => $alt ? $alt : 'Hình ảnh Beauty Spa',
        );
    }

    if (!empty($beauty_fallback_images)) {
        $item = $beauty_fallback_images[$fallback_index % count($beauty_fallback_images)];
        $src = wp_get_attachment_image_url($item->ID, 'large');
        $full = wp_get_attachment_image_url($item->ID, 'full');
        $alt = get_post_meta($item->ID, '_wp_attachment_image_alt', true);
        if ($src) {
            return array(
                'src' => $src,
                'full' => $full ? $full : $src,
                'alt' => $alt ? $alt : 'Hình ảnh Beauty Spa',
            );
        }
    }

    return array(
        'src' => '',
        'full' => '',
        'alt' => 'Vui lòng thêm ảnh trong thư viện Media',
    );
};

$beauty_featured = array();
foreach ($beauty_gallery_items as $index => $item) {
    $beauty_featured[] = array_merge($item, $beauty_get_image($item['id'], $index));
}
?>

<main class="beauty-gallery-page">
    <section class="beauty-gallery-hero">
        <div class="beauty-gallery-hero__image" aria-hidden="true">
    <img src="<?php echo esc_url($theme_uri . '/assets/images/spa-album-consultation.jpg'); ?>" alt="" fetchpriority="high">
    </div>
        <div class="beauty-gallery-hero__veil"></div>
        <div class="beauty-gallery-container beauty-gallery-hero__content">
            <div class="beauty-gallery-breadcrumb">
                <a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a>
                <span>›</span>
                <span>Hình ảnh</span>
            </div>
            <p class="beauty-gallery-eyebrow">BEAUTY SPA &amp; BEAUTY</p>
            <h1>Hình ảnh</h1>
            <p class="beauty-gallery-hero__lead">Khoảnh khắc đẹp tại Beauty Spa</p>
            <span class="beauty-gallery-flourish" aria-hidden="true">✧ ───── ❀ ───── ✧</span>
            <p class="beauty-gallery-hero__text">Cùng khám phá những hình ảnh chân thực về không gian,<br> dịch vụ và trải nghiệm thư giãn tại Beauty Spa.</p>
        </div>
    </section>

    <section class="beauty-gallery-featured beauty-gallery-container">
        <div class="beauty-gallery-section-heading">
            <p class="beauty-gallery-eyebrow">BEAUTY SPA GALLERY</p>
            <h2>Hành trình làm đẹp và thư giãn</h2>
            <p>Mỗi bức ảnh là một khoảnh khắc, một trải nghiệm,<br class="beauty-desktop-only"> phản ánh sự tận tâm và chuyên nghiệp của Beauty Spa.</p>
            <span class="beauty-gallery-divider" aria-hidden="true">──── ❀ ────</span>
        </div>

        <div class="beauty-gallery-mosaic">
            <?php foreach ($beauty_featured as $index => $item) : ?>
                <button
                    class="beauty-gallery-tile beauty-gallery-tile--<?php echo esc_attr($index + 1); ?>"
                    type="button"
                    data-beauty-lightbox="<?php echo esc_url($item['full']); ?>"
                    aria-label="<?php echo esc_attr('Xem ảnh ' . $item['label']); ?>">
                    <?php if ($item['src']) : ?>
                        <img src="<?php echo esc_url($item['src']); ?>" alt="<?php echo esc_attr($item['alt']); ?>" loading="lazy">
                    <?php else : ?>
                        <span class="beauty-gallery-empty">Thêm hình ảnh trong Media</span>
                    <?php endif; ?>
                    <span class="beauty-gallery-tile__shade"></span>
                    <span class="beauty-gallery-tile__caption">
                        <strong><?php echo esc_html($item['label']); ?></strong>
                        <small><?php echo esc_html($item['description']); ?></small>
                    </span>
                    <span class="beauty-gallery-tile__arrow" aria-hidden="true">↗</span>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="beauty-gallery-center">
            <button class="beauty-gallery-button beauty-gallery-button--solid" type="button" data-beauty-show-all>
                <span aria-hidden="true">▧</span> XEM TẤT CẢ HÌNH ẢNH <span aria-hidden="true">→</span>
            </button>
        </div>
    </section>

    <section class="beauty-gallery-library" id="beauty-gallery-library">
        <div class="beauty-gallery-container">
            <div class="beauty-gallery-section-heading beauty-gallery-section-heading--library">
                <p class="beauty-gallery-eyebrow">THƯ VIỆN ẢNH ĐẦY ĐỦ</p>
                <h2>Tất cả hình ảnh về Beauty Spa</h2>
                <p>Lưu giữ những khoảnh khắc chăm sóc và yêu chiều bản thân.</p>
            </div>

            <div class="beauty-gallery-filters" role="group" aria-label="Lọc hình ảnh">
                <button class="is-active" type="button" data-beauty-filter="all">Tất cả</button>
                <button type="button" data-beauty-filter="cham-soc-da">Chăm sóc da</button>
                <button type="button" data-beauty-filter="massage">Massage thư giãn</button>
                <button type="button" data-beauty-filter="goi-dau">Gội đầu dưỡng sinh</button>
                <button type="button" data-beauty-filter="khong-gian">Không gian Spa</button>
                <button type="button" data-beauty-filter="san-pham">Sản phẩm</button>
                <button type="button" data-beauty-filter="lieu-trinh">Liệu trình</button>
            </div>

            <div class="beauty-gallery-grid" data-beauty-gallery-grid>
                <?php
                $library_images = array();
                foreach ($beauty_gallery_items as $index => $item) {
                    $library_images[] = array_merge($item, $beauty_get_image($item['id'], $index));
                }
                // Bổ sung ảnh mới nhất trong Media Library để thư viện có nhiều ảnh hơn.
                foreach ($beauty_fallback_images as $attachment) {
                    $exists = false;
                    foreach ($library_images as $existing) {
                        if (!empty($existing['id']) && (int) $existing['id'] === (int) $attachment->ID) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists) {
                        $src = wp_get_attachment_image_url($attachment->ID, 'large');
                        $full = wp_get_attachment_image_url($attachment->ID, 'full');
                        if ($src) {
                            $library_images[] = array(
                                'src' => $src,
                                'full' => $full ? $full : $src,
                                'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true) ?: 'Hình ảnh Beauty Spa',
                                'category' => 'khong-gian',
                                'label' => get_the_title($attachment->ID),
                                'description' => '',
                            );
                        }
                    }
                }
                foreach ($library_images as $index => $item) :
                    if (empty($item['src'])) continue;
                ?>
                    <button class="beauty-gallery-grid__item" type="button"
                        data-beauty-category="<?php echo esc_attr($item['category']); ?>"
                        data-beauty-lightbox="<?php echo esc_url($item['full']); ?>"
                        aria-label="<?php echo esc_attr('Phóng to ' . $item['alt']); ?>">
                        <img src="<?php echo esc_url($item['src']); ?>" alt="<?php echo esc_attr($item['alt']); ?>" loading="lazy">
                        <span class="beauty-gallery-grid__overlay"><span>＋</span></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="beauty-gallery-center beauty-gallery-library__more">
                <button class="beauty-gallery-button beauty-gallery-button--outline" type="button" data-beauty-load-more>
                    XEM THÊM HÌNH ẢNH <span aria-hidden="true">↓</span>
                </button>
            </div>
        </div>
    </section>

    <section class="beauty-gallery-cta beauty-gallery-container">
        <div class="beauty-gallery-cta__photo" aria-hidden="true">
            <?php if (!empty($beauty_featured[1]['src'])) : ?>
                <img src="<?php echo esc_url($beauty_featured[1]['src']); ?>" alt="" loading="lazy">
            <?php endif; ?>
        </div>
        <div class="beauty-gallery-cta__content">
            <p class="beauty-gallery-eyebrow">SẴN SÀNG CHO HÀNH TRÌNH LÀM ĐẸP CỦA BẠN?</p>
            <h2>Đặt lịch ngay hôm nay</h2>
            <p>Để nhận tư vấn và trải nghiệm dịch vụ tốt nhất tại Beauty Spa.</p>
        </div>
        <a class="beauty-gallery-button beauty-gallery-button--solid" href="<?php echo esc_url($booking_url); ?>">ĐẶT LỊCH NGAY <span aria-hidden="true">→</span></a>
    </section>
</main>

<div class="beauty-gallery-lightbox" data-beauty-lightbox-modal aria-hidden="true">
    <button class="beauty-gallery-lightbox__close" type="button" aria-label="Đóng ảnh">×</button>
    <button class="beauty-gallery-lightbox__prev" type="button" aria-label="Ảnh trước">‹</button>
    <img src="" alt="Ảnh Beauty Spa đang xem">
    <button class="beauty-gallery-lightbox__next" type="button" aria-label="Ảnh tiếp theo">›</button>
</div>

<?php
get_footer();
