<?php
/**
 * Giỏ hàng + thanh toán tích hợp (dùng khi KHÔNG cài WooCommerce).
 * - Giỏ hàng lưu trong cookie "beauty_cart" dạng "id:sl,id:sl"; giá luôn được tính lại ở máy chủ.
 * - Đơn hàng lưu thành bài viết riêng tư (Admin → Đơn hàng). Thanh toán: COD hoặc chuyển khoản (xác nhận thủ công).
 * - Không có cổng thanh toán trực tuyến; muốn thanh toán thẻ/ví hãy cài WooCommerce.
 */
if (!defined('ABSPATH')) exit;

define('BEAUTY_CART_COOKIE', 'beauty_cart');

/* ---------- Dữ liệu giỏ ---------- */
function beauty_product_unit_price($id) {
    $price = (int) get_post_meta($id, '_p_price', true);
    $sale = (int) get_post_meta($id, '_p_sale', true);
    return ($sale > 0 && $price > $sale) ? $sale : $price;
}
function beauty_product_purchasable($id) {
    return 'spa_product' === get_post_type($id) && 'publish' === get_post_status($id)
        && 'het-hang' !== get_post_meta($id, '_p_stock', true) && beauty_product_unit_price($id) > 0;
}
function beauty_cart_get() {
    $items = array();
    if (empty($_COOKIE[BEAUTY_CART_COOKIE])) return $items;
    $raw = sanitize_text_field(wp_unslash($_COOKIE[BEAUTY_CART_COOKIE]));
    foreach (explode(',', $raw) as $pair) {
        $parts = explode(':', $pair);
        if (2 !== count($parts)) continue;
        $id = absint($parts[0]); $qty = absint($parts[1]);
        if ($id && $qty) $items[$id] = min(99, (isset($items[$id]) ? $items[$id] : 0) + $qty);
    }
    return $items;
}
function beauty_cart_save($items) {
    $pairs = array();
    foreach ($items as $id => $qty) {
        if ((int) $id > 0 && (int) $qty > 0) $pairs[] = (int) $id . ':' . min(99, (int) $qty);
    }
    $value = implode(',', $pairs);
    $expires = $pairs ? time() + 30 * DAY_IN_SECONDS : time() - HOUR_IN_SECONDS;
    if (!headers_sent()) {
        setcookie(BEAUTY_CART_COOKIE, $value, array('expires' => $expires, 'path' => '/', 'secure' => is_ssl(), 'httponly' => false, 'samesite' => 'Lax'));
    }
    if ($pairs) $_COOKIE[BEAUTY_CART_COOKIE] = $value; else unset($_COOKIE[BEAUTY_CART_COOKIE]);
}
function beauty_cart_data() {
    $lines = array(); $subtotal = 0; $count = 0;
    foreach (beauty_cart_get() as $id => $qty) {
        if (!beauty_product_purchasable($id)) continue;
        $price = beauty_product_unit_price($id);
        $total = $price * $qty;
        $subtotal += $total; $count += $qty;
        $lines[] = array('id' => $id, 'title' => get_the_title($id), 'size' => get_post_meta($id, '_p_size', true), 'qty' => $qty, 'price' => $price, 'total' => $total);
    }
    return array('lines' => $lines, 'subtotal' => $subtotal, 'count' => $count);
}
function beauty_shipping_fee($subtotal) {
    $fee = max(0, (int) get_theme_mod('beaute_spa_ship_fee', 0));
    $free = (int) get_theme_mod('beaute_spa_free_ship', 0);
    return ($free > 0 && $subtotal >= $free) ? 0 : $fee;
}
function beauty_cart_count() {
    if (beauty_use_woocommerce()) return (function_exists('WC') && WC()->cart) ? (int) WC()->cart->get_cart_contents_count() : 0;
    $data = beauty_cart_data();
    return (int) $data['count'];
}
function beauty_cart_url() {
    return (beauty_use_woocommerce() && function_exists('wc_get_cart_url')) ? wc_get_cart_url() : home_url('/gio-hang/');
}
function beauty_checkout_url() {
    return (beauty_use_woocommerce() && function_exists('wc_get_checkout_url')) ? wc_get_checkout_url() : home_url('/thanh-toan/');
}

