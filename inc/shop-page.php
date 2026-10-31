<?php
/**
 * Beauty Spa – Trang Cửa hàng (archive spa_product + taxonomy spa_product_cat).
 *
 * Tệp này CHỈ phục vụ trang cửa hàng: truy vấn (lọc giá, sắp xếp, 12 sản phẩm/trang),
 * dữ liệu hiển thị (nhãn, đánh giá, sản phẩm nổi bật) và nạp CSS/JS riêng.
 * Không đổi CPT, taxonomy, slug, meta key, giỏ hàng hay các trang khác.
 * Tất cả hàm dùng tiền tố bvs_ để không trùng với hàm hiện có của theme.
 */
if (!defined('ABSPATH')) exit;

/* ---------- Nhận diện trang cửa hàng ---------- */
function bvs_is_shop_page() {
    return !is_admin() && (is_post_type_archive('spa_product') || is_tax('spa_product_cat'));
}

/* ---------- Tham số lọc / sắp xếp từ URL (đã làm sạch) ---------- */
function bvs_sort_options() {
    return array(
        ''         => 'Mặc định',
        'moi-nhat' => 'Mới nhất',
        'gia-tang' => 'Giá thấp đến cao',
        'gia-giam' => 'Giá cao đến thấp',
    );
}
function bvs_get_int($key) {
    if (!isset($_GET[$key]) || !is_scalar($_GET[$key])) return 0;
    return absint(wp_unslash($_GET[$key]));
}
function bvs_params() {
    static $p = null;
    if (null !== $p) return $p;
    $min = bvs_get_int('gia_tu');
    $keyword = isset($_GET['tu_khoa']) && is_scalar($_GET['tu_khoa']) ? sanitize_text_field(wp_unslash($_GET['tu_khoa'])) : '';
    $max = bvs_get_int('gia_den');
    if ($max > 0 && $min > $max) { $t = $min; $min = $max; $max = $t; }
    $sort = '';
    if (isset($_GET['sap_xep']) && is_scalar($_GET['sap_xep'])) {
        $sort = sanitize_key(wp_unslash($_GET['sap_xep']));
        if (!array_key_exists($sort, bvs_sort_options())) $sort = '';
    }
    $p = array('min' => $min, 'max' => $max, 'sort' => $sort, 'q' => $keyword);
    return $p;
}
/** Các điều kiện lọc đang bật, dùng để giữ nguyên khi đổi danh mục / chuyển trang. */
function bvs_keep_args() {
    $p = bvs_params();
    $args = array();
    if ($p['min'] > 0)  $args['gia_tu']   = $p['min'];
    if ($p['max'] > 0)  $args['gia_den']  = $p['max'];
    if ('' !== $p['sort']) $args['sap_xep'] = $p['sort'];
    return $args;
}
function bvs_link($url) {
    $args = bvs_keep_args();
    return $args ? add_query_arg($args, $url) : $url;
}

/* ---------- SQL giá hiệu lực = giá khuyến mãi (nếu hợp lệ) hoặc giá gốc ---------- */
function bvs_price_join() {
    global $wpdb;
    return " LEFT JOIN {$wpdb->postmeta} AS bvs_p ON (bvs_p.post_id = {$wpdb->posts}.ID AND bvs_p.meta_key = '_p_price')"
         . " LEFT JOIN {$wpdb->postmeta} AS bvs_s ON (bvs_s.post_id = {$wpdb->posts}.ID AND bvs_s.meta_key = '_p_sale') ";
}
function bvs_price_expr() {
    return "(CASE WHEN CAST(COALESCE(bvs_s.meta_value,'0') AS UNSIGNED) > 0"
         . " AND CAST(COALESCE(bvs_s.meta_value,'0') AS UNSIGNED) < CAST(COALESCE(bvs_p.meta_value,'0') AS UNSIGNED)"
         . " THEN CAST(COALESCE(bvs_s.meta_value,'0') AS UNSIGNED)"
         . " ELSE CAST(COALESCE(bvs_p.meta_value,'0') AS UNSIGNED) END)";
}

/* ---------- Truy vấn chính của trang cửa hàng ---------- */
function bvs_pre_get_posts($q) {
    if (is_admin() || !$q->is_main_query()) return;
    if (!($q->is_post_type_archive('spa_product') || $q->is_tax('spa_product_cat'))) return;
    $p = bvs_params();
    $q->set('posts_per_page', (int) apply_filters('beauty_shop_per_page', 9));
    $q->set('bvs_shop', 1);
    if ('' !== $p['q']) { $q->set('s', $p['q']); }
    if ('moi-nhat' === $p['sort']) {
        $q->set('orderby', 'date');
        $q->set('order', 'DESC');
    } else {
        $q->set('orderby', array('menu_order' => 'ASC', 'date' => 'DESC'));
    }
}
add_action('pre_get_posts', 'bvs_pre_get_posts', 999); /* chạy sau cùng để không bị plugin ghi đè số sản phẩm/trang */

