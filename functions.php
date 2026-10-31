<?php
/**
 * Beaute Spa Beauty theme functions.
 */
if (!defined('ABSPATH')) exit;

define('BEAUTY_SPA_VERSION', '9.1.0');

/** Tìm bài theo tiêu đề chính xác (thay cho get_page_by_title đã bị đánh dấu lỗi thời từ WP 6.2). */
function beauty_find_post_by_title($title, $output = OBJECT, $post_type = 'page') {
    $found = get_posts(array('post_type' => $post_type, 'title' => $title, 'post_status' => array('publish', 'draft', 'pending', 'private', 'future'), 'numberposts' => 1, 'orderby' => 'ID', 'order' => 'ASC', 'no_found_rows' => true));
    return $found ? $found[0] : null;
}

/** Thông tin liên hệ lấy từ Tùy biến; giá trị mặc định là dữ liệu MẪU cần thay bằng thông tin thật. */
function beauty_contact_info($key) {
    $defaults = array('phone' => '0900 000 000', 'email' => 'beauty@example.com', 'address' => 'Hải Châu, Đà Nẵng (địa chỉ mẫu)', 'hours' => '09:00 - 20:00, hằng ngày', 'zalo' => '', 'messenger' => '', 'map' => '');
    return trim((string) get_theme_mod('beaute_spa_' . $key, isset($defaults[$key]) ? $defaults[$key] : ''));
}

/** Bài viết "Trải nghiệm tại Beauty Spa" ở trang chủ. Sửa trong Giao diện > Tùy biến > Bài trải nghiệm trang chủ. */
function beauty_experience_defaults() {
    return array(
        'title' => 'Một buổi chăm sóc, bắt đầu bằng việc được lắng nghe',
        'body'  => "Bước vào Beauty Spa, bạn không cần vội. Ánh sáng dịu, không gian yên tĩnh và nhịp làm việc chậm rãi giúp bạn tạm gác lại những bộn bề thường ngày.\n\nTrước khi bắt đầu, chuyên viên dành vài phút trò chuyện cùng bạn: làn da dạo này ra sao, bạn thường khô hay đổ dầu, có vùng nào nhạy cảm hay đang dùng sản phẩm nào không. Những câu hỏi nhỏ ấy giúp liệu trình được điều chỉnh theo đúng làn da của bạn, thay vì áp dụng một công thức chung cho tất cả.\n\nKhi nằm xuống giường spa, mọi thao tác đều nhẹ và chậm. Bạn có thể nói với chuyên viên nếu muốn lực tay nhẹ hơn hoặc nhiệt độ ấm hơn, và chỉ cần nhắm mắt nghỉ ngơi.\n\nKết thúc buổi chăm sóc, chuyên viên gợi ý cách chăm sóc tại nhà trong những ngày tiếp theo, từ bước làm sạch đến thói quen chống nắng, để cảm giác dễ chịu sau spa được giữ lâu hơn.",
    );
}
function beauty_experience_content() {
    $d = beauty_experience_defaults();
    $title = trim((string) get_theme_mod('beaute_spa_exp_title', ''));
    $body = trim((string) get_theme_mod('beaute_spa_exp_body', ''));
    if ('' === $title) $title = $d['title'];
    if ('' === $body) $body = $d['body'];
    $paras = array_values(array_filter(array_map('trim', preg_split('/\R{2,}/', $body))));
    return array('title' => $title, 'paragraphs' => $paras);
}

/** Phản hồi minh họa, chỉ hiện khi chưa có đánh giá thật nào trong Admin > Đánh giá khách hàng. */
function beauty_sample_feedback() {
    return array(
        array('stars' => 5, 'name' => 'Ngọc Anh', 'meta' => 'Chăm sóc da mặt', 'text' => 'Mình thích nhất là được hỏi kỹ về làn da trước khi làm. Chuyên viên điều chỉnh lực tay theo ý mình, làm xong da mềm và người nhẹ hẳn.'),
        array('stars' => 5, 'name' => 'Minh Thư', 'meta' => 'Massage thư giãn', 'text' => 'Không gian yên tĩnh, đúng kiểu nghỉ ngơi sau một tuần dài. Nhân viên nhẹ nhàng và không thúc ép mua thêm dịch vụ.'),
        array('stars' => 5, 'name' => 'Thanh Hà', 'meta' => 'Gội đầu dưỡng sinh', 'text' => 'Gội đầu rất dễ chịu, vai gáy đỡ mỏi nhiều. Sau buổi làm còn được dặn cách chăm sóc tóc tại nhà, mình thấy rất chu đáo.'),
    );
}

/** Liên kết bản đồ: dùng liên kết đã cấu hình, hoặc tạo từ địa chỉ thật (không tạo từ địa chỉ mẫu). */
function beauty_map_url() {
    $map = beauty_contact_info('map');
    if ($map) return $map;
    $address = trim(preg_replace('/\s*\(.*?\)\s*/u', ' ', beauty_contact_info('address')));
    return $address ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($address) : '';
}

/** Trả về URL ảnh trong theme; nếu tệp không tồn tại thì dùng ảnh dự phòng cục bộ. */
function beauty_theme_image_url($file) {
    $file = ltrim((string) $file, '/');
    if (!$file || !file_exists(get_template_directory() . '/assets/images/' . $file)) $file = 'spa-album-room.jpg';
    return get_template_directory_uri() . '/assets/images/' . $file;
}

/** Nội dung mặc định của 5 slide banner. Mọi trường đều sửa được trong Giao diện > Tùy biến > Banner trang chủ. */
function beauty_hero_defaults() {
    return array(
        array('image'=>'spa-album-facial.jpg','eyebrow'=>'Chăm sóc da chuyên sâu','title'=>'Đánh thức vẻ đẹp tự nhiên của làn da','desc'=>'Làm sạch, cấp ẩm và chăm sóc da theo quy trình được trao đổi rõ ràng, phù hợp với tình trạng da của bạn.','cta'=>'Xem dịch vụ chăm sóc da','url'=>'/dich-vu/','alt'=>'Khách hàng được chăm sóc da mặt tại spa'),
        array('image'=>'spa-album-massage.jpg','eyebrow'=>'Massage và thư giãn','title'=>'Dành thời gian cho cơ thể được nghỉ ngơi','desc'=>'Liệu trình massage thư giãn với lực được điều chỉnh theo cảm nhận của bạn.','cta'=>'Xem bảng giá','url'=>'/bang-gia/','alt'=>'Massage lưng thư giãn tại spa'),
        array('image'=>'spa-album-hairwash.jpg','eyebrow'=>'Gội đầu dưỡng sinh','title'=>'Thư giãn từ da đầu, cân bằng từ bên trong','desc'=>'Gội đầu kết hợp massage da đầu, cổ và vai trong không gian ấm áp, yên tĩnh.','cta'=>'Đặt lịch gội đầu','url'=>'/dat-lich/','alt'=>'Gội đầu dưỡng sinh tại bồn gội'),
        array('image'=>'spa-service-hair-removal.jpg','eyebrow'=>'Triệt lông và chăm sóc cơ thể','title'=>'Chăm sóc cơ thể nhẹ nhàng, tự tin mỗi ngày','desc'=>'Tư vấn tình trạng da và vùng cần thực hiện trước khi bắt đầu mọi liệu trình.','cta'=>'Xem dịch vụ','url'=>'/dich-vu/','alt'=>'Dịch vụ triệt lông tại spa'),
        array('image'=>'spa-album-products.jpg','eyebrow'=>'Chăm sóc tại nhà','title'=>'Duy trì làn da khỏe đẹp mỗi ngày','desc'=>'Chọn sản phẩm chăm sóc da, tóc và cơ thể phù hợp để tiếp nối trải nghiệm tại spa.','cta'=>'Xem sản phẩm','url'=>'{shop}','alt'=>'Bộ sản phẩm chăm sóc da đặt trên khay đá'),
    );
}

function beauty_hero_slides() {
    $slides = array();
    foreach (beauty_hero_defaults() as $i => $d) {
        $n = $i + 1;
        if (!get_theme_mod("beauty_hero_{$n}_show", true)) continue;
        $slide = array('attachment' => absint(get_theme_mod("beauty_hero_{$n}_image", 0)), 'file' => $d['image']);
        foreach (array('eyebrow', 'title', 'desc', 'cta', 'url', 'alt') as $key) {
            $value = trim((string) get_theme_mod("beauty_hero_{$n}_{$key}", ''));
            $slide[$key] = ('' !== $value) ? $value : $d[$key];
        }
        if ('{shop}' === $slide['url']) { $slide['url'] = beauty_shop_url(); } elseif (0 === strpos($slide['url'], '/')) { $slide['url'] = home_url($slide['url']); }
        $slides[] = $slide;
    }
    return $slides;
}

function beauty_hero_image_html($slide, $index) {
    $eager = (0 === $index);
    if (!empty($slide['attachment'])) {
        $alt = get_post_meta($slide['attachment'], '_wp_attachment_image_alt', true);
        $html = wp_get_attachment_image($slide['attachment'], 'large', false, array('class' => 'hero-img', 'loading' => $eager ? 'eager' : 'lazy', 'fetchpriority' => $eager ? 'high' : 'auto', 'decoding' => 'async', 'alt' => $alt ? $alt : $slide['title']));
        if ($html) return $html;
    }
    return '<img class="hero-img" src="' . esc_url(beauty_theme_image_url($slide['file'])) . '" alt="' . esc_attr($slide['alt']) . '" width="504" height="504" loading="' . ($eager ? 'eager' : 'lazy') . '" fetchpriority="' . ($eager ? 'high' : 'auto') . '" decoding="async">';
}

function beauty_hero_customize_register($wp_customize) {
    $wp_customize->add_section('beauty_hero', array('title' => 'Banner trang chủ (slider)', 'priority' => 34, 'description' => 'Tối đa 5 slide. Để trống ô nào thì dùng nội dung/ảnh minh họa mặc định của theme. Nên thay bằng ảnh thật của cơ sở.'));
    foreach (beauty_hero_defaults() as $i => $d) {
        $n = $i + 1;
        $wp_customize->add_setting("beauty_hero_{$n}_show", array('default' => true, 'sanitize_callback' => 'wp_validate_boolean'));
        $wp_customize->add_control("beauty_hero_{$n}_show", array('label' => "Slide {$n}: hiển thị", 'section' => 'beauty_hero', 'type' => 'checkbox'));
        $wp_customize->add_setting("beauty_hero_{$n}_image", array('default' => 0, 'sanitize_callback' => 'absint'));
        $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, "beauty_hero_{$n}_image", array('label' => "Slide {$n}: ảnh", 'section' => 'beauty_hero', 'mime_type' => 'image')));
        $fields = array('eyebrow' => array('Nhãn nhỏ', 'sanitize_text_field', 'text'), 'title' => array('Tiêu đề', 'sanitize_text_field', 'text'), 'desc' => array('Mô tả', 'sanitize_textarea_field', 'textarea'), 'cta' => array('Chữ trên nút', 'sanitize_text_field', 'text'), 'url' => array('Liên kết nút (vd: /dat-lich/)', 'esc_url_raw', 'text'));
        foreach ($fields as $key => $f) {
            $wp_customize->add_setting("beauty_hero_{$n}_{$key}", array('default' => '', 'sanitize_callback' => $f[1]));
            $wp_customize->add_control("beauty_hero_{$n}_{$key}", array('label' => "Slide {$n}: {$f[0]}", 'section' => 'beauty_hero', 'type' => $f[2]));
        }
    }
}
add_action('customize_register', 'beauty_hero_customize_register');

/** Số lượng giỏ hàng cập nhật ngay khi thêm sản phẩm (AJAX fragments của WooCommerce). */
function beauty_cart_count_fragment($fragments) {
    $count = (function_exists('WC') && WC()->cart) ? (int) WC()->cart->get_cart_contents_count() : 0;
    $fragments['span.cart-count'] = '<span class="cart-count">' . $count . '</span>';
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'beauty_cart_count_fragment');

function beaute_spa_setup() {
    load_theme_textdomain('beaute-spa', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array('height' => 90, 'width' => 240, 'flex-height' => true, 'flex-width' => true));
    add_theme_support('html5', array('search-form','comment-form','comment-list','gallery','caption','style','script'));
    add_theme_support('woocommerce');
    register_nav_menus(array('primary' => __('Menu chính', 'beaute-spa'), 'footer' => __('Menu chân trang', 'beaute-spa')));
}
add_action('after_setup_theme', 'beaute_spa_setup');