/* ---------- Thao tác giỏ (admin-post) ---------- */
function beauty_handle_cart_action() {
    $op = isset($_POST['op']) ? sanitize_key(wp_unslash($_POST['op'])) : '';
    $back = wp_get_referer() ? remove_query_arg('cart_msg', wp_get_referer()) : beauty_cart_url();
    $items = beauty_cart_get();
    $id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $qty = isset($_POST['qty']) ? max(1, min(99, absint($_POST['qty']))) : 1;

    if ('add' === $op || 'buynow' === $op) {
        if (!beauty_product_purchasable($id)) {
            wp_safe_redirect(add_query_arg('cart_msg', 'unavailable', $back)); exit;
        }
        $items[$id] = min(99, (isset($items[$id]) ? $items[$id] : 0) + $qty);
        beauty_cart_save($items);
        wp_safe_redirect('buynow' === $op ? beauty_checkout_url() : add_query_arg('cart_msg', 'added', $back)); exit;
    }
    if (isset($_POST['remove'])) {
        unset($items[absint($_POST['remove'])]);
        beauty_cart_save($items);
        wp_safe_redirect(add_query_arg('cart_msg', 'removed', beauty_cart_url())); exit;
    }
    if ('update' === $op && isset($_POST['qty_map']) && is_array($_POST['qty_map'])) {
        $new = array();
        foreach (wp_unslash($_POST['qty_map']) as $pid => $q) {
            $pid = absint($pid); $q = absint($q);
            if ($pid && $q) $new[$pid] = min(99, $q);
        }
        beauty_cart_save($new);
        wp_safe_redirect(add_query_arg('cart_msg', 'updated', beauty_cart_url())); exit;
    }
    wp_safe_redirect(beauty_cart_url()); exit;
}
add_action('admin_post_nopriv_beauty_cart', 'beauty_handle_cart_action');
add_action('admin_post_beauty_cart', 'beauty_handle_cart_action');

/* Thông báo nổi sau khi thêm/xóa */
function beauty_cart_toast() {
    if (empty($_GET['cart_msg']) || beauty_use_woocommerce()) return;
    $msgs = array('added' => 'Đã thêm sản phẩm vào giỏ hàng.', 'updated' => 'Đã cập nhật giỏ hàng.', 'removed' => 'Đã xóa sản phẩm khỏi giỏ hàng.', 'unavailable' => 'Sản phẩm hiện không thể mua. Vui lòng liên hệ spa.');
    $key = sanitize_key(wp_unslash($_GET['cart_msg']));
    if (!isset($msgs[$key])) return;
    echo '<div class="bv-toast" role="status"><span>' . esc_html($msgs[$key]) . '</span>';
    if ('added' === $key) echo ' <a href="' . esc_url(beauty_cart_url()) . '">Xem giỏ hàng</a> <a href="' . esc_url(beauty_checkout_url()) . '">Thanh toán</a>';
    echo '</div>';
}
add_action('wp_body_open', 'beauty_cart_toast', 20);

/* ---------- Form dùng trong thẻ/chi tiết sản phẩm ---------- */
function beauty_add_to_cart_form($id, $detail = false) {
    if (beauty_use_woocommerce()) return '';
    if ('het-hang' === get_post_meta($id, '_p_stock', true)) return '<span class="bv-soldout">Hết hàng</span>';
    if (!beauty_product_purchasable($id)) return '<a class="btn" href="' . esc_url(home_url('/lien-he/')) . '">Liên hệ đặt mua</a>';
    /* [FIX 3] NÚT GIỎ HÀNG - thẻ sản phẩm dùng liên kết chuẩn WooCommerce; JS (beauty-cart.js) thêm bằng AJAX,
       nếu JS lỗi thì ?add-to-cart=ID vẫn hoạt động (xử lý trong inc/fixes.php). */
    if (!$detail) {
        return '<a href="' . esc_url(add_query_arg(array('add-to-cart' => (int) $id, 'cart_msg' => false))) . '" data-quantity="1" class="btn button add_to_cart_button ajax_add_to_cart" data-product_id="' . (int) $id . '" rel="nofollow">Thêm vào giỏ</a>';
    }
    $out = '<form class="bv-addcart" method="post" action="' . esc_url(admin_url('admin-post.php')) . '"><input type="hidden" name="action" value="beauty_cart"><input type="hidden" name="product_id" value="' . (int) $id . '">';
    if ($detail) {
        $out .= '<label class="bv-qty"><span>Số lượng</span><input type="number" name="qty" value="1" min="1" max="99" inputmode="numeric"></label>';
        $out .= '<div class="bv-actions"><button class="btn" type="submit" name="op" value="add">Thêm vào giỏ</button><button class="btn btn-outline" type="submit" name="op" value="buynow">Mua ngay</button></div>';
    } else {
        $out .= '<input type="hidden" name="qty" value="1"><button class="btn" type="submit" name="op" value="add">Thêm vào giỏ</button>';
    }
    return $out . '</form>';
}

