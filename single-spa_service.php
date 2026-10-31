<?php get_header(); while (have_posts()) : the_post();
$beauty_id = get_the_ID();
$beauty_terms = get_the_terms($beauty_id, 'spa_service_cat');
$beauty_price = beaute_spa_price($beauty_id); $beauty_sale = beaute_spa_price($beauty_id, true);
$beauty_duration = get_post_meta($beauty_id, '_spa_duration', true);
$beauty_phone = preg_replace('/[^0-9+]/', '', beauty_contact_info('phone'));
$beauty_book = add_query_arg('dich-vu', $beauty_id, home_url('/dat-lich/'));
?>
<section class="bv-detail-hero"><div class="container bv-detail-grid">
 <div class="bv-detail-media"><?php if (has_post_thumbnail()) { the_post_thumbnail('large', array('loading' => 'eager', 'fetchpriority' => 'high')); } else { ?><img src="<?php echo esc_url(beauty_v7_service_image_url($beauty_id)); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" width="504" height="504" loading="eager" fetchpriority="high"><?php } ?></div>
 <div class="bv-detail-info">
  <nav class="bv-crumbs" aria-label="Điều hướng"><a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a> / <a href="<?php echo esc_url(get_post_type_archive_link('spa_service')); ?>">Dịch vụ</a><?php if ($beauty_terms && !is_wp_error($beauty_terms)) : ?> / <a href="<?php echo esc_url(get_term_link($beauty_terms[0])); ?>"><?php echo esc_html($beauty_terms[0]->name); ?></a><?php endif; ?></nav>
  <h1 class="page-title"><?php the_title(); ?></h1>
  <?php if (has_excerpt()) : ?><p class="bv-lead"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
  <div class="bv-facts">
   <div><span>Giá</span><strong><?php if ($beauty_sale) { echo esc_html($beauty_sale) . ' <del>' . esc_html($beauty_price) . '</del>'; } elseif ($beauty_price) { echo 'Từ ' . esc_html($beauty_price); } else { echo 'Liên hệ'; } ?></strong></div>
   <div><span>Thời lượng</span><strong><?php echo $beauty_duration ? esc_html($beauty_duration) : 'Theo tư vấn'; ?></strong></div>
  </div>
  <div class="bv-actions"><a class="btn" href="<?php echo esc_url($beauty_book); ?>">Đặt lịch dịch vụ này</a><?php if ($beauty_phone) : ?><a class="btn btn-outline" href="tel:<?php echo esc_attr($beauty_phone); ?>">Gọi tư vấn</a><?php endif; ?></div>
  <p class="bv-note">Đây là yêu cầu đặt lịch, chỉ được giữ chỗ sau khi spa liên hệ xác nhận. Kết quả có thể khác nhau tùy từng người.</p>
 </div>
</div></section>
<main class="container bv-detail-body">
 <div class="bv-detail-main">
  <section class="bv-block"><div class="entry-content"><?php the_content(); ?></div></section>
  <?php
  $beauty_sections = array('_s_suitable' => array('Phù hợp với nhu cầu nào', 'ul'), '_s_benefits' => array('Lợi ích và giới hạn thực tế', 'ul'), '_s_caution' => array('Cần cân nhắc hoặc tư vấn trước', 'ul'), '_s_steps' => array('Các bước thực hiện', 'ol'), '_s_prepare' => array('Chuẩn bị trước khi đến spa', 'ul'), '_s_aftercare' => array('Chăm sóc sau dịch vụ', 'ul'));
  foreach ($beauty_sections as $beauty_key => $beauty_def) {
      $beauty_html = beauty_lines(get_post_meta($beauty_id, $beauty_key, true), $beauty_def[1]);
      if ($beauty_html) echo '<section class="bv-block"><h2>' . esc_html($beauty_def[0]) . '</h2>' . $beauty_html . '</section>';
  }
  $beauty_faq = get_posts(array('post_type' => 'spa_faq', 'numberposts' => 4, 'orderby' => 'menu_order', 'order' => 'ASC'));
  if ($beauty_faq) : ?><section class="bv-block"><h2>Câu hỏi thường gặp</h2><div class="bv-faq"><?php foreach ($beauty_faq as $beauty_q) : ?><details><summary><?php echo esc_html($beauty_q->post_title); ?></summary><div><?php echo wp_kses_post(wpautop($beauty_q->post_content)); ?></div></details><?php endforeach; ?></div></section><?php endif; ?>
 </div>
 <aside class="bv-detail-side">
  <div class="bv-side-card bv-side-cta"><h3>Đặt lịch hẹn</h3><p>Gửi yêu cầu, spa sẽ liên hệ xác nhận thời gian phù hợp.</p><a class="btn" href="<?php echo esc_url($beauty_book); ?>">Đặt lịch ngay</a></div>
  <?php
  $beauty_related = new WP_Query(array('post_type' => 'spa_service', 'posts_per_page' => 3, 'post__not_in' => array($beauty_id), 'post_status' => 'publish', 'tax_query' => ($beauty_terms && !is_wp_error($beauty_terms)) ? array(array('taxonomy' => 'spa_service_cat', 'field' => 'term_id', 'terms' => wp_list_pluck($beauty_terms, 'term_id'))) : array()));
  if ($beauty_related->have_posts()) : ?><div class="bv-side-card"><h3>Dịch vụ liên quan</h3><ul class="bv-side-list"><?php while ($beauty_related->have_posts()) : $beauty_related->the_post(); ?><li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li><?php endwhile; ?></ul></div><?php endif; wp_reset_postdata(); ?>
   <div class="bv-side-card bv-spa-journey">
    <span class="bv-spa-journey__eyebrow">BEAUTY SPA</span>
    <h3>Hành trình chăm sóc</h3>
    <p class="bv-spa-journey__intro">Tận hưởng trải nghiệm chăm sóc và thư giãn theo từng bước.</p>

    <div class="bv-spa-journey__step">
      <span class="bv-spa-journey__number">01</span>
      <div>
        <strong>Tư vấn cá nhân</strong>
        <p>Lắng nghe nhu cầu và mong muốn của bạn.</p>
      </div>
    </div>

    <div class="bv-spa-journey__step">
      <span class="bv-spa-journey__number">02</span>
      <div>
        <strong>Trải nghiệm liệu trình</strong>
        <p>Thực hiện dịch vụ với quy trình phù hợp.</p>
      </div>
    </div>

    <div class="bv-spa-journey__step">
      <span class="bv-spa-journey__number">03</span>
      <div>
        <strong>Chăm sóc sau dịch vụ</strong>
        <p>Hướng dẫn chăm sóc và duy trì tại nhà.</p>
      </div>
    </div>

    <div class="bv-spa-journey__footer">
      BEAUTY IS A JOURNEY, NOT A DESTINATION
    </div>
  </div>
 </aside>
</main>
<?php endwhile; get_footer(); ?>
