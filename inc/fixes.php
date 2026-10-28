<?php
/**
 * Beauty Spa - bản sửa lỗi (V8.1)
 *  [FIX 1] Trang Liên hệ: markup mới + class riêng (bvc-*)
 *  [FIX 3] Nút "Thêm vào giỏ": AJAX + liên kết ?add-to-cart=ID chuẩn WooCommerce
 * (FIX 2 - Footer nằm trong footer.php và style.css)
 */
if (!defined('ABSPATH')) exit;

/* =========================================================================
 * [FIX 1] TRANG LIÊN HỆ
 * ========================================================================= */

/** Thêm class vào <body> để CSS gỡ giới hạn 850px của .entry-content ở trang Liên hệ. */
function beauty_fix_contact_body_class($classes) {
    if (is_page('lien-he')) $classes[] = 'beauty-contact-page';
    return $classes;
}
add_filter('body_class', 'beauty_fix_contact_body_class');

/** Dựng toàn bộ nội dung trang Liên hệ (được gọi từ beauty_custom_page_content). */
function beauty_render_contact_page() {
    $phone   = beauty_contact_info('phone');
    $email   = beauty_contact_info('email');
    $address = beauty_contact_info('address');
    $hours   = beauty_contact_info('hours');
    $tel     = preg_replace('/[^0-9+]/', '', $phone);

    // Bỏ phần chú thích trong ngoặc, ví dụ "(địa chỉ mẫu)", để Google Maps tìm đúng.
    $map_q = trim(preg_replace('/\s*\(.*?\)\s*/u', ' ', $address));
    if ('' === $map_q) $map_q = $address;
    $map_src = 'https://www.google.com/maps?q=' . rawurlencode($map_q) . '&output=embed';

    $services = get_posts(array(
        'post_type' => 'spa_service', 'post_status' => 'publish', 'numberposts' => 50,
        'orderby' => 'title', 'order' => 'ASC', 'no_found_rows' => true,
    ));
    $sent = isset($_GET['contact_sent']) && '1' === $_GET['contact_sent'];

    ob_start();
    ?>
    <div class="bvc-wrap">
      <?php if ($sent) : ?>
        <div class="bvc-notice" role="status">Cảm ơn bạn đã liên hệ. Yêu cầu đã được gửi, Beauty sẽ phản hồi sau khi tiếp nhận.</div>
      <?php endif; ?>
      <div class="bvc-grid">

        <!-- Cột trái: Form -->
        <section class="bvc-card bvc-form-card" aria-labelledby="bvc-form-title">
          <h2 id="bvc-form-title" class="bvc-title">Gửi lời nhắn cho chúng tôi</h2>
          <p class="bvc-lead">Các trường có dấu * là bắt buộc. Beauty sẽ liên hệ lại với bạn sớm nhất.</p>
          <form class="bvc-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="beauty_contact_submit">
            <?php wp_nonce_field('beauty_contact_submit', 'beauty_contact_nonce'); ?>

            <div class="bvc-field">
              <label for="bvc-name">Họ và tên *</label>
              <input id="bvc-name" name="contact_name" type="text" required maxlength="100" autocomplete="name" placeholder="Nhập họ và tên của bạn">
            </div>
            <div class="bvc-field">
              <label for="bvc-phone">Số điện thoại *</label>
              <input id="bvc-phone" name="contact_phone" type="tel" required maxlength="30" autocomplete="tel" placeholder="Nhập số điện thoại liên hệ">
            </div>
            <div class="bvc-field">
              <label for="bvc-email">Email</label>
              <input id="bvc-email" name="contact_email" type="email" maxlength="150" autocomplete="email" placeholder="email@example.com">
            </div>
            <div class="bvc-field">
              <label for="bvc-topic">Dịch vụ quan tâm</label>
              <select id="bvc-topic" name="contact_topic">
                <option value="Tư vấn dịch vụ">Tư vấn chung</option>
                <?php if ($services) : ?>
                  <optgroup label="Dịch vụ">
                    <?php foreach ($services as $service) : ?>
                      <option value="<?php echo esc_attr($service->post_title); ?>"><?php echo esc_html($service->post_title); ?></option>
                    <?php endforeach; ?>
                  </optgroup>
                <?php endif; ?>
                <option value="Sản phẩm">Sản phẩm</option>
                <option value="Đặt lịch">Đặt lịch</option>
                <option value="Góp ý khác">Góp ý khác</option>
              </select>
            </div>
            <div class="bvc-field">
              <label for="bvc-message">Lời nhắn *</label>
              <textarea id="bvc-message" name="contact_message" rows="5" required maxlength="3000" placeholder="Bạn cần Beauty hỗ trợ điều gì?"></textarea>
            </div>
            <button class="bvc-submit" type="submit">Gửi tin nhắn</button>
            <p class="bvc-privacy">Thông tin chỉ được dùng để phản hồi yêu cầu liên hệ của bạn.</p>
          </form>
        </section>

        <!-- Cột phải: Thông tin + Bản đồ -->
        <aside class="bvc-card bvc-info-card" aria-labelledby="bvc-info-title">
          <h2 id="bvc-info-title" class="bvc-title">Thông tin liên hệ</h2>
          <ul class="bvc-info">
            <li>
              <span class="bvc-ico" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-6 7-11a7 7 0 0 0-14 0c0 5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
              <div><strong>Địa chỉ</strong><span><?php echo esc_html($address); ?></span></div>
            </li>
            <li>
              <span class="bvc-ico" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg></span>
              <div><strong>Hotline</strong><?php if ($tel) : ?><a href="tel:<?php echo esc_attr($tel); ?>"><?php echo esc_html($phone); ?></a><?php else : ?><span><?php echo esc_html($phone); ?></span><?php endif; ?></div>
            </li>
            <li>
              <span class="bvc-ico" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
              <div><strong>Email</strong><?php if ($email) : ?><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a><?php endif; ?></div>
            </li>
            <li>
              <span class="bvc-ico" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
              <div><strong>Giờ mở cửa</strong><span><?php echo esc_html($hours); ?></span></div>
            </li>
          </ul>
          <div class="bvc-map">
            <iframe title="Bản đồ vị trí Beauty Spa" src="<?php echo esc_url($map_src); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
          </div>
        </aside>

      </div>
    </div>
    <?php
    return ob_get_clean();
}

