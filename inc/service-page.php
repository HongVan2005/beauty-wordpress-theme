<?php
/**
 * Beauty Spa – Trang Dịch vụ (archive spa_service + taxonomy spa_service_cat).
 *
 * Bố cục, class CSS (bvs-*) và JS tái sử dụng từ trang Cửa hàng (inc/shop-page.php) để hai trang giống hệt nhau.
 * Tệp này chỉ lo: truy vấn (tìm kiếm, lọc giá, sắp xếp, 9 dịch vụ/trang), nhãn, giá, dịch vụ nổi bật, phân trang, nạp CSS/JS.
 * Không đổi CPT, taxonomy, slug, meta key hay các trang khác. Mọi hàm dùng tiền tố bvsv_.
 */
if (!defined('ABSPATH')) exit;

/* ---------- Nhận diện trang Dịch vụ ---------- */
function bvsv_is_service_page() {
    return !is_admin() && !is_search() && (is_post_type_archive('spa_service') || is_tax('spa_service_cat'));
}

/* ---------- Tham số từ URL (đã làm sạch) ---------- */
function bvsv_sort_options() {
    return array(
        ''         => 'Mặc định',
        'moi-nhat' => 'Mới nhất',
        'gia-tang' => 'Giá thấp đến cao',
        'gia-giam' => 'Giá cao đến thấp',
    );
}
function bvsv_get_int($key) {
    if (!isset($_GET[$key]) || !is_scalar($_GET[$key])) return 0;
    return absint(wp_unslash($_GET[$key]));
}
function bvsv_params() {
    static $p = null;
    if (null !== $p) return $p;
    $min = bvsv_get_int('gia_tu');
    $max = bvsv_get_int('gia_den');
    if ($max > 0 && $min > $max) { $t = $min; $min = $max; $max = $t; }
    $sort = '';
    if (isset($_GET['sap_xep']) && is_scalar($_GET['sap_xep'])) {
        $sort = sanitize_key(wp_unslash($_GET['sap_xep']));
        if (!array_key_exists($sort, bvsv_sort_options())) $sort = '';
    }
    $q = '';
    if (isset($_GET['tu_khoa']) && is_scalar($_GET['tu_khoa'])) {
        $q = trim(sanitize_text_field(wp_unslash($_GET['tu_khoa'])));
        if (function_exists('mb_substr')) $q = mb_substr($q, 0, 80, 'UTF-8');
    }
    $p = array('min' => $min, 'max' => $max, 'sort' => $sort, 'q' => $q);
    return $p;
}
/** Các điều kiện đang bật, giữ nguyên khi đổi danh mục / chuyển trang. */
function bvsv_keep_args() {
    $p = bvsv_params();
    $args = array();
    if ($p['min'] > 0)     $args['gia_tu']   = $p['min'];
    if ($p['max'] > 0)     $args['gia_den']  = $p['max'];
    if ('' !== $p['sort']) $args['sap_xep']  = $p['sort'];
    if ('' !== $p['q'])    $args['tu_khoa']  = $p['q'];
    return $args;
}
function bvsv_link($url) {
    $args = bvsv_keep_args();
    return $args ? add_query_arg($args, $url) : $url;
}

/* ---------- SQL: giá hiệu lực = giá ưu đãi (nếu hợp lệ) hoặc giá gốc ---------- */
function bvsv_price_join() {
    global $wpdb;
    return " LEFT JOIN {$wpdb->postmeta} AS bvsv_p ON (bvsv_p.post_id = {$wpdb->posts}.ID AND bvsv_p.meta_key = '_spa_price')"
         . " LEFT JOIN {$wpdb->postmeta} AS bvsv_s ON (bvsv_s.post_id = {$wpdb->posts}.ID AND bvsv_s.meta_key = '_spa_sale_price') ";
}
function bvsv_price_expr() {
    return "(CASE WHEN CAST(COALESCE(bvsv_s.meta_value,'0') AS UNSIGNED) > 0"
         . " AND CAST(COALESCE(bvsv_s.meta_value,'0') AS UNSIGNED) < CAST(COALESCE(bvsv_p.meta_value,'0') AS UNSIGNED)"
         . " THEN CAST(COALESCE(bvsv_s.meta_value,'0') AS UNSIGNED)"
         . " ELSE CAST(COALESCE(bvsv_p.meta_value,'0') AS UNSIGNED) END)";
}

