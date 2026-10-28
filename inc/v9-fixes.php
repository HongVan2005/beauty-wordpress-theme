<?php
/**
 * Beauty V9 — 4 bản sửa:
 *  [FIX 1] Ảnh sản phẩm đúng tên, tỷ lệ 1:1 (trang Sản phẩm + Giỏ hàng, cả giỏ tích hợp lẫn WooCommerce)
 *  [FIX 2] Ảnh trang Giới thiệu bị trống
 *  [FIX 3] Trang Hình ảnh -> Album + lightbox (tối đa 16 ảnh, loại ảnh trùng)
 *  [FIX 4] Thẻ Gói dịch vụ / Combo có ảnh, quyền lợi, giá lớn, 2 nút cân đối
 */
if (!defined('ABSPATH')) exit;

/* =====================================================================
 * [FIX 1] ẢNH SẢN PHẨM
 * Thứ tự ưu tiên ảnh:
 *   1) URL nhập tay ở ô "Ảnh sản phẩm (URL)" trong trang sửa sản phẩm (dán link Unsplash/Pexels vào đây)
 *   2) Ảnh đại diện do quản trị tự chọn (không phải ảnh mẫu cũ của theme)
 *   3) Ảnh minh họa vuông của theme, chọn theo TÊN sản phẩm (assets/images/products/sq-*.jpg)
 *   4) placeholder.svg
 * ===================================================================== */

/** Từ khóa (không dấu) trong tên sản phẩm -> tên tệp ảnh. Từ khóa cụ thể đặt trước. */
function beauty_v9_product_keyword_map() {
    return array(
        'tay te bao chet' => 'tay-te-bao-chet', 'tay trang' => 'tay-trang',
        'gel rua mat' => 'gel-rua-mat', 'sua rua mat' => 'sua-rua-mat', 'face wash' => 'sua-rua-mat',
        'nuoc can bang' => 'toner', 'toner' => 'toner',
        'serum' => 'serum', 'tinh chat' => 'tinh-chat',
        'kem chong nang' => 'chong-nang', 'chong nang' => 'chong-nang', 'sunscreen' => 'chong-nang',
        'kem duong ban dem' => 'kem-dem', 'kem dem' => 'kem-dem',
        'kem duong tay' => 'kem-tay', 'kem tay' => 'kem-tay',
        'kem duong am' => 'kem-duong', 'kem duong' => 'kem-duong', 'moisturizer' => 'kem-duong',
        'mat na' => 'mat-na', 'son duong' => 'son-duong',
        'bo cham soc' => 'bo-cham-soc',
        'sua duong the' => 'sua-duong-the', 'sua tam' => 'sua-tam',
        'dau goi' => 'dau-goi', 'dau xa' => 'dau-xa',
    );
}
/** Tên sản phẩm -> slug ảnh (hoặc ''). */
function beauty_v9_product_slug($post_id) {
    $title = strtolower(remove_accents(wp_strip_all_tags(get_the_title($post_id))));
    $title = str_replace('đ', 'd', $title);
    foreach (beauty_v9_product_keyword_map() as $kw => $slug) {
        if (false !== strpos($title, $kw)) return $slug;
    }
    return '';
}
/** URL ảnh vuông của theme theo tên sản phẩm, hoặc ''. */
function beauty_v9_local_product_url($post_id) {
    $slug = beauty_v9_product_slug($post_id);
    if (!$slug) return '';
    $file = 'sq-' . $slug . '.jpg';
    if (!file_exists(get_template_directory() . '/assets/images/products/' . $file)) return '';
    return get_template_directory_uri() . '/assets/images/products/' . $file;
}
/** Ảnh đại diện cũ do theme tự gán (tệp tên như sua-rua-mat.jpg) -> coi là ảnh mẫu, được phép thay. */
function beauty_v9_is_legacy_thumb($thumb_id) {
    $file = get_attached_file($thumb_id);
    if (!$file) return true;
    $base = strtolower(basename($file));
    return (bool) preg_match('/^(bo-cham-soc|chong-nang|dau-goi|dau-xa|gel-rua-mat|kem-dem|kem-duong|kem-tay|mat-na|serum|son-duong|sua-duong-the|sua-rua-mat|sua-tam|tay-te-bao-chet|tay-trang|tinh-chat|toner)(-\d+)?\.(jpe?g|png|webp)$/', $base);
}
/** Ảnh THẬT: chép tệp vào wp-content/uploads/beauty-products/ đặt tên theo slug (vd: sua-rua-mat.jpg) hoặc theo đường dẫn bài (post_name). */
function beauty_v9_upload_product_url($post_id) {
    $u = wp_upload_dir();
    if (!empty($u['error'])) return '';
    $names = array_filter(array(beauty_v9_product_slug($post_id), get_post_field('post_name', $post_id)));
    foreach ($names as $n) {
        foreach (array('jpg', 'jpeg', 'png', 'webp') as $ext) {
            if (file_exists($u['basedir'] . '/beauty-products/' . $n . '.' . $ext)) return $u['baseurl'] . '/beauty-products/' . rawurlencode($n) . '.' . $ext;
        }
    }
    return '';
}
/** URL ảnh nhập tay (Unsplash/Pexels...) hoặc ảnh thật trong thư mục beauty-products. */
function beauty_v9_custom_product_url($post_id) {
    $u = trim((string) get_post_meta($post_id, '_p_image_url', true));
    if (0 === strpos($u, 'https://')) return $u;
    $up = beauty_v9_upload_product_url($post_id);
    if ($up) return $up;
    /* [FIX 4] Ảnh thật chép vào theme: assets/images/products/real/<slug>.jpg|png|webp */
    $slug_real = beauty_v9_product_slug($post_id);
    if ($slug_real) {
        foreach (array('jpg', 'jpeg', 'png', 'webp') as $ext_real) {
            if (file_exists(get_template_directory() . '/assets/images/products/real/' . $slug_real . '.' . $ext_real)) {
                return get_template_directory_uri() . '/assets/images/products/real/' . $slug_real . '.' . $ext_real;
            }
        }
    }
    /* [FIX 4] Ảnh thật mặc định theo loại sản phẩm (xem bảng bên dưới) */
    return beauty_v9_remote_product_url($post_id);
}

