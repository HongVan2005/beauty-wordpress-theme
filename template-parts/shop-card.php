<?php
/** Thẻ sản phẩm dành riêng cho trang Cửa hàng (trang chủ / chi tiết vẫn dùng product-card.php). */
if (!defined('ABSPATH')) exit;
$bvs_id     = get_the_ID();
$bvs_terms  = get_the_terms($bvs_id, 'spa_product_cat');
$bvs_size   = trim((string) get_post_meta($bvs_id, '_p_size', true));
$bvs_badge  = bvs_badge($bvs_id);
$bvs_rating = bvs_rating($bvs_id);
?>
<article class="bvs-card">
 <div class="bvs-card__media">
  <a class="bvs-card__img" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php echo beauty_product_image($bvs_id); ?></a>
  <?php if ($bvs_badge) : ?><span class="bvs-badge bvs-badge--<?php echo esc_attr($bvs_badge['type']); ?>"><?php echo esc_html($bvs_badge['label']); ?></span><?php endif; ?>
  <button class="beauty-wishlist-toggle bvs-fav" type="button" data-wishlist-id="<?php echo esc_attr($bvs_id); ?>" aria-label="Thêm vào yêu thích" aria-pressed="false">♡</button>
 </div>
 <div class="bvs-card__body">
  <?php if ($bvs_terms && !is_wp_error($bvs_terms)) : ?><span class="bvs-chip"><?php echo esc_html($bvs_terms[0]->name); ?></span><?php endif; ?>
  <h3 class="bvs-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
  <?php if ('' !== $bvs_size) : ?><p class="bvs-card__size"><?php echo esc_html($bvs_size); ?></p><?php endif; ?>
  <?php if ($bvs_rating) : ?>
   <div class="bvs-rating"><span class="bvs-stars" style="--bvs-r:<?php echo esc_attr(number_format((float) $bvs_rating['avg'], 1, '.', '')); ?>" role="img" aria-label="<?php echo esc_attr(sprintf('Đánh giá %s trên 5 sao', number_format_i18n($bvs_rating['avg'], 1))); ?>">★★★★★</span><span class="bvs-rating__n">(<?php echo (int) $bvs_rating['n']; ?>)</span></div>
  <?php endif; ?>
  <div class="bvs-price"><?php echo beauty_product_price_html($bvs_id); ?></div>
  <div class="bvs-card__actions"><a class="bvs-btn bvs-btn--ghost" href="<?php the_permalink(); ?>">Xem chi tiết</a><?php echo beauty_add_to_cart_form($bvs_id); ?></div>
 </div>
</article>