/* ---------- Đơn hàng ---------- */
function beauty_shop_register() {
    register_post_type('spa_order', array(
        'labels' => array('name' => 'Đơn hàng', 'singular_name' => 'Đơn hàng', 'all_items' => 'Tất cả đơn hàng', 'edit_item' => 'Chi tiết đơn hàng'),
        'public' => false, 'show_ui' => true, 'menu_icon' => 'dashicons-cart', 'supports' => array('title'),
        'capability_type' => 'post', 'capabilities' => array('create_posts' => 'do_not_allow'), 'map_meta_cap' => true,
    ));
}
add_action('init', 'beauty_shop_register');

function beauty_order_statuses() {
    return array('cho-xac-nhan' => 'Chờ xác nhận', 'da-xac-nhan' => 'Đã xác nhận', 'dang-giao' => 'Đang giao', 'hoan-thanh' => 'Hoàn thành', 'da-huy' => 'Đã hủy');
}
function beauty_order_boxes() {
    add_meta_box('beauty_order_details', 'Chi tiết đơn hàng', 'beauty_order_details_box', 'spa_order', 'normal', 'high');
}
add_action('add_meta_boxes', 'beauty_order_boxes');

function beauty_order_details_box($post) {
    $m = function ($k) use ($post) { return get_post_meta($post->ID, $k, true); };
    $pay = array('cod' => 'Thanh toán khi nhận hàng', 'bank' => 'Chuyển khoản');
    echo '<p><strong>Khách:</strong> ' . esc_html($m('_o_name')) . ' — ' . esc_html($m('_o_phone')) . ($m('_o_email') ? ' — ' . esc_html($m('_o_email')) : '') . '</p>';
    echo '<p><strong>Địa chỉ giao:</strong> ' . esc_html($m('_o_address')) . '</p>';
    if ($m('_o_note')) echo '<p><strong>Ghi chú:</strong> ' . esc_html($m('_o_note')) . '</p>';
    $pm = $m('_o_payment');
    echo '<p><strong>Thanh toán:</strong> ' . esc_html(isset($pay[$pm]) ? $pay[$pm] : $pm) . '</p>';
    echo '<table class="widefat striped"><thead><tr><th>Sản phẩm</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead><tbody>';
    $items = $m('_o_items');
    if (is_array($items)) foreach ($items as $it) echo '<tr><td>' . esc_html($it['title']) . '</td><td>' . (int) $it['qty'] . '</td><td>' . esc_html(beauty_money($it['price'])) . '</td><td>' . esc_html(beauty_money($it['total'])) . '</td></tr>';
    echo '</tbody></table><p>Tạm tính: ' . esc_html(beauty_money((int) $m('_o_subtotal'))) . ' · Vận chuyển: ' . esc_html(beauty_money((int) $m('_o_shipping'))) . ' · <strong>Tổng: ' . esc_html(beauty_money((int) $m('_o_total'))) . '</strong></p>';
    wp_nonce_field('beauty_order_status', 'beauty_order_nonce');
    echo '<p><label><strong>Trạng thái đơn</strong><br><select name="_o_status">';
    $cur = $m('_o_status') ? $m('_o_status') : 'cho-xac-nhan';
    foreach (beauty_order_statuses() as $k => $label) echo '<option value="' . esc_attr($k) . '"' . selected($cur, $k, false) . '>' . esc_html($label) . '</option>';
    echo '</select></label></p>';
}
function beauty_order_save($post_id) {
    if (!isset($_POST['beauty_order_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['beauty_order_nonce'])), 'beauty_order_status')) return;
    if (!current_user_can('edit_post', $post_id) || !isset($_POST['_o_status'])) return;
    $status = sanitize_key(wp_unslash($_POST['_o_status']));
    if (isset(beauty_order_statuses()[$status])) update_post_meta($post_id, '_o_status', $status);
}
add_action('save_post_spa_order', 'beauty_order_save');

function beauty_order_columns($cols) {
    return array('cb' => $cols['cb'], 'title' => 'Mã đơn', 'o_customer' => 'Khách hàng', 'o_total' => 'Tổng tiền', 'o_status' => 'Trạng thái', 'date' => 'Ngày đặt');
}
add_filter('manage_spa_order_posts_columns', 'beauty_order_columns');
function beauty_order_column_data($col, $post_id) {
    if ('o_customer' === $col) echo esc_html(get_post_meta($post_id, '_o_name', true) . ' · ' . get_post_meta($post_id, '_o_phone', true));
    if ('o_total' === $col) echo esc_html(beauty_money((int) get_post_meta($post_id, '_o_total', true)));
    if ('o_status' === $col) { $s = beauty_order_statuses(); $v = get_post_meta($post_id, '_o_status', true); echo esc_html(isset($s[$v]) ? $s[$v] : 'Chờ xác nhận'); }
}
add_action('manage_spa_order_posts_custom_column', 'beauty_order_column_data', 10, 2);

/* ---------- Xử lý đặt hàng (trước khi xuất HTML để còn xóa được cookie) ---------- */
$GLOBALS['beauty_checkout_state'] = array('errors' => array(), 'old' => array());

function beauty_process_checkout() {
    if (beauty_use_woocommerce() || empty($_POST['beauty_checkout_nonce']) || !is_page()) return;
    global $beauty_checkout_state;
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['beauty_checkout_nonce'])), 'beauty_checkout')) {
        $beauty_checkout_state['errors'][] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.'; return;
    }
    if (!empty($_POST['website'])) return; // honeypot
    $old = array(
        'name' => sanitize_text_field(wp_unslash($_POST['co_name'] ?? '')),
        'phone' => sanitize_text_field(wp_unslash($_POST['co_phone'] ?? '')),
        'email' => sanitize_email(wp_unslash($_POST['co_email'] ?? '')),
        'address' => sanitize_textarea_field(wp_unslash($_POST['co_address'] ?? '')),
        'note' => sanitize_textarea_field(wp_unslash($_POST['co_note'] ?? '')),
        'payment' => sanitize_key(wp_unslash($_POST['co_payment'] ?? 'cod')),
    );
    $beauty_checkout_state['old'] = $old;
    $errors = array();
    if (mb_strlen($old['name']) < 2) $errors[] = 'Vui lòng nhập họ tên.';
    if (!preg_match('/^[0-9+().\s-]{8,20}$/', $old['phone'])) $errors[] = 'Số điện thoại chưa hợp lệ.';
    if ($old['email'] && !is_email($old['email'])) $errors[] = 'Email chưa hợp lệ.';
    if (mb_strlen($old['address']) < 8) $errors[] = 'Vui lòng nhập địa chỉ nhận hàng đầy đủ.';
    if (!in_array($old['payment'], array('cod', 'bank'), true)) $old['payment'] = 'cod';
    $cart = beauty_cart_data();
    if (!$cart['lines']) $errors[] = 'Giỏ hàng đang trống hoặc sản phẩm không còn khả dụng.';
    $ip_key = 'beauty_co_' . md5(isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'x');
    if (!$errors && get_transient($ip_key)) $errors[] = 'Bạn vừa gửi đơn hàng. Vui lòng đợi ít phút trước khi gửi tiếp.';
    if ($errors) { $beauty_checkout_state['errors'] = $errors; return; }

    $shipping = beauty_shipping_fee($cart['subtotal']);
    $total = $cart['subtotal'] + $shipping;
    $order_id = wp_insert_post(array('post_type' => 'spa_order', 'post_status' => 'publish', 'post_title' => 'Đơn hàng mới'), true);
    if (is_wp_error($order_id) || !$order_id) { $beauty_checkout_state['errors'][] = 'Không thể tạo đơn hàng. Vui lòng thử lại hoặc gọi hotline.'; return; }
    $code = 'BS-' . wp_date('ymd') . '-' . $order_id;
    $key = wp_generate_password(14, false);
    wp_update_post(array('ID' => $order_id, 'post_title' => $code));
    foreach (array('_o_items' => $cart['lines'], '_o_subtotal' => $cart['subtotal'], '_o_shipping' => $shipping, '_o_total' => $total, '_o_name' => $old['name'], '_o_phone' => $old['phone'], '_o_email' => $old['email'], '_o_address' => $old['address'], '_o_note' => $old['note'], '_o_payment' => $old['payment'], '_o_status' => 'cho-xac-nhan', '_o_key' => $key) as $k => $v) update_post_meta($order_id, $k, $v);
    set_transient($ip_key, 1, 30);

    $body = "Mã đơn: $code\nKhách: {$old['name']}\nĐiện thoại: {$old['phone']}\nEmail: {$old['email']}\nĐịa chỉ: {$old['address']}\nGhi chú: {$old['note']}\nThanh toán: " . ('bank' === $old['payment'] ? 'Chuyển khoản' : 'Khi nhận hàng') . "\n\n";
    foreach ($cart['lines'] as $l) $body .= '- ' . $l['title'] . ' x' . $l['qty'] . ' = ' . beauty_money($l['total']) . "\n";
    $body .= "\nTạm tính: " . beauty_money($cart['subtotal']) . "\nVận chuyển: " . beauty_money($shipping) . "\nTổng: " . beauty_money($total) . "\n";
    $admin_to = sanitize_email(beauty_contact_info('email'));
    if (!$admin_to || false !== strpos($admin_to, 'example.com')) $admin_to = get_option('admin_email');
    wp_mail($admin_to, '[' . get_bloginfo('name') . '] Đơn hàng mới ' . $code, $body);
    if ($old['email']) wp_mail($old['email'], '[' . get_bloginfo('name') . '] Đã nhận đơn hàng ' . $code, "Cảm ơn bạn đã đặt hàng. Spa sẽ liên hệ xác nhận đơn sớm.\n\n" . $body);

    beauty_cart_save(array());
    wp_safe_redirect(add_query_arg(array('oid' => $order_id, 'k' => $key), beauty_checkout_url())); exit;
}
add_action('template_redirect', 'beauty_process_checkout');

