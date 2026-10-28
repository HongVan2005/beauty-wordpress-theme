<?php get_header(); ?><main>
<?php $beauty_slides = beauty_hero_slides(); $beauty_slide_total = count($beauty_slides); if ($beauty_slide_total) : ?>
<section class="hero hero-slider" aria-roledescription="carousel" aria-label="Banner Beauty Spa" data-autoplay="5000">
<div class="hero-slides">
<?php foreach ($beauty_slides as $i => $slide) : ?>
<article class="hero-slide<?php echo 0 === $i ? ' active' : ''; ?>" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr(($i + 1) . ' / ' . $beauty_slide_total); ?>"><div class="container hero-grid"><div class="hero-copy"><span class="eyebrow"><?php echo esc_html($slide['eyebrow']); ?></span><?php if (0 === $i) : ?><h1 class="hero-title"><?php echo esc_html($slide['title']); ?></h1><?php else : ?><h2 class="hero-title"><?php echo esc_html($slide['title']); ?></h2><?php endif; ?><p><?php echo esc_html($slide['desc']); ?></p><a class="btn" href="<?php echo esc_url($slide['url']); ?>"><?php echo esc_html($slide['cta']); ?> →</a></div><div class="hero-image"><?php echo beauty_hero_image_html($slide, $i); ?></div></div></article>
<?php endforeach; ?>
</div>
<?php if ($beauty_slide_total > 1) : ?>
<button class="slider-arrow prev" type="button" aria-label="Banner trước">‹</button><button class="slider-arrow next" type="button" aria-label="Banner tiếp theo">›</button>
<div class="slider-dots" role="group" aria-label="Chọn banner"><?php for ($d = 0; $d < $beauty_slide_total; $d++) : ?><button type="button" class="<?php echo 0 === $d ? 'active' : ''; ?>" aria-label="<?php echo esc_attr('Banner ' . ($d + 1)); ?>"></button><?php endfor; ?></div>
<?php endif; ?>
</section>
<?php endif; ?>
<section class="trust-strip"><div class="container trust-grid"><span>✧ Đội ngũ tận tâm</span><span>✧ Không gian thư giãn</span><span>✧ Dịch vụ đa dạng</span><span>✧ Chăm sóc khách hàng chu đáo</span></div></section>
<?php /* [FIX 1] Đã XÓA section "Chọn dịch vụ theo nhu cầu của bạn" */ ?>
<section class="section bv-section" id="danh-muc-dich-vu-chi-tiet"><div class="container">
 <?php echo do_shortcode('[beauty_service_categories]'); ?>
</div></section>

<section class="section section-soft bv-section"><div class="container">
 <div class="section-heading"><span class="eyebrow">Dịch vụ nổi bật</span><h2>Được nhiều khách lựa chọn</h2></div>
 <?php
 $beauty_feat = new WP_Query(array('post_type' => 'spa_service', 'posts_per_page' => 6, 'post_status' => 'publish', 'meta_key' => '_spa_featured', 'meta_value' => '1', 'no_found_rows' => true));
 if (!$beauty_feat->have_posts()) $beauty_feat = new WP_Query(array('post_type' => 'spa_service', 'posts_per_page' => 6, 'post_status' => 'publish', 'orderby' => 'menu_order title', 'order' => 'ASC', 'no_found_rows' => true));
 if ($beauty_feat->have_posts()) : ?><div class="bv-grid"><?php while ($beauty_feat->have_posts()) : $beauty_feat->the_post(); get_template_part('template-parts/service-card'); endwhile; ?></div><?php else : ?><p class="empty-state">Chưa có dịch vụ. Thêm trong WordPress Admin → Dịch vụ Spa.</p><?php endif; wp_reset_postdata(); ?>
 <div class="section-cta"><a class="btn btn-outline" href="<?php echo esc_url(get_post_type_archive_link('spa_service')); ?>">Xem tất cả dịch vụ</a></div>
</div></section>

<?php $beauty_pk = new WP_Query(array('post_type' => 'spa_package', 'posts_per_page' => 3, 'post_status' => 'publish', 'orderby' => 'menu_order title', 'order' => 'ASC', 'no_found_rows' => true)); if ($beauty_pk->have_posts()) : ?>
<section class="section bv-section"><div class="container">
 <div class="section-heading"><span class="eyebrow">Gói liệu trình</span><h2>Combo hoàn hảo - Tiết kiệm tối đa</h2><?php /* [FIX 2] ĐỔI TIÊU ĐỀ */ ?><p>Giá và quyền lợi hiển thị theo dữ liệu quản trị nhập. Nội dung mẫu cần được chủ spa xác nhận.</p></div>
 <div class="bv-grid"><?php while ($beauty_pk->have_posts()) : $beauty_pk->the_post(); $beauty_pid = get_the_ID(); $beauty_pp = beaute_spa_price($beauty_pid); ?>
  <?php get_template_part('template-parts/package-card'); ?>
 <?php endwhile; ?></div>
 <div class="section-cta"><a class="btn btn-outline" href="<?php echo esc_url(home_url('/bang-gia/')); ?>">Xem bảng giá đầy đủ</a></div>
