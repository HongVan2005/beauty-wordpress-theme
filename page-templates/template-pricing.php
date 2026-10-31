<?php
/**
 * Template Name: Bảng giá Spa
 * Pricing page redesign – giữ dữ liệu động từ WordPress.
 */
wp_enqueue_style(
    'beauty-pricing-redesign',
    get_template_directory_uri() . '/assets/css/pricing-redesign.css',
    array(),
    defined('BEAUTY_SPA_VERSION') ? BEAUTY_SPA_VERSION : '9.7'
);
get_header();

$services = get_posts(array(
    'post_type' => 'spa_service', 'post_status' => 'publish', 'numberposts' => -1,
    'orderby' => array('menu_order' => 'ASC', 'title' => 'ASC'),
));
$published_products = wp_count_posts('spa_product');
$total_products = isset($published_products->publish) ? (int) $published_products->publish : 0;
$per_page = 35;
$max_product_pages = max(1, (int) ceil($total_products / $per_page));
$product_page = isset($_GET['product_page']) ? max(1, absint($_GET['product_page'])) : 1;
$product_page = min($product_page, $max_product_pages);
$product_query = new WP_Query(array(
    'post_type' => 'spa_product',
    'post_status' => 'publish',
    'posts_per_page' => 35,
    'paged' => $product_page,
    'orderby' => array('menu_order' => 'ASC', 'title' => 'ASC'),
));
$products = $product_query->posts;
$packages = get_posts(array(
    'post_type' => 'spa_package', 'post_status' => 'publish', 'numberposts' => -1,
    'orderby' => array('menu_order' => 'ASC', 'title' => 'ASC'),
));
$promos = get_posts(array(
    'post_type' => 'spa_promo', 'post_status' => 'publish', 'numberposts' => 6,
    'orderby' => array('menu_order' => 'ASC', 'date' => 'DESC'),
));
$terms = get_terms(array('taxonomy' => 'spa_service_cat', 'hide_empty' => false));

$treatment_packages = array();
$combo_packages = array();
foreach ($packages as $package) {
    $package_terms = get_the_terms($package->ID, 'spa_package_cat');
    $is_combo = false;
    if ($package_terms && !is_wp_error($package_terms)) {
        foreach ($package_terms as $package_term) {
            $term_name = function_exists('mb_strtolower') ? mb_strtolower($package_term->name, 'UTF-8') : strtolower($package_term->name);
            if (false !== strpos($term_name, 'combo') || false !== strpos($term_name, 'ưu đãi')) {
                $is_combo = true;
                break;
            }
        }
    }
    if ($is_combo) $combo_packages[] = $package;
    else $treatment_packages[] = $package;
}

$hero_image = function_exists('beauty_theme_image_url')
    ? beauty_theme_image_url('spa-banner-selected.jpg')
    : get_template_directory_uri() . '/assets/images/spa-banner-selected.jpg';
?>
<div class="beauty-pricing-page">
<section class="bpr-hero" style="--bpr-hero-image:url('<?php echo esc_url($hero_image); ?>')">
    <div class="container bpr-hero-inner">
        <div class="bpr-hero-copy">
            <span class="bpr-eyebrow">THƯ GIÃN · TÁI TẠO · TỎA SÁNG</span>
            <h1>Bảng giá<br><em>Dịch vụ &amp; Sản phẩm</em></h1>
            <p>Chăm sóc sắc đẹp toàn diện, nâng niu bạn từ những điều nhỏ nhất.</p>
            <a class="bpr-button bpr-button-solid" href="<?php echo esc_url(home_url('/dat-lich/')); ?>">Đặt lịch ngay <span>→</span></a>
        </div>
    </div>
</section>

<div class="container bpr-trust">
    <div class="bpr-trust-item"><span class="bpr-trust-icon">♧</span><span><strong>Chuyên nghiệp</strong><small>Dịch vụ chuẩn spa</small></span></div>
    <div class="bpr-trust-item"><span class="bpr-trust-icon">♡</span><span><strong>An toàn</strong><small>Sản phẩm chính hãng</small></span></div>
    <div class="bpr-trust-item"><span class="bpr-trust-icon">❀</span><span><strong>Thư giãn</strong><small>Không gian tinh tế</small></span></div>
    <div class="bpr-trust-item"><span class="bpr-trust-icon">◇</span><span><strong>Hiệu quả</strong><small>Nâng niu vẻ đẹp</small></span></div>