/* ---------- Shortcode giỏ hàng / thanh toán ---------- */
function beauty_cart_shortcode() {
    if (beauty_use_woocommerce()) return '<p>Giỏ hàng đang do WooCommerce quản lý.</p>';
    $d = beauty_cart_data();
    ob_start();
    if (!$d['lines']) {
        echo '<div class="empty-state"><h2>Giỏ hàng đang trống</h2><p>Hãy chọn sản phẩm bạn quan tâm.</p><a class="btn" href="' . esc_url(beauty_shop_url()) . '">Xem sản phẩm</a></div>';
        return ob_get_clean();
    }
    $ship = beauty_shipping_fee($d['subtotal']); ?>
    <div class="bv-cart">
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="bv-cart-lines">
        <input type="hidden" name="action" value="beauty_cart"><input type="hidden" name="op" value="update">
        <?php foreach ($d['lines'] as $l) : ?>
        <div class="bv-cart-row">
          <a class="bv-cart-thumb" href="<?php echo esc_url(get_permalink($l['id'])); ?>"><?php echo beauty_product_image($l['id'], 'thumbnail'); ?></a>
          <div class="bv-cart-info"><a href="<?php echo esc_url(get_permalink($l['id'])); ?>"><strong><?php echo esc_html($l['title']); ?></strong></a><small><?php echo esc_html($l['size']); ?></small><span><?php echo esc_html(beauty_money($l['price'])); ?></span></div>
          <label class="bv-qty"><span class="screen-reader-text">Số lượng</span><input type="number" min="1" max="99" name="qty_map[<?php echo (int) $l['id']; ?>]" value="<?php echo (int) $l['qty']; ?>" inputmode="numeric"></label>
          <div class="bv-cart-total"><?php echo esc_html(beauty_money($l['total'])); ?></div>
          <button class="bv-cart-remove" type="submit" name="remove" value="<?php echo (int) $l['id']; ?>" aria-label="<?php echo esc_attr('Xóa ' . $l['title']); ?>">×</button>
        </div>
        <?php endforeach; ?>
        <div class="bv-actions"><button class="btn btn-outline" type="submit">Cập nhật giỏ hàng</button><a class="btn btn-outline" href="<?php echo esc_url(beauty_shop_url()); ?>">Tiếp tục mua sắm</a></div>
      </form>
      <aside class="bv-cart-summary bv-side-card"><h3>Tổng đơn hàng</h3>
        <dl><div><dt>Tạm tính</dt><dd><?php echo esc_html(beauty_money($d['subtotal'])); ?></dd></div><div><dt>Vận chuyển</dt><dd><?php echo $ship ? esc_html(beauty_money($ship)) : 'Miễn phí / spa báo khi xác nhận'; ?></dd></div><div class="bv-cart-grand"><dt>Tổng cộng</dt><dd><?php echo esc_html(beauty_money($d['subtotal'] + $ship)); ?></dd></div></dl>
        <a class="btn" href="<?php echo esc_url(beauty_checkout_url()); ?>">Tiến hành thanh toán</a>
      </aside>
    </div>
    <?php return ob_get_clean();
}
add_shortcode('beauty_cart', 'beauty_cart_shortcode');

