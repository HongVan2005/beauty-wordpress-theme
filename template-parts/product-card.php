<?php $beauty_id = get_the_ID(); $beauty_pt = get_the_terms($beauty_id, 'spa_product_cat'); $beauty_size = get_post_meta($beauty_id, '_p_size', true); ?>
<article class="bv-prod">
 <div class="bv-prod-media"><a class="bv-prod-image-link" href="<?php the_permalink(); ?>"><?php echo beauty_product_image($beauty_id); ?></a><button class="beauty-wishlist-toggle" type="button" data-wishlist-id="<?php echo esc_attr($beauty_id); ?>" aria-label="Thêm vào yêu thích" aria-pressed="false">♡</button></div>
 <div class="bv-prod-body">
  <?php if ($beauty_pt && !is_wp_error($beauty_pt)) : ?><span class="bv-chip"><?php echo esc_html($beauty_pt[0]->name); ?></span><?php endif; ?>
  <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
  <p class="bv-meta"><?php echo esc_html($beauty_size); ?></p>
  <div class="bv-price"><?php echo beauty_product_price_html($beauty_id); ?></div>
  <div class="bv-prod-actions"><a class="btn btn-outline" href="<?php the_permalink(); ?>">Xem chi tiết</a><?php echo beauty_add_to_cart_form($beauty_id); ?></div>
 </div>
</article>