function bvs_posts_clauses($clauses, $q) {
    if (!$q->get('bvs_shop')) return $clauses;
    $p = bvs_params();
    $by_price = in_array($p['sort'], array('gia-tang', 'gia-giam'), true);
    if ($p['min'] <= 0 && $p['max'] <= 0 && !$by_price) return $clauses;
    global $wpdb;
    $expr = bvs_price_expr();
    $clauses['join'] .= bvs_price_join();
    if ($p['min'] > 0) $clauses['where'] .= ' AND ' . $expr . ' >= ' . (int) $p['min'];
    if ($p['max'] > 0) $clauses['where'] .= ' AND ' . $expr . ' <= ' . (int) $p['max'];
    $tie = "{$wpdb->posts}.menu_order ASC, {$wpdb->posts}.post_date DESC";
    if ('gia-tang' === $p['sort']) {
        // Sản phẩm "Liên hệ để biết giá" (giá 0) xếp cuối.
        $clauses['orderby'] = '(' . $expr . ' = 0) ASC, ' . $expr . ' ASC, ' . $tie;
    } elseif ('gia-giam' === $p['sort']) {
        $clauses['orderby'] = $expr . ' DESC, ' . $tie;
    }
    return $clauses;
}
add_filter('posts_clauses', 'bvs_posts_clauses', 10, 2);

