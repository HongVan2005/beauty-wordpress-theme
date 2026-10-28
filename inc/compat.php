<?php
/**
 * Tương thích ngược, kiểm tra ảnh, album ảnh quản lý được trong Admin.
 */
if (!defined('ABSPATH')) exit;

/* ---------- 1. Ảnh cũ đã bị xóa khỏi theme -> ảnh thay thế (không sửa dữ liệu trong DB) ---------- */
function beauty_legacy_image_map() {
    return array(
        'spa-hero.png' => 'spa-album-facial.jpg',
        'spa-promo.png' => 'spa-album-products.jpg',
        'spa-banner-facial.png' => 'spa-album-facial.jpg',
        'spa-banner-relax.png' => 'spa-album-massage.jpg',
        'spa-banner-relax.jpg' => 'spa-album-massage.jpg',
        'spa-banner-treatment.png' => 'spa-album-room.jpg',
        'spa-banner-treatment.jpg' => 'spa-album-room.jpg',
        'spa-gallery-care.jpg' => 'spa-service-cleansing.jpg',
        'spa-gallery-detail.jpg' => 'spa-album-consultation.jpg',
        'spa-gallery-room.jpg' => 'spa-album-room.jpg',
        'spa-gallery-therapy.jpg' => 'spa-album-massage.jpg',
    );
}
function beauty_fix_legacy_images($html) {
    if (!is_string($html) || false === strpos($html, 'spa-')) return $html;
    return strtr($html, beauty_legacy_image_map());
}
add_filter('the_content', 'beauty_fix_legacy_images', 5);
add_filter('widget_text', 'beauty_fix_legacy_images', 5);
add_filter('widget_block_content', 'beauty_fix_legacy_images', 5);

/* Trang Hình ảnh còn nội dung cũ trỏ vào ảnh đã xóa -> chuyển sang shortcode album mới (chỉ khi nội dung cũ đã hỏng) */
function beauty_repair_gallery_page() {
    if (get_option('beauty_gallery_page_repaired')) return;
    $page = get_page_by_path('hinh-anh');
    if ($page && false === strpos($page->post_content, '[beauty_gallery_page') && preg_match('/spa-(hero|promo|banner|gallery)/', $page->post_content)) {
        wp_update_post(array('ID' => $page->ID, 'post_content' => '[beauty_gallery_page]'));
    }
    update_option('beauty_gallery_page_repaired', 1, false);
}
add_action('init', 'beauty_repair_gallery_page', 92);

/* ---------- 2. Công cụ quét ảnh lỗi: Công cụ → Kiểm tra ảnh Beauty ---------- */
function beauty_url_to_path($url) {
    $url = strtok(html_entity_decode((string) $url), '?#');
    $u = wp_upload_dir();
    if (!empty($u['baseurl']) && 0 === strpos($url, $u['baseurl'])) return $u['basedir'] . substr($url, strlen($u['baseurl']));
    $t = get_template_directory_uri();
    if (0 === strpos($url, $t)) return get_template_directory() . substr($url, strlen($t));
    return '';
}
function beauty_image_check_menu() {
    add_management_page('Kiểm tra ảnh Beauty', 'Kiểm tra ảnh Beauty', 'manage_options', 'beauty-image-check', 'beauty_image_check_page');
}
add_action('admin_menu', 'beauty_image_check_menu');
function beauty_image_check_page() {
    if (!current_user_can('manage_options')) return;
    echo '<div class="wrap"><h1>Kiểm tra ảnh Beauty</h1><p>Quét tối đa 300 nội dung đã đăng, tìm ảnh nằm trong website này nhưng không còn tệp trên máy chủ. Ảnh ở website khác không được kiểm tra.</p>';
    $posts = get_posts(array('post_type' => 'any', 'post_status' => 'publish', 'numberposts' => 300));
    $missing = array();
    foreach ($posts as $p) {
        $map = beauty_legacy_image_map();
        $content = strtr($p->post_content, $map);
        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m)) {
            foreach ($m[1] as $src) { $path = beauty_url_to_path($src); if ($path && !file_exists($path)) $missing[] = array($p, 'Ảnh trong nội dung', $src); }
        }
        $tid = get_post_thumbnail_id($p->ID);
        if ($tid) { $file = get_attached_file($tid); if ($file && !file_exists($file)) $missing[] = array($p, 'Ảnh đại diện', basename($file)); }
        elseif (in_array($p->post_type, array('post', 'spa_service', 'spa_promo'), true)) $missing[] = array($p, 'Chưa có ảnh đại diện (đang dùng ảnh dự phòng)', '');
    }
    if (!$missing) { echo '<p><strong>Không phát hiện ảnh lỗi.</strong></p></div>'; return; }
    echo '<table class="widefat striped"><thead><tr><th>Nội dung</th><th>Loại</th><th>Vấn đề</th><th>Chi tiết</th></tr></thead><tbody>';
    foreach ($missing as $row) {
        echo '<tr><td><a href="' . esc_url(get_edit_post_link($row[0]->ID)) . '">' . esc_html($row[0]->post_title) . '</a></td><td>' . esc_html($row[0]->post_type) . '</td><td>' . esc_html($row[1]) . '</td><td>' . esc_html($row[2]) . '</td></tr>';
    }
    echo '</tbody></table></div>';
}

