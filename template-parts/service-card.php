<?php
$beauty_id = get_the_ID();
$beauty_duration = get_post_meta($beauty_id, '_spa_duration', true);
$beauty_price = beaute_spa_price($beauty_id);
$beauty_sale = beaute_spa_price($beauty_id, true);
$beauty_terms = get_the_terms($beauty_id, 'spa_service_cat');
?>
<article <?php post_class('bv-card'); ?>>
 <a class="bv-card-media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php if (has_post_thumbnail()) { the_post_thumbnail('medium_large', array('loading' => 'lazy', 'decoding' => 'async')); } else { ?><img src="<?php echo esc_url(beauty_v7_service_image_url($beauty_id)); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" width="504" height="504" loading="lazy" decoding="async"><?php } ?></a>
 <div class="bv-card-body">
  <div class="bv-chips"><?php if ('1' === get_post_meta($beauty_id, '_spa_featured', true)) : ?><span class="bv-chip bv-chip-gold">Được yêu thích</span><?php endif; ?><?php if ($beauty_terms && !is_wp_error($beauty_terms)) : ?><span class="bv-chip"><?php echo esc_html($beauty_terms[0]->name); ?></span><?php endif; ?></div>
  <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
  <p class="bv-excerpt"><?php beaute_spa_excerpt(20); ?></p>
  <div class="bv-card-foot">
   <div class="bv-price"><?php if ($beauty_sale) : ?><span class="bv-price-now"><?php echo esc_html($beauty_sale); ?></span> <del><?php echo esc_html($beauty_price); ?></del><?php elseif ($beauty_price) : ?><small>Từ</small> <span class="bv-price-now"><?php echo esc_html($beauty_price); ?></span><?php else : ?><span class="bv-price-contact">Liên hệ để biết giá</span><?php endif; ?><?php if ($beauty_duration) : ?><span class="bv-duration">◷ <?php echo esc_html($beauty_duration); ?></span><?php endif; ?></div>
   <div class="bv-actions"><a class="btn btn-outline" href="<?php the_permalink(); ?>">Xem chi tiết</a><a class="btn" href="<?php echo esc_url(add_query_arg('dich-vu', $beauty_id, home_url('/dat-lich/'))); ?>">Đặt lịch</a></div>
  </div>
 </div>
</article>