/* ---------- Truy vấn chính ---------- */
function bvsv_pre_get_posts($q) {
    if (is_admin() || !$q->is_main_query() || $q->is_search()) return;
    if (!($q->is_post_type_archive('spa_service') || $q->is_tax('spa_service_cat'))) return;
    $p = bvsv_params();
    $q->set('posts_per_page', (int) apply_filters('beauty_service_per_page', 9));
    $q->set('bvsv_service', 1);
    if ('' !== $p['q']) $q->set('s', $p['q']);
    if ('moi-nhat' === $p['sort']) {
        $q->set('orderby', 'date');
        $q->set('order', 'DESC');
    } else {
        $q->set('orderby', array('menu_order' => 'ASC', 'date' => 'DESC'));
    }
}
add_action('pre_get_posts', 'bvsv_pre_get_posts');

function bvsv_posts_clauses($clauses, $q) {
    if (!$q->get('bvsv_service')) return $clauses;
    $p = bvsv_params();
    $by_price = in_array($p['sort'], array('gia-tang', 'gia-giam'), true);
    if ($p['min'] <= 0 && $p['max'] <= 0 && !$by_price) return $clauses;
    global $wpdb;
    $expr = bvsv_price_expr();
    $clauses['join'] .= bvsv_price_join();
    if ($p['min'] > 0) $clauses['where'] .= ' AND ' . $expr . ' >= ' . (int) $p['min'];
    if ($p['max'] > 0) $clauses['where'] .= ' AND ' . $expr . ' <= ' . (int) $p['max'];
    $tie = "{$wpdb->posts}.menu_order ASC, {$wpdb->posts}.post_date DESC";
    if ('gia-tang' === $p['sort']) {
        // Dịch vụ "Liên hệ để biết giá" (giá 0) xếp cuối.
        $clauses['orderby'] = '(' . $expr . ' = 0) ASC, ' . $expr . ' ASC, ' . $tie;
    } elseif ('gia-giam' === $p['sort']) {
        $clauses['orderby'] = $expr . ' DESC, ' . $tie;
    }
    return $clauses;
}
add_filter('posts_clauses', 'bvsv_posts_clauses', 10, 2);

/** Giá thấp nhất / cao nhất của dịch vụ (làm tròn 10.000đ) cho thanh kéo lọc giá. */
function bvsv_price_bounds() {
    static $b = null;
    if (null !== $b) return $b;
    global $wpdb;
    $expr = bvsv_price_expr();
    $sql = "SELECT MIN({$expr}) AS lo, MAX({$expr}) AS hi FROM {$wpdb->posts}" . bvsv_price_join()
         . "WHERE {$wpdb->posts}.post_type = 'spa_service' AND {$wpdb->posts}.post_status = 'publish' AND {$expr} > 0";
    $row = $wpdb->get_row($sql);
    $b = array('min' => 0, 'max' => 0, 'step' => 10000);
    if ($row && (int) $row->hi > 0) {
        $lo = (int) floor(((int) $row->lo) / 10000) * 10000;
        $hi = (int) ceil(((int) $row->hi) / 10000) * 10000;
        if ($hi <= $lo) $hi = $lo + 10000;
        $b['min'] = $lo;
        $b['max'] = $hi;
    }
    return $b;
}