/* ---------- 3. Album ảnh quản lý trong Admin ---------- */
function beauty_gallery_register() {
    register_post_type('spa_gallery', array(
        'labels' => array('name' => 'Album ảnh', 'singular_name' => 'Ảnh album', 'add_new_item' => 'Thêm ảnh vào album', 'all_items' => 'Tất cả ảnh', 'featured_image' => 'Ảnh (bắt buộc)', 'set_featured_image' => 'Chọn ảnh'),
        'public' => false, 'show_ui' => true, 'menu_icon' => 'dashicons-format-gallery', 'supports' => array('title', 'excerpt', 'thumbnail', 'page-attributes'),
    ));
    register_taxonomy('spa_album', 'spa_gallery', array(
        'labels' => array('name' => 'Album', 'singular_name' => 'Album', 'add_new_item' => 'Thêm album'),
        'public' => false, 'show_ui' => true, 'hierarchical' => true, 'show_admin_column' => true,
    ));
}
add_action('init', 'beauty_gallery_register');

function beauty_gallery_album_names() {
    return array('cham-soc-da' => 'Chăm sóc da', 'massage' => 'Massage & thư giãn', 'goi-dau' => 'Gội đầu dưỡng sinh', 'khong-gian' => 'Không gian & tư vấn', 'san-pham' => 'Sản phẩm', 'co-the' => 'Chăm sóc cơ thể');
}
function beauty_gallery_seed_terms() {
    if (get_option('beauty_gallery_terms_done')) return;
    foreach (beauty_gallery_album_names() as $slug => $name) { if (!term_exists($slug, 'spa_album')) wp_insert_term($name, 'spa_album', array('slug' => $slug)); }
    update_option('beauty_gallery_terms_done', 1, false);
}
add_action('init', 'beauty_gallery_seed_terms', 50);

/** Ưu tiên ảnh do quản trị đăng trong Album ảnh; nếu chưa có thì dùng ảnh minh họa của theme. */
function beauty_gallery_items() {
    $items = array();
    $posts = get_posts(array('post_type' => 'spa_gallery', 'post_status' => 'publish', 'numberposts' => 80, 'orderby' => 'menu_order date', 'order' => 'ASC'));
    foreach ($posts as $p) {
        $tid = get_post_thumbnail_id($p->ID);
        $full = $tid ? wp_get_attachment_image_url($tid, 'large') : '';
        if (!$full) continue;
        $thumb = wp_get_attachment_image_url($tid, 'medium_large');
        $terms = get_the_terms($p->ID, 'spa_album');
        $slug = 'khac'; $label = 'Khác';
        if ($terms && !is_wp_error($terms)) { $slug = $terms[0]->slug; $label = $terms[0]->name; }
        $alt = get_post_meta($tid, '_wp_attachment_image_alt', true);
        $items[] = array('full' => $full, 'thumb' => $thumb ? $thumb : $full, 'title' => $p->post_title, 'desc' => $p->post_excerpt, 'label' => $label, 'slug' => $slug, 'alt' => $alt ? $alt : $p->post_title);
    }
    if ($items) return $items;
    $names = beauty_gallery_album_names();
    foreach (beauty_v6_gallery_items() as $it) {
        $url = beauty_theme_image_url($it[0]);
        $items[] = array('full' => $url, 'thumb' => $url, 'title' => $it[1], 'desc' => $it[2], 'label' => isset($names[$it[4]]) ? $names[$it[4]] : $it[3], 'slug' => $it[4], 'alt' => $it[5]);
    }
    return $items;
}