</div>

<main class="container bpr-main">
    <div class="bpr-tools">
        <label>Tìm dịch vụ<input id="pricing-search" type="search" placeholder="Ví dụ: massage, chăm sóc da..."></label>
        <label>Nhóm dịch vụ<select id="pricing-category">
            <option value="all">Tất cả nhóm</option>
            <?php if (!is_wp_error($terms)) foreach ($terms as $term) : ?>
                <option value="<?php echo esc_attr(sanitize_title($term->name)); ?>"><?php echo esc_html($term->name); ?></option>
            <?php endforeach; ?>
        </select></label>
        <label>Khoảng giá<select id="pricing-price">
            <option value="all">Tất cả mức giá</option><option value="under300">Dưới 300.000đ</option>
            <option value="300to500">300.000–500.000đ</option><option value="over500">Trên 500.000đ</option>
            <option value="contact">Liên hệ</option>
        </select></label>
    </div>

    <section class="bpr-price-columns">
        <div class="bpr-panel bpr-services-panel">
            <div class="bpr-panel-heading"><span class="bpr-panel-icon">❀</span><div><h2>Bảng giá dịch vụ</h2><p>Trải nghiệm chăm sóc sắc đẹp chuyên nghiệp</p></div></div>
            <?php if ($services) : ?>
            <div class="bpr-table-scroll">
                <table class="bpr-table pricing-table">
                    <thead><tr><th>Dịch vụ</th><th>Thời gian</th><th>Giá niêm yết</th><th>Giá ưu đãi</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($services as $service) :
                        $price = (int) get_post_meta($service->ID, '_spa_price', true);
                        $sale = (int) get_post_meta($service->ID, '_spa_sale_price', true);
                        $has_sale = ($price > 0 && $sale > 0 && $sale < $price);
                        $duration = get_post_meta($service->ID, '_spa_duration', true);
                        $cats = get_the_terms($service->ID, 'spa_service_cat');
                        $cat_names = array();
                        if ($cats && !is_wp_error($cats)) foreach ($cats as $cat) $cat_names[] = sanitize_title($cat->name);
                        $category_data = implode(' ', $cat_names);
                        $effective = $has_sale ? $sale : $price;
                    ?>
                    <tr class="pricing-row" data-name="<?php echo esc_attr(function_exists('mb_strtolower') ? mb_strtolower($service->post_title, 'UTF-8') : strtolower($service->post_title)); ?>" data-category="<?php echo esc_attr($category_data); ?>" data-price="<?php echo esc_attr($effective); ?>">
                        <td><strong><?php echo esc_html($service->post_title); ?></strong><small><?php echo esc_html(wp_trim_words($service->post_excerpt ?: wp_strip_all_tags($service->post_content), 13)); ?></small></td>
                        <td><?php echo $duration ? esc_html($duration) : '—'; ?></td>
                        <td class="bpr-old"><?php echo $has_sale ? esc_html(number_format_i18n($price) . ' đ') : '—'; ?></td>
                        <td class="bpr-price"><?php echo $effective > 0 ? esc_html(number_format_i18n($effective) . ' đ') : 'Liên hệ'; ?></td>
                        <td><a class="bpr-mini-button" href="<?php echo esc_url(add_query_arg('dich-vu', $service->ID, home_url('/dat-lich/'))); ?>">Đặt lịch</a></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p id="pricing-no-results" class="bpr-empty" hidden>Không tìm thấy dịch vụ phù hợp. Hãy thử thay đổi bộ lọc.</p>
            <?php else : ?><p class="bpr-empty">Chưa có dữ liệu dịch vụ. Hãy thêm dịch vụ trong WordPress Admin → Dịch vụ Spa.</p><?php endif; ?>
            <div class="bpr-help"><span>♧</span><div><strong>Bạn cần tư vấn thêm?</strong><small>Liên hệ với chúng tôi để được hỗ trợ tận tình.</small></div><a class="bpr-mini-button" href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ tư vấn →</a></div>
        </div>

        <div class="bpr-panel bpr-products-panel">
            <div class="bpr-panel-heading"><span class="bpr-panel-icon">♧</span><div><h2>Bảng giá sản phẩm</h2><p>Sản phẩm chăm sóc chính hãng, an toàn cho làn da</p></div></div>
            <?php if ($products) : ?>
            <div class="bpr-product-list">
                <?php foreach ($products as $product) :
                    $pid = $product->ID;
                    $product_terms = get_the_terms($pid, 'spa_product_cat');
                    $product_cat = ($product_terms && !is_wp_error($product_terms)) ? $product_terms[0]->name : 'Sản phẩm chăm sóc';
                ?>
                <article class="bpr-product">
                    <a class="bpr-product-image" href="<?php echo esc_url(get_permalink($pid)); ?>"><?php echo beauty_product_image($pid, 'thumbnail'); ?></a>
                    <div class="bpr-product-info"><span><?php echo esc_html($product_cat); ?></span><strong><a href="<?php echo esc_url(get_permalink($pid)); ?>"><?php echo esc_html(get_the_title($pid)); ?></a></strong><small><?php echo esc_html(wp_trim_words($product->post_excerpt ?: wp_strip_all_tags($product->post_content), 9)); ?></small></div>
                    <div class="bpr-product-price"><?php echo beauty_product_price_html($pid); ?></div>
                    <a class="bpr-mini-button" href="<?php echo esc_url(get_permalink($pid)); ?>">Mua ngay</a>
                </article>
                <?php endforeach; ?>
            </div>
            <?php if ($product_query->max_num_pages > 1) : ?>
            <nav class="bpr-pagination" aria-label="Phân trang sản phẩm">
                <?php
                echo paginate_links(array(
                    'base' => esc_url(add_query_arg('product_page', '%#%')),
                    'format' => '',
                    'current' => $product_page,
                    'total' => $product_query->max_num_pages,
                    'prev_text' => '← Trước',
                    'next_text' => 'Tiếp →',
                    'type' => 'list',
                ));
                ?>
            </nav>
            <?php endif; ?>
            <a class="bpr-product-more" href="<?php echo esc_url(get_post_type_archive_link('spa_product')); ?>">Xem tất cả sản phẩm →</a>
            <?php else : ?><p class="bpr-empty">Chưa có sản phẩm. Hãy thêm sản phẩm trong WordPress Admin → Sản phẩm.</p><?php endif; ?>
            <div class="bpr-promo-strip"><span>♧</span><div><strong>Ưu đãi đặc biệt</strong><small>Khám phá các sản phẩm và chương trình ưu đãi mới nhất.</small></div><a href="<?php echo esc_url(get_post_type_archive_link('spa_promo')); ?>">Khám phá →</a></div>
        </div>
    </section>

    <?php if ($treatment_packages) : ?>
    <section class="bpr-package-section">
        <div class="bpr-section-heading"><div><span class="bpr-section-icon">❀</span><div><h2>Gói liệu trình</h2><p>Chăm sóc chuyên sâu, duy trì vẻ đẹp lâu dài</p></div></div><a href="<?php echo esc_url(get_post_type_archive_link('spa_package')); ?>">Xem tất cả →</a></div>
        <div class="bpr-package-grid">
            <?php foreach ($treatment_packages as $package) :
                $pid = $package->ID; $img = function_exists('beauty_v9_package_image') ? beauty_v9_package_image($pid) : array('url' => $hero_image, 'thumb' => false);
                $price = beaute_spa_price($pid); $sale = beaute_spa_price($pid, true);
            ?>
            <article class="bpr-package-card">
                <a class="bpr-package-image" href="<?php echo esc_url(get_permalink($pid)); ?>">
                    <?php if (has_post_thumbnail($pid)) : echo get_the_post_thumbnail($pid, 'medium_large', array('loading'=>'lazy')); else : ?><img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr(get_the_title($pid)); ?>" loading="lazy"><?php endif; ?>
                </a>
                <div class="bpr-package-body"><h3><a href="<?php echo esc_url(get_permalink($pid)); ?>"><?php echo esc_html(get_the_title($pid)); ?></a></h3><p><?php echo esc_html(wp_trim_words($package->post_excerpt ?: wp_strip_all_tags($package->post_content), 12)); ?></p>
                    <div class="bpr-package-price"><?php echo $sale ? esc_html($sale) : ($price ? esc_html($price) : 'Liên hệ'); ?><?php if ($sale && $price) : ?> <del><?php echo esc_html($price); ?></del><?php endif; ?></div>
                    <a class="bpr-book-button" href="<?php echo esc_url(add_query_arg('goi', $pid, home_url('/dat-lich/'))); ?>">Đặt lịch</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($combo_packages || $promos) : ?>
    <section class="bpr-package-section bpr-combo-section">
        <div class="bpr-section-heading"><div><span class="bpr-section-icon">◇</span><div><h2>Combo ưu đãi</h2><p>Kết hợp dịch vụ – tiết kiệm hơn, hiệu quả hơn</p></div></div><a href="<?php echo esc_url(get_post_type_archive_link('spa_promo')); ?>">Xem tất cả →</a></div>
        <div class="bpr-package-grid">
            <?php foreach ($combo_packages as $package) :
                $pid = $package->ID; $img = function_exists('beauty_v9_package_image') ? beauty_v9_package_image($pid) : array('url' => $hero_image, 'thumb' => false);
                $price = beaute_spa_price($pid); $sale = beaute_spa_price($pid, true);
            ?>
            <article class="bpr-package-card">
                <a class="bpr-package-image" href="<?php echo esc_url(get_permalink($pid)); ?>"><?php if (has_post_thumbnail($pid)) : echo get_the_post_thumbnail($pid, 'medium_large', array('loading'=>'lazy')); else : ?><img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr(get_the_title($pid)); ?>" loading="lazy"><?php endif; ?></a>
                <div class="bpr-package-body"><h3><a href="<?php echo esc_url(get_permalink($pid)); ?>"><?php echo esc_html(get_the_title($pid)); ?></a></h3><p><?php echo esc_html(wp_trim_words($package->post_excerpt ?: wp_strip_all_tags($package->post_content), 12)); ?></p><div class="bpr-package-price"><?php echo $sale ? esc_html($sale) : ($price ? esc_html($price) : 'Liên hệ'); ?></div><a class="bpr-book-button" href="<?php echo esc_url(add_query_arg('goi', $pid, home_url('/dat-lich/'))); ?>">Đặt lịch</a></div>
            </article>
            <?php endforeach; ?>
            <?php if (!$combo_packages) foreach ($promos as $promo) : ?>
            <article class="bpr-package-card">
                <a class="bpr-package-image" href="<?php echo esc_url(get_permalink($promo->ID)); ?>"><?php if (has_post_thumbnail($promo->ID)) : echo get_the_post_thumbnail($promo->ID, 'medium_large', array('loading'=>'lazy')); else : ?><img src="<?php echo esc_url($hero_image); ?>" alt="<?php echo esc_attr(get_the_title($promo->ID)); ?>" loading="lazy"><?php endif; ?></a>
                <div class="bpr-package-body"><h3><a href="<?php echo esc_url(get_permalink($promo->ID)); ?>"><?php echo esc_html(get_the_title($promo->ID)); ?></a></h3><p><?php echo esc_html(wp_trim_words($promo->post_excerpt ?: wp_strip_all_tags($promo->post_content), 12)); ?></p><div class="bpr-package-price">Ưu đãi đang áp dụng</div><a class="bpr-book-button" href="<?php echo esc_url(get_permalink($promo->ID)); ?>">Xem ưu đãi</a></div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <p class="bpr-note">Giá hiển thị được cập nhật từ dữ liệu quản trị. Vui lòng liên hệ spa để xác nhận giá và điều kiện ưu đãi trước khi sử dụng dịch vụ.</p>
</main>
<footer class="bpr-footer"><div class="bpr-footer-brand">Beauty Spa &amp; Wellness</div><p>ĐỒNG HÀNH CÙNG BẠN TRÊN HÀNH TRÌNH VẺ ĐẸP BỀN VỮNG</p></footer>
</div>
<?php get_footer(); ?>
