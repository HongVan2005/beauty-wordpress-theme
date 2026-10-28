<?php
/* [FIX 4] Thẻ Gói dịch vụ / Combo: ảnh 4:3 + tiêu đề + quyền lợi + giá lớn + 2 nút 50/50 */
$beauty_pid  = get_the_ID();
$beauty_price = beaute_spa_price($beauty_pid);
$beauty_sale  = beaute_spa_price($beauty_pid, true);
$beauty_img   = beauty_v9_package_image($beauty_pid);
$beauty_inc   = beauty_v9_package_includes($beauty_pid);
?>
<article <?php post_class('bv-card bv-tier bvp-card'); ?>>
  <a class="bvp-media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
    <?php if ($beauty_img['thumb']) { the_post_thumbnail('medium_large', array('loading' => 'lazy', 'decoding' => 'async')); } else { ?>
    <img src="<?php echo esc_url($beauty_img['url']); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" width="800" height="600" loading="lazy" decoding="async" data-fallback="<?php echo esc_url(beauty_theme_image_url('spa-album-room.jpg')); ?>">
    <?php } ?>
    <span class="bv-chip bv-chip-gold bvp-badge">Gói dịch vụ</span>
  </a>
  <div class="bvp-body">
    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
    <?php if ($beauty_inc) : ?>
    <div class="bvp-inc"><strong>Bao gồm:</strong>
      <ul><?php foreach ($beauty_inc as $beauty_line) : ?><li><?php echo esc_html($beauty_line); ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>
    <div class="bvp-price">
      <?php if ($beauty_sale) : ?><span class="bvp-price-now"><?php echo esc_html($beauty_sale); ?></span> <del><?php echo esc_html($beauty_price); ?></del>
      <?php elseif ($beauty_price) : ?><span class="bvp-price-now"><?php echo esc_html($beauty_price); ?></span>
      <?php else : ?><span class="bvp-price-contact">Liên hệ để biết giá</span><?php endif; ?>
    </div>
    <div class="bvp-actions">
      <a class="btn btn-outline" href="<?php the_permalink(); ?>">Xem chi tiết</a>
      <a class="btn" href="<?php echo esc_url(add_query_arg('goi', $beauty_pid, home_url('/dat-lich/'))); ?>">Đặt gói</a>
    </div>
  </div>
</article>