</div></section>
<?php endif; wp_reset_postdata(); ?>

<section class="section section-soft bv-section" id="san-pham"><div class="container">
 <div class="section-heading"><span class="eyebrow">Chăm sóc tại nhà</span><h2>Sản phẩm Beauty</h2><p>Tiếp nối trải nghiệm tại spa bằng thói quen chăm sóc phù hợp mỗi ngày.</p></div>
 <?php if (beauty_use_woocommerce()) :
  $beauty_wc = wc_get_products(array('status' => 'publish', 'limit' => 8, 'orderby' => 'date', 'order' => 'DESC'));
  if ($beauty_wc) : ?><div class="bv-prod-grid"><?php foreach ($beauty_wc as $beauty_p) : ?><article class="bv-prod"><a class="bv-prod-media" href="<?php echo esc_url($beauty_p->get_permalink()); ?>" tabindex="-1" aria-hidden="true"><?php echo wp_kses_post($beauty_p->get_image('woocommerce_thumbnail')); ?></a><div class="bv-prod-body"><h3><a href="<?php echo esc_url($beauty_p->get_permalink()); ?>"><?php echo esc_html($beauty_p->get_name()); ?></a></h3><div class="bv-price"><?php echo wp_kses_post($beauty_p->get_price_html()); ?></div><a class="btn btn-outline" href="<?php echo esc_url($beauty_p->get_permalink()); ?>">Xem chi tiết</a></div></article><?php endforeach; ?></div><?php else : ?><p class="empty-state">Chưa có sản phẩm WooCommerce.</p><?php endif;
 else :
  $beauty_pq = new WP_Query(array('post_type' => 'spa_product', 'posts_per_page' => 8, 'post_status' => 'publish', 'orderby' => 'menu_order title', 'order' => 'ASC', 'no_found_rows' => true));
  $beauty_pcats = get_terms(array('taxonomy' => 'spa_product_cat', 'hide_empty' => true));
  if (!is_wp_error($beauty_pcats) && $beauty_pcats) : ?><div class="bv-chips-row center"><?php foreach ($beauty_pcats as $beauty_pc) : ?><a class="bv-pill" href="<?php echo esc_url(get_term_link($beauty_pc)); ?>"><?php echo esc_html($beauty_pc->name); ?></a><?php endforeach; ?></div><?php endif;
  if ($beauty_pq->have_posts()) : ?><div class="bv-prod-grid"><?php while ($beauty_pq->have_posts()) : $beauty_pq->the_post(); get_template_part('template-parts/product-card'); endwhile; ?></div><?php else : ?><p class="empty-state">Chưa có sản phẩm. Thêm trong WordPress Admin → Sản phẩm (cửa hàng Beauty).</p><?php endif; wp_reset_postdata();
 endif; ?>
 <div class="section-cta"><a class="btn btn-outline" href="<?php echo esc_url(beauty_shop_url()); ?>">Xem tất cả sản phẩm</a></div>
</div></section>

<?php /* [MOVE] "Không gian & trải nghiệm thực tế" chuyển xuống dưới "Sản phẩm Beauty" */ ?>
<section class="section bv-section" id="hinh-anh-beauty"><div class="container">
 <?php echo do_shortcode('[beauty_image_categories]'); ?>
</div></section>