/* =========================================================================
 * [FIX 3] NÚT THÊM VÀO GIỎ HÀNG (giỏ hàng tích hợp, khi KHÔNG có WooCommerce)
 * ========================================================================= */

/** Thêm sản phẩm bằng AJAX: trả JSON để JS cập nhật số giỏ hàng và hiện thông báo, không tải lại trang. */
function beauty_ajax_add_to_cart() {
    if (beauty_use_woocommerce()) {
        wp_send_json_error(array('message' => 'Giỏ hàng đang do WooCommerce quản lý.'));
    }
    $id  = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $qty = isset($_POST['quantity']) ? max(1, min(99, absint($_POST['quantity']))) : 1;
    if (!$id || !beauty_product_purchasable($id)) {
        wp_send_json_error(array('message' => 'Sản phẩm hiện không thể mua. Vui lòng liên hệ spa.'));
    }
    $items = beauty_cart_get();
    $items[$id] = min(99, (isset($items[$id]) ? $items[$id] : 0) + $qty);
    beauty_cart_save($items);
    wp_send_json_success(array(
        'count'        => (int) beauty_cart_count(),
        'message'      => sprintf('Đã thêm “%s” vào giỏ hàng.', wp_specialchars_decode(get_the_title($id), ENT_QUOTES)),
        'cart_url'     => beauty_cart_url(),
        'checkout_url' => beauty_checkout_url(),
    ));
}
add_action('wp_ajax_nopriv_beauty_add_to_cart', 'beauty_ajax_add_to_cart');
add_action('wp_ajax_beauty_add_to_cart', 'beauty_ajax_add_to_cart');

/** Dự phòng khi trình duyệt tắt JS / JS lỗi: liên kết ?add-to-cart=ID (giống WooCommerce) vẫn thêm được vào giỏ. */
function beauty_handle_get_add_to_cart() {
    if (beauty_use_woocommerce() || empty($_GET['add-to-cart']) || (function_exists('wp_doing_ajax') && wp_doing_ajax())) return;
    $id   = absint($_GET['add-to-cart']);
    $qty  = isset($_GET['quantity']) ? max(1, min(99, absint($_GET['quantity']))) : 1;
    $back = remove_query_arg(array('add-to-cart', 'quantity', 'cart_msg'));
    if (!$id || !beauty_product_purchasable($id)) {
        wp_safe_redirect(add_query_arg('cart_msg', 'unavailable', $back)); exit;
    }
    $items = beauty_cart_get();
    $items[$id] = min(99, (isset($items[$id]) ? $items[$id] : 0) + $qty);
    beauty_cart_save($items);
    wp_safe_redirect(add_query_arg('cart_msg', 'added', $back)); exit;
}
add_action('template_redirect', 'beauty_handle_get_add_to_cart', 1);

