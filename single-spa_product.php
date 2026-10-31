<?php get_header(); while (have_posts()) : the_post();
$beauty_id = get_the_ID();
$beauty_terms = get_the_terms($beauty_id, 'spa_product_cat');
$beauty_stock = get_post_meta($beauty_id, '_p_stock', true);
$beauty_zalo = beauty_contact_info('zalo'); $beauty_phone = preg_replace('/[^0-9+]/', '', beauty_contact_info('phone'));
$beauty_buy = $beauty_zalo ? $beauty_zalo : ($beauty_phone ? 'tel:' . $beauty_phone : home_url('/lien-he/'));
$beauty_rows = array('Thương hiệu' => get_post_meta($beauty_id, '_p_brand', true), 'Dung tích / quy cách' => get_post_meta($beauty_id, '_p_size', true), 'Mã SKU' => get_post_meta($beauty_id, '_p_sku', true), 'Xuất xứ' => get_post_meta($beauty_id, '_p_origin', true), 'Phù hợp với' => get_post_meta($beauty_id, '_p_skin', true));
?>
<section class="bv-detail-hero"><div class="container bv-detail-grid">
 <div class="bv-detail-media bv-detail-media-product"><?php echo beauty_product_image($beauty_id, 'large', true); ?></div>
 <div class="bv-detail-info">
  <nav class="bv-crumbs" aria-label="Điều hướng"><a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a> / <a href="<?php echo esc_url(get_post_type_archive_link('spa_product')); ?>">Sản phẩm</a><?php if ($beauty_terms && !is_wp_error($beauty_terms)) : ?> / <a href="<?php echo esc_url(get_term_link($beauty_terms[0])); ?>"><?php echo esc_html($beauty_terms[0]->name); ?></a><?php endif; ?></nav>
  <h1 class="page-title"><?php the_title(); ?></h1>
  <div class="bv-price bv-price-lg"><?php echo beauty_product_price_html($beauty_id); ?></div>
  <p class="bv-stock bv-stock-<?php echo esc_attr($beauty_stock ? $beauty_stock : 'con-hang'); ?>"><?php echo esc_html(beauty_product_stock_label($beauty_id)); ?></p>
  <?php if (has_excerpt()) : ?><p class="bv-lead"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
  <dl class="bv-specs"><?php foreach ($beauty_rows as $beauty_label => $beauty_val) { if ('' !== (string) $beauty_val) echo '<div><dt>' . esc_html($beauty_label) . '</dt><dd>' . esc_html($beauty_val) . '</dd></div>'; } ?></dl>
  <?php echo beauty_add_to_cart_form($beauty_id, true); ?>
  <div class="bv-actions"><a class="btn btn-outline" href="<?php echo esc_url(home_url('/dat-lich/')); ?>">Đặt lịch tư vấn</a></div>
  <p class="bv-note">Thanh toán khi nhận hàng hoặc chuyển khoản. Đơn hàng có hiệu lực sau khi spa liên hệ xác nhận.</p>
 </div>
</div></section>
<main class="container bv-detail-body">
 <div class="bv-detail-main">
  <section class="bv-block"><h2>Mô tả sản phẩm</h2><div class="entry-content"><?php the_content(); ?></div></section>
  <section class="bv-block"><h2>Thành phần</h2><?php $beauty_ing = trim((string) get_post_meta($beauty_id, '_p_ingredients', true)); echo $beauty_ing ? '<p>' . nl2br(esc_html($beauty_ing)) . '</p>' : '<p class="muted">Chưa cập nhật. Thành phần cần được xác nhận từ nhà cung cấp trước khi mở bán.</p>'; ?></section>
  <?php foreach (array('_p_usage' => 'Hướng dẫn sử dụng', '_p_caution' => 'Lưu ý, bảo quản và cảnh báo') as $beauty_key => $beauty_title) { $beauty_html = beauty_lines(get_post_meta($beauty_id, $beauty_key, true)); if ($beauty_html) echo '<section class="bv-block"><h2>' . esc_html($beauty_title) . '</h2>' . $beauty_html . '</section>'; } ?>
 </div>
 <aside class="bv-detail-side">
  <div class="bv-side-card bv-side-cta bv-spa-intro">
    <span class="bv-spa-intro__eyebrow">BEAUTY SPA</span>
    <h3>Chăm sóc làn da, nâng niu vẻ đẹp</h3>
    <p class="bv-spa-intro__desc">Đồng hành cùng bạn trên hành trình chăm sóc và nuôi dưỡng làn da khỏe đẹp mỗi ngày.</p>

    <div class="bv-spa-intro__item">
      <span class="bv-spa-intro__icon">01</span>
      <div>
        <strong>Sản phẩm chọn lọc</strong>
        <p>Ưu tiên chất lượng và nguồn gốc rõ ràng.</p>
      </div>
    </div>

    <div class="bv-spa-intro__item">
      <span class="bv-spa-intro__icon">02</span>
      <div>
        <strong>Chăm sóc phù hợp</strong>
        <p>Hướng dẫn lựa chọn sản phẩm theo nhu cầu làn da.</p>
      </div>
    </div>

    <div class="bv-spa-intro__item">
      <span class="bv-spa-intro__icon">03</span>
      <div>
        <strong>Đồng hành tận tâm</strong>
        <p>Luôn sẵn sàng hỗ trợ bạn trong quá trình chăm sóc da.</p>
      </div>
    </div>

    <div class="bv-spa-intro__footer">BEAUTY IS A JOURNEY, NOT A DESTINATION</div>
  </div>
</aside>
</main>
<?php
$beauty_rel = new WP_Query(array('post_type' => 'spa_product', 'posts_per_page' => 4, 'post__not_in' => array($beauty_id), 'post_status' => 'publish', 'tax_query' => ($beauty_terms && !is_wp_error($beauty_terms)) ? array(array('taxonomy' => 'spa_product_cat', 'field' => 'term_id', 'terms' => wp_list_pluck($beauty_terms, 'term_id'))) : array()));
if ($beauty_rel->have_posts()) : ?><section class="section section-soft"><div class="container"><div class="section-heading"><h2>Sản phẩm tương tự</h2></div><div class="bv-prod-grid"><?php while ($beauty_rel->have_posts()) : $beauty_rel->the_post(); get_template_part('template-parts/product-card'); endwhile; ?></div></div></section><?php endif; wp_reset_postdata();
endwhile; get_footer(); ?>