function beauty_checkout_shortcode() {
    if (beauty_use_woocommerce()) return '<p>Thanh toán đang do WooCommerce quản lý.</p>';
    global $beauty_checkout_state;
    ob_start();
    // Trang cảm ơn
    if (!empty($_GET['oid']) && !empty($_GET['k'])) {
        $oid = absint($_GET['oid']);
        $key = sanitize_text_field(wp_unslash($_GET['k']));
        if ($oid && 'spa_order' === get_post_type($oid) && hash_equals((string) get_post_meta($oid, '_o_key', true), $key)) {
            echo '<div class="bv-thanks"><h2>Cảm ơn bạn đã đặt hàng!</h2><p>Mã đơn của bạn: <strong>' . esc_html(get_the_title($oid)) . '</strong>. Đơn hàng đang <strong>chờ spa xác nhận</strong>; nhân viên sẽ liên hệ qua số điện thoại bạn đã cung cấp.</p>';
            echo '<p>Tổng thanh toán: <strong>' . esc_html(beauty_money((int) get_post_meta($oid, '_o_total', true))) . '</strong></p>';
            if ('bank' === get_post_meta($oid, '_o_payment', true)) {
                $bank = trim((string) get_theme_mod('beaute_spa_bank_info', ''));
                echo '<div class="bv-bank"><strong>Thông tin chuyển khoản</strong><br>' . ($bank ? nl2br(esc_html($bank)) : 'Spa sẽ gửi thông tin chuyển khoản khi xác nhận đơn.') . '<br>Nội dung chuyển khoản: ' . esc_html(get_the_title($oid)) . '</div>';
            }
            echo '<a class="btn" href="' . esc_url(beauty_shop_url()) . '">Tiếp tục mua sắm</a></div>';
            return ob_get_clean();
        }
    }
    $d = beauty_cart_data();
    if (!$d['lines']) {
        echo '<div class="empty-state"><h2>Giỏ hàng đang trống</h2><a class="btn" href="' . esc_url(beauty_shop_url()) . '">Xem sản phẩm</a></div>';
        return ob_get_clean();
    }
    $ship = beauty_shipping_fee($d['subtotal']);
    $o = wp_parse_args($beauty_checkout_state['old'], array('name' => '', 'phone' => '', 'email' => '', 'address' => '', 'note' => '', 'payment' => 'cod'));
    foreach ($beauty_checkout_state['errors'] as $err) echo '<div class="notice error">' . esc_html($err) . '</div>'; ?>
    <div class="bv-cart">
      <form method="post" class="bv-checkout-form booking-form" action="<?php echo esc_url(beauty_checkout_url()); ?>">
        <?php wp_nonce_field('beauty_checkout', 'beauty_checkout_nonce'); ?>
        <div class="screen-reader-text" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
        <h3>Thông tin nhận hàng</h3>
        <label>Họ tên *<input type="text" name="co_name" value="<?php echo esc_attr($o['name']); ?>" required autocomplete="name"></label>
        <label>Số điện thoại *<input type="tel" name="co_phone" value="<?php echo esc_attr($o['phone']); ?>" required autocomplete="tel"></label>
        <label>Email (không bắt buộc)<input type="email" name="co_email" value="<?php echo esc_attr($o['email']); ?>" autocomplete="email"></label>
        <label>Địa chỉ nhận hàng *<textarea name="co_address" rows="3" required autocomplete="street-address"><?php echo esc_textarea($o['address']); ?></textarea></label>
        <label>Ghi chú<textarea name="co_note" rows="2"><?php echo esc_textarea($o['note']); ?></textarea></label>
        <fieldset class="bv-pay"><legend>Phương thức thanh toán</legend>
          <label><input type="radio" name="co_payment" value="cod" <?php checked($o['payment'], 'cod'); ?>> Thanh toán khi nhận hàng</label>
          <label><input type="radio" name="co_payment" value="bank" <?php checked($o['payment'], 'bank'); ?>> Chuyển khoản (spa gửi thông tin sau khi xác nhận)</label>
        </fieldset>
        <button class="btn" type="submit">Đặt hàng</button>
        <p class="bv-note">Đơn hàng chỉ có hiệu lực sau khi spa liên hệ xác nhận.</p>
      </form>
      <aside class="bv-cart-summary bv-side-card"><h3>Đơn hàng của bạn</h3>
        <ul class="bv-side-list"><?php foreach ($d['lines'] as $l) : ?><li><?php echo esc_html($l['title'] . ' × ' . $l['qty']); ?><strong><?php echo esc_html(beauty_money($l['total'])); ?></strong></li><?php endforeach; ?></ul>
        <dl><div><dt>Vận chuyển</dt><dd><?php echo $ship ? esc_html(beauty_money($ship)) : 'Miễn phí / spa báo khi xác nhận'; ?></dd></div><div class="bv-cart-grand"><dt>Tổng cộng</dt><dd><?php echo esc_html(beauty_money($d['subtotal'] + $ship)); ?></dd></div></dl>
      </aside>
    </div>
    <?php return ob_get_clean();
}
add_shortcode('beauty_checkout', 'beauty_checkout_shortcode');