/* =====================================================================
 * [FIX 4] BẢNG ẢNH THẬT THEO LOẠI SẢN PHẨM (Unsplash / Pexels / GitHub raw)
 * Dán link ảnh vuông vào đúng dòng. Để '' = dùng ảnh minh họa của theme.
 * Link phải là https. Dán xong, ảnh tự đồng bộ ở trang chủ, danh sách, chi tiết, giỏ hàng.
 * Tìm ảnh: https://unsplash.com/s/photos/face-wash  |  /serum-bottle  |  /moisturizer-jar  |  /scrub  |  /toner-bottle
 * Định dạng gợi ý: https://images.unsplash.com/photo-XXXXXXXX?auto=format&fit=crop&w=800&h=800&q=80
 * ===================================================================== */
/* [FIX 4] Thư mục GitHub chứa ảnh thật. Ví dụ:
 * define('BEAUTY_PRODUCT_IMG_BASE', 'https://raw.githubusercontent.com/TEN-BAN/TEN-REPO/main/products');
 * Để trống = không dùng. Có thể khai báo trong wp-config.php thay vì sửa ở đây. */
if (!defined('BEAUTY_PRODUCT_IMG_BASE')) define('BEAUTY_PRODUCT_IMG_BASE', '');
function beauty_v9_remote_image_map() {
    /* Ảnh thật từ Pexels (miễn phí, dùng thương mại, không cần ghi nguồn). Ảnh nào hỏng -> tự quay về ảnh minh họa. */
    return array(
        'sua-rua-mat' => 'https://images.pexels.com/photos/19080517/pexels-photo-19080517.jpeg?auto=compress&cs=tinysrgb&w=800&h=800&fit=crop', // Sữa rửa mặt - tuýp mỹ phẩm trắng, nền trắng (Volker Thimm)
        'gel-rua-mat' => 'https://images.pexels.com/photos/2587177/pexels-photo-2587177.jpeg?auto=compress&cs=tinysrgb&w=800&h=800&fit=crop', // Gel rửa mặt tạo bọt - chai foaming cleanser (Venus HD Make-up)
        'tay-trang' => 'https://images.pexels.com/photos/15569182/pexels-photo-15569182.jpeg?auto=compress&cs=tinysrgb&w=800&h=800&fit=crop', // Tẩy trang - chai có vòi bơm đặt trên rêu (Pexels)
        'toner' => 'https://images.pexels.com/photos/8131585/pexels-photo-8131585.jpeg?auto=compress&cs=tinysrgb&w=800&h=800&fit=crop', // Nước cân bằng - chai thủy tinh toner trong tay, nền hồng (Ron Lach)
        'serum' => 'https://images.pexels.com/photos/30229017/pexels-photo-30229017.jpeg?auto=compress&cs=tinysrgb&w=800&h=800&fit=crop', // Serum - chai nâu có ống nhỏ giọt, nền đào (Ani Coloca)
        'tinh-chat' => 'https://images.pexels.com/photos/34939709/pexels-photo-34939709.jpeg?auto=compress&cs=tinysrgb&w=800&h=800&fit=crop', // Tinh chất - chai serum vitamin C tối giản (Daniel & Hannah Snipes)
        'kem-duong' => 'https://images.pexels.com/photos/13794471/pexels-photo-13794471.jpeg?auto=compress&cs=tinysrgb&w=800&h=800&fit=crop', // Kem dưỡng ẩm - hũ kem mở nắp trên đá cẩm thạch (Brian Phetmeuangmay)
        'kem-dem' => 'https://images.pexels.com/photos/8490089/pexels-photo-8490089.jpeg?auto=compress&cs=tinysrgb&w=800&h=800&fit=crop', // Kem dưỡng ban đêm - hũ kem trắng trên mặt gỗ (PNW Production)
        'chong-nang' => 'https://images.pexels.com/photos/16378485/pexels-photo-16378485.jpeg?auto=compress&cs=tinysrgb&w=800&h=800&fit=crop', // Kem chống nắng - chai SPF 50 trên cát (mearlywan)
    );
}
function beauty_v9_remote_product_url($post_id) {
    $slug = beauty_v9_product_slug($post_id);
    if (!$slug) return '';
    $map = beauty_v9_remote_image_map();
    $url = isset($map[$slug]) ? trim((string) $map[$slug]) : '';
    if (0 === strpos($url, 'https://')) return $url;
    /* Cách nhanh: đưa ảnh thật lên GitHub, đặt tên theo slug (serum.jpg, sua-rua-mat.jpg...) rồi điền BEAUTY_PRODUCT_IMG_BASE */
    if (defined('BEAUTY_PRODUCT_IMG_BASE') && 0 === strpos((string) BEAUTY_PRODUCT_IMG_BASE, 'https://')) {
        return rtrim(BEAUTY_PRODUCT_IMG_BASE, '/') . '/' . $slug . '.jpg';
    }
    return '';
}
/** Sản phẩm này có cần dùng ảnh theo tên (thay ảnh mặc định cũ) không? */
function beauty_v9_has_override($post_id) {
    if (beauty_v9_custom_product_url($post_id)) return true;
    if (!beauty_v9_local_product_url($post_id)) return false;
    $tid = get_post_thumbnail_id($post_id);
    return (!$tid || beauty_v9_is_legacy_thumb($tid));
}