/** Giá thấp nhất / cao nhất trong cửa hàng (làm tròn 10.000đ) cho bộ lọc giá. */
function bvs_price_bounds() {
    static $b = null;
    if (null !== $b) return $b;
    global $wpdb;
    $expr = bvs_price_expr();
    $sql = "SELECT MIN({$expr}) AS lo, MAX({$expr}) AS hi FROM {$wpdb->posts}" . bvs_price_join()
         . "WHERE {$wpdb->posts}.post_type = 'spa_product' AND {$wpdb->posts}.post_status = 'publish' AND {$expr} > 0";
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

/* ---------- Nhãn sản phẩm: chỉ dùng dữ liệu thật ---------- */
function bvs_badge($id) {
    $price = (int) get_post_meta($id, '_p_price', true);
    $sale  = (int) get_post_meta($id, '_p_sale', true);
    if ($sale > 0 && $price > $sale) return array('type' => 'sale', 'label' => 'Giảm giá');
    $days = (int) apply_filters('beauty_shop_new_days', 14);
    $ts = (int) get_post_time('U', true, $id);
    if ($days > 0 && $ts > 0 && $ts >= time() - $days * DAY_IN_SECONDS) return array('type' => 'new', 'label' => 'Mới');
    return null;
}

/* ---------- Đánh giá: lấy từ mục "Đánh giá khách hàng" (spa_review) ----------
   Khớp theo ô "Dịch vụ / sản phẩm liên quan" == tên sản phẩm. Không có dữ liệu thì không hiện gì. */
function bvs_norm($s) {
    $s = trim(wp_strip_all_tags((string) $s));
    return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
}
function bvs_rating_map() {
    static $map = null;
    if (null !== $map) return $map;
    $map = array();
    global $wpdb;
    $rows = $wpdb->get_results(
        "SELECT s.meta_value AS svc, AVG(CAST(r.meta_value AS DECIMAL(4,2))) AS avg_r, COUNT(*) AS n"
        . " FROM {$wpdb->postmeta} s"
        . " INNER JOIN {$wpdb->posts} rp ON (rp.ID = s.post_id AND rp.post_type = 'spa_review' AND rp.post_status = 'publish')"
        . " INNER JOIN {$wpdb->postmeta} r ON (r.post_id = rp.ID AND r.meta_key = '_r_rating')"
        . " WHERE s.meta_key = '_r_service' AND s.meta_value <> '' AND CAST(r.meta_value AS DECIMAL(4,2)) BETWEEN 1 AND 5"
        . " GROUP BY s.meta_value"
    );
    if ($rows) {
        foreach ($rows as $row) {
            $map[bvs_norm($row->svc)] = array('avg' => (float) $row->avg_r, 'n' => (int) $row->n);
        }
    }
    return $map;
}
function bvs_rating($id) {
    $map = bvs_rating_map();
    $key = bvs_norm(get_the_title($id));
    return isset($map[$key]) && $map[$key]['n'] > 0 ? $map[$key] : null;
}

/* ---------- Sản phẩm nổi bật: ưu tiên sản phẩm đang giảm giá, bù bằng thứ tự ưu tiên trong admin ---------- */
function bvs_featured_ids($limit = 3) {
    $ids = array();
    $on_sale = get_posts(array(
        'post_type' => 'spa_product', 'post_status' => 'publish', 'posts_per_page' => 12, 'fields' => 'ids',
        'orderby' => array('menu_order' => 'ASC', 'date' => 'DESC'), 'no_found_rows' => true,
        'meta_query' => array(array('key' => '_p_sale', 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC')),
    ));
    foreach ($on_sale as $pid) {
        if ((int) get_post_meta($pid, '_p_price', true) > (int) get_post_meta($pid, '_p_sale', true)) $ids[] = (int) $pid;
        if (count($ids) >= $limit) break;
    }
    if (count($ids) < $limit) {
        $more = get_posts(array(
            'post_type' => 'spa_product', 'post_status' => 'publish', 'posts_per_page' => $limit - count($ids), 'fields' => 'ids',
            'orderby' => array('menu_order' => 'ASC', 'date' => 'DESC'), 'no_found_rows' => true,
            'post__not_in' => $ids ? $ids : array(0),
        ));
        foreach ($more as $pid) $ids[] = (int) $pid;
    }
    return $ids;
}

/* ---------- Biểu tượng danh mục (SVG nội tuyến, theo slug đang có) ---------- */
function bvs_cat_icon($slug) {
    $paths = array(
        'lam-sach'        => '<path d="M12 3c3 4 5.5 6.7 5.5 10a5.5 5.5 0 0 1-11 0C6.5 9.7 9 7 12 3z"/>',
        'duong-am'        => '<rect x="6" y="9" width="12" height="10" rx="3"/><path d="M8 9V7a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/>',
        'chong-nang'      => '<circle cx="12" cy="12" r="4"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M18.4 5.6 17 7M7 17l-1.4 1.4"/>',
        'cham-soc-da-mat' => '<circle cx="12" cy="12" r="8"/><path d="M8.5 13.5c1 1.6 2.2 2.3 3.5 2.3s2.5-.7 3.5-2.3M9 9.5h.01M15 9.5h.01"/>',
        'cham-soc-co-the' => '<path d="M12 20c-4-2-7-5-7-9a4 4 0 0 1 7-2.5A4 4 0 0 1 19 11c0 4-3 7-7 9z"/>',
        'cham-soc-toc'    => '<path d="M5 10a7 7 0 0 1 14 0v1H5zM8 14v3M12 14v5M16 14v3"/>',
    );
    $d = isset($paths[$slug]) ? $paths[$slug] : '<path d="M12 21c-5-2.5-8-6-8-10a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 11c0 4-3 7.5-8 10z"/>';
    return '<svg class="bvs-cat__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $d . '</svg>';
}

/* ---------- Ảnh banner: Customizer > Banner trang Cửa hàng; mặc định dùng ảnh có sẵn trong theme ---------- */
function bvs_hero_image_url() {
    $custom = trim((string) get_theme_mod('beauty_shop_hero_image', ''));
    return $custom ? $custom : get_template_directory_uri() . '/assets/images/spa-album-products.jpg';
}
function bvs_customize($wp_customize) {
    $wp_customize->add_section('beauty_shop_page', array('title' => 'Banner trang Cửa hàng', 'priority' => 162));
    $wp_customize->add_setting('beauty_shop_hero_image', array('default' => '', 'sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'beauty_shop_hero_image', array(
        'label' => 'Ảnh banner bên phải (nên dùng ảnh thật của cửa hàng, ngang, tối thiểu 1200×700)',
        'section' => 'beauty_shop_page',
    )));
}
add_action('customize_register', 'bvs_customize');

/* ---------- Phân trang giữ nguyên điều kiện lọc ---------- */
function bvs_pagination() {
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
        'add_args'  => bvs_keep_args(),
    ));
    if ($links) echo '<nav class="bvs-pagination" aria-label="Phân trang sản phẩm">' . $links . '</nav>';
}

/* ---------- CSS/JS: chỉ nạp trên trang cửa hàng ---------- */
function bvs_assets() {
    if (!bvs_is_shop_page()) return;
    $dir = get_template_directory();
    $uri = get_template_directory_uri();
    $css = $dir . '/assets/css/beauty-shop.css';
    $js  = $dir . '/assets/js/beauty-shop.js';
    wp_enqueue_style('beauty-shop', $uri . '/assets/css/beauty-shop.css', array('beaute-spa-style', 'beauty-v9'), file_exists($css) ? (string) filemtime($css) : BEAUTY_SPA_VERSION);
    wp_enqueue_script('beauty-shop', $uri . '/assets/js/beauty-shop.js', array(), file_exists($js) ? (string) filemtime($js) : BEAUTY_SPA_VERSION, true);
}
add_action('wp_enqueue_scripts', 'bvs_assets', 100);