/* ---------- Tạo trang Giỏ hàng / Thanh toán một lần ---------- */
function beauty_shop_seed_pages() {
    if (get_option('beauty_shop_pages_done')) return;
    foreach (array('gio-hang' => array('Giỏ hàng', '[beauty_cart]'), 'thanh-toan' => array('Thanh toán', '[beauty_checkout]')) as $slug => $def) {
        if (get_page_by_path($slug)) continue;
        wp_insert_post(array('post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $def[0], 'post_content' => $def[1]));
    }
    update_option('beauty_shop_pages_done', 1, false);
}
add_action('init', 'beauty_shop_seed_pages', 45);

/* ---------- Tùy biến: vận chuyển, chuyển khoản ---------- */
function beauty_shop_customize($wp_customize) {
    $wp_customize->add_section('beauty_shop', array('title' => 'Cửa hàng (giỏ hàng tích hợp)', 'priority' => 36));
    $fields = array('beaute_spa_ship_fee' => array('Phí vận chuyển cố định (VNĐ, 0 = miễn phí/báo sau)', 'absint', 'number'), 'beaute_spa_free_ship' => array('Miễn phí vận chuyển từ (VNĐ, 0 = không áp dụng)', 'absint', 'number'), 'beaute_spa_bank_info' => array('Thông tin chuyển khoản (ngân hàng, số tài khoản, chủ tài khoản)', 'sanitize_textarea_field', 'textarea'));
    foreach ($fields as $id => $f) {
        $wp_customize->add_setting($id, array('default' => '', 'sanitize_callback' => $f[1]));
        $wp_customize->add_control($id, array('label' => $f[0], 'section' => 'beauty_shop', 'type' => $f[2]));
    }
}
add_action('customize_register', 'beauty_shop_customize');