function beaute_spa_assets() {
    /* Font: Playfair Display (tiêu đề) + Montserrat (nội dung), có hỗ trợ tiếng Việt. Nếu không tải được, CSS tự dùng font có sẵn trong theme. */
    wp_enqueue_style('beauty-google-fonts', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap', array(), null);
    wp_enqueue_style('beaute-spa-style', get_stylesheet_uri(), array('beauty-google-fonts'), (string) filemtime(get_stylesheet_directory() . '/style.css')); /* [FIX CACHE] đổi version theo thời gian sửa file */
}
add_action('wp_enqueue_scripts', 'beaute_spa_assets');
/* Trang Giới thiệu (template page-gioi-thieu.php): CSS + JS riêng, chỉ nạp ở trang này.
 * about-luxury.css (bản cũ) không còn được dùng nữa. */
function beauty_about_luxury_assets() {
    if (!is_page('gioi-thieu')) return;
    wp_enqueue_style('beauty-about-fonts', 'https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap', array(), null);
    wp_enqueue_style(
        'beauty-about-page',
        get_template_directory_uri() . '/assets/css/about-page.css',
        array('beaute-spa-style', 'beauty-about-fonts'),
        file_exists(get_template_directory() . '/assets/css/about-page.css') ? (string) filemtime(get_template_directory() . '/assets/css/about-page.css') : '2.0.0'
    );
    wp_enqueue_script(
        'beauty-about-page',
        get_template_directory_uri() . '/assets/js/about-page.js',
        array(),
        file_exists(get_template_directory() . '/assets/js/about-page.js') ? (string) filemtime(get_template_directory() . '/assets/js/about-page.js') : '2.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'beauty_about_luxury_assets', 99);

/* Đánh dấu mục "Về chúng tôi/Giới thiệu" trên menu bằng gạch chân nâu khi đang ở trang này */
function beauty_about_body_class($classes) {
    if (is_page('gioi-thieu')) $classes[] = 'ba-about-active';
    return $classes;
}
add_filter('body_class', 'beauty_about_body_class');

function beauty_fonts_resource_hints($urls, $relation_type) {
    if ('preconnect' === $relation_type) {
        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = array('href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous');
    }
    return $urls;
}
add_filter('wp_resource_hints', 'beauty_fonts_resource_hints', 10, 2);

function beaute_spa_pricing_assets() {
    if (is_page_template('page-templates/template-pricing.php')) {
        wp_enqueue_script('beaute-spa-pricing-filter', get_template_directory_uri() . '/assets/js/pricing-filter.js', array(), BEAUTY_SPA_VERSION, true);
    }
}
add_action('wp_enqueue_scripts', 'beaute_spa_pricing_assets');

function beaute_spa_register_content() {
    register_post_type('spa_service', array(
        'labels' => array('name'=>'Dịch vụ Spa','singular_name'=>'Dịch vụ','add_new_item'=>'Thêm dịch vụ mới','edit_item'=>'Sửa dịch vụ'),
        'public'=>true,'has_archive'=>true,'rewrite'=>array('slug'=>'dich-vu'),'menu_icon'=>'dashicons-heart',
        'supports'=>array('title','editor','excerpt','thumbnail','revisions','page-attributes'),
        'show_in_rest'=>true
    ));
    register_taxonomy('spa_service_cat', 'spa_service', array(
        'labels'=>array('name'=>'Danh mục dịch vụ','singular_name'=>'Danh mục dịch vụ','add_new_item'=>'Thêm danh mục'),
        'hierarchical'=>true,'show_admin_column'=>true,'show_in_rest'=>true,'rewrite'=>array('slug'=>'nhom-dich-vu')
    ));
    register_post_type('spa_package', array(
        'labels'=>array('name'=>'Gói liệu trình & bảng giá','singular_name'=>'Gói liệu trình','add_new_item'=>'Thêm gói giá','edit_item'=>'Sửa gói giá'),
        'public'=>true,'has_archive'=>true,'rewrite'=>array('slug'=>'goi-lieu-trinh'),'menu_icon'=>'dashicons-money-alt',
        'supports'=>array('title','editor','excerpt','thumbnail','revisions','page-attributes'),'show_in_rest'=>true
    ));
    register_taxonomy('spa_package_cat','spa_package',array(
        'labels'=>array('name'=>'Nhóm bảng giá','singular_name'=>'Nhóm bảng giá'),
        'hierarchical'=>true,'show_admin_column'=>true,'show_in_rest'=>true,'rewrite'=>array('slug'=>'nhom-bang-gia')
    ));
    register_post_type('spa_promo',array('labels'=>array('name'=>'Ưu đãi & khuyến mãi','singular_name'=>'Ưu đãi','add_new_item'=>'Thêm ưu đãi mới','edit_item'=>'Sửa ưu đãi'),'public'=>true,'has_archive'=>true,'rewrite'=>array('slug'=>'uu-dai'),'menu_icon'=>'dashicons-megaphone','supports'=>array('title','editor','excerpt','thumbnail','revisions','page-attributes'),'show_in_rest'=>true));
    register_taxonomy('spa_promo_cat','spa_promo',array('labels'=>array('name'=>'Danh mục ưu đãi','singular_name'=>'Danh mục ưu đãi'),'hierarchical'=>true,'show_admin_column'=>true,'show_in_rest'=>true,'rewrite'=>array('slug'=>'loai-uu-dai')));
}
add_action('init', 'beaute_spa_register_content');

function beaute_spa_meta_boxes() {
    add_meta_box('beaute_spa_service_details','Thông tin dịch vụ','beaute_spa_service_fields','spa_service','normal','high');
    add_meta_box('beaute_spa_package_details','Thông tin gói giá','beaute_spa_package_fields','spa_package','normal','high');
}
add_action('add_meta_boxes','beaute_spa_meta_boxes');

function beaute_spa_service_fields($post) {
    wp_nonce_field('beaute_spa_save_meta','beaute_spa_meta_nonce');
    $price = get_post_meta($post->ID,'_spa_price',true);
    $duration = get_post_meta($post->ID,'_spa_duration',true);
    $featured = get_post_meta($post->ID,'_spa_featured',true);
    $sale = get_post_meta($post->ID,'_spa_sale_price',true);
    echo '<p><label>Giá từ (VNĐ; để trống nếu chưa có giá)<br><input type="number" min="0" name="spa_price" value="'.esc_attr($price).'" style="width:100%"></label></p>';
    echo '<p><label>Giá ưu đãi (VNĐ; để trống nếu không có)<br><input type="number" min="0" name="spa_sale_price" value="'.esc_attr($sale).'" style="width:100%"></label></p>';
    echo '<p><label>Thời lượng (ví dụ: 60 phút)<br><input type="text" name="spa_duration" value="'.esc_attr($duration).'" style="width:100%"></label></p>';
    echo '<p><label><input type="checkbox" name="spa_featured" value="1" '.checked($featured,'1',false).'> Dịch vụ nổi bật / Best seller</label></p>';
}
function beaute_spa_package_fields($post) {
    wp_nonce_field('beaute_spa_save_meta','beaute_spa_meta_nonce');
    $price = get_post_meta($post->ID,'_spa_price',true);
    $sale = get_post_meta($post->ID,'_spa_sale_price',true);
    $duration = get_post_meta($post->ID,'_spa_duration',true);
    $service = get_post_meta($post->ID,'_spa_related_service',true);
    echo '<p><label>Giá niêm yết (VNĐ)<br><input type="number" min="0" name="spa_price" value="'.esc_attr($price).'" style="width:100%"></label></p>';
    echo '<p><label>Giá ưu đãi (để trống nếu không có)<br><input type="number" min="0" name="spa_sale_price" value="'.esc_attr($sale).'" style="width:100%"></label></p>';
    echo '<p><label>Thời lượng<br><input type="text" name="spa_duration" value="'.esc_attr($duration).'" style="width:100%"></label></p>';
    echo '<p><label>ID dịch vụ liên quan (nếu có)<br><input type="number" min="0" name="spa_related_service" value="'.esc_attr($service).'" style="width:100%"></label></p>';
}
function beaute_spa_save_meta($post_id) {
    if (!isset($_POST['beaute_spa_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['beaute_spa_meta_nonce'])),'beaute_spa_save_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post',$post_id)) return;
    foreach (array('spa_price'=>'_spa_price','spa_sale_price'=>'_spa_sale_price','spa_duration'=>'_spa_duration','spa_related_service'=>'_spa_related_service') as $key=>$meta) {
        if (isset($_POST[$key])) {
            $value = ($key==='spa_duration') ? sanitize_text_field(wp_unslash($_POST[$key])) : absint($_POST[$key]);
            update_post_meta($post_id,$meta,$value);
        }
    }
    update_post_meta($post_id,'_spa_featured',isset($_POST['spa_featured'])?'1':'0');
}
add_action('save_post_spa_service','beaute_spa_save_meta');
add_action('save_post_spa_package','beaute_spa_save_meta');

function beaute_spa_customize_register($wp_customize) {
    $wp_customize->add_section('beaute_spa_contact',array('title'=>'Thông tin liên hệ Spa','priority'=>35));
    $fields = array(
        'beaute_spa_phone'=>array('Hotline (thông tin mẫu)','0900 000 000'),
        'beaute_spa_email'=>array('Email (thông tin mẫu)','beauty@example.com'),
        'beaute_spa_address'=>array('Địa chỉ (thông tin mẫu)','Hải Châu, Đà Nẵng (địa chỉ mẫu)'),
        'beaute_spa_hours'=>array('Giờ hoạt động (mẫu)','09:00 - 20:00, hằng ngày'),
        'beaute_spa_zalo'=>array('Liên kết Zalo',''),
        'beaute_spa_messenger'=>array('Liên kết Facebook / Messenger (dán link trang Facebook của spa)',''),
        'beaute_spa_map'=>array('Liên kết bản đồ (Google Maps, để trống nếu chưa có)','')
    );
    foreach ($fields as $id=>$data) {
        $wp_customize->add_setting($id,array('default'=>$data[1],'sanitize_callback'=>'sanitize_text_field'));
        $wp_customize->add_control($id,array('label'=>$data[0],'section'=>'beaute_spa_contact','type'=>'text'));
    }
    $wp_customize->add_section('beaute_spa_experience',array('title'=>'Bài trải nghiệm trang chủ','priority'=>36));
    $exp_d = beauty_experience_defaults();
    $wp_customize->add_setting('beaute_spa_exp_title',array('default'=>$exp_d['title'],'sanitize_callback'=>'sanitize_text_field'));
    $wp_customize->add_control('beaute_spa_exp_title',array('label'=>'Tiêu đề bài viết','section'=>'beaute_spa_experience','type'=>'text'));
    $wp_customize->add_setting('beaute_spa_exp_body',array('default'=>$exp_d['body'],'sanitize_callback'=>'sanitize_textarea_field'));
    $wp_customize->add_control('beaute_spa_exp_body',array('label'=>'Nội dung (cách nhau một dòng trống = một đoạn; đoạn đầu được in nổi bật)','section'=>'beaute_spa_experience','type'=>'textarea'));
}
add_action('customize_register','beaute_spa_customize_register');

function beaute_spa_price($post_id, $sale = false) {
    $regular = (int) get_post_meta($post_id, '_spa_price', true);
    $discount = (int) get_post_meta($post_id, '_spa_sale_price', true);

    // Chỉ coi là giá ưu đãi khi có đủ giá gốc và giá ưu đãi thấp hơn giá gốc.
    // Nhờ vậy bảng giá, thẻ dịch vụ, gói liệu trình và trang chi tiết luôn hiển thị cùng một giá hiệu lực.
    if ($sale) {
        if ($regular <= 0 || $discount <= 0 || $discount >= $regular) return '';
        $value = $discount;
    } else {
        $value = $regular;
    }
    if ($value <= 0) return '';
    return number_format_i18n($value, 0) . ' đ';
}

function beaute_spa_booking_shortcode() {
    $message = '';
    if (isset($_POST['beaute_spa_booking_submit'])) {
        $nonce = isset($_POST['beaute_spa_booking_nonce']) ? sanitize_text_field(wp_unslash($_POST['beaute_spa_booking_nonce'])) : '';
        if (!wp_verify_nonce($nonce,'beaute_spa_booking')) {
            $message = '<div class="notice error">Phiên gửi không hợp lệ. Vui lòng tải lại trang và thử lại.</div>';
        } else {
            $name = sanitize_text_field(wp_unslash($_POST['booking_name'] ?? ''));
            $phone = sanitize_text_field(wp_unslash($_POST['booking_phone'] ?? ''));
            $email = sanitize_email(wp_unslash($_POST['booking_email'] ?? ''));
            $service = absint($_POST['booking_service'] ?? ($_GET['dich-vu'] ?? ($_GET['service'] ?? 0)));
            $package = absint($_POST['booking_package'] ?? ($_GET['goi'] ?? 0));
            $date = sanitize_text_field(wp_unslash($_POST['booking_date'] ?? ''));
            $time = sanitize_text_field(wp_unslash($_POST['booking_time'] ?? ''));
            $note = sanitize_textarea_field(wp_unslash($_POST['booking_note'] ?? ''));
            $people = max(1, min(20, absint($_POST['booking_people'] ?? 1)));
            $method = sanitize_text_field(wp_unslash($_POST['booking_method'] ?? ''));
            $trap = sanitize_text_field(wp_unslash($_POST['website'] ?? ''));
            $ip_key = 'beauty_bk_' . md5(isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'x');
            $too_soon = ($date === wp_date('Y-m-d') && $time <= wp_date('H:i'));
            if ($trap !== '') {
                $message = '<div class="notice">Cảm ơn bạn.</div>';
            } elseif (get_transient($ip_key)) {
                $message = '<div class="notice error">Bạn vừa gửi yêu cầu. Vui lòng đợi ít phút rồi thử lại, hoặc gọi trực tiếp cho spa.</div>';
            } elseif ($too_soon) {
                $message = '<div class="notice error">Giờ mong muốn đã qua. Vui lòng chọn khung giờ sau thời điểm hiện tại.</div>';
            } elseif (!$name || !preg_match('/^[0-9+().\\s-]{8,20}$/',$phone) || !$date || !$time || strtotime($date) < strtotime(wp_date('Y-m-d'))) {
                $message = '<div class="notice error">Vui lòng nhập họ tên, số điện thoại hợp lệ, ngày và giờ mong muốn.</div>';
            } else {
                $service_name = ($service && get_post_type($service)==='spa_service') ? get_the_title($service) : 'Chưa chọn';
                $package_name = ($package && get_post_type($package)==='spa_package') ? get_the_title($package) : 'Không chọn';
                $post_id = wp_insert_post(array(
                    'post_type'=>'spa_booking','post_status'=>'private',
                    'post_title'=>'Đặt lịch - '.$name.' - '.current_time('Y-m-d H:i'),
                    'post_content'=>"Họ tên: $name\nĐiện thoại: $phone\nEmail: $email\nDịch vụ: $service_name\nSố người: $people\nLiên hệ xác nhận qua: $method\nNgày: $date\nGiờ: $time\nGhi chú: $note"
                ),true);
                if (!is_wp_error($post_id)) {
                    update_post_meta($post_id,'_booking_name',$name);
                    update_post_meta($post_id,'_booking_phone',$phone);
                    update_post_meta($post_id,'_booking_status','cho-xac-nhan');
                    set_transient($ip_key,1,60);
                    $admin_email = get_option('admin_email');
                    wp_mail($admin_email,'Yêu cầu đặt lịch Spa mới',"Khách hàng: $name\nĐiện thoại: $phone\nEmail: $email\nDịch vụ: $service_name\nSố người: $people\nLiên hệ xác nhận qua: $method\nNgày: $date\nGiờ: $time\nGhi chú: $note");
                    $message = '<div class="notice">Đã nhận yêu cầu đặt lịch của bạn. Spa sẽ liên hệ để xác nhận thời gian.</div>';
                } else {
                    $message = '<div class="notice error">Chưa gửi được yêu cầu. Vui lòng liên hệ trực tiếp với Spa.</div>';
                }
            }
        }
    }
    $services = get_posts(array('post_type'=>'spa_service','numberposts'=>-1,'post_status'=>'publish'));
    $selected_service = absint($_GET['dich-vu'] ?? ($_GET['service'] ?? 0));
    $selected_package = absint($_POST['booking_package'] ?? ($_GET['goi'] ?? 0));
    $packages = get_posts(array('post_type'=>'spa_package','numberposts'=>-1,'post_status'=>'publish')); 
    ob_start();
    echo wp_kses_post($message);
    ?>
    <form class="booking-form" method="post">
      <?php wp_nonce_field('beaute_spa_booking','beaute_spa_booking_nonce'); ?>
      <label>Họ và tên *<input name="booking_name" required maxlength="100"></label>
      <label>Số điện thoại *<input name="booking_phone" required inputmode="tel" maxlength="20"></label>
      <label>Email (không bắt buộc)<input type="email" name="booking_email"></label>
      <label>Dịch vụ muốn đặt<select name="booking_service"><option value="0">Chọn dịch vụ</option><?php foreach($services as $s): ?><option value="<?php echo esc_attr($s->ID); ?>" <?php selected($selected_service,$s->ID); ?>><?php echo esc_html($s->post_title); ?></option><?php endforeach; ?></select></label>
      <label>Gói liệu trình (không bắt buộc)<select name="booking_package"><option value="0">Không chọn gói</option><?php foreach($packages as $pkg): ?><option value="<?php echo esc_attr($pkg->ID); ?>" <?php selected($selected_package,$pkg->ID); ?>><?php echo esc_html($pkg->post_title); ?></option><?php endforeach; ?></select></label>
      <label>Số người<input type="number" name="booking_people" value="1" min="1" max="20"></label>
      <label>Xác nhận lịch qua<select name="booking_method"><option value="Điện thoại">Điện thoại</option><option value="Zalo">Zalo</option><option value="Messenger">Messenger</option><option value="Email">Email</option></select></label>
      <label>Ngày mong muốn *<input type="date" name="booking_date" min="<?php echo esc_attr(wp_date('Y-m-d')); ?>" required></label>
      <label>Giờ mong muốn *<input type="time" name="booking_time" required></label>
      <label class="full">Ghi chú<textarea name="booking_note" rows="4" maxlength="1500"></textarea></label>
      <label style="position:absolute;left:-9999px" aria-hidden="true">Để trống ô này<input name="website" tabindex="-1" autocomplete="off"></label>
      <div class="full"><button class="btn" type="submit" name="beaute_spa_booking_submit" value="1">Gửi yêu cầu đặt lịch</button><p class="muted">Đây là yêu cầu đặt lịch, chưa phải xác nhận lịch hẹn.</p></div>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('beaute_spa_booking','beaute_spa_booking_shortcode');

function beaute_spa_register_booking_type() {
    register_post_type('spa_booking',array(
        'labels'=>array('name'=>'Yêu cầu đặt lịch','singular_name'=>'Yêu cầu đặt lịch'),
        'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'menu_icon'=>'dashicons-calendar-alt',
        'supports'=>array('title','editor'),'capability_type'=>'post'
    ));
}
add_action('init','beaute_spa_register_booking_type',20);

function beaute_spa_excerpt($length=20) {
    $excerpt = get_the_excerpt();
    $words = preg_split('/\\s+/', wp_strip_all_tags($excerpt));
    if (count($words)>$length) $excerpt = implode(' ',array_slice($words,0,$length)).'…';
    echo esc_html($excerpt);
}

/** Contact form: safe server-side processing and email notification. */
function beauty_contact_form_shortcode(){
 $notice=''; if(isset($_GET['contact_sent']) && $_GET['contact_sent']==='1') $notice='<div class="beauty-form-success">Cảm ơn bạn đã liên hệ. Yêu cầu đã được gửi, Beauty sẽ phản hồi sau khi tiếp nhận.</div>';
 ob_start(); echo $notice; ?>
 <div class="beauty-contact-layout"><div class="beauty-contact-copy"><span class="eyebrow">BEAUTY SPA</span><h2>Chúng tôi sẵn sàng lắng nghe bạn</h2><p>Gửi câu hỏi về dịch vụ, sản phẩm hoặc nhu cầu đặt lịch. Vui lòng điền thông tin bên dưới, các trường có dấu * là bắt buộc.</p><ul><li><strong>Điện thoại</strong><br><?php echo esc_html(get_theme_mod('beaute_spa_phone','Vui lòng cập nhật hotline')); ?></li><li><strong>Địa chỉ</strong><br><?php echo esc_html(get_theme_mod('beaute_spa_address','Vui lòng cập nhật địa chỉ')); ?></li><li><strong>Giờ hoạt động</strong><br><?php echo esc_html(get_theme_mod('beaute_spa_hours','Vui lòng cập nhật giờ hoạt động')); ?></li></ul></div>
 <form class="beauty-contact-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="beauty_contact_submit"><?php wp_nonce_field('beauty_contact_submit','beauty_contact_nonce'); ?><label>Họ và tên *<input name="contact_name" required maxlength="100" autocomplete="name" placeholder="Tên của bạn"></label><label>Số điện thoại *<input name="contact_phone" required maxlength="30" autocomplete="tel" placeholder="Số điện thoại liên hệ"></label><label>Email<input type="email" name="contact_email" maxlength="150" autocomplete="email" placeholder="email@example.com"></label><label>Chủ đề<select name="contact_topic"><option value="Tư vấn dịch vụ">Tư vấn dịch vụ</option><option value="Sản phẩm">Sản phẩm</option><option value="Đặt lịch">Đặt lịch</option><option value="Góp ý khác">Góp ý khác</option></select></label><label class="full">Nội dung cần tư vấn *<textarea name="contact_message" required rows="5" maxlength="3000" placeholder="Bạn cần Beauty hỗ trợ điều gì?"></textarea></label><button class="btn full" type="submit">Gửi liên hệ →</button><p class="beauty-privacy-note">Thông tin chỉ được sử dụng để phản hồi yêu cầu liên hệ của bạn.</p></form></div>
 <?php return ob_get_clean();
}
add_shortcode('beauty_contact_form','beauty_contact_form_shortcode');
function beauty_contact_submit_handler(){
 if(!isset($_POST['beauty_contact_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['beauty_contact_nonce'])),'beauty_contact_submit')) wp_die('Yêu cầu không hợp lệ. Vui lòng quay lại và thử lại.');
 $name=sanitize_text_field(wp_unslash($_POST['contact_name']??'')); $phone=sanitize_text_field(wp_unslash($_POST['contact_phone']??'')); $email=sanitize_email(wp_unslash($_POST['contact_email']??'')); $topic=sanitize_text_field(wp_unslash($_POST['contact_topic']??'Góp ý khác')); $message=sanitize_textarea_field(wp_unslash($_POST['contact_message']??''));
 if(!$name || !$phone || !$message || (isset($_POST['contact_email']) && $_POST['contact_email']!=='' && !is_email($email))) wp_die('Vui lòng kiểm tra lại thông tin bắt buộc.');
 $to=get_option('admin_email'); $subject='Liên hệ Beauty Spa: '.$topic; $body="Họ tên: $name\nĐiện thoại: $phone\nEmail: $email\nChủ đề: $topic\n\nNội dung:\n$message"; $headers=array(); if($email) $headers[]='Reply-To: '.$name.' <'.$email.'>'; wp_mail($to,$subject,$body,$headers);
 wp_safe_redirect(add_query_arg('contact_sent','1',home_url('/lien-he/'))); exit;
}
add_action('admin_post_nopriv_beauty_contact_submit','beauty_contact_submit_handler'); add_action('admin_post_beauty_contact_submit','beauty_contact_submit_handler');
function beauty_assign_local_product_images(){
 if(!class_exists('WooCommerce') || !post_type_exists('product') || get_option('beauty_product_images_v1')) return;
 $map=array('Sữa rửa mặt dịu nhẹ Beauty'=>'sua-rua-mat','Nước cân bằng cấp ẩm Beauty'=>'toner','Kem dưỡng ẩm hằng ngày Beauty'=>'kem-duong','Kem chống nắng dùng ban ngày Beauty'=>'chong-nang','Sữa dưỡng thể Beauty'=>'sua-duong-the','Dầu gội chăm sóc tóc Beauty'=>'dau-goi','Serum cấp ẩm Beauty'=>'serum','Mặt nạ dưỡng ẩm Beauty'=>'mat-na','Tẩy trang dịu nhẹ Beauty'=>'tay-trang','Gel rửa mặt tạo bọt Beauty'=>'gel-rua-mat','Kem dưỡng ban đêm Beauty'=>'kem-dem','Tinh chất làm dịu da Beauty'=>'tinh-chat','Son dưỡng môi Beauty'=>'son-duong','Tẩy tế bào chết body Beauty'=>'tay-te-bao-chet','Sữa tắm dưỡng ẩm Beauty'=>'sua-tam','Kem dưỡng tay Beauty'=>'kem-tay','Dầu xả mềm mượt Beauty'=>'dau-xa','Bộ chăm sóc da cơ bản Beauty'=>'bo-cham-soc');
 foreach($map as $title=>$slug){$found=beauty_find_post_by_title($title,OBJECT,'product'); if(!$found || has_post_thumbnail($found->ID)) continue; $posts=array($found); $file=get_template_directory().'/assets/images/products/'.$slug.'.jpg'; if(!file_exists($file))continue; $upload=wp_upload_bits(basename($file),null,file_get_contents($file)); if(!empty($upload['error']))continue; $type=wp_check_filetype($upload['file'],null); $att=wp_insert_attachment(array('post_mime_type'=>$type['type'],'post_title'=>sanitize_file_name($title),'post_status'=>'inherit'),$upload['file'],$posts[0]->ID); if(!is_wp_error($att)){require_once ABSPATH.'wp-admin/includes/image.php'; $meta=wp_generate_attachment_metadata($att,$upload['file']); wp_update_attachment_metadata($att,$meta); set_post_thumbnail($posts[0]->ID,$att);}}
 update_option('beauty_product_images_v1',1);
}
add_action('init','beauty_assign_local_product_images',50);

/** Tạo dữ liệu khởi tạo Beauty một lần khi kích hoạt theme. Dữ liệu và giá là mẫu tham khảo để chủ website xác nhận trước khi công khai. */
function beaute_spa_seed_demo_content() {
    if (get_option('beaute_spa_seed_v2_done')) return;

    $service_categories = array(
        'cham-soc-da' => array('Chăm sóc da', 'Các liệu trình làm sạch, cấp ẩm và chăm sóc da cơ bản.'),
        'mun-va-lam-sach' => array('Làm sạch da & chăm sóc da mụn', 'Các bước làm sạch da và chăm sóc da có xu hướng nổi mụn.'),
        'massage-thu-gian' => array('Massage thư giãn', 'Liệu trình thư giãn cơ thể và chăm sóc tinh thần.'),
        'goi-dau-duong-sinh' => array('Gội đầu dưỡng sinh', 'Gội đầu kết hợp massage da đầu và thư giãn.'),
        'triet-long' => array('Triệt lông', 'Dịch vụ chăm sóc da và triệt lông theo vùng.'),
        'cham-soc-co-the' => array('Chăm sóc cơ thể', 'Tẩy tế bào chết và dưỡng ẩm cho cơ thể.'),
        'cham-soc-tay-chan' => array('Chăm sóc tay & chân', 'Chăm sóc móng, bàn tay và bàn chân.')
    );
    $term_ids = array();
    foreach ($service_categories as $slug => $data) {
        $term = term_exists($slug, 'spa_service_cat');
        if (!$term) $term = wp_insert_term($data[0], 'spa_service_cat', array('slug'=>$slug, 'description'=>$data[1]));
        if (!is_wp_error($term)) $term_ids[$slug] = (int)(is_array($term) ? $term['term_id'] : $term);
    }

    $services = array(
        array('Làm sạch và cấp ẩm cơ bản','Làm sạch da, cân bằng độ ẩm và chăm sóc nhẹ nhàng phù hợp với nhu cầu hằng ngày.',250000,45,'cham-soc-da'),
        array('Chăm sóc da chuyên sâu','Quy trình chăm sóc nhiều bước, lựa chọn sản phẩm theo tình trạng da sau khi tư vấn.',450000,75,'cham-soc-da'),
        array('Chăm sóc da mụn cơ bản','Làm sạch và chăm sóc da có mụn theo quy trình vệ sinh phù hợp. Không thay thế tư vấn y khoa.',350000,60,'mun-va-lam-sach'),
        array('Làm sạch da và tẩy tế bào chết','Hỗ trợ loại bỏ tế bào chết bề mặt và giúp da mềm mịn hơn.',300000,50,'mun-va-lam-sach'),
        array('Massage thư giãn toàn thân','Liệu trình massage thư giãn với lực massage được điều chỉnh theo cảm nhận của khách.',550000,60,'massage-thu-gian'),
        array('Massage lưng và vai gáy','Tập trung vùng lưng, vai và gáy để mang lại cảm giác thư giãn.',300000,30,'massage-thu-gian'),
        array('Gội đầu dưỡng sinh thư giãn','Gội đầu kết hợp massage da đầu, cổ và vai trong không gian thư giãn.',180000,45,'goi-dau-duong-sinh'),
        array('Gội đầu dưỡng sinh chuyên sâu','Gội và massage da đầu kết hợp chăm sóc tóc theo quy trình của spa.',280000,70,'goi-dau-duong-sinh'),
        array('Triệt lông vùng nách','Dịch vụ triệt lông theo vùng; cần tư vấn tình trạng da và liệu trình phù hợp trước khi thực hiện.',180000,20,'triet-long'),
        array('Triệt lông tay hoặc chân','Triệt lông theo vùng lựa chọn, thời gian thực hiện tùy diện tích và tình trạng lông.',350000,40,'triet-long'),
        array('Tẩy tế bào chết toàn thân','Làm sạch tế bào chết bề mặt và dưỡng ẩm sau liệu trình.',450000,60,'cham-soc-co-the'),
        array('Chăm sóc bàn tay và bàn chân','Làm sạch, chăm sóc móng và dưỡng ẩm cơ bản cho tay hoặc chân.',220000,45,'cham-soc-tay-chan')
    );
    foreach ($services as $s) {
        if (beauty_find_post_by_title($s[0], OBJECT, 'spa_service')) continue;
        $id = wp_insert_post(array('post_type'=>'spa_service','post_status'=>'publish','post_title'=>$s[0],'post_excerpt'=>$s[1],'post_content'=>'<h2>Giới thiệu dịch vụ</h2><p>'.esc_html($s[1]).'</p><h2>Quy trình tham khảo</h2><ol><li>Tư vấn nhu cầu và tình trạng hiện tại.</li><li>Chuẩn bị khu vực và thực hiện các bước phù hợp.</li><li>Hướng dẫn chăm sóc sau dịch vụ.</li></ol><p><em>Thời lượng và giá đang là dữ liệu mẫu để hoàn thiện bản demo. Vui lòng xác nhận với cơ sở trước khi công khai.</em></p>'), true);
        if (!is_wp_error($id)) {
            update_post_meta($id, '_spa_price', $s[2]); update_post_meta($id, '_spa_duration', $s[3].' phút');
            update_post_meta($id, '_spa_featured', in_array($s[0], array('Chăm sóc da chuyên sâu','Gội đầu dưỡng sinh thư giãn','Massage thư giãn toàn thân'), true) ? '1' : '0');
            if (isset($term_ids[$s[4]])) wp_set_object_terms($id, array($term_ids[$s[4]]), 'spa_service_cat');
        }
    }

    $package_categories = array('goi-cham-soc-da'=>'Gói chăm sóc da','goi-thu-gian'=>'Gói thư giãn','goi-ket-hop'=>'Combo kết hợp');
    foreach ($package_categories as $slug=>$name) if (!term_exists($slug,'spa_package_cat')) wp_insert_term($name,'spa_package_cat',array('slug'=>$slug));
    $packages = array(
        array('Combo da sạch khỏe','Kết hợp làm sạch và chăm sóc da cơ bản cho một buổi chăm sóc.',500000,450000,'goi-cham-soc-da'),
        array('Combo thư giãn vai gáy','Gội đầu dưỡng sinh kết hợp massage vai gáy.',420000,380000,'goi-thu-gian'),
        array('Ngày thư giãn trọn vẹn','Kết hợp gội đầu dưỡng sinh và massage thư giãn toàn thân.',730000,650000,'goi-ket-hop'),
        array('Liệu trình chăm sóc da 3 buổi','Gói tham khảo gồm ba buổi chăm sóc da cơ bản, lịch thực hiện theo tư vấn.',750000,690000,'goi-cham-soc-da'),
        array('Combo chăm sóc cơ thể','Kết hợp tẩy tế bào chết toàn thân và dưỡng ẩm sau liệu trình.',600000,550000,'goi-ket-hop')
    );
    foreach ($packages as $p) {
        if (beauty_find_post_by_title($p[0], OBJECT, 'spa_package')) continue;
        $id = wp_insert_post(array('post_type'=>'spa_package','post_status'=>'publish','post_title'=>$p[0],'post_excerpt'=>$p[1],'post_content'=>'<p>'.esc_html($p[1]).'</p><p>Thành phần gói và giá cần được cơ sở xác nhận trước khi áp dụng. Vui lòng đặt lịch để được tư vấn.</p>'), true);
        if (!is_wp_error($id)) { update_post_meta($id,'_spa_price',$p[2]); update_post_meta($id,'_spa_sale_price',$p[3]); if ($term=get_term_by('slug',$p[4],'spa_package_cat')) wp_set_object_terms($id,array((int)$term->term_id),'spa_package_cat'); }
    }

    $promo_categories = array('uu-dai-combo'=>'Ưu đãi combo','uu-dai-khach-moi'=>'Ưu đãi khách hàng mới','uu-dai-theo-mua'=>'Ưu đãi theo mùa');
    foreach ($promo_categories as $slug=>$name) if (!term_exists($slug,'spa_promo_cat')) wp_insert_term($name,'spa_promo_cat',array('slug'=>$slug));
    $promos = array(
        array('Ưu đãi trải nghiệm lần đầu','Gợi ý gói chăm sóc cơ bản để bạn làm quen với quy trình và không gian Beauty. Liên hệ để xác nhận điều kiện áp dụng.','uu-dai-khach-moi'),
        array('Combo gội đầu và massage','Kết hợp gội đầu dưỡng sinh cùng massage vai gáy trong một lần ghé thăm. Vui lòng xác nhận giá và lịch áp dụng.','uu-dai-combo'),
        array('Chăm sóc bản thân theo mùa','Tham khảo các lựa chọn chăm sóc da và cơ thể theo nhu cầu thời tiết. Hãy hỏi Beauty về chương trình hiện hành.','uu-dai-theo-mua')
    );
    foreach ($promos as $p) { if (!beauty_find_post_by_title($p[0],OBJECT,'spa_promo')) { $id=wp_insert_post(array('post_type'=>'spa_promo','post_status'=>'publish','post_title'=>$p[0],'post_excerpt'=>$p[1],'post_content'=>'<p>'.esc_html($p[1]).'</p><p><strong>Lưu ý:</strong> Nội dung ưu đãi mẫu, chưa phải chương trình đang có hiệu lực. Cập nhật điều kiện và thời hạn trước khi quảng bá.</p>')); if (!is_wp_error($id) && ($t=get_term_by('slug',$p[2],'spa_promo_cat'))) wp_set_object_terms($id,array((int)$t->term_id),'spa_promo_cat'); } }

    $blog_posts = array(
        array('Các bước chăm sóc da cơ bản tại nhà','Làm sạch dịu nhẹ, dưỡng ẩm và chống nắng là những bước nền tảng trong thói quen chăm sóc da. Hãy lựa chọn sản phẩm phù hợp với loại da và ngưng sử dụng nếu có dấu hiệu kích ứng.'),
        array('Cách chuẩn bị cho buổi gội đầu dưỡng sinh','Bạn có thể trao đổi trước về độ nhạy cảm của da đầu, tình trạng tóc và lực massage mong muốn. Nếu có tổn thương da đầu, hãy thông báo với nhân viên trước khi sử dụng dịch vụ.'),
        array('Chọn liệu trình spa theo nhu cầu cá nhân','Xác định mục tiêu, thời gian và ngân sách trước khi chọn dịch vụ. Nên hỏi rõ các bước thực hiện, sản phẩm sử dụng, chi phí phát sinh và hướng dẫn chăm sóc sau liệu trình.')
    );
    foreach ($blog_posts as $p) if (!beauty_find_post_by_title($p[0],OBJECT,'post')) wp_insert_post(array('post_type'=>'post','post_status'=>'publish','post_title'=>$p[0],'post_excerpt'=>$p[1],'post_content'=>'<p>'.esc_html($p[1]).'</p><p>Nội dung mang tính thông tin chung, không thay thế tư vấn chuyên môn. Nếu có vấn đề da liễu kéo dài hoặc nghiêm trọng, hãy tìm đến chuyên gia y tế phù hợp.</p>'));

    $pages = array(
        'gioi-thieu'=>array('Giới thiệu','<div class="about-intro"><p><strong>Beauty Spa — nơi bạn dành thời gian chăm sóc chính mình.</strong></p><p>Beauty được định hướng như một không gian chăm sóc sắc đẹp và thư giãn, nơi khách hàng có thể tìm hiểu dịch vụ, được giải thích rõ về quy trình và lựa chọn trải nghiệm phù hợp với nhu cầu cá nhân.</p></div><h2>Câu chuyện và định hướng</h2><p>Vẻ đẹp không chỉ nằm ở diện mạo mà còn đến từ cảm giác được quan tâm và có thời gian nghỉ ngơi. Vì vậy, Beauty hướng đến trải nghiệm chỉn chu từ lúc tiếp nhận nhu cầu, tư vấn dịch vụ đến hướng dẫn chăm sóc sau khi sử dụng dịch vụ. Nội dung lịch sử, ngày thành lập và câu chuyện người sáng lập cần được cập nhật theo thông tin chính thức của cơ sở.</p><h2>Tầm nhìn và sứ mệnh</h2><p><strong>Tầm nhìn:</strong> xây dựng trải nghiệm chăm sóc sắc đẹp gần gũi, dễ tiếp cận và minh bạch.</p><p><strong>Sứ mệnh:</strong> giúp khách hàng có thêm lựa chọn chăm sóc bản thân trong không gian thư giãn, với thông tin dịch vụ và chi phí rõ ràng.</p><h2>Giá trị cốt lõi</h2><ul><li><strong>Tận tâm:</strong> lắng nghe nhu cầu và tôn trọng lựa chọn của khách hàng.</li><li><strong>Minh bạch:</strong> giải thích dịch vụ, thời lượng và giá trước khi thực hiện.</li><li><strong>Chỉn chu:</strong> quan tâm đến vệ sinh, sự thoải mái và trải nghiệm trong từng bước.</li><li><strong>Cải thiện liên tục:</strong> tiếp nhận phản hồi để nâng cao chất lượng phục vụ.</li></ul><h2>Đội ngũ chăm sóc</h2><p>Đội ngũ có thể bao gồm nhân sự đón tiếp và tư vấn, chuyên viên chăm sóc da, kỹ thuật viên massage và nhân sự hỗ trợ khách hàng. Mỗi thành viên cần giới thiệu rõ vai trò và phạm vi chuyên môn của mình. Vui lòng bổ sung tên, ảnh chân dung, chứng chỉ và kinh nghiệm thực tế đã xác minh trước khi công bố hồ sơ nhân viên.</p><h2>Quy trình phục vụ</h2><ol><li>Tiếp nhận mong muốn và thông tin cần thiết của khách hàng.</li><li>Tư vấn dịch vụ, thời lượng, chi phí và lưu ý liên quan.</li><li>Chuẩn bị không gian và thực hiện các bước phù hợp với dịch vụ đã chọn.</li><li>Trao đổi cảm nhận, hướng dẫn chăm sóc sau dịch vụ nếu cần.</li><li>Tiếp nhận góp ý và hỗ trợ các câu hỏi sau trải nghiệm.</li></ol><h2>Không gian và trải nghiệm</h2><p>Không gian được định hướng theo phong cách ấm áp với sắc kem, nâu và ánh vàng dịu. Hãy bổ sung ảnh chụp thật của khu vực lễ tân, phòng chăm sóc da, giường massage, khu gội đầu và góc thư giãn để khách hàng có hình dung chính xác về cơ sở.</p><h2>Cam kết với khách hàng</h2><ul><li>Thông tin giá và dịch vụ được trình bày rõ ràng.</li><li>Tôn trọng quyền riêng tư và thông tin cá nhân.</li><li>Không cam kết kết quả tuyệt đối hoặc thay thế tư vấn y tế.</li><li>Khuyến khích khách hàng trao đổi về nhu cầu, sự nhạy cảm và các lưu ý trước dịch vụ.</li></ul><p><em>Lưu ý quản trị: nội dung giới thiệu đang là bản mẫu để hoàn thiện website. Cập nhật thông tin thật về cơ sở, nhân sự và hình ảnh trước khi xuất bản.</em></p><p><a href="/dich-vu">Khám phá dịch vụ</a> · <a href="/dat-lich">Gửi yêu cầu đặt lịch</a></p>',''),
        'lien-he'=>array('Liên hệ','[beauty_contact_form]',''),
        'hinh-anh'=>array('Hình ảnh','<div class="beauty-gallery-intro"><p>Khám phá hình ảnh không gian và trải nghiệm chăm sóc tại Beauty Spa. Ảnh hiện tại là hình minh họa cho bản dựng; hãy thay bằng ảnh chụp thật của cơ sở trước khi công khai.</p></div><div class="spa-gallery-grid"><figure><img src="/wp-content/themes/beauty-wordpress-theme-v2/assets/images/spa-album-facial.jpg" alt="Chăm sóc da tại spa"><figcaption>Chăm sóc da</figcaption></figure><figure><img src="/wp-content/themes/beauty-wordpress-theme-v2/assets/images/spa-album-massage.jpg" alt="Không gian thư giãn"><figcaption>Khoảnh khắc thư giãn</figcaption></figure><figure><img src="/wp-content/themes/beauty-wordpress-theme-v2/assets/images/spa-album-room.jpg" alt="Liệu trình chăm sóc"><figcaption>Liệu trình chăm sóc</figcaption></figure><figure><img src="/wp-content/themes/beauty-wordpress-theme-v2/assets/images/spa-album-products.jpg" alt="Trải nghiệm spa"><figcaption>Trải nghiệm Beauty</figcaption></figure></div>',''),
        'dat-lich'=>array('Đặt lịch','[beaute_spa_booking]','page-templates/template-booking.php'),
        'bang-gia'=>array('Bảng giá','', 'page-templates/template-pricing.php'),
        'tin-tuc'=>array('Tin tức','<p>Cập nhật kiến thức chăm sóc da, thư giãn và làm đẹp từ Beauty.</p>',''),
        'san-pham'=>array('Sản phẩm','<p>Khám phá các sản phẩm chăm sóc sắc đẹp. Danh mục và sản phẩm sẽ được quản lý trong WooCommerce sau khi cài đặt plugin.</p>','')
    );
    foreach ($pages as $slug=>$p) {
        $existing = get_page_by_path($slug);
        if (!$existing) { $id=wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>$p[0],'post_name'=>$slug,'post_content'=>$p[1])); } else { $id=$existing->ID; if ($slug==='gioi-thieu' && strpos((string)$existing->post_content,'Chào mừng đến với Beauty')!==false) { wp_update_post(array('ID'=>$id,'post_content'=>$p[1])); } }
        if (!empty($id) && !is_wp_error($id) && !empty($p[2])) update_post_meta($id,'_wp_page_template',$p[2]);
    }
    // Danh mục và sản phẩm mẫu cho WooCommerce nếu plugin đã được cài đặt.
    if (class_exists('WooCommerce') && taxonomy_exists('product_cat')) {
        $product_cats = array(
            'cham-soc-da-mat'=>'Chăm sóc da mặt',
            'duong-am'=>'Dưỡng ẩm',
            'lam-sach'=>'Làm sạch',
            'chong-nang'=>'Chống nắng',
            'cham-soc-co-the'=>'Chăm sóc cơ thể',
            'cham-soc-toc'=>'Chăm sóc tóc'
        );
        foreach ($product_cats as $slug=>$name) if (!term_exists($slug,'product_cat')) wp_insert_term($name,'product_cat',array('slug'=>$slug));
        $products = array(
            array('Sữa rửa mặt dịu nhẹ Beauty','Làm sạch bụi bẩn hằng ngày. Chọn sản phẩm phù hợp với loại da và thử trên vùng nhỏ trước khi dùng.',189000,'lam-sach'),
            array('Nước cân bằng cấp ẩm Beauty','Bước dưỡng da sau làm sạch, hỗ trợ mang lại cảm giác mềm mại.',219000,'duong-am'),
            array('Kem dưỡng ẩm hằng ngày Beauty','Kem dưỡng ẩm dùng trong quy trình chăm sóc da. Kiểm tra thành phần nếu bạn có làn da nhạy cảm.',289000,'duong-am'),
            array('Kem chống nắng dùng ban ngày Beauty','Sản phẩm chống nắng mẫu. Cần bổ sung thông tin SPF, PA, thành phần và nhãn hàng thực tế trước khi bán.',329000,'chong-nang'),
            array('Sữa dưỡng thể Beauty','Sản phẩm dưỡng ẩm cơ thể mẫu, phù hợp để bổ sung vào danh mục chăm sóc tại nhà.',199000,'cham-soc-co-the'),
            array('Dầu gội chăm sóc tóc Beauty','Sản phẩm chăm sóc tóc mẫu. Cần thay bằng sản phẩm có nguồn gốc, nhãn mác và thông tin nhà sản xuất đã xác minh.',159000,'cham-soc-toc'),
            array('Serum cấp ẩm Beauty','Serum mẫu cho quy trình dưỡng da tại nhà; cập nhật thành phần và nhãn hàng thật.',359000,'duong-am'),
            array('Mặt nạ dưỡng ẩm Beauty','Mặt nạ dưỡng ẩm mẫu; kiểm tra thành phần trước khi sử dụng.',99000,'cham-soc-da-mat'),
            array('Tẩy trang dịu nhẹ Beauty','Sản phẩm làm sạch lớp trang điểm và bụi bẩn; thông tin mẫu.',179000,'lam-sach'),
            array('Gel rửa mặt tạo bọt Beauty','Sản phẩm làm sạch mẫu cho quy trình chăm sóc da.',169000,'lam-sach'),
            array('Kem dưỡng ban đêm Beauty','Kem dưỡng ban đêm mẫu; cần xác minh thông tin sản phẩm trước khi bán.',319000,'duong-am'),
            array('Tinh chất làm dịu da Beauty','Tinh chất chăm sóc da mẫu, không phải sản phẩm điều trị.',389000,'cham-soc-da-mat'),
            array('Son dưỡng môi Beauty','Sản phẩm dưỡng môi mẫu; bổ sung thông tin thành phần thực tế.',89000,'cham-soc-da-mat'),
            array('Tẩy tế bào chết body Beauty','Sản phẩm chăm sóc cơ thể mẫu; sử dụng theo hướng dẫn nhãn hàng thực tế.',229000,'cham-soc-co-the'),
            array('Sữa tắm dưỡng ẩm Beauty','Sản phẩm làm sạch cơ thể mẫu.',149000,'cham-soc-co-the'),
            array('Kem dưỡng tay Beauty','Sản phẩm dưỡng ẩm tay mẫu.',119000,'cham-soc-co-the'),
            array('Dầu xả mềm mượt Beauty','Sản phẩm chăm sóc tóc mẫu; cần thay bằng thông tin nhãn hàng đã xác minh.',169000,'cham-soc-toc'),
            array('Bộ chăm sóc da cơ bản Beauty','Bộ sản phẩm minh họa gồm các bước chăm sóc cơ bản; thay bằng danh sách sản phẩm thực tế.',599000,'cham-soc-da-mat'),
        );
        foreach ($products as $p) {
            if (beauty_find_post_by_title($p[0],OBJECT,'product')) continue;
            $product_id = wp_insert_post(array('post_type'=>'product','post_status'=>'publish','post_title'=>$p[0],'post_excerpt'=>$p[1],'post_content'=>'<p>'.esc_html($p[1]).'</p><p><strong>Lưu ý:</strong> Đây là sản phẩm và giá minh họa cho bản dựng website, không đại diện cho hàng hóa đã được xác minh. Hãy thay bằng sản phẩm thật, thông tin nhãn hàng, thành phần, xuất xứ và chính sách bán hàng trước khi mở bán.</p>'), true);
            if (!is_wp_error($product_id)) {
                update_post_meta($product_id,'_regular_price',(string)$p[2]);
                update_post_meta($product_id,'_price',(string)$p[2]);
                update_post_meta($product_id,'_virtual','no');
                update_post_meta($product_id,'_manage_stock','no');
                wp_set_object_terms($product_id,'simple','product_type');
                if ($term = get_term_by('slug',$p[3],'product_cat')) wp_set_object_terms($product_id,array((int)$term->term_id),'product_cat');
            }
        }
    }

    update_option('beaute_spa_seed_v2_done',1);
}
add_action('init','beaute_spa_seed_demo_content',30);

/** WooCommerce có thể được cài sau theme; khởi tạo riêng để danh mục mẫu vẫn được tạo. */
function beaute_spa_seed_woocommerce_content() {
    if (!class_exists('WooCommerce') || !taxonomy_exists('product_cat') || get_option('beaute_spa_wc_seed_v2_done')) return;
    $categories = array('cham-soc-da-mat'=>'Chăm sóc da mặt','duong-am'=>'Dưỡng ẩm','lam-sach'=>'Làm sạch','chong-nang'=>'Chống nắng','cham-soc-co-the'=>'Chăm sóc cơ thể','cham-soc-toc'=>'Chăm sóc tóc');
    foreach ($categories as $slug=>$name) if (!term_exists($slug,'product_cat')) wp_insert_term($name,'product_cat',array('slug'=>$slug));
    $products = array(
        array('Sữa rửa mặt dịu nhẹ Beauty','Làm sạch bụi bẩn hằng ngày. Chọn sản phẩm phù hợp với loại da và thử trên vùng nhỏ trước khi dùng.',189000,'lam-sach'),
        array('Nước cân bằng cấp ẩm Beauty','Bước dưỡng da sau làm sạch, hỗ trợ mang lại cảm giác mềm mại.',219000,'duong-am'),
        array('Kem dưỡng ẩm hằng ngày Beauty','Kem dưỡng ẩm dùng trong quy trình chăm sóc da. Kiểm tra thành phần nếu bạn có làn da nhạy cảm.',289000,'duong-am'),
        array('Kem chống nắng dùng ban ngày Beauty','Sản phẩm chống nắng mẫu. Cần bổ sung thông tin SPF, PA, thành phần và nhãn hàng thực tế trước khi bán.',329000,'chong-nang'),
        array('Sữa dưỡng thể Beauty','Sản phẩm dưỡng ẩm cơ thể mẫu, phù hợp để bổ sung vào danh mục chăm sóc tại nhà.',199000,'cham-soc-co-the'),
        array('Dầu gội chăm sóc tóc Beauty','Sản phẩm chăm sóc tóc mẫu. Cần thay bằng sản phẩm có nguồn gốc, nhãn mác và thông tin nhà sản xuất đã xác minh.',159000,'cham-soc-toc'),
        array('Serum cấp ẩm Beauty','Serum mẫu cho quy trình dưỡng da tại nhà; cập nhật thành phần và nhãn hàng thật.',359000,'duong-am'),
        array('Mặt nạ dưỡng ẩm Beauty','Mặt nạ dưỡng ẩm mẫu; kiểm tra thành phần trước khi sử dụng.',99000,'cham-soc-da-mat'),
        array('Tẩy trang dịu nhẹ Beauty','Sản phẩm làm sạch lớp trang điểm và bụi bẩn; thông tin mẫu.',179000,'lam-sach'),
        array('Gel rửa mặt tạo bọt Beauty','Sản phẩm làm sạch mẫu cho quy trình chăm sóc da.',169000,'lam-sach'),
        array('Kem dưỡng ban đêm Beauty','Kem dưỡng ban đêm mẫu; cần xác minh thông tin sản phẩm trước khi bán.',319000,'duong-am'),
        array('Tinh chất làm dịu da Beauty','Tinh chất chăm sóc da mẫu, không phải sản phẩm điều trị.',389000,'cham-soc-da-mat'),
        array('Son dưỡng môi Beauty','Sản phẩm dưỡng môi mẫu; bổ sung thông tin thành phần thực tế.',89000,'cham-soc-da-mat'),
        array('Tẩy tế bào chết body Beauty','Sản phẩm chăm sóc cơ thể mẫu; sử dụng theo hướng dẫn nhãn hàng thực tế.',229000,'cham-soc-co-the'),
        array('Sữa tắm dưỡng ẩm Beauty','Sản phẩm làm sạch cơ thể mẫu.',149000,'cham-soc-co-the'),
        array('Kem dưỡng tay Beauty','Sản phẩm dưỡng ẩm tay mẫu.',119000,'cham-soc-co-the'),
        array('Dầu xả mềm mượt Beauty','Sản phẩm chăm sóc tóc mẫu; cần thay bằng thông tin nhãn hàng đã xác minh.',169000,'cham-soc-toc'),
        array('Bộ chăm sóc da cơ bản Beauty','Bộ sản phẩm minh họa gồm các bước chăm sóc cơ bản; thay bằng danh sách sản phẩm thực tế.',599000,'cham-soc-da-mat'),
    );
    foreach ($products as $p) {
        if (beauty_find_post_by_title($p[0],OBJECT,'product')) continue;
        $id = wp_insert_post(array('post_type'=>'product','post_status'=>'publish','post_title'=>$p[0],'post_excerpt'=>$p[1],'post_content'=>'<p>'.esc_html($p[1]).'</p><p><strong>Lưu ý:</strong> Sản phẩm và giá minh họa, không đại diện cho hàng hóa đã được xác minh. Thay bằng sản phẩm thật và thông tin nhãn hàng đã kiểm chứng trước khi mở bán.</p>'),true);
        if (!is_wp_error($id)) {
            update_post_meta($id,'_regular_price',(string)$p[2]); update_post_meta($id,'_price',(string)$p[2]);
            wp_set_object_terms($id,'simple','product_type');
            if ($term=get_term_by('slug',$p[3],'product_cat')) wp_set_object_terms($id,array((int)$term->term_id),'product_cat');
        }
    }
    update_option('beaute_spa_wc_seed_v2_done',1);
}
add_action('init','beaute_spa_seed_woocommerce_content',40);

function beauty_enhance_products_metadata(){
 if(!class_exists('WooCommerce') || get_option('beauty_products_enhanced_v1')) return;
 $featured=array('Kem dưỡng ẩm hằng ngày Beauty','Serum cấp ẩm Beauty','Dầu gội chăm sóc tóc Beauty','Bộ chăm sóc da cơ bản Beauty');
 $sale=array('Mặt nạ dưỡng ẩm Beauty','Dầu gội chăm sóc tóc Beauty','Bộ chăm sóc da cơ bản Beauty');
 foreach(get_posts(array('post_type'=>'product','post_status'=>'publish','numberposts'=>-1)) as $p){$product=wc_get_product($p->ID); if(!$product)continue; if(in_array($p->post_title,$featured,true)){$product->set_featured(true);} $product->save(); $content=get_post_field('post_content',$p->ID); if(strpos($content,'<h2>Thông tin sản phẩm</h2>')===false){$description='<h2>Thông tin sản phẩm</h2><p>'.esc_html($p->post_excerpt).'</p><h2>Công dụng và trải nghiệm</h2><p>Sản phẩm mẫu được sắp xếp theo nhu cầu chăm sóc tương ứng như làm sạch, cấp ẩm, chăm sóc tóc hoặc cơ thể. Hiệu quả sử dụng phụ thuộc vào công thức thực tế, cách dùng và đặc điểm từng người.</p><h2>Hướng dẫn sử dụng</h2><p>Đọc hướng dẫn trên nhãn sản phẩm thực tế, sử dụng đúng lượng khuyến nghị và ngưng dùng nếu có dấu hiệu kích ứng. Với da nhạy cảm, nên thử trên vùng nhỏ trước.</p><h2>Thông tin cần xác nhận</h2><p>Đây là dữ liệu sản phẩm minh họa cho bản dựng website. Chủ cửa hàng cần thay ảnh, nhãn hiệu, thành phần, dung tích, xuất xứ, hướng dẫn và giá bằng thông tin xác thực trước khi mở bán.</p>'; wp_update_post(array('ID'=>$p->ID,'post_content'=>$description));}}
 update_option('beauty_products_enhanced_v1',1);
}
add_action('init','beauty_enhance_products_metadata',55);

function beauty_fix_gallery_theme_asset_paths($content){
 if(strpos($content,'beauty-wordpress-theme-v2/assets/images/')!==false){$content=str_replace('/wp-content/themes/beauty-wordpress-theme-v2/assets/images/',get_template_directory_uri().'/assets/images/',$content);} return $content;
}
add_filter('the_content','beauty_fix_gallery_theme_asset_paths',20);
function beauty_v4_upgrade_pages(){
 if(get_option('beauty_v4_pages_done'))return;
 $about=get_page_by_path('gioi-thieu'); if($about && strpos($about->post_title,'Về chúng tôi')===false){wp_update_post(array('ID'=>$about->ID,'post_title'=>'Giới thiệu'));}
 $gallery=get_page_by_path('hinh-anh'); if(!$gallery){$content='<div class="beauty-gallery-intro"><p>Khám phá hình ảnh minh họa không gian và trải nghiệm chăm sóc tại Beauty Spa. Hãy thay bằng ảnh chụp thật của cơ sở trước khi công khai.</p></div><div class="spa-gallery-grid"><figure><img src="'.esc_url(get_template_directory_uri().'/assets/images/spa-album-facial.jpg').'" alt="Chăm sóc da tại spa"><figcaption>Chăm sóc da</figcaption></figure><figure><img src="'.esc_url(get_template_directory_uri().'/assets/images/spa-album-massage.jpg').'" alt="Không gian thư giãn"><figcaption>Khoảnh khắc thư giãn</figcaption></figure><figure><img src="'.esc_url(get_template_directory_uri().'/assets/images/spa-album-room.jpg').'" alt="Liệu trình chăm sóc"><figcaption>Liệu trình chăm sóc</figcaption></figure><figure><img src="'.esc_url(get_template_directory_uri().'/assets/images/spa-album-products.jpg').'" alt="Trải nghiệm spa"><figcaption>Trải nghiệm Beauty</figcaption></figure></div>';wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>'Hình ảnh','post_name'=>'hinh-anh','post_content'=>$content));}
 $contact=get_page_by_path('lien-he');if($contact && strpos($contact->post_content,'beauty_contact_form')===false)wp_update_post(array('ID'=>$contact->ID,'post_content'=>'[beauty_contact_form]'));
 update_option('beauty_v4_pages_done',1);
}
add_action('init','beauty_v4_upgrade_pages',60);


/** V6: media-library-backed photo album, detailed about page and service thumbnails. */
function beauty_v6_import_local_image($filename, $title = '') {
    $key = 'beauty_v6_media_' . sanitize_key(pathinfo($filename, PATHINFO_FILENAME));
    $saved = (int) get_option($key, 0);
    if ($saved && get_post($saved) && wp_get_attachment_url($saved)) return $saved;
    $path = get_template_directory() . '/assets/images/' . $filename;
    if (!file_exists($path)) return 0;
    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) return 0;
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    $file = array('name' => basename($filename), 'type' => wp_check_filetype($filename)['type'], 'tmp_name' => $path, 'error' => 0, 'size' => filesize($path));
    // Copy to uploads first; sideload_image requires a temporary file path.
    $tmp = wp_tempnam($filename);
    if (!$tmp || !copy($path, $tmp)) return 0;
    $file['tmp_name'] = $tmp;
    $attachment_id = media_handle_sideload($file, 0, $title ? $title : pathinfo($filename, PATHINFO_FILENAME));
    if (is_wp_error($attachment_id)) { @unlink($tmp); return 0; }
    update_option($key, (int) $attachment_id, false);
    return (int) $attachment_id;
}

function beauty_v6_gallery_items() {
    // [tệp ảnh, tiêu đề, mô tả, nhãn, nhóm album, alt]. Mỗi tệp ảnh là một ảnh khác nhau; thay bằng ảnh thật của cơ sở khi có.
    $items = array(
      array('spa-album-facial.jpg','Chăm sóc da chuyên sâu','Khoảnh khắc chăm sóc da nhẹ nhàng, chú trọng sự thoải mái và quy trình phù hợp từng nhu cầu.','CHĂM SÓC DA','cham-soc-da','Khách hàng được chăm sóc da mặt với khăn quấn tóc'),
      array('spa-service-cleansing.jpg','Làm sạch và cấp ẩm','Các bước làm sạch dịu nhẹ trước khi dưỡng ẩm và hoàn tất liệu trình.','CHĂM SÓC DA','cham-soc-da','Chuyên viên làm sạch da mặt cho khách'),
      array('spa-service-extraction.jpg','Chăm sóc da mụn cơ bản','Làm sạch và chăm sóc da có xu hướng nổi mụn theo quy trình vệ sinh phù hợp.','CHĂM SÓC DA','cham-soc-da','Chuyên viên đeo khẩu trang chăm sóc da mặt cho khách'),
      array('spa-album-massage.jpg','Massage thư giãn','Không gian thư thái cho trải nghiệm massage, giúp bạn dành thời gian nghỉ ngơi sau ngày dài.','MASSAGE & THƯ GIÃN','massage','Massage lưng thư giãn cho khách tại spa'),
      array('spa-service-body-scrub.jpg','Tẩy tế bào chết cơ thể','Làm sạch tế bào chết bề mặt và dưỡng ẩm sau liệu trình.','MASSAGE & THƯ GIÃN','massage','Chăm sóc lưng bằng muối tẩy tế bào chết'),
      array('spa-album-hairwash.jpg','Gội đầu dưỡng sinh','Trải nghiệm gội đầu kết hợp chăm sóc da đầu trong không gian ấm áp, dễ chịu.','GỘI ĐẦU DƯỠNG SINH','goi-dau','Gội đầu dưỡng sinh tại bồn gội'),
      array('spa-service-scalp-wash.jpg','Massage da đầu','Massage da đầu nhẹ nhàng trong lúc gội, giúp thả lỏng vùng đầu, cổ và vai.','GỘI ĐẦU DƯỠNG SINH','goi-dau','Chuyên viên massage da đầu cho khách khi gội'),
      array('spa-album-room.jpg','Không gian chăm sóc','Thiết kế ấm áp với ánh sáng dịu, tông màu tự nhiên và những chi tiết tối giản.','KHÔNG GIAN','khong-gian','Phòng spa với giường massage, ánh sáng dịu và cây xanh'),
      array('spa-album-consultation.jpg','Tư vấn cùng chuyên viên','Trao đổi nhu cầu và lựa chọn dịch vụ phù hợp trước khi bắt đầu trải nghiệm.','KHÔNG GIAN','khong-gian','Chuyên viên tư vấn cho khách bằng máy tính bảng'),
      array('spa-album-products.jpg','Sản phẩm chăm sóc','Hình ảnh minh họa cho các sản phẩm và vật dụng chăm sóc được sắp xếp gọn gàng.','SẢN PHẨM','san-pham','Bộ sản phẩm chăm sóc da đặt trên khay đá cạnh cây xanh'),
      array('spa-service-hair-removal.jpg','Triệt lông theo vùng','Cần tư vấn tình trạng da và liệu trình phù hợp trước khi thực hiện.','CHĂM SÓC CƠ THỂ','co-the','Chuyên viên thực hiện triệt lông vùng chân'),
      array('spa-service-manicure.jpg','Chăm sóc tay và móng','Làm sạch, chăm sóc móng và dưỡng ẩm cơ bản cho bàn tay.','CHĂM SÓC CƠ THỂ','co-the','Chăm sóc bàn tay và móng bên cạnh bộ sơn móng')
    );
    $groups = array();
    foreach ($items as $item) $groups[$item[4]][] = $item;
    $files = array('spa-album-facial.jpg','spa-album-massage.jpg','spa-album-hairwash.jpg','spa-album-room.jpg','spa-album-consultation.jpg','spa-album-products.jpg','spa-service-cleansing.jpg','spa-service-scalp-wash.jpg');
    foreach ($groups as $slug => $group) {
        $seed = $group[0];
        for ($i = count($group); $i < 6; $i++) {
            $file = $files[($i + count($groups)) % count($files)];
            $copy = $seed;
            $copy[0] = $file;
            $copy[1] = $seed[1] . ' · ' . ($i + 1);
            $copy[2] = $seed[2];
            $copy[5] = $seed[5] . ' - ảnh minh họa ' . ($i + 1);
            $items[] = $copy;
        }
    }
    return $items;
}
function beauty_v6_gallery_shortcode() {
    $items = beauty_gallery_items();
    $albums = array();
    foreach ($items as $item) {
        if (!isset($albums[$item['slug']])) $albums[$item['slug']] = array('label' => $item['label'], 'count' => 0, 'cover' => $item['thumb'], 'alt' => $item['alt']);
        $albums[$item['slug']]['count']++;
    }
    $active = isset($_GET['album']) ? sanitize_key(wp_unslash($_GET['album'])) : 'all';
    if ('all' !== $active && !isset($albums[$active])) $active = 'all';
    $base = get_permalink() ? get_permalink() : home_url('/hinh-anh/');
    $first = $items ? $items[0] : array('thumb' => '', 'alt' => '');
    ob_start(); ?>
    <div class="beauty-gallery-page beauty-gallery-album">
      <div class="beauty-gallery-lead"><span class="eyebrow">BEAUTY SPA · PHOTO ALBUM</span><h2>Một chút bình yên dành riêng cho bạn</h2><p>Chọn một album để xem ảnh theo từng chủ đề. Ảnh hiện là ảnh minh họa; quản trị viên thay bằng ảnh thật tại Admin → Album ảnh.</p></div>
      <nav class="bv-albums" aria-label="Chọn album ảnh">
        <a class="bv-album<?php echo 'all' === $active ? ' active' : ''; ?>" href="<?php echo esc_url($base . '#album-grid'); ?>" data-filter="all" data-label="Tất cả ảnh"><img src="<?php echo esc_url($first['thumb']); ?>" alt="" width="504" height="504" loading="lazy"><span class="bv-album-name">Tất cả ảnh</span><small><?php echo (int) count($items); ?> ảnh</small></a>
        <?php foreach ($albums as $slug => $album) : ?>
        <a class="bv-album<?php echo $slug === $active ? ' active' : ''; ?>" href="<?php echo esc_url(add_query_arg('album', $slug, $base) . '#album-grid'); ?>" data-filter="<?php echo esc_attr($slug); ?>" data-label="<?php echo esc_attr($album['label']); ?>"><img src="<?php echo esc_url($album['cover']); ?>" alt="" width="504" height="504" loading="lazy"><span class="bv-album-name"><?php echo esc_html($album['label']); ?></span><small><?php echo (int) $album['count']; ?> ảnh</small></a>
        <?php endforeach; ?>
      </nav>
      <h3 class="bv-album-current" id="album-grid"><span id="album-title"><?php echo 'all' === $active ? 'Tất cả ảnh' : esc_html($albums[$active]['label']); ?></span></h3>
      <div class="beauty-album-grid">
      <?php foreach ($items as $item) : $hide = ('all' !== $active && $item['slug'] !== $active); ?>
        <figure class="beauty-album-card" data-album="<?php echo esc_attr($item['slug']); ?>"<?php echo $hide ? ' hidden' : ''; ?>><a class="beauty-album-image" href="<?php echo esc_url($item['full']); ?>" data-caption="<?php echo esc_attr($item['title']); ?>" aria-label="Xem ảnh lớn: <?php echo esc_attr($item['title']); ?>"><img src="<?php echo esc_url($item['thumb']); ?>" alt="<?php echo esc_attr($item['alt']); ?>" width="504" height="504" loading="lazy" decoding="async"><span class="beauty-album-zoom" aria-hidden="true">⌕</span></a><figcaption><span class="eyebrow"><?php echo esc_html($item['label']); ?></span><h3><?php echo esc_html($item['title']); ?></h3><?php if ($item['desc']) : ?><p><?php echo esc_html($item['desc']); ?></p><?php endif; ?></figcaption></figure>
      <?php endforeach; ?>
      </div>
      <section class="beauty-gallery-bottom"><div><span class="eyebrow">YOUR MOMENT OF BEAUTY</span><h2>Hẹn bạn tại Beauty Spa</h2><p>Hãy chia sẻ điều bạn đang tìm kiếm — một làn da được chăm sóc, phút giây thư giãn hay một khoảng nghỉ dành riêng cho bản thân.</p></div><a class="btn" href="<?php echo esc_url(home_url('/dat-lich/')); ?>">Liên hệ đặt lịch</a></section>
    </div>
    <?php return ob_get_clean();
}
remove_shortcode('beauty_gallery_page');
add_shortcode('beauty_gallery_page', 'beauty_v6_gallery_shortcode');

function beauty_v7_service_image_url($post_id = 0) {
    $post_id = $post_id ? (int) $post_id : get_the_ID();
    $map = array(
      'Làm sạch và cấp ẩm cơ bản'=>'spa-service-cleansing.jpg',
      'Chăm sóc da chuyên sâu'=>'spa-album-facial.jpg',
      'Chăm sóc da mụn cơ bản'=>'spa-service-extraction.jpg',
      'Làm sạch da và tẩy tế bào chết'=>'spa-album-products.jpg',
      'Massage thư giãn toàn thân'=>'spa-album-massage.jpg',
      'Massage lưng và vai gáy'=>'spa-album-room.jpg',
      'Gội đầu dưỡng sinh thư giãn'=>'spa-album-hairwash.jpg',
      'Gội đầu dưỡng sinh chuyên sâu'=>'spa-service-scalp-wash.jpg',
      'Triệt lông vùng nách'=>'spa-service-hair-removal.jpg',
      'Triệt lông tay hoặc chân'=>'spa-service-hair-removal.jpg',
      'Tẩy tế bào chết toàn thân'=>'spa-service-body-scrub.jpg',
      'Chăm sóc bàn tay và bàn chân'=>'spa-service-manicure.jpg'
    );
    $title = get_the_title($post_id);
    $file = isset($map[$title]) ? $map[$title] : 'spa-album-room.jpg';
    return beauty_theme_image_url($file);
}

function beauty_v6_upgrade_pages_and_services() {
    if (get_option('beauty_v7_content_done')) return;
    $about = get_page_by_path('gioi-thieu');
    if ($about) {
      $content = '<div class="beauty-about-page"><section class="beauty-about-hero"><div class="beauty-about-hero-copy"><span class="eyebrow">CÂU CHUYỆN BEAUTY SPA</span><h2>Chăm sóc vẻ đẹp, nuôi dưỡng sự cân bằng</h2><p class="beauty-about-lead">Beauty Spa được xây dựng với mong muốn tạo nên một khoảng dừng nhẹ nhàng giữa nhịp sống bận rộn — nơi bạn có thể chăm sóc bản thân, được lắng nghe và lựa chọn dịch vụ phù hợp với nhu cầu của mình.</p><p>Chúng tôi tin rằng trải nghiệm tốt bắt đầu từ những điều giản dị: thông tin rõ ràng, không gian sạch sẽ, sự tôn trọng và cách phục vụ tận tâm. Mỗi buổi chăm sóc đều cần được trao đổi trước để khách hiểu quy trình, thời lượng, chi phí và các lưu ý liên quan.</p><a class="btn" href="'.esc_url(home_url('/dich-vu/')).'">Khám phá dịch vụ</a></div><div class="beauty-about-hero-image"><img src="'.esc_url(get_template_directory_uri() . '/assets/images/spa-album-room.jpg').'" alt="Không gian thư giãn tại Beauty Spa" loading="lazy"></div></section>
      <section class="beauty-about-story"><div><span class="eyebrow">ĐIỀU CHÚNG TÔI TIN TƯỞNG</span><h2>Vẻ đẹp bắt đầu từ sự quan tâm</h2></div><div><p>Mỗi người có làn da, thói quen sinh hoạt và mong muốn riêng. Vì vậy, Beauty hướng đến việc tư vấn dựa trên nhu cầu thực tế thay vì áp dụng một lựa chọn cho tất cả. Trước khi thực hiện, khách hàng được trao đổi về các bước, thời gian dự kiến, mức giá và những điều nên lưu ý.</p><p>Chúng tôi mong muốn mỗi lần ghé thăm không chỉ là một dịch vụ làm đẹp, mà còn là khoảng thời gian để bạn chậm lại, cảm thấy thoải mái và chủ động trong lựa chọn của mình.</p></div></section>
      <section class="beauty-about-values"><div><span>01</span><h3>Tận tâm lắng nghe</h3><p>Tiếp nhận mong muốn, thói quen chăm sóc và những lưu ý riêng trước khi đề xuất dịch vụ.</p></div><div><span>02</span><h3>Rõ ràng, minh bạch</h3><p>Trao đổi về các bước thực hiện, thời lượng và chi phí trước khi khách hàng quyết định.</p></div><div><span>03</span><h3>Chỉn chu từng chi tiết</h3><p>Quan tâm đến vệ sinh, cách chuẩn bị không gian và sự thoải mái trong suốt trải nghiệm.</p></div></section>
      <section class="beauty-about-services"><div class="beauty-about-section-title"><span class="eyebrow">CHĂM SÓC THEO NHU CẦU</span><h2>Những trải nghiệm dành cho bạn</h2><p>Tìm hiểu từng nhóm dịch vụ và chọn lựa chọn phù hợp với thời gian cũng như mong muốn của bạn.</p></div><div class="beauty-about-service-grid"><a href="'.esc_url(home_url('/dich-vu/')).'"><img src="'.esc_url(get_template_directory_uri() . '/assets/images/spa-album-facial.jpg').'" alt="Chăm sóc da"><strong>Chăm sóc da</strong><span>Làm sạch và chăm sóc theo nhu cầu.</span></a><a href="'.esc_url(home_url('/dich-vu/')).'"><img src="'.esc_url(get_template_directory_uri() . '/assets/images/spa-album-massage.jpg').'" alt="Massage thư giãn"><strong>Massage thư giãn</strong><span>Dành thời gian nghỉ ngơi và phục hồi.</span></a><a href="'.esc_url(home_url('/dich-vu/')).'"><img src="'.esc_url(get_template_directory_uri() . '/assets/images/spa-album-hairwash.jpg').'" alt="Gội đầu dưỡng sinh"><strong>Gội đầu dưỡng sinh</strong><span>Chăm sóc da đầu trong không gian thư thái.</span></a></div></section>
      <section class="beauty-about-process"><span class="eyebrow">TRẢI NGHIỆM TẠI BEAUTY</span><h2>Quy trình phục vụ</h2><ol><li><strong>Lắng nghe nhu cầu:</strong> tiếp nhận mong muốn và những lưu ý khách hàng muốn chia sẻ.</li><li><strong>Tư vấn rõ ràng:</strong> giới thiệu lựa chọn, thời lượng, chi phí và các lưu ý trước khi thực hiện.</li><li><strong>Chuẩn bị chu đáo:</strong> chuẩn bị khu vực chăm sóc, dụng cụ và các bước cần thiết theo dịch vụ đã chọn.</li><li><strong>Thực hiện dịch vụ:</strong> trao đổi với khách trong quá trình thực hiện để điều chỉnh khi phù hợp.</li><li><strong>Chăm sóc sau trải nghiệm:</strong> hướng dẫn lưu ý cơ bản và tiếp nhận phản hồi của khách hàng.</li></ol></section>
      <section class="beauty-about-commitment"><div><span class="eyebrow">CAM KẾT TRẢI NGHIỆM</span><h2>Để bạn luôn an tâm khi lựa chọn</h2></div><ul><li>Không thực hiện dịch vụ khi khách chưa hiểu và đồng ý với lựa chọn đã trao đổi.</li><li>Khuyến khích hỏi rõ về thành phần sản phẩm, thời lượng, giá và các lưu ý trước khi bắt đầu.</li><li>Tôn trọng sự riêng tư và thông tin cá nhân của khách hàng.</li><li>Không cam kết kết quả tuyệt đối; hiệu quả thực tế có thể khác nhau tùy từng người.</li></ul></section>
      <section class="beauty-about-cta"><div><h2>Bắt đầu từ nhu cầu của bạn</h2><p>Xem dịch vụ, tham khảo bảng giá hoặc gửi yêu cầu để được hỗ trợ lựa chọn.</p></div><div><a class="btn" href="'.esc_url(home_url('/dich-vu/')).'">Xem dịch vụ</a> <a class="btn btn-outline" href="'.esc_url(home_url('/dat-lich/')).'">Đặt lịch</a></div></section><p class="beauty-content-disclaimer">Lưu ý: nội dung trên là phần giới thiệu mẫu cho giao diện. Vui lòng cập nhật thông tin chính thức về lịch sử hoạt động, địa chỉ, đội ngũ và chứng chỉ của cơ sở trước khi công bố.</p></div>';
      wp_update_post(array('ID'=>$about->ID,'post_title'=>'Giới thiệu','post_content'=>$content));
    }
    $gallery = get_page_by_path('hinh-anh');
    if ($gallery) wp_update_post(array('ID'=>$gallery->ID,'post_title'=>'Hình ảnh','post_content'=>'[beauty_gallery_page]'));
    // Import the six distinct local photos into the Media Library and assign appropriate service thumbnails.
    foreach (beauty_v6_gallery_items() as $item) beauty_v6_import_local_image($item[0], $item[1]);
    $service_images = array(
      'Làm sạch và cấp ẩm cơ bản'=>'spa-service-cleansing.jpg',
      'Chăm sóc da chuyên sâu'=>'spa-album-facial.jpg',
      'Chăm sóc da mụn cơ bản'=>'spa-service-extraction.jpg',
      'Làm sạch da và tẩy tế bào chết'=>'spa-album-products.jpg',
      'Massage thư giãn toàn thân'=>'spa-album-massage.jpg',
      'Massage lưng và vai gáy'=>'spa-album-room.jpg',
      'Gội đầu dưỡng sinh thư giãn'=>'spa-album-hairwash.jpg',
      'Gội đầu dưỡng sinh chuyên sâu'=>'spa-service-scalp-wash.jpg',
      'Triệt lông vùng nách'=>'spa-service-hair-removal.jpg',
      'Triệt lông tay hoặc chân'=>'spa-service-hair-removal.jpg',
      'Tẩy tế bào chết toàn thân'=>'spa-service-body-scrub.jpg',
      'Chăm sóc bàn tay và bàn chân'=>'spa-service-manicure.jpg'
    );    $services = get_posts(array('post_type'=>'spa_service','post_status'=>'publish','numberposts'=>-1));
    foreach ($services as $service) {
      if (!isset($service_images[$service->post_title])) continue;
      $filename = $service_images[$service->post_title];
      $id = beauty_v6_import_local_image($filename, $service->post_title);
      if ($id) set_post_thumbnail($service->ID, $id);
    }
    update_option('beauty_v7_content_done', 1, false);
}
add_action('init','beauty_v6_upgrade_pages_and_services',80);

/** V7.1: sửa ảnh đại diện bị gán sai ở bản trước. Chỉ thay khi ảnh hiện tại vẫn là ảnh do theme gán, không đụng ảnh người dùng đã đổi. */
function beauty_v71_fix_service_images() {
    if (get_option('beauty_v71_images_done')) return;
    $fixes = array(
        'Massage lưng và vai gáy' => array('spa-gallery-therapy', 'spa-album-room.jpg'),
        'Triệt lông tay hoặc chân' => array('spa-album-consultation', 'spa-service-hair-removal.jpg'),
    );
    foreach ($fixes as $title => $fix) {
        $post = beauty_find_post_by_title($title, OBJECT, 'spa_service');
        if (!$post) continue;
        $old_id = (int) get_option('beauty_v6_media_' . sanitize_key($fix[0]), 0);
        if ($old_id && (int) get_post_thumbnail_id($post->ID) === $old_id) {
            $new_id = beauty_v6_import_local_image($fix[1], $title);
            if ($new_id) set_post_thumbnail($post->ID, $new_id);
        }
    }
    update_option('beauty_v71_images_done', 1, false);
}
add_action('init', 'beauty_v71_fix_service_images', 85);

require_once get_template_directory() . '/inc/v8.php';
require_once get_template_directory() . '/inc/shop.php';
require_once get_template_directory() . '/inc/compat.php';
require_once get_template_directory() . '/inc/fixes.php'; /* [FIX 1][FIX 3] */
require_once get_template_directory() . '/inc/v9-fixes.php'; /* [V9 FIX 1-4] ảnh sản phẩm, giới thiệu, album, combo */
require_once get_template_directory() . '/inc/shop-page.php'; /* [SHOP PAGE] giao diện trang Cửa hàng */
require_once get_template_directory() . '/inc/service-page.php'; /* [SERVICE PAGE] giao diện trang Dịch vụ (dùng chung style trang Cửa hàng) */


/* Beauty UX enhancements: wishlist, robust image fallback, and shared navigation. */
function beauty_enhancements_assets() {
    wp_enqueue_script('beauty-enhancements', get_template_directory_uri() . '/assets/js/beauty-enhancements.js', array(), BEAUTY_SPA_VERSION, true);
    wp_add_inline_script('beauty-enhancements', 'window.beautySpaAssets = ' . wp_json_encode(get_template_directory_uri() . '/assets') . ';', 'before');
}
add_action('wp_enqueue_scripts', 'beauty_enhancements_assets', 30);

function beauty_wishlist_page_content() {
    $ids = isset($_GET['ids']) ? array_filter(array_map('absint', explode(',', sanitize_text_field(wp_unslash($_GET['ids']))))) : array();
    ob_start(); ?>
    <section class="page-hero"><div class="container"><span class="eyebrow">BEAUTY SPA</span><h1 class="page-title">Sản phẩm yêu thích</h1><p>Các sản phẩm bạn đã lưu trên thiết bị này.</p></div></section>
    <main class="content-area container"><div class="beauty-wishlist-client"><p class="beauty-wishlist-empty">Danh sách yêu thích được lưu trong trình duyệt. Hãy bấm ♡ trên sản phẩm để lưu sản phẩm tại đây.</p><div class="bv-prod-grid beauty-wishlist-grid"></div></div></main>
    <?php return ob_get_clean();
}
function beauty_virtual_pages() {
    if (beauty_is_wishlist_request()) {
        status_header(200); get_header(); echo beauty_wishlist_page_content(); get_footer(); exit;
    }
}
add_action('template_redirect', 'beauty_virtual_pages', 1);

/* Tạo trang wishlist bằng ID sản phẩm được lưu localStorage; không cần plugin bổ sung. */
function beauty_wishlist_page_script() {
    if (!beauty_is_wishlist_request()) return;
    $ids = get_posts(array('post_type'=>'spa_product','post_status'=>'publish','numberposts'=>-1,'fields'=>'ids'));
    $items = array();
    foreach ($ids as $id) {
        $items[] = array('id'=>(string)$id,'title'=>get_the_title($id),'url'=>get_permalink($id),'image'=>beauty_product_image_src($id,'medium'),'price'=>beauty_product_price_html($id));
    }
    $json = wp_json_encode($items);
    wp_add_inline_script('beauty-enhancements', 'document.addEventListener("DOMContentLoaded",function(){var all='.$json.';var ids=[];try{ids=JSON.parse(localStorage.getItem("beauty_wishlist")||"[]").map(String)}catch(e){}var grid=document.querySelector(".beauty-wishlist-grid"),empty=document.querySelector(".beauty-wishlist-empty");if(!grid)return;var chosen=all.filter(function(p){return ids.indexOf(p.id)>-1});if(chosen.length){empty.hidden=true;grid.innerHTML=chosen.map(function(p){return \'<article class="bv-prod"><div class="bv-prod-media"><a href="\'+p.url+\'"><img src="\'+p.image+\'" alt="\'+p.title.replace(/&/g,"&amp;").replace(/"/g,"&quot;")+\'"></a><button class="beauty-wishlist-toggle is-favorite" data-wishlist-id="\'+p.id+\'" aria-pressed="true">♥</button></div><div class="bv-prod-body"><h3><a href="\'+p.url+\'">\'+p.title+\'</a></h3><div class="bv-price">\'+p.price+\'</div><a class="btn btn-outline" href="\'+p.url+\'">XEM CHI TIẾT</a></div></article>\'}).join("")}});', 'after');
}
add_action('wp_enqueue_scripts', 'beauty_wishlist_page_script', 40);

function beauty_custom_page_content($content) {
    if (is_admin() || !is_main_query() || !in_the_loop()) return $content;
    if (is_page('lien-he')) {
        /* [FIX 1] FORM LIÊN HỆ - giao diện mới nằm trong inc/fixes.php (beauty_render_contact_page) */
        return beauty_render_contact_page();
    }
    if (is_page('gioi-thieu') && !has_shortcode($content,'beauty_about_expanded')) {
        $content .= '<section class="beauty-about-extra"><div class="beauty-about-story"><span class="eyebrow">CÂU CHUYỆN BEAUTY</span><h2>Vẻ đẹp bắt đầu từ sự quan tâm</h2><p>Beauty Spa được xây dựng với mong muốn tạo nên một không gian chăm sóc bản thân nhẹ nhàng, nơi mỗi khách hàng được lắng nghe, tư vấn rõ ràng và lựa chọn liệu trình phù hợp với nhu cầu thực tế.</p></div><div class="beauty-about-values"><article><h3>Tầm nhìn</h3><p>Trở thành điểm đến chăm sóc sắc đẹp đáng tin cậy, đề cao trải nghiệm và sự an tâm.</p></article><article><h3>Sứ mệnh</h3><p>Đồng hành cùng khách hàng bằng quy trình minh bạch, tư vấn có trách nhiệm và dịch vụ chu đáo.</p></article><article><h3>Giá trị cốt lõi</h3><p>Tận tâm, minh bạch, tôn trọng sự khác biệt và không ngừng cải thiện chất lượng.</p></article></div><section class="beauty-about-team"><span class="eyebrow">ĐỘI NGŨ CHUYÊN VIÊN</span><h2>Chăm sóc bằng sự tận tâm</h2><div class="beauty-about-values"><article><img src="'.esc_url(beauty_theme_image_url('spa-album-consultation.jpg')).'" alt="Chuyên viên tư vấn khách hàng" loading="lazy"><h3>Chuyên viên tư vấn</h3><p>Chức danh: Tư vấn liệu trình</p><p>Kinh nghiệm: cập nhật theo hồ sơ nhân sự thực tế.</p></article><article><img src="'.esc_url(beauty_theme_image_url('spa-album-facial.jpg')).'" alt="Chuyên viên chăm sóc da" loading="lazy"><h3>Chuyên viên chăm sóc da</h3><p>Chức danh: Chăm sóc da</p><p>Kinh nghiệm: cập nhật theo hồ sơ nhân sự thực tế.</p></article><article><img src="'.esc_url(beauty_theme_image_url('spa-album-hairwash.jpg')).'" alt="Chuyên viên gội đầu dưỡng sinh" loading="lazy"><h3>Chuyên viên thư giãn</h3><p>Chức danh: Gội đầu dưỡng sinh / massage</p><p>Kinh nghiệm: cập nhật theo hồ sơ nhân sự thực tế.</p></article></div></section><section class="beauty-about-facility"><span class="eyebrow">KHÔNG GIAN SPA</span><h2>Một không gian để nghỉ ngơi</h2><div class="beauty-about-values"><img src="'.esc_url(beauty_theme_image_url('spa-album-room.jpg')).'" alt="Không gian phòng spa" loading="lazy"><img src="'.esc_url(beauty_theme_image_url('spa-album-massage.jpg')).'" alt="Không gian massage thư giãn" loading="lazy"><img src="'.esc_url(beauty_theme_image_url('spa-album-products.jpg')).'" alt="Sản phẩm chăm sóc tại spa" loading="lazy"></div></section><section class="beauty-about-proof"><h2>Chứng nhận & dấu mốc</h2><p>Chỉ hiển thị chứng nhận, giải thưởng và số liệu đã được Beauty Spa xác minh. Hãy cập nhật số năm hoạt động, số khách hàng và số chuyên viên từ hồ sơ chính thức trước khi công bố.</p><div class="beauty-about-values"><article><h3>Năm kinh nghiệm</h3><p>Cập nhật số liệu đã xác minh</p></article><article><h3>Khách hàng</h3><p>Cập nhật số liệu đã xác minh</p></article><article><h3>Chuyên viên</h3><p>Cập nhật số liệu đã xác minh</p></article></div></section><div class="beauty-about-commit"><h2>Cam kết chất lượng</h2><p>Thông tin dịch vụ và sản phẩm được trình bày rõ ràng; khách hàng được giải thích về quy trình, chi phí và lưu ý trước khi quyết định. Nội dung chứng nhận, số liệu và hồ sơ chuyên viên chỉ nên công bố sau khi được xác minh.</p><img src="'.esc_url(beauty_theme_image_url('spa-album-room.jpg')).'" alt="Không gian thư giãn tại Beauty Spa" loading="lazy"></div></section>';
    }
    return $content;
}
add_filter('the_content','beauty_custom_page_content',20);

function beauty_ensure_wishlist_page() {
    if (!get_page_by_path('san-pham-yeu-thich')) {
        wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>'Sản phẩm yêu thích','post_name'=>'san-pham-yeu-thich','post_content'=>'Danh sách sản phẩm yêu thích của bạn.'));
    }
}
add_action('after_switch_theme','beauty_ensure_wishlist_page');

/* Google Font - Great Vibes for About Hero */
function beauty_about_great_vibes_font() {
    wp_enqueue_style(
        'beauty-great-vibes',
        'https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap',
        array(),
        null
    );
}
add_action('wp_enqueue_scripts', 'beauty_about_great_vibes_font', 20);


/* ==========================================================================
   [SLIDER SẢN PHẨM] Swiper + lọc danh mục cho section "Sản phẩm Beauty" (chỉ trang chủ)
   - Swiper nạp từ jsDelivr (đã ghim phiên bản). Muốn tự host: xem ghi chú ở cuối file này.
   ========================================================================== */
function beauty_products_slider_assets() {
    if (!is_front_page()) return;

    $dir = get_template_directory();
    $uri = get_template_directory_uri();
    $css = $dir . '/assets/css/beauty-product-slider.css';
    $js  = $dir . '/assets/js/beauty-product-slider.js';
    $ver = '11.1.14';

    wp_enqueue_style('beauty-swiper', 'https://cdn.jsdelivr.net/npm/swiper@' . $ver . '/swiper-bundle.min.css', array(), $ver);
    wp_enqueue_script('beauty-swiper', 'https://cdn.jsdelivr.net/npm/swiper@' . $ver . '/swiper-bundle.min.js', array(), $ver, true);

    wp_enqueue_style(
        'beauty-product-slider',
        $uri . '/assets/css/beauty-product-slider.css',
        array('beauty-swiper', 'beaute-spa-style', 'beauty-v9'),
        file_exists($css) ? (string) filemtime($css) : BEAUTY_SPA_VERSION
    );
    wp_enqueue_script(
        'beauty-product-slider',
        $uri . '/assets/js/beauty-product-slider.js',
        array('beauty-swiper'),
        file_exists($js) ? (string) filemtime($js) : BEAUTY_SPA_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'beauty_products_slider_assets', 110);
/* Tự host Swiper (không phụ thuộc CDN): tải swiper-bundle.min.css/.js vào assets/vendor/swiper/
   rồi đổi 2 URL ở trên thành $uri . '/assets/vendor/swiper/swiper-bundle.min.(css|js)'. */

// BEAUTY SPA - CONTACT CSS BANNER
add_action('wp_footer', function () {
    return; // Banner cũ đã được thay bằng contact-banner.php

    if (!is_page()) return;

    $title = get_the_title();
    if (mb_stripos($title, 'liên hệ') === false && mb_stripos($title, 'contact') === false) return;
    ?>
    <style>
    /* An banner anh loi cu */
    .beauty-contact-hero {
        display: none !important;
    }

    /* Banner lien he moi */
    .beauty-contact-banner-css {
        position: relative;
        width: min(1200px, calc(100% - 40px));
        margin: 28px auto 30px;
        padding: 48px 56px;
        box-sizing: border-box;
        overflow: hidden;
        border-radius: 20px;
        background:
            radial-gradient(circle at 90% 15%, rgba(255,255,255,.75), transparent 28%),
            linear-gradient(115deg, #f8e6cf 0%, #f4d8b5 52%, #edc99f 100%);
        border: 1px solid #ecd4b8;
        box-shadow: 0 10px 28px rgba(112,75,43,.09);
        color: #604638;
    }

    .beauty-contact-banner-css:before {
        content: "✦";
        position: absolute;
        right: 10%;
        top: 8px;
        font-size: 115px;
        line-height: 1;
        color: rgba(255,255,255,.42);
    }

    .beauty-contact-banner-css:after {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        right: -65px;
        bottom: -145px;
        border: 1px solid rgba(255,255,255,.55);
        border-radius: 50%;
        box-shadow: 0 0 0 22px rgba(255,255,255,.10),
                    0 0 0 44px rgba(255,255,255,.08);
    }

    .beauty-contact-banner-css .contact-eyebrow {
        position: relative;
        z-index: 1;
        margin: 0 0 10px;
        color: #a8794e;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 3px;
        text-transform: uppercase;
    }

    .beauty-contact-banner-css h2 {
        position: relative;
        z-index: 1;
        margin: 0 0 12px;
        color: #624536;
        font-family: Georgia, serif;
        font-size: clamp(25px, 3vw, 38px);
        font-weight: 500;
        line-height: 1.25;
    }

    .beauty-contact-banner-css p {
        position: relative;
        z-index: 1;
        max-width: 570px;
        margin: 0;
        color: #806957;
        font-size: 14px;
        line-height: 1.8;
    }

    @media(max-width: 768px) {
        .beauty-contact-banner-css {
            width: calc(100% - 28px);
            margin: 18px auto 22px;
            padding: 32px 24px;
            border-radius: 15px;
        }
        .beauty-contact-banner-css:before {
            right: 7%;
            font-size: 75px;
        }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (document.querySelector('.beauty-contact-banner-css')) return;

        const banner = document.createElement('section');
        banner.className = 'beauty-contact-banner-css';
        banner.innerHTML = `
            <div class="contact-eyebrow">BEAUTY SPA · CONTACT</div>
            <h2>Chúng tôi luôn sẵn sàng lắng nghe</h2>
            <p>Hãy chia sẻ mong muốn của bạn. Beauty Spa sẽ đồng hành và tư vấn liệu trình chăm sóc phù hợp để bạn luôn tự tin, rạng rỡ.</p>
        `;

        const header = document.querySelector(
            '#masthead, header.site-header, .site-header, header'
        );

        if (header) {
            header.insertAdjacentElement('afterend', banner);
        }
    });
    </script>
    <?php
}, 100);
// END BEAUTY SPA - CONTACT CSS BANNER




// BEAUTY SPA - CONTACT BANNER NEW
require_once get_stylesheet_directory() . '/contact-banner.php';
