<?php
/**
 * Beauty Spa V8: cửa hàng tích hợp (không cần WooCommerce), FAQ, đánh giá, trường chi tiết dịch vụ,
 * JS nội tuyến chống bị plugin tối ưu trì hoãn, và flush rewrite một lần mỗi phiên bản.
 */
if (!defined('ABSPATH')) exit;

/* ---------- Tiện ích ---------- */
function beauty_money($n) {
    return number_format_i18n((int) $n, 0) . ' đ';
}
function beauty_use_woocommerce() {
    return class_exists('WooCommerce') && function_exists('wc_get_products');
}
function beauty_shop_url() {
    if (function_exists('wc_get_page_permalink')) {
        $url = wc_get_page_permalink('shop');
        if ($url) return $url;
    }
    $archive = get_post_type_archive_link('spa_product');
    return $archive ? $archive : home_url('/');
}

/* ---------- Đăng ký nội dung ---------- */
function beauty_v8_register() {
    register_post_type('spa_product', array(
        'labels' => array('name' => 'Sản phẩm (cửa hàng Beauty)', 'singular_name' => 'Sản phẩm', 'add_new_item' => 'Thêm sản phẩm', 'edit_item' => 'Sửa sản phẩm', 'all_items' => 'Tất cả sản phẩm'),
        'public' => true, 'has_archive' => true, 'rewrite' => array('slug' => 'cua-hang'), 'menu_icon' => 'dashicons-products',
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes'), 'show_in_rest' => true,
    ));
    register_taxonomy('spa_product_cat', 'spa_product', array(
        'labels' => array('name' => 'Danh mục sản phẩm', 'singular_name' => 'Danh mục sản phẩm', 'add_new_item' => 'Thêm danh mục'),
        'hierarchical' => true, 'show_admin_column' => true, 'show_in_rest' => true, 'rewrite' => array('slug' => 'nhom-san-pham'),
    ));
    register_post_type('spa_faq', array(
        'labels' => array('name' => 'Câu hỏi thường gặp', 'singular_name' => 'Câu hỏi', 'add_new_item' => 'Thêm câu hỏi'),
        'public' => false, 'show_ui' => true, 'menu_icon' => 'dashicons-editor-help', 'supports' => array('title', 'editor', 'page-attributes'),
    ));
    register_post_type('spa_review', array(
        'labels' => array('name' => 'Đánh giá khách hàng', 'singular_name' => 'Đánh giá', 'add_new_item' => 'Thêm đánh giá (chỉ nhập đánh giá thật, đã được khách đồng ý)'),
        'public' => false, 'show_ui' => true, 'menu_icon' => 'dashicons-star-filled', 'supports' => array('title', 'editor'),
    ));
}
add_action('init', 'beauty_v8_register');

/* ---------- Trường dữ liệu quản trị ---------- */
function beauty_v8_fields($type) {
    $stock = array('con-hang' => 'Còn hàng', 'het-hang' => 'Hết hàng', 'dat-truoc' => 'Đặt trước');
    $yesno = array('0' => 'Không', '1' => 'Có – hiện nhãn "Mẫu"');
    $all = array(
        'spa_product' => array(
            '_p_price' => array('Giá bán (VNĐ)', 'number'),
            '_p_sale' => array('Giá khuyến mãi (VNĐ) – chỉ nhập khi có chương trình thật', 'number'),
            '_p_brand' => array('Thương hiệu', 'text'),
            '_p_size' => array('Dung tích / quy cách', 'text'),
            '_p_sku' => array('Mã SKU', 'text'),
            '_p_stock' => array('Tình trạng', 'select', $stock),
            '_p_skin' => array('Loại da / nhu cầu phù hợp', 'text'),
            '_p_ingredients' => array('Thành phần (theo nhà cung cấp)', 'textarea'),
            '_p_usage' => array('Hướng dẫn sử dụng', 'textarea'),
            '_p_caution' => array('Lưu ý, bảo quản, cảnh báo', 'textarea'),
            '_p_origin' => array('Xuất xứ / nhà sản xuất', 'text'),
            '_p_image_file' => array('Tên tệp ảnh trong theme (assets/images/products/…) – bỏ qua nếu dùng Ảnh đại diện', 'text'),
            '_p_demo' => array('Dữ liệu mẫu?', 'select', $yesno),
        ),
        'spa_service' => array(
            '_s_suitable' => array('Phù hợp với nhu cầu nào (mỗi dòng một ý)', 'textarea'),
            '_s_benefits' => array('Lợi ích và giới hạn thực tế (mỗi dòng một ý)', 'textarea'),
            '_s_caution' => array('Cần cân nhắc / tư vấn trước khi thực hiện (mỗi dòng một ý)', 'textarea'),
            '_s_steps' => array('Các bước thực hiện (mỗi dòng một bước)', 'textarea'),
            '_s_prepare' => array('Chuẩn bị trước khi đến spa (mỗi dòng một ý)', 'textarea'),
            '_s_aftercare' => array('Chăm sóc sau dịch vụ (mỗi dòng một ý)', 'textarea'),
        ),
        'spa_review' => array(
            '_r_rating' => array('Số sao (1–5)', 'number'),
            '_r_service' => array('Dịch vụ / sản phẩm liên quan', 'text'),
            '_r_date' => array('Ngày đánh giá (dd/mm/yyyy)', 'text'),
        ),
    );
    return isset($all[$type]) ? $all[$type] : array();
}