/* ---------- Giá / nhãn / đánh giá: chỉ dùng dữ liệu thật ---------- */
function bvsv_on_sale($id) {
    $price = (int) get_post_meta($id, '_spa_price', true);
    $sale  = (int) get_post_meta($id, '_spa_sale_price', true);
    return ($sale > 0 && $price > $sale);
}
/** Ưu tiên: Giảm giá > Bán chạy (ô "Dịch vụ nổi bật / Best seller" trong admin) > Mới (đăng trong 14 ngày). */
function bvsv_badge($id) {
    if (bvsv_on_sale($id)) return array('type' => 'sale', 'label' => 'Giảm giá');
    if ('1' === (string) get_post_meta($id, '_spa_featured', true)) return array('type' => 'best', 'label' => 'Bán chạy');
    $days = (int) apply_filters('beauty_service_new_days', 14);
    $ts = (int) get_post_time('U', true, $id);
    if ($days > 0 && $ts > 0 && $ts >= time() - $days * DAY_IN_SECONDS) return array('type' => 'new', 'label' => 'Mới');
    return null;
}
function bvsv_price_html($id) {
    $price = beaute_spa_price($id);
    $sale  = beaute_spa_price($id, true);
    if ($price && $sale && bvsv_on_sale($id)) {
        return '<span class="bv-price-now">' . esc_html($sale) . '</span><del>' . esc_html($price) . '</del>';
    }
    if ($price) return '<small>Từ</small> <span class="bv-price-now">' . esc_html($price) . '</span>';
    return '<span class="bv-price-contact">Liên hệ để biết giá</span>';
}
/** Đánh giá lấy từ mục "Đánh giá khách hàng", khớp theo ô "Dịch vụ / sản phẩm liên quan" == tên dịch vụ. */
function bvsv_rating($id) {
    return function_exists('bvs_rating') ? bvs_rating($id) : null;
}
function bvsv_image($id, $size = 'medium_large') {
    if (has_post_thumbnail($id)) {
        return get_the_post_thumbnail($id, $size, array('loading' => 'lazy', 'decoding' => 'async'));
    }
    return '<img src="' . esc_url(beauty_v7_service_image_url($id)) . '" alt="' . esc_attr(get_the_title($id)) . '" width="504" height="504" loading="lazy" decoding="async">';
}

/* ---------- Dịch vụ nổi bật: ưu tiên dịch vụ đánh dấu "nổi bật / best seller", bù bằng thứ tự ưu tiên trong admin ---------- */
function bvsv_featured_ids($limit = 3) {
    $ids = array();
    $feat = get_posts(array(
        'post_type' => 'spa_service', 'post_status' => 'publish', 'posts_per_page' => $limit, 'fields' => 'ids',
        'orderby' => array('menu_order' => 'ASC', 'date' => 'DESC'), 'no_found_rows' => true,
        'meta_query' => array(array('key' => '_spa_featured', 'value' => '1')),
    ));
    foreach ($feat as $pid) $ids[] = (int) $pid;
    if (count($ids) < $limit) {
        $more = get_posts(array(
            'post_type' => 'spa_service', 'post_status' => 'publish', 'posts_per_page' => $limit - count($ids), 'fields' => 'ids',
            'orderby' => array('menu_order' => 'ASC', 'date' => 'DESC'), 'no_found_rows' => true,
            'post__not_in' => $ids ? $ids : array(0),
        ));
        foreach ($more as $pid) $ids[] = (int) $pid;
    }
    return $ids;
}

/* ---------- Biểu tượng danh mục (SVG nội tuyến, theo slug đang có) ---------- */
function bvsv_cat_icon($slug) {
    $paths = array(
        'tat-ca'             => '<rect x="4" y="4" width="6.5" height="6.5" rx="1.6"/><rect x="13.5" y="4" width="6.5" height="6.5" rx="1.6"/><rect x="4" y="13.5" width="6.5" height="6.5" rx="1.6"/><rect x="13.5" y="13.5" width="6.5" height="6.5" rx="1.6"/>',
        'cham-soc-da'        => '<circle cx="12" cy="12" r="8"/><path d="M8.5 13.5c1 1.6 2.2 2.3 3.5 2.3s2.5-.7 3.5-2.3M9 9.5h.01M15 9.5h.01"/>',
        'mun-va-lam-sach'    => '<path d="M12 3c3 4 5.5 6.7 5.5 10a5.5 5.5 0 0 1-11 0C6.5 9.7 9 7 12 3z"/>',
        'massage-thu-gian'   => '<path d="M5 19c0-8 5-13 14-14 0 9-5 14-14 14z"/><path d="M5 19 13 11"/>',
        'goi-dau-duong-sinh' => '<path d="M5 10a7 7 0 0 1 14 0v1H5zM8 14v3M12 14v5M16 14v3"/>',
        'triet-long'         => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z"/>',
        'cham-soc-co-the'    => '<path d="M12 20c-4-2-7-5-7-9a4 4 0 0 1 7-2.5A4 4 0 0 1 19 11c0 4-3 7-7 9z"/>',
        'cham-soc-tay-chan'  => '<path d="M8 12V6.5a1.2 1.2 0 0 1 2.4 0V11M10.4 11V5a1.2 1.2 0 0 1 2.4 0v6M12.8 11V6a1.2 1.2 0 0 1 2.4 0v7.5c0 4-2.2 6.5-5.2 6.5-2.4 0-4-1.6-4.8-3.8L4 13.5a1.2 1.2 0 0 1 2.2-.8L8 15"/>',
    );
    $d = isset($paths[$slug]) ? $paths[$slug] : '<path d="M12 21c-5-2.5-8-6-8-10a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 11c0 4-3 7.5-8 10z"/>';
    return '<svg class="bvs-cat__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $d . '</svg>';
}