/** Nạp JS giỏ hàng (không có WooCommerce) hoặc đảm bảo script AJAX của WooCommerce được nạp. */
function beauty_fix_cart_assets() {
    if (beauty_use_woocommerce()) {
        // Chưa kiểm thử trên WooCommerce thật: chỉ nạp thêm khi WooCommerce cung cấp sẵn hàm này.
        if (is_callable(array('WC_Frontend_Scripts', 'enqueue_script'))) {
            WC_Frontend_Scripts::enqueue_script('wc-add-to-cart');
        }
        return;
    }
    wp_enqueue_script('beauty-cart', get_template_directory_uri() . '/assets/js/beauty-cart.js', array(), BEAUTY_SPA_VERSION . '.1', true);
    wp_localize_script('beauty-cart', 'beautyCart', array('ajaxUrl' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'beauty_fix_cart_assets', 99);

/* =========================================================================
 * [FIX 4] TRANG GIỎ HÀNG / THANH TOÁN / YÊU THÍCH
 * ========================================================================= */

/** Có đang mở trang "Sản phẩm yêu thích" không (kể cả khi trang chưa tồn tại trong admin -> trước đây báo 404). */
function beauty_is_wishlist_request() {
    if (is_page('san-pham-yeu-thich')) return true;
    $uri  = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
    $path = trim((string) wp_parse_url($uri, PHP_URL_PATH), '/');
    return '' !== $path && 'san-pham-yeu-thich' === basename($path);
}

/** Nhận diện trang giỏ hàng / thanh toán theo slug hoặc tiêu đề (kể cả slug bị đổi thành gio-hang-2...). */
function beauty_fix_page_kind() {
    if (!is_page()) return '';
    $obj = get_queried_object();
    if (!$obj || empty($obj->post_name)) return '';
    $keys = array($obj->post_name, sanitize_title($obj->post_title));
    foreach ($keys as $k) {
        if (0 === strpos($k, 'gio-hang')) return 'cart';
        if (0 === strpos($k, 'thanh-toan')) return 'checkout';
    }
    return '';
}

/** Trang giỏ hàng / thanh toán bị trống (thiếu shortcode, hoặc còn block WooCommerce cũ): tự hiển thị đúng nội dung. */
function beauty_fix_shop_pages_content($content) {
    if (is_admin() || !is_main_query() || !in_the_loop() || beauty_use_woocommerce()) return $content;
    $kind = beauty_fix_page_kind();
    if ('cart' === $kind && !has_shortcode($content, 'beauty_cart')) return do_shortcode('[beauty_cart]');
    if ('checkout' === $kind && !has_shortcode($content, 'beauty_checkout')) return do_shortcode('[beauty_checkout]');
    return $content;
}
add_filter('the_content', 'beauty_fix_shop_pages_content', 12);

/** Tạo / khôi phục các trang còn thiếu (bị xóa, để nháp...) một lần. */
function beauty_fix_ensure_pages() {
    if (get_option('beauty_pages_fix_v2')) return;
    $defs = array('san-pham-yeu-thich' => array('Sản phẩm yêu thích', 'Danh sách sản phẩm yêu thích của bạn.'));
    if (!beauty_use_woocommerce()) {
        $defs['gio-hang']   = array('Giỏ hàng', '[beauty_cart]');
        $defs['thanh-toan'] = array('Thanh toán', '[beauty_checkout]');
    }
    foreach ($defs as $slug => $def) {
        $found = get_posts(array('post_type' => 'page', 'name' => $slug, 'post_status' => array('publish', 'draft', 'pending', 'private', 'future'), 'numberposts' => 1, 'no_found_rows' => true));
        if ($found) {
            $page = $found[0];
            $args = array('ID' => $page->ID);
            if ('publish' !== $page->post_status) $args['post_status'] = 'publish';
            if ('[' === substr($def[1], 0, 1) && !has_shortcode($page->post_content, trim($def[1], '[]'))) $args['post_content'] = $def[1];
            if (count($args) > 1) wp_update_post($args);
        } else {
            wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $def[0], 'post_content' => $def[1]));
        }
    }
    update_option('beauty_pages_fix_v2', 1, false);
}
add_action('init', 'beauty_fix_ensure_pages', 60);