function beauty_v8_meta_boxes() {
    foreach (array('spa_product' => 'Thông tin sản phẩm', 'spa_service' => 'Chi tiết trang dịch vụ', 'spa_review' => 'Thông tin đánh giá') as $type => $title) {
        add_meta_box('beauty_v8_' . $type, $title, 'beauty_v8_meta_box', $type, 'normal', 'high');
    }
}
add_action('add_meta_boxes', 'beauty_v8_meta_boxes');

function beauty_v8_meta_box($post) {
    wp_nonce_field('beauty_v8_meta', 'beauty_v8_nonce');
    foreach (beauty_v8_fields($post->post_type) as $key => $def) {
        $val = get_post_meta($post->ID, $key, true);
        echo '<p><label><strong>' . esc_html($def[0]) . '</strong><br>';
        if ('textarea' === $def[1]) {
            echo '<textarea name="' . esc_attr($key) . '" rows="4" style="width:100%">' . esc_textarea($val) . '</textarea>';
        } elseif ('select' === $def[1]) {
            echo '<select name="' . esc_attr($key) . '">';
            foreach ($def[2] as $v => $label) echo '<option value="' . esc_attr($v) . '"' . selected($val, (string) $v, false) . '>' . esc_html($label) . '</option>';
            echo '</select>';
        } else {
            $type_attr = ('number' === $def[1]) ? 'number" min="0' : 'text';
            echo '<input type="' . $type_attr . '" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '" style="width:100%">';
        }
        echo '</label></p>';
    }
}