/* ---------- Ảnh banner: Customizer > Banner trang Dịch vụ; mặc định dùng ảnh có sẵn trong theme ---------- */
function bvsv_hero_image_url() {
    $custom = trim((string) get_theme_mod('beauty_service_hero_image', ''));
    return $custom ? $custom : beauty_theme_image_url('spa-album-facial.jpg');
}
function bvsv_customize($wp_customize) {
    $wp_customize->add_section('beauty_service_page', array('title' => 'Banner trang Dịch vụ', 'priority' => 163));
    $wp_customize->add_setting('beauty_service_hero_image', array('default' => '', 'sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'beauty_service_hero_image', array(
        'label' => 'Ảnh banner bên phải (nên dùng ảnh thật của spa, ngang, tối thiểu 1200×700)',
        'section' => 'beauty_service_page',
    )));
}
add_action('customize_register', 'bvsv_customize');

/* ---------- Câu "Hiển thị 1–9 của X dịch vụ" ---------- */
function bvsv_range_text() {
    global $wp_query;
    $total = (int) $wp_query->found_posts;
    $count = (int) $wp_query->post_count;
    if ($total < 1 || $count < 1) return 'Không có dịch vụ nào';
    $per   = max(1, (int) $wp_query->get('posts_per_page'));
    $paged = max(1, (int) get_query_var('paged'));
    $from  = ($paged - 1) * $per + 1;
    $to    = min($total, $from + $count - 1);
    return sprintf('Hiển thị %s–%s của %s dịch vụ', number_format_i18n($from), number_format_i18n($to), number_format_i18n($total));
}

/* ---------- Phân trang giữ nguyên điều kiện lọc ---------- */
function bvsv_pagination() {
    global $wp_query;
    $total = (int) $wp_query->max_num_pages;
    if ($total < 2) return;
    $links = paginate_links(array(
        'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
        'format'    => '',
        'current'   => max(1, (int) get_query_var('paged')),
        'total'     => $total,
        'mid_size'  => 1,
        'end_size'  => 1,
        'prev_text' => '<span aria-hidden="true">‹</span><span class="screen-reader-text">Trang trước</span>',
        'next_text' => '<span aria-hidden="true">›</span><span class="screen-reader-text">Trang sau</span>',
        'type'      => 'list',
        'add_args'  => bvsv_keep_args(),
    ));
    if ($links) echo '<nav class="bvs-pagination" aria-label="Phân trang dịch vụ">' . $links . '</nav>';
}

/* ---------- CSS/JS: chỉ nạp trên trang Dịch vụ (dùng chung CSS/JS của trang Cửa hàng + CSS bổ sung) ---------- */
function bvsv_assets() {
    if (!bvsv_is_service_page()) return;
    $dir = get_template_directory();
    $uri = get_template_directory_uri();
    $css_shop = $dir . '/assets/css/beauty-shop.css';
    $js_shop  = $dir . '/assets/js/beauty-shop.js';
    $css_svc  = $dir . '/assets/css/beauty-service.css';
    if (!wp_style_is('beauty-shop', 'enqueued')) {
        wp_enqueue_style('beauty-shop', $uri . '/assets/css/beauty-shop.css', array('beaute-spa-style', 'beauty-v9'), file_exists($css_shop) ? (string) filemtime($css_shop) : BEAUTY_SPA_VERSION);
    }
    wp_enqueue_style('beauty-service', $uri . '/assets/css/beauty-service.css', array('beauty-shop'), file_exists($css_svc) ? (string) filemtime($css_svc) : BEAUTY_SPA_VERSION);
    if (!wp_script_is('beauty-shop', 'enqueued')) {
        wp_enqueue_script('beauty-shop', $uri . '/assets/js/beauty-shop.js', array(), file_exists($js_shop) ? (string) filemtime($js_shop) : BEAUTY_SPA_VERSION, true);
    }
}
add_action('wp_enqueue_scripts', 'bvsv_assets', 101);