/** Meta box: ô nhập URL ảnh sản phẩm. */
function beauty_v9_add_metaboxes() {
    foreach (array('spa_product', 'product') as $pt) add_meta_box('beauty_v9_prod_img', 'Ảnh sản phẩm (URL, tuỳ chọn)', 'beauty_v9_prod_img_box', $pt, 'side', 'default');
    add_meta_box('beauty_v9_pk', 'Combo: ảnh &amp; quyền lợi hiển thị', 'beauty_v9_pk_box', 'spa_package', 'normal', 'high');
}
add_action('add_meta_boxes', 'beauty_v9_add_metaboxes');
function beauty_v9_prod_img_box($post) {
    wp_nonce_field('beauty_v9_save', 'beauty_v9_nonce');
    echo '<p><input type="url" name="_p_image_url" class="widefat" placeholder="https://images.unsplash.com/..." value="' . esc_attr(get_post_meta($post->ID, '_p_image_url', true)) . '"></p><p class="description">Để trống = dùng ảnh minh họa vuông theo tên sản phẩm. Chỉ nhận link https. Nên chọn ảnh vuông (1:1).</p>';
}
function beauty_v9_pk_box($post) {
    wp_nonce_field('beauty_v9_save', 'beauty_v9_nonce');
    echo '<p><label><strong>URL ảnh combo</strong> (để trống = dùng ảnh đại diện hoặc ảnh mẫu của theme)</label><input type="url" name="_pk_image_url" class="widefat" placeholder="https://images.unsplash.com/..." value="' . esc_attr(get_post_meta($post->ID, '_pk_image_url', true)) . '"></p>';
    echo '<p><label><strong>Quyền lợi / dịch vụ bao gồm</strong> (mỗi dòng 1 mục, nên 3–4 mục)</label><textarea name="_pk_includes" rows="5" class="widefat">' . esc_textarea(get_post_meta($post->ID, '_pk_includes', true)) . '</textarea></p>';
}
function beauty_v9_save_meta($post_id) {
    if (!isset($_POST['beauty_v9_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['beauty_v9_nonce'])), 'beauty_v9_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    foreach (array('_p_image_url', '_pk_image_url') as $k) {
        if (isset($_POST[$k])) update_post_meta($post_id, $k, esc_url_raw(wp_unslash($_POST[$k]), array('https')));
    }
    if (isset($_POST['_pk_includes'])) update_post_meta($post_id, '_pk_includes', sanitize_textarea_field(wp_unslash($_POST['_pk_includes'])));
}
add_action('save_post', 'beauty_v9_save_meta');

/** WooCommerce: giỏ hàng, mini-cart, checkout, danh sách -> cùng bộ ảnh theo tên. */
function beauty_v9_wc_cart_thumb($thumb, $cart_item, $cart_item_key = '') {
    $id = !empty($cart_item['product_id']) ? (int) $cart_item['product_id'] : 0;
    if ($id && beauty_v9_has_override($id)) return beauty_product_image($id, 'thumbnail');
    return $thumb;
}
add_filter('woocommerce_cart_item_thumbnail', 'beauty_v9_wc_cart_thumb', 20, 3);
function beauty_v9_wc_product_image($image, $product = null) {
    if ($product && is_object($product) && method_exists($product, 'get_id')) {
        $id = (int) $product->get_id();
        if ($id && beauty_v9_has_override($id)) return beauty_product_image($id, 'woocommerce_thumbnail');
    }
    return $image;
}
add_filter('woocommerce_product_get_image', 'beauty_v9_wc_product_image', 20, 2);

/* =====================================================================
 * [FIX 2] TRANG GIỚI THIỆU — ẢNH BỊ TRỐNG
 * Nội dung trang đã lưu trong DB nên đường dẫn ảnh cũ bị hỏng. Thay lúc hiển thị, không cần sửa DB.
 * Đổi ảnh: dán link khác vào hàm lọc 'beauty_about_hero_url' hoặc sửa $url bên dưới.
 * ===================================================================== */
function beauty_v9_fix_about_hero($content) {
    if (false === strpos($content, 'beauty-about-hero-image')) return $content;
    $url   = apply_filters('beauty_about_hero_url', 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1200&h=1500&q=80');
    $local = beauty_theme_image_url('spa-album-room.jpg'); // ảnh dự phòng nằm trong theme
    $html  = '<div class="beauty-about-hero-image"><img src="' . esc_url($url) . '" alt="Không gian thư giãn tại Beauty Spa" width="1200" height="1500" loading="eager" decoding="async" referrerpolicy="no-referrer" data-fallback="' . esc_url($local) . '"></div>';
    $out = preg_replace('#<div class="beauty-about-hero-image">.*?</div>#s', $html, $content, 1);
    return $out ? $out : $content;
}
add_filter('the_content', 'beauty_v9_fix_about_hero', 25);

/* =====================================================================
 * [FIX 3] TRANG HÌNH ẢNH -> ALBUM + LIGHTBOX
 * ===================================================================== */
/** Ảnh THẬT: chép ảnh vào wp-content/uploads/beauty-album/<ten-album>/ (vd: cham-soc-da, massage, khong-gian) là tự hiện trong album. */
function beauty_v9_folder_items() {
    $u = wp_upload_dir(); $items = array();
    if (!empty($u['error'])) return $items;
    $root = $u['basedir'] . '/beauty-album';
    if (!is_dir($root)) return $items;
    $names = beauty_gallery_album_names();
    foreach (glob($root . '/*', GLOB_ONLYDIR) as $dir) {
        $slug = sanitize_title(basename($dir));
        $label = isset($names[$slug]) ? $names[$slug] : ucfirst(str_replace('-', ' ', $slug));
        $files = glob($dir . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
        if (!$files) continue;
        natsort($files);
        foreach ($files as $f) {
            $url = $u['baseurl'] . '/beauty-album/' . rawurlencode(basename($dir)) . '/' . rawurlencode(basename($f));
            $t = ucfirst(str_replace(array('-', '_'), ' ', pathinfo($f, PATHINFO_FILENAME)));
            $items[] = array('full' => $url, 'thumb' => $url, 'title' => $t, 'desc' => '', 'label' => $label, 'slug' => $slug, 'alt' => $label . ' - ' . $t);
        }
    }
    return $items;
}
/** Ảnh album: thư mục beauty-album + Album ảnh trong Admin + ảnh minh họa của theme. Bỏ ảnh trùng và ảnh mất tệp. */
function beauty_v9_gallery_items() {
    $max = (int) apply_filters('beauty_gallery_max', 48);
    $raw = array_merge(beauty_v9_folder_items(), beauty_gallery_items());
    $seen = array(); $out = array();
    foreach ($raw as $it) {
        $path = beauty_url_to_path($it['full']);
        if ($path && !file_exists($path)) continue;                     // tệp đã mất trên máy chủ -> bỏ, không hiện ô trống
        $key = basename(strtok($it['full'], '?'));
        $key = preg_replace('/-\d+x\d+(?=\.[a-z]+$)/i', '', $key);
        $key = preg_replace('/-\d+(?=\.[a-z]+$)/i', '', $key);
        if (isset($seen[$key])) continue;
        $seen[$key] = 1; $out[] = $it;
        if (count($out) >= $max) break;
    }
    if (!$out) { // mọi ảnh đều hỏng -> dùng ảnh minh họa trong theme
        $names = beauty_gallery_album_names(); $seen = array();
        foreach (beauty_v6_gallery_items() as $it) {
            if (isset($seen[$it[0]])) continue; $seen[$it[0]] = 1;
            $url = beauty_theme_image_url($it[0]);
            $out[] = array('full' => $url, 'thumb' => $url, 'title' => $it[1], 'desc' => $it[2], 'label' => isset($names[$it[4]]) ? $names[$it[4]] : $it[3], 'slug' => $it[4], 'alt' => $it[5]);
        }
    }
    return $out;
}
function beauty_v9_gallery_shortcode() {
    wp_enqueue_script('beauty-albums', get_template_directory_uri() . '/assets/js/beauty-albums.js', array(), (string) filemtime(get_template_directory() . '/assets/js/beauty-albums.js'), true);
    $albums = array();
    foreach (beauty_v9_gallery_items() as $it) {
        $s = $it['slug'];
        if (!isset($albums[$s])) $albums[$s] = array('label' => $it['label'], 'images' => array());
        $albums[$s]['images'][] = array('full' => $it['full'], 'thumb' => $it['thumb'], 'caption' => $it['title'], 'alt' => $it['alt']);
    }
    ob_start(); ?>
    <div class="beauty-gallery-page bva-wrap">
      <div class="beauty-gallery-lead"><span class="eyebrow">BEAUTY SPA · PHOTO ALBUM</span><h2>Một chút bình yên dành riêng cho bạn</h2><p>Chọn một album để xem ảnh chi tiết. Ảnh hiện là ảnh minh họa; quản trị viên thay bằng ảnh thật tại Admin → Album ảnh.</p></div>
      <div class="bva-grid">
      <?php $i = 0; foreach ($albums as $slug => $al) : $cover = $al['images'][0]; $n = count($al['images']); ?>
        <a class="bva-card<?php echo 0 === $i ? ' bva-card--big' : ''; ?>" href="<?php echo esc_url($cover['full']); ?>" data-album="<?php echo esc_attr($slug); ?>" data-title="<?php echo esc_attr($al['label']); ?>" data-images="<?php echo esc_attr(wp_json_encode($al['images'])); ?>" aria-haspopup="dialog" aria-label="<?php echo esc_attr('Mở album ' . $al['label'] . ' (' . $n . ' ảnh)'); ?>">
          <img src="<?php echo esc_url($cover['thumb']); ?>" alt="<?php echo esc_attr($cover['alt']); ?>" width="800" height="600" loading="<?php echo $i < 2 ? 'eager' : 'lazy'; ?>" decoding="async" data-fallback="<?php echo esc_url(beauty_theme_image_url('spa-album-room.jpg')); ?>">
          <span class="bva-info"><span class="bva-name"><?php echo esc_html($al['label']); ?></span><span class="bva-count"><?php echo (int) $n; ?> ảnh</span></span>
          <span class="bva-open" aria-hidden="true">Xem album ›</span>
        </a>
      <?php $i++; endforeach; ?>
      </div>
      <section class="beauty-gallery-bottom"><div><span class="eyebrow">YOUR MOMENT OF BEAUTY</span><h2>Hẹn bạn tại Beauty Spa</h2><p>Hãy chia sẻ điều bạn đang tìm kiếm — một làn da được chăm sóc, phút giây thư giãn hay một khoảng nghỉ dành riêng cho bản thân.</p></div><a class="btn" href="<?php echo esc_url(home_url('/dat-lich/')); ?>">Liên hệ đặt lịch</a></section>
    </div>
    <?php return ob_get_clean();
}
remove_shortcode('beauty_gallery_page');
add_shortcode('beauty_gallery_page', 'beauty_v9_gallery_shortcode');

/* =====================================================================
 * [FIX 6] DANH MỤC HÌNH ẢNH CÓ BỘ LỌC
 * Shortcode: [beauty_image_categories]
 * Dùng ảnh album thật đã tải lên; nếu chưa có sẽ dùng ảnh mẫu trong theme.
 * ===================================================================== */
function beauty_v9_image_categories_shortcode() {
    $items = beauty_v9_gallery_items();
    if (empty($items)) return '<p class="empty-state">Chưa có hình ảnh. Vui lòng thêm ảnh trong mục Album ảnh.</p>';

    $albums = array();
    foreach ($items as $it) {
        $slug = sanitize_html_class(isset($it['slug']) ? $it['slug'] : 'beauty');
        $label = isset($it['label']) ? $it['label'] : 'Beauty Spa';
        if (!isset($albums[$slug])) $albums[$slug] = array('label' => $label, 'items' => array());
        $albums[$slug]['items'][] = $it;
    }

    wp_enqueue_script('beauty-image-category-filter', get_template_directory_uri() . '/assets/js/beauty-image-category-filter.js', array(), (string) filemtime(get_template_directory() . '/assets/js/beauty-image-category-filter.js'), true);
    wp_enqueue_script('beauty-albums', get_template_directory_uri() . '/assets/js/beauty-albums.js', array(), (string) filemtime(get_template_directory() . '/assets/js/beauty-albums.js'), true);

    ob_start(); ?>
    <section class="beauty-image-categories" aria-labelledby="beauty-image-categories-title">
      <div class="bic-heading">
        <span class="eyebrow">HÌNH ẢNH BEAUTY SPA</span>
        <h2 id="beauty-image-categories-title">Không gian & trải nghiệm thực tế</h2>
        <p>Chọn danh mục để khám phá hình ảnh dịch vụ, không gian và những khoảnh khắc tại Beauty.</p>
      </div>
      <div class="bic-filters" role="group" aria-label="Lọc danh mục hình ảnh">
        <button type="button" class="bic-filter is-active" data-filter="all" aria-pressed="true">Tất cả</button>
        <?php foreach ($albums as $slug => $album) : ?>
          <button type="button" class="bic-filter" data-filter="<?php echo esc_attr($slug); ?>" aria-pressed="false"><?php echo esc_html($album['label']); ?></button>
        <?php endforeach; ?>
      </div>
      <div class="bic-grid">
        <?php $shown = 0; foreach ($albums as $slug => $album) : foreach ($album['items'] as $it) :
          if ($shown >= 16) break 2;
          $full = isset($it['full']) ? $it['full'] : '';
          $thumb = !empty($it['thumb']) ? $it['thumb'] : $full;
          $title = isset($it['title']) ? $it['title'] : $album['label'];
          $alt = isset($it['alt']) ? $it['alt'] : $title;
          $one = array(array('full' => $full, 'thumb' => $thumb, 'caption' => $title, 'alt' => $alt)); ?>
          <a class="bic-card bva-card" href="<?php echo esc_url($full); ?>" data-filter-category="<?php echo esc_attr($slug); ?>" data-title="<?php echo esc_attr($title); ?>" data-images="<?php echo esc_attr(wp_json_encode($one)); ?>" aria-label="Xem ảnh: <?php echo esc_attr($title); ?>" aria-haspopup="dialog">
            <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($alt); ?>" width="640" height="480" loading="lazy" decoding="async" data-fallback="<?php echo esc_url(beauty_theme_image_url('spa-album-room.jpg')); ?>">
            <span class="bic-card-label"><?php echo esc_html($title); ?></span>
          </a>
        <?php $shown++; endforeach; endforeach; ?>
      </div>
      <div class="bic-more"><a class="btn btn-outline" href="<?php echo esc_url(home_url('/hinh-anh/')); ?>">Xem tất cả hình ảnh <span aria-hidden="true">→</span></a></div>
    </section>
    <?php return ob_get_clean();
}
add_shortcode('beauty_image_categories', 'beauty_v9_image_categories_shortcode');

/* =====================================================================
 * [FIX 4] GÓI DỊCH VỤ / COMBO
 * ===================================================================== */
/** Ảnh combo: URL nhập tay -> ảnh đại diện -> ảnh mẫu theo tên combo. */
function beauty_v9_package_image($post_id) {
    $u = trim((string) get_post_meta($post_id, '_pk_image_url', true));
    if (0 === strpos($u, 'https://')) return array('url' => $u, 'thumb' => false);
    if (has_post_thumbnail($post_id)) return array('url' => '', 'thumb' => true);
    $t = strtolower(str_replace('đ', 'd', remove_accents(get_the_title($post_id))));
    $map = array('ngay thu gian' => 'spa-album-room.jpg', 'co the' => 'spa-service-body-scrub.jpg', 'da sach' => 'spa-service-cleansing.jpg', 'cham soc da' => 'spa-album-facial.jpg', 'vai gay' => 'spa-album-massage.jpg', 'goi dau' => 'spa-album-hairwash.jpg', 'massage' => 'spa-album-massage.jpg');
    $file = 'spa-album-room.jpg';
    foreach ($map as $kw => $f) { if (false !== strpos($t, $kw)) { $file = $f; break; } }
    return array('url' => beauty_theme_image_url($file), 'thumb' => false);
}
/** Quyền lợi: lấy từ ô "Quyền lợi" trong admin; chưa có thì dùng nội dung mẫu theo tên combo (chủ spa cần xác nhận). */
function beauty_v9_package_includes($post_id) {
    $raw = trim((string) get_post_meta($post_id, '_pk_includes', true));
    if ($raw) return array_slice(array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $raw)))), 0, 6);
    $t = strtolower(str_replace('đ', 'd', remove_accents(get_the_title($post_id))));
    if (false !== strpos($t, 'ngay thu gian')) return array('Gội đầu dưỡng sinh', 'Massage thư giãn toàn thân', 'Massage vai gáy', 'Thức uống thảo mộc');
    if (false !== strpos($t, 'co the'))        return array('Tẩy tế bào chết toàn thân', 'Massage body thư giãn', 'Dưỡng ẩm sau liệu trình', 'Thức uống thảo mộc');
    if (false !== strpos($t, 'da sach'))       return array('Làm sạch sâu & tẩy trang', 'Tẩy tế bào chết da mặt', 'Đắp mặt nạ cấp ẩm', 'Dưỡng ẩm hoàn tất');
    if (false !== strpos($t, 'vai gay'))       return array('Gội đầu dưỡng sinh', 'Massage da đầu', 'Massage vai gáy', 'Tư vấn chăm sóc tại nhà');
    if (false !== strpos($t, 'lieu trinh'))    return array('3 buổi chăm sóc da cơ bản', 'Tư vấn tình trạng da mỗi buổi', 'Lịch hẹn linh hoạt theo tư vấn', 'Hướng dẫn chăm sóc tại nhà');
    return array('Tư vấn nhu cầu trước khi thực hiện', 'Dịch vụ theo gói đã được xác nhận', 'Lịch hẹn linh hoạt theo tư vấn');
}

/** CSS + JS gắn thêm (tệp riêng, không đụng style.css). */
function beauty_v9_assets() {
    $css = get_template_directory() . '/assets/css/beauty-v9.css';
    /* [FIX ẢNH] Tự cứu ảnh lỗi trên toàn site (album, sản phẩm, giỏ hàng...) */
    $rec = get_template_directory() . '/assets/js/beauty-img-recover.js';
    wp_enqueue_script('beauty-img-recover', get_template_directory_uri() . '/assets/js/beauty-img-recover.js', array(), file_exists($rec) ? (string) filemtime($rec) : BEAUTY_SPA_VERSION, false);
    wp_add_inline_script('beauty-img-recover', 'window.BEAUTY_IMG_CFG=' . wp_json_encode(array('dataUrl' => get_template_directory_uri() . '/assets/js/beauty-img-data.js')) . ';', 'before');
    wp_enqueue_style('beauty-v9', get_template_directory_uri() . '/assets/css/beauty-v9.css', array('beaute-spa-style'), file_exists($css) ? (string) filemtime($css) : BEAUTY_SPA_VERSION);
}
add_action('wp_enqueue_scripts', 'beauty_v9_assets', 99);


/* =====================================================================
 * [FIX 5] DANH MỤC DỊCH VỤ (tab biểu tượng + khung mô tả + thẻ ảnh) — màu sắc theo giao diện Beauty
 * Dùng: tự thay khối "Những trải nghiệm dành cho bạn" ở trang Giới thiệu; hoặc chèn shortcode [beauty_service_categories] ở bất kỳ trang nào.
 * ===================================================================== */
function beauty_v9_svc_icon($k) {
    $i = array(
        'da'   => '<path d="M12 3c3.5 0 6 2.6 6 6.2 0 4.6-3 8.8-6 11.8-3-3-6-7.2-6-11.8C6 5.6 8.5 3 12 3z"/><path d="M9 10h.01M15 10h.01M9.5 14c.7.6 1.5.9 2.5.9s1.8-.3 2.5-.9"/>',
        'goi'  => '<path d="M12 3s5 5.2 5 9a5 5 0 0 1-10 0c0-3.8 5-9 5-9z"/><path d="M9.5 13a2.5 2.5 0 0 0 2.5 2.5"/>',
        'mas'  => '<path d="M4 15c2-4 5-6 8-6s6 2 8 6"/><path d="M3 19h18"/><circle cx="12" cy="6" r="2"/>',
        'lon'  => '<path d="M5 21c0-7 3-13 14-17-1 9-5 14-12 15"/><path d="M5 21c2-5 5-8 9-10"/>',
        'body' => '<circle cx="12" cy="12" r="8"/><path d="M8 12c1.3-2 2.7-3 4-3s2.7 1 4 3c-1.3 2-2.7 3-4 3s-2.7-1-4-3z"/>',
        'tay'  => '<path d="M8 21v-9a2 2 0 0 1 4 0v-6a2 2 0 0 1 4 0v10c0 3-2 5-5 5H8z"/>',
    );
    return '<svg viewBox="0 0 24 24" width="38" height="38" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $i[$k] . '</svg>';
}
function beauty_v9_svc_categories() {
    return apply_filters('beauty_service_categories', array(
        array('key' => 'da',   'name' => 'Chăm sóc da',   'term' => 'Chăm sóc da',        'img' => 'spa-album-facial.jpg',      'text' => 'Chăm sóc da tại Beauty bắt đầu từ việc lắng nghe tình trạng da và nhu cầu của bạn. Các bước làm sạch, tẩy tế bào chết nhẹ, đắp mặt nạ và dưỡng ẩm được trao đổi rõ ràng về thời lượng và chi phí trước khi thực hiện.'),
        array('key' => 'goi',  'name' => 'Gội đầu',       'term' => 'Gội đầu dưỡng sinh', 'img' => 'spa-album-hairwash.jpg',    'text' => 'Gội đầu dưỡng sinh kết hợp massage da đầu, cổ và vai trong không gian ấm áp, giúp bạn thả lỏng sau một ngày dài. Thời lượng và các bước được tư vấn theo nhu cầu của từng khách.'),
        array('key' => 'mas',  'name' => 'Massage',       'term' => 'Massage thư giãn',   'img' => 'spa-album-massage.jpg',     'text' => 'Massage thư giãn toàn thân hoặc vùng lưng, vai gáy với lực tay điều chỉnh theo cảm giác của bạn. Hãy cho nhân viên biết các vùng cần lưu ý trước khi bắt đầu.'),
        array('key' => 'lon',  'name' => 'Triệt lông',    'term' => 'Triệt lông',         'img' => 'spa-service-hair-removal.jpg', 'text' => 'Dịch vụ triệt lông theo vùng cần được tư vấn kỹ về tình trạng da và liệu trình phù hợp trước khi thực hiện. Hiệu quả thực tế có thể khác nhau tùy từng người.'),
        array('key' => 'body', 'name' => 'Chăm sóc cơ thể', 'term' => 'Chăm sóc cơ thể',  'img' => 'spa-service-body-scrub.jpg', 'text' => 'Tẩy tế bào chết và dưỡng ẩm cơ thể giúp làn da mềm mại hơn sau liệu trình. Có thể kết hợp cùng massage trong các gói combo của Beauty.'),
        array('key' => 'tay',  'name' => 'Tay & chân',    'term' => 'Chăm sóc tay và chân', 'img' => 'spa-service-manicure.jpg',  'text' => 'Làm sạch, chăm sóc móng và dưỡng ẩm cơ bản cho bàn tay, bàn chân, thực hiện nhẹ nhàng và vệ sinh.'),
    ));
}
function beauty_v9_svc_cards() {
    return apply_filters('beauty_service_cards', array(
        array('Chăm sóc da chuyên sâu', 'spa-album-facial.jpg'),
        array('Massage thư giãn', 'spa-album-massage.jpg'),
        array('Gội đầu dưỡng sinh', 'spa-service-scalp-wash.jpg'),
        array('Tẩy tế bào chết cơ thể', 'spa-service-body-scrub.jpg'),
    ));
}
function beauty_v9_svc_shortcode() {
    wp_enqueue_script('beauty-svc-tabs', get_template_directory_uri() . '/assets/js/beauty-svc-tabs.js', array(), (string) filemtime(get_template_directory() . '/assets/js/beauty-svc-tabs.js'), true);
    $fb = esc_js(beauty_theme_image_url('spa-album-room.jpg'));
    $all = home_url('/dich-vu/');
    ob_start(); ?>
    <section class="bsc-wrap" aria-labelledby="bsc-title">
      <div class="bsc-head"><span class="eyebrow">CHĂM SÓC THEO NHU CẦU</span><h2 id="bsc-title">Danh mục dịch vụ</h2><span class="bsc-dot" aria-hidden="true"></span></div>
      <div class="bsc-tabs" role="tablist" aria-label="Danh mục dịch vụ">
      <?php foreach (beauty_v9_svc_categories() as $i => $c) : ?>
        <button type="button" class="bsc-tab<?php echo 0 === $i ? ' is-active' : ''; ?>" role="tab" id="bsc-tab-<?php echo esc_attr($c['key']); ?>" aria-controls="bsc-panel-<?php echo esc_attr($c['key']); ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" tabindex="<?php echo 0 === $i ? '0' : '-1'; ?>"><span class="bsc-ico"><?php echo beauty_v9_svc_icon($c['key']); ?></span><span class="bsc-name"><?php echo esc_html($c['name']); ?></span></button>
      <?php endforeach; ?>
      </div>
      <?php foreach (beauty_v9_svc_categories() as $i => $c) :
          $t = get_term_by('name', $c['term'], 'spa_service_cat'); $link = ($t && !is_wp_error($t)) ? get_term_link($t) : $all; if (is_wp_error($link)) $link = $all; ?>
      <div class="bsc-panel" role="tabpanel" id="bsc-panel-<?php echo esc_attr($c['key']); ?>" aria-labelledby="bsc-tab-<?php echo esc_attr($c['key']); ?>"<?php echo 0 === $i ? '' : ' hidden'; ?>>
        <div class="bsc-text"><p><?php echo esc_html($c['text']); ?></p><a class="bsc-more" href="<?php echo esc_url($link); ?>">Xem thêm <span aria-hidden="true">→</span></a></div>
        <div class="bsc-pic"><img src="<?php echo esc_url(beauty_theme_image_url($c['img'])); ?>" alt="<?php echo esc_attr($c['name']); ?>" width="800" height="500" loading="lazy" decoding="async" data-fallback="<?php echo esc_url($fb); ?>"></div>
      </div>
      <?php endforeach; ?>
      <div class="bsc-cards">
      <?php foreach (beauty_v9_svc_cards() as $card) : ?>
        <a class="bsc-card" href="<?php echo esc_url($all); ?>"><img src="<?php echo esc_url(beauty_theme_image_url($card[1])); ?>" alt="<?php echo esc_attr($card[0]); ?>" width="600" height="600" loading="lazy" decoding="async" data-fallback="<?php echo esc_url($fb); ?>"><span><?php echo esc_html($card[0]); ?></span></a>
      <?php endforeach; ?>
      </div>
      <div class="bsc-cta"><a class="btn btn-outline" href="<?php echo esc_url($all); ?>">Xem thêm dịch vụ →</a></div>
    </section>
    <?php return ob_get_clean();
}
add_shortcode('beauty_service_categories', 'beauty_v9_svc_shortcode');
/** Trang Giới thiệu: thay khối lưới 3 ảnh bị lỗi bằng Danh mục dịch vụ. */
function beauty_v9_about_services($content) {
    if (false === strpos($content, 'beauty-about-services')) return $content;
    $out = preg_replace('#<section class="beauty-about-services">.*?</section>#s', '[beauty_service_categories]', $content, 1);
    return $out ? do_shortcode($out) : $content;
}
add_filter('the_content', 'beauty_v9_about_services', 26);

/* =====================================================================
 * CÔNG CỤ CHẨN ĐOÁN: Công cụ → Chẩn đoán ảnh Beauty (cho biết ảnh nào đang không tải được và vì sao)
 * ===================================================================== */
function beauty_v9_diag_menu() { add_management_page('Chẩn đoán ảnh Beauty', 'Chẩn đoán ảnh Beauty', 'manage_options', 'beauty-diag', 'beauty_v9_diag_page'); }
add_action('admin_menu', 'beauty_v9_diag_menu');
function beauty_v9_diag_page() {
    if (!current_user_can('manage_options')) return;
    $u = wp_upload_dir(); $t = get_template_directory_uri();
    echo '<div class="wrap"><h1>Chẩn đoán ảnh Beauty</h1>';
    echo '<p>Thư mục theme: <code>' . esc_html(get_template_directory()) . '</code><br>URL theme: <code>' . esc_html($t) . '</code></p>';
    if (is_ssl() && 0 === strpos($t, 'http://')) echo '<p style="color:#b32d2e"><strong>Cảnh báo:</strong> website chạy https nhưng URL theme là http → trình duyệt chặn ảnh. Vào Cài đặt → Chung, đổi cả 2 địa chỉ sang https.</p>';
    echo '<h2>Ảnh trong theme</h2><table class="widefat striped"><thead><tr><th>Tệp</th><th>Có trên máy chủ?</th><th>Mở thử</th></tr></thead><tbody>';
    foreach (array('spa-album-facial.jpg', 'spa-album-massage.jpg', 'spa-album-hairwash.jpg', 'spa-album-room.jpg', 'spa-service-body-scrub.jpg', 'products/sq-serum.jpg') as $f) {
        $ok = file_exists(get_template_directory() . '/assets/images/' . $f);
        echo '<tr><td>' . esc_html($f) . '</td><td>' . ($ok ? 'Có' : '<strong style="color:#b32d2e">THIẾU</strong>') . '</td><td><a target="_blank" rel="noopener" href="' . esc_url($t . '/assets/images/' . $f) . '">' . esc_html($t . '/assets/images/' . $f) . '</a></td></tr>';
    }
    echo '</tbody></table><h2>Album đang hiển thị</h2><table class="widefat striped"><thead><tr><th>Album</th><th>Ảnh</th><th>Tệp tồn tại?</th></tr></thead><tbody>';
    foreach (beauty_v9_gallery_items() as $it) {
        $p = beauty_url_to_path($it['full']);
        echo '<tr><td>' . esc_html($it['label']) . '</td><td><a target="_blank" rel="noopener" href="' . esc_url($it['full']) . '">' . esc_html($it['full']) . '</a></td><td>' . (!$p ? 'Ảnh ngoài' : (file_exists($p) ? 'Có' : '<strong>THIẾU</strong>')) . '</td></tr>';
    }
    echo '</tbody></table><h2>Thư mục ảnh thật</h2><p>Ảnh album: <code>' . esc_html($u['basedir']) . '/beauty-album/&lt;ten-album&gt;/anh.jpg</code> ' . (is_dir($u['basedir'] . '/beauty-album') ? '(đã có)' : '(chưa tạo)') . '<br>Ảnh sản phẩm: <code>' . esc_html($u['basedir']) . '/beauty-products/&lt;ten-san-pham&gt;.jpg</code> ' . (is_dir($u['basedir'] . '/beauty-products') ? '(đã có)' : '(chưa tạo)') . '</p></div>';
}