function beauty_v8_save($post_id) {
    if (!isset($_POST['beauty_v8_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['beauty_v8_nonce'])), 'beauty_v8_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    foreach (beauty_v8_fields(get_post_type($post_id)) as $key => $def) {
        if (!isset($_POST[$key])) continue;
        $raw = wp_unslash($_POST[$key]);
        if (is_array($raw)) continue;
        if ('number' === $def[1]) $value = ('' === $raw) ? '' : absint($raw);
        elseif ('textarea' === $def[1]) $value = sanitize_textarea_field($raw);
        elseif ('select' === $def[1]) $value = array_key_exists($raw, $def[2]) ? $raw : '';
        else $value = sanitize_text_field($raw);
        update_post_meta($post_id, $key, $value);
    }
}
add_action('save_post', 'beauty_v8_save');

/* ---------- Hiển thị sản phẩm tích hợp ---------- */
function beauty_product_image_url($file) {
    $file = basename((string) $file);
    if (!$file || !file_exists(get_template_directory() . '/assets/images/products/' . $file)) return get_template_directory_uri() . '/assets/images/placeholder.svg';
    return get_template_directory_uri() . '/assets/images/products/' . rawurlencode($file);
}
/* [FIX 1] Nguồn ảnh sản phẩm (đồng bộ theo TÊN, dùng chung cho danh sách, chi tiết, giỏ hàng):
   URL nhập tay -> ảnh đại diện do quản trị chọn -> ảnh vuông của theme theo tên -> ảnh theo tên tệp cũ -> placeholder. */
function beauty_product_image_src($post_id, $size = 'medium_large') {
    $custom = beauty_v9_custom_product_url($post_id);
    if ($custom) return $custom;
    $tid = get_post_thumbnail_id($post_id);
    if ($tid && !beauty_v9_is_legacy_thumb($tid)) {
        $file = get_attached_file($tid);
        if ($file && file_exists($file)) {
            $url = wp_get_attachment_image_url($tid, $size);
            if ($url) return $url;
        }
    }
    $local = beauty_v9_local_product_url($post_id);
    if ($local) return $local;
    return beauty_product_image_url(get_post_meta($post_id, '_p_image_file', true));
}
function beauty_product_image($post_id, $size = 'medium_large', $eager = false) {
    $placeholder = get_template_directory_uri() . '/assets/images/placeholder.svg';
    $local = beauty_v9_local_product_url($post_id);
    $fallback = $local ? $local : $placeholder; // ảnh ngoài/ảnh tải lên hỏng -> ảnh theo tên trong theme (JS beauty-img-recover xử lý)
    $demo = ('1' === get_post_meta($post_id, '_p_demo', true)) ? ' (ảnh minh họa)' : '';
    return '<img class="bv-img-sq" src="' . esc_url(beauty_product_image_src($post_id, $size)) . '" alt="' . esc_attr(get_the_title($post_id) . $demo) . '" width="800" height="800" loading="' . ($eager ? 'eager' : 'lazy') . '" decoding="async" referrerpolicy="no-referrer" data-fallback="' . esc_url($fallback) . '">';
}
function beauty_product_price_html($post_id) {
    $price = (int) get_post_meta($post_id, '_p_price', true);
    $sale = (int) get_post_meta($post_id, '_p_sale', true);
    $tag = ('1' === get_post_meta($post_id, '_p_demo', true)) ? ' <small class="bv-sample">Giá mẫu</small>' : '';
    if ($sale > 0 && $price > $sale) return '<span class="bv-price-now">' . esc_html(beauty_money($sale)) . '</span> <del>' . esc_html(beauty_money($price)) . '</del>' . $tag;
    if ($price > 0) return '<span class="bv-price-now">' . esc_html(beauty_money($price)) . '</span>' . $tag;
    return '<span class="bv-price-contact">Liên hệ để biết giá</span>';
}
function beauty_product_stock_label($post_id) {
    $labels = array('con-hang' => 'Còn hàng', 'het-hang' => 'Hết hàng', 'dat-truoc' => 'Đặt trước');
    $v = get_post_meta($post_id, '_p_stock', true);
    return isset($labels[$v]) ? $labels[$v] : 'Còn hàng';
}

/* Danh sách dòng -> <ul>/<ol> */
function beauty_lines($text, $tag = 'ul') {
    $lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $text)));
    if (!$lines) return '';
    $out = '<' . $tag . ' class="bv-list">';
    foreach ($lines as $line) $out .= '<li>' . esc_html($line) . '</li>';
    return $out . '</' . $tag . '>';
}

/* ---------- Biểu tượng danh mục dịch vụ (SVG nội tuyến) ---------- */
function beauty_cat_icon($slug) {
    $paths = array(
        'cham-soc-da' => '<circle cx="12" cy="12" r="8"/><path d="M8.5 13.5c1 1.6 2.2 2.3 3.5 2.3s2.5-.7 3.5-2.3M9 9.5h.01M15 9.5h.01"/>',
        'mun-va-lam-sach' => '<path d="M12 3c3 4 5.5 6.7 5.5 10a5.5 5.5 0 0 1-11 0C6.5 9.700 9 7 12 3z"/>',
        'massage-thu-gian' => '<path d="M3 15c2-3 4-3 6 0s4 3 6 0 4-3 6 0M3 9c2-3 4-3 6 0s4 3 6 0 4-3 6 0"/>',
        'goi-dau-duong-sinh' => '<path d="M5 10a7 7 0 0 1 14 0v1H5zM8 14v3M12 14v5M16 14v3"/>',
        'triet-long' => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.500 2.500M15.500 15.500L18 18M18 6l-2.500 2.500M8.500 15.500L6 18"/><circle cx="12" cy="12" r="2.200"/>',
        'cham-soc-co-the' => '<path d="M12 20c-4-2-7-5-7-9a4 4 0 0 1 7-2.500A4 4 0 0 1 19 11c0 4-3 7-7 9z"/>',
        'cham-soc-tay-chan' => '<path d="M8 21v-8l-2-3a1.500 1.500 0 0 1 2.500-1.500L10 10V4.500a1.500 1.500 0 0 1 3 0V10l1-5a1.500 1.500 0 0 1 3 .500l-1 7 2-1.500a1.500 1.500 0 0 1 1.800 2.400L15 21z"/>',
    );
    $d = isset($paths[$slug]) ? $paths[$slug] : '<path d="M12 21c-5-2.500-8-6-8-10a4.500 4.500 0 0 1 8-2.800A4.500 4.500 0 0 1 20 11c0 4-3 7.500-8 10z"/>';
    return '<svg class="bv-icon" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $d . '</svg>';
}