<?php
$beauty_exp = beauty_experience_content();
$beauty_rv = get_posts(array('post_type' => 'spa_review', 'numberposts' => 3, 'post_status' => 'publish'));
$beauty_rv_sample = empty($beauty_rv);
$beauty_feedbacks = array();
if (!$beauty_rv_sample) {
 foreach ($beauty_rv as $beauty_r) {
  $beauty_feedbacks[] = array(
   'stars' => max(0, min(5, (int) get_post_meta($beauty_r->ID, '_r_rating', true))),
   'text' => wp_strip_all_tags($beauty_r->post_content),
   'name' => $beauty_r->post_title,
   'meta' => trim(get_post_meta($beauty_r->ID, '_r_service', true) . ((get_post_meta($beauty_r->ID, '_r_date', true)) ? ' · ' . get_post_meta($beauty_r->ID, '_r_date', true) : ''), ' ·'),
  );
 }
} else {
 $beauty_feedbacks = beauty_sample_feedback();
}
?>
<section class="section bv-section bv-experience" id="trai-nghiem"><div class="container">
 <article class="bv-exp-article">
  <div class="bv-exp-media"><img src="<?php echo esc_url(beauty_theme_image_url('spa-album-room.jpg')); ?>" alt="Phòng chăm sóc với giường spa, ánh sáng dịu và cây xanh" width="504" height="504" loading="lazy" decoding="async"></div>
  <div class="bv-exp-post">
   <span class="eyebrow">Trải nghiệm tại Beauty Spa</span>
   <h2><?php echo esc_html($beauty_exp['title']); ?></h2>
   <?php foreach ($beauty_exp['paragraphs'] as $beauty_pi => $beauty_para) : ?><p<?php echo 0 === $beauty_pi ? ' class="bv-exp-lead"' : ''; ?>><?php echo esc_html($beauty_para); ?></p><?php endforeach; ?>
   <ol class="bv-exp-steps" aria-label="Các bước của một buổi chăm sóc"><li>Tư vấn</li><li>Chuẩn bị</li><li>Thực hiện</li><li>Hướng dẫn sau dịch vụ</li></ol>
   <div class="bv-actions"><a class="btn" href="<?php echo esc_url(home_url('/dat-lich/')); ?>">Đặt lịch</a><a class="btn btn-outline" href="<?php echo esc_url(home_url('/gioi-thieu/')); ?>">Về chúng tôi</a></div>
  </div>
 </article>
 <div class="bv-feedback" aria-labelledby="bv-feedback-title">
  <div class="bv-feedback-head"><span class="eyebrow">Khách hàng nói gì</span><h3 id="bv-feedback-title">Chia sẻ từ những khách đã trải nghiệm</h3></div>
  <div class="bv-fb-grid"><?php foreach ($beauty_feedbacks as $beauty_f) : ?>
   <blockquote class="bv-fb-card"><?php if ($beauty_f['stars']) : ?><div class="bv-stars" aria-label="<?php echo esc_attr($beauty_f['stars'] . ' trên 5 sao'); ?>"><?php echo esc_html(str_repeat('★', $beauty_f['stars']) . str_repeat('☆', 5 - $beauty_f['stars'])); ?></div><?php endif; ?>
    <p><?php echo esc_html($beauty_f['text']); ?></p>
    <footer><span class="bv-fb-avatar" aria-hidden="true"><?php echo esc_html(function_exists('mb_substr') ? mb_strtoupper(mb_substr(trim($beauty_f['name']), 0, 1)) : strtoupper(substr(trim($beauty_f['name']), 0, 1))); ?></span><span><strong><?php echo esc_html($beauty_f['name']); ?></strong><?php if (!empty($beauty_f['meta'])) : ?><small><?php echo esc_html($beauty_f['meta']); ?></small><?php endif; ?></span></footer></blockquote>
  <?php endforeach; ?></div>
  <?php if ($beauty_rv_sample) : ?><p class="bv-fb-note">Phản hồi minh họa. Hãy thay bằng đánh giá thật (đã được khách đồng ý) tại Admin → Đánh giá khách hàng.</p><?php endif; ?>
 </div>
</div></section>

<?php /* [FIX 3] Đã XÓA section "Khoảnh khắc tại spa" (trùng với "Không gian & trải nghiệm thực tế") */ ?>

<?php $beauty_news = new WP_Query(array('post_type' => 'post', 'posts_per_page' => 3, 'post_status' => 'publish', 'ignore_sticky_posts' => true, 'no_found_rows' => true)); if ($beauty_news->have_posts()) : ?>
<section class="section bv-section"><div class="container">
 <div class="section-heading"><span class="eyebrow">Kiến thức làm đẹp</span><h2>Bài viết gần đây</h2></div>
 <div class="bv-grid"><?php while ($beauty_news->have_posts()) : $beauty_news->the_post(); get_template_part('template-parts/news-card'); endwhile; ?></div>
 <div class="section-cta"><a class="btn btn-outline" href="<?php echo esc_url(home_url('/tin-tuc/')); ?>">Xem tất cả bài viết</a></div>
</div></section>
<?php endif; wp_reset_postdata(); ?>

<?php $beauty_faq = get_posts(array('post_type' => 'spa_faq', 'numberposts' => 8, 'orderby' => 'menu_order', 'order' => 'ASC')); if ($beauty_faq) : ?>
<section class="section bv-section"><div class="container bv-narrow"><div class="section-heading"><span class="eyebrow">Giải đáp</span><h2>Câu hỏi thường gặp</h2></div>
 <div class="bv-faq"><?php foreach ($beauty_faq as $beauty_q) : ?><details><summary><?php echo esc_html($beauty_q->post_title); ?></summary><div><?php echo wp_kses_post(wpautop($beauty_q->post_content)); ?></div></details><?php endforeach; ?></div></div></section>
<?php endif; ?>

<section class="bv-cta-band"><div class="container"><div><h2>Sẵn sàng dành thời gian cho bản thân?</h2><p>Gửi yêu cầu đặt lịch, spa sẽ liên hệ xác nhận thời gian phù hợp.</p></div><a class="btn btn-light" href="<?php echo esc_url(home_url('/dat-lich/')); ?>">Đặt lịch ngay</a></div></section>
</main>
<?php get_footer(); ?>