/* ---------- JS nội tuyến: không bị plugin tối ưu/cache trì hoãn ---------- */
function beauty_v8_inline_js() {
    $file = get_template_directory() . '/assets/js/theme.js';
    if (!file_exists($file)) return;
    $js = file_get_contents($file);
    if (!$js) return;
    $js = str_ireplace('</script', '<\/script', $js);
    echo '<script id="beauty-theme-js" data-no-optimize="1" data-no-defer="1" data-no-minify="1" data-cfasync="false" data-pagespeed-no-defer nowprocket>' . $js . '</script>' . "\n";
}
add_action('wp_footer', 'beauty_v8_inline_js', 99);

/* ---------- Flush rewrite một lần mỗi phiên bản (sửa lỗi trang dịch vụ/sản phẩm 404 sau khi cập nhật) ---------- */
function beauty_v8_maybe_flush() {
    if (get_option('beauty_rewrite_version') !== BEAUTY_SPA_VERSION) {
        flush_rewrite_rules(false);
        update_option('beauty_rewrite_version', BEAUTY_SPA_VERSION, false);
    }
}
add_action('init', 'beauty_v8_maybe_flush', 999);

/* ---------- Dữ liệu khởi tạo (chạy một lần, không ghi đè nội dung đã có) ---------- */
function beauty_v8_seed() {
    if (get_option('beauty_v8_seed_done')) return;
    $cats = array('lam-sach' => 'Làm sạch', 'duong-am' => 'Dưỡng ẩm', 'chong-nang' => 'Chống nắng', 'cham-soc-da-mat' => 'Chăm sóc da mặt', 'cham-soc-co-the' => 'Chăm sóc cơ thể', 'cham-soc-toc' => 'Chăm sóc tóc');
    foreach ($cats as $slug => $name) {
        if (!term_exists($slug, 'spa_product_cat')) wp_insert_term($name, 'spa_product_cat', array('slug' => $slug));
    }
    $products = array(
        array('Sữa rửa mặt dịu nhẹ Beauty', 'Làm sạch bụi bẩn hằng ngày, phù hợp bước đầu trong quy trình chăm sóc da.', 189000, 'lam-sach', 'sua-rua-mat.jpg', '150 ml'),
        array('Gel rửa mặt tạo bọt Beauty', 'Gel tạo bọt nhẹ cho bước làm sạch buổi sáng và tối.', 169000, 'lam-sach', 'gel-rua-mat.jpg', '150 ml'),
        array('Tẩy trang dịu nhẹ Beauty', 'Làm sạch lớp trang điểm và bụi bẩn trước khi rửa mặt.', 179000, 'lam-sach', 'tay-trang.jpg', '200 ml'),
        array('Nước cân bằng cấp ẩm Beauty', 'Bước dưỡng sau làm sạch, mang lại cảm giác mềm mại.', 219000, 'duong-am', 'toner.jpg', '150 ml'),
        array('Serum cấp ẩm Beauty', 'Serum cấp ẩm cho quy trình dưỡng da tại nhà.', 359000, 'duong-am', 'serum.jpg', '30 ml'),
        array('Kem dưỡng ẩm hằng ngày Beauty', 'Kem dưỡng ẩm dùng ban ngày trong quy trình chăm sóc da.', 289000, 'duong-am', 'kem-duong.jpg', '50 g'),
        array('Kem dưỡng ban đêm Beauty', 'Kem dưỡng dùng buổi tối để duy trì độ ẩm cho da.', 319000, 'duong-am', 'kem-dem.jpg', '50 g'),
        array('Kem chống nắng dùng ban ngày Beauty', 'Sản phẩm chống nắng cho bước cuối của quy trình ban ngày.', 329000, 'chong-nang', 'chong-nang.jpg', '50 ml'),
        array('Mặt nạ dưỡng ẩm Beauty', 'Mặt nạ dưỡng ẩm dùng 1–2 lần mỗi tuần.', 99000, 'cham-soc-da-mat', 'mat-na.jpg', '1 miếng'),
        array('Tinh chất làm dịu da Beauty', 'Tinh chất chăm sóc da, không phải sản phẩm điều trị.', 389000, 'cham-soc-da-mat', 'tinh-chat.jpg', '30 ml'),
        array('Son dưỡng môi Beauty', 'Son dưỡng giúp môi mềm mại.', 89000, 'cham-soc-da-mat', 'son-duong.jpg', '4 g'),
        array('Bộ chăm sóc da cơ bản Beauty', 'Bộ các bước chăm sóc da cơ bản theo liệu trình tại nhà.', 599000, 'cham-soc-da-mat', 'bo-cham-soc.jpg', 'Bộ 3 sản phẩm'),
        array('Sữa dưỡng thể Beauty', 'Dưỡng ẩm cơ thể sau khi tắm hoặc sau liệu trình spa.', 199000, 'cham-soc-co-the', 'sua-duong-the.jpg', '250 ml'),
        array('Tẩy tế bào chết body Beauty', 'Làm sạch tế bào chết bề mặt cho da cơ thể.', 229000, 'cham-soc-co-the', 'tay-te-bao-chet.jpg', '200 g'),
        array('Sữa tắm dưỡng ẩm Beauty', 'Làm sạch cơ thể nhẹ nhàng.', 149000, 'cham-soc-co-the', 'sua-tam.jpg', '300 ml'),
        array('Kem dưỡng tay Beauty', 'Dưỡng ẩm cho da tay.', 119000, 'cham-soc-co-the', 'kem-tay.jpg', '50 g'),
        array('Dầu gội chăm sóc tóc Beauty', 'Dầu gội cho quy trình chăm sóc tóc và da đầu tại nhà.', 159000, 'cham-soc-toc', 'dau-goi.jpg', '300 ml'),
        array('Dầu xả mềm mượt Beauty', 'Dầu xả dùng sau gội để tóc mềm mượt hơn.', 169000, 'cham-soc-toc', 'dau-xa.jpg', '300 ml'),
    );
    foreach ($products as $i => $p) {
        if (beauty_find_post_by_title($p[0], OBJECT, 'spa_product')) continue;
        $id = wp_insert_post(array(
            'post_type' => 'spa_product', 'post_status' => 'publish', 'post_title' => $p[0], 'post_excerpt' => $p[1], 'menu_order' => $i,
            'post_content' => '<p>' . esc_html($p[1]) . '</p><p><em>Đây là sản phẩm minh họa cho website. Hãy thay bằng sản phẩm thật, thành phần, xuất xứ và giá đã được xác nhận trước khi mở bán.</em></p>',
        ), true);
        if (is_wp_error($id) || !$id) continue;
        update_post_meta($id, '_p_price', $p[2]);
        update_post_meta($id, '_p_brand', 'Beauty (mẫu)');
        update_post_meta($id, '_p_size', $p[5]);
        update_post_meta($id, '_p_stock', 'con-hang');
        update_post_meta($id, '_p_image_file', $p[4]);
        update_post_meta($id, '_p_demo', '1');
        update_post_meta($id, '_p_usage', "Đọc hướng dẫn trên nhãn sản phẩm thực tế trước khi dùng.\nSử dụng đúng lượng khuyến nghị.");
        update_post_meta($id, '_p_caution', "Ngưng dùng nếu có dấu hiệu kích ứng.\nVới da nhạy cảm, nên thử trên vùng da nhỏ trước.\nBảo quản nơi khô ráo, tránh ánh nắng trực tiếp.");
        $term = get_term_by('slug', $p[3], 'spa_product_cat');
        if ($term) wp_set_object_terms($id, array((int) $term->term_id), 'spa_product_cat');
    }
    $faqs = array(
        array('Làm thế nào để đặt lịch?', 'Bạn chọn dịch vụ, ngày và giờ mong muốn trong form Đặt lịch, hoặc gọi hotline. Yêu cầu của bạn được ghi nhận và nhân viên sẽ liên hệ để xác nhận; lịch chỉ được giữ sau khi spa xác nhận với bạn.'),
        array('Có cần đặt lịch trước không?', 'Bạn nên đặt trước để spa sắp xếp thời gian phù hợp. Khung giờ chỉ được giữ sau khi spa xác nhận.'),
        array('Có thể thay đổi hoặc hủy lịch không?', 'Bạn vui lòng liên hệ hotline, Zalo hoặc Messenger càng sớm càng tốt. [Chủ spa cần cập nhật quy định thay đổi/hủy lịch cụ thể tại đây.]'),
        array('Nên chuẩn bị gì trước khi đến spa?', 'Bạn nên cho nhân viên biết tình trạng da, dị ứng, thuốc đang dùng hoặc vấn đề sức khỏe liên quan trước khi bắt đầu để được tư vấn phù hợp. Mỗi dịch vụ có mục chuẩn bị riêng trong trang chi tiết.'),
        array('Thời lượng mỗi dịch vụ là bao lâu?', 'Thời lượng dự kiến được ghi trên từng dịch vụ và trong Bảng giá. Thời gian thực tế có thể thay đổi theo tình trạng của từng khách.'),
        array('Làm thế nào để chọn dịch vụ phù hợp?', 'Hãy mô tả nhu cầu của bạn qua form Liên hệ hoặc hotline, nhân viên sẽ tư vấn các lựa chọn. Với vấn đề da liễu kéo dài, bạn nên hỏi ý kiến bác sĩ.'),
        array('Có thể mua sản phẩm trực tuyến không?', 'Bạn có thể xem sản phẩm trên website và đặt mua qua hotline/Zalo. Giỏ hàng và thanh toán trực tuyến chỉ hoạt động khi chủ website cài và cấu hình WooCommerce.'),
        array('Chính sách giao hàng và đổi trả như thế nào?', '[Chủ spa cần cập nhật chính sách giao hàng và đổi trả chính thức tại đây.]'),
    );
    foreach ($faqs as $i => $f) {
        if (beauty_find_post_by_title($f[0], OBJECT, 'spa_faq')) continue;
        wp_insert_post(array('post_type' => 'spa_faq', 'post_status' => 'publish', 'post_title' => $f[0], 'post_content' => '<p>' . esc_html($f[1]) . '</p>', 'menu_order' => $i));
    }
    update_option('beauty_v8_seed_done', 1, false);
}
add_action('init', 'beauty_v8_seed', 40);

/** Điền mục chuẩn bị/chăm sóc sau/lưu ý dạng chung cho dịch vụ chưa có nội dung; chỉ điền trường còn trống. */
function beauty_v8_service_defaults() {
    if (get_option('beauty_v8_service_meta_done')) return;
    $defaults = array(
        '_s_caution' => "Hãy cho nhân viên biết nếu bạn đang mang thai, có bệnh da liễu, dị ứng hoặc đang điều trị y tế.\nVới vấn đề sức khỏe hoặc da kéo dài, nên hỏi ý kiến bác sĩ trước.",
        '_s_prepare' => "Đến đúng giờ hẹn hoặc sớm vài phút để trao đổi nhu cầu.\nThông báo tình trạng da, dị ứng và sản phẩm bạn đang dùng.\nMặc trang phục thoải mái.",
        '_s_aftercare' => "Uống đủ nước và nghỉ ngơi hợp lý sau dịch vụ.\nTránh tác động mạnh lên vùng vừa được chăm sóc theo hướng dẫn của nhân viên.\nLiên hệ spa nếu có cảm giác khó chịu kéo dài.",
    );
    $services = get_posts(array('post_type' => 'spa_service', 'numberposts' => -1, 'post_status' => array('publish', 'draft')));
    foreach ($services as $svc) {
        foreach ($defaults as $key => $text) {
            if ('' === trim((string) get_post_meta($svc->ID, $key, true))) update_post_meta($svc->ID, $key, $text);
        }
    }
    update_option('beauty_v8_service_meta_done', 1, false);
}
add_action('init', 'beauty_v8_service_defaults', 90);

/** Phân trang đồng bộ giao diện (1 2 … Trước / Sau). */
function beauty_pagination() {
    the_posts_pagination(array('mid_size' => 1, 'end_size' => 1, 'prev_text' => '‹ Trước', 'next_text' => 'Sau ›', 'screen_reader_text' => 'Phân trang', 'aria_label' => 'Phân trang'));
}
