<?php
/** Thẻ dịch vụ dành riêng cho trang Dịch vụ – cùng cấu trúc/class với thẻ sản phẩm (shop-card.php). service-card.php cũ vẫn giữ nguyên cho nơi khác. */
if (!defined('ABSPATH')) exit;
$bvsv_id       = get_the_ID();
$bvsv_terms    = get_the_terms($bvsv_id, 'spa_service_cat');
$bvsv_duration = trim((string) get_post_meta($bvsv_id, '_spa_duration', true));
$bvsv_badge    = bvsv_badge($bvsv_id);
$bvsv_rating   = bvsv_rating($bvsv_id);
?>
<article class="bvs-card bvsv-card">
 <div class="bvs-card__media">
  <a class="bvs-card__img" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php echo bvsv_image($bvsv_id); ?></a>
  <?php if ($bvsv_badge) : ?><span class="bvs-badge bvs-badge--<?php echo esc_attr($bvsv_badge['type']); ?>"><?php echo esc_html($bvsv_badge['label']); ?></span><?php endif; ?>
  <button class="beauty-wishlist-toggle bvs-fav" type="button" data-wishlist-id="<?php echo esc_attr($bvsv_id); ?>" aria-label="Thêm vào yêu thích" aria-pressed="false">♡</button>
 </div>
 <div class="bvs-card__body">
  <?php if ($bvsv_terms && !is_wp_error($bvsv_terms)) : ?><span class="bvs-chip"><?php echo esc_html($bvsv_terms[0]->name); ?></span><?php endif; ?>
  <h3 class="bvs-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
  <?php if ('' !== $bvsv_duration) : ?><p class="bvs-card__size bvsv-duration"><span aria-hidden="true">◷</span> <?php echo esc_html($bvsv_duration); ?></p><?php endif; ?>
  <?php if ($bvsv_rating) : ?>
   <div class="bvs-rating"><span class="bvs-stars" style="--bvs-r:<?php echo esc_attr(number_format((float) $bvsv_rating['avg'], 1, '.', '')); ?>" role="img" aria-label="<?php echo esc_attr(sprintf('Đánh giá %s trên 5 sao', number_format_i18n($bvsv_rating['avg'], 1))); ?>">★★★★★</span><span class="bvs-rating__n">(<?php echo (int) $bvsv_rating['n']; ?>)</span></div>
  <?php else : ?>
   <div class="bvs-rating bvs-rating--empty"><span class="bvs-stars" style="--bvs-r:0" role="img" aria-label="Chưa có đánh giá">★★★★★</span><span class="bvs-rating__n">Chưa có đánh giá</span></div>
  <?php endif; ?>
  <div class="bvs-price"><?php echo bvsv_price_html($bvsv_id); ?></div>
  <div class="bvs-card__actions"><a class="bvs-btn bvs-btn--ghost" href="<?php the_permalink(); ?>">Xem chi tiết</a><a class="btn" href="<?php echo esc_url(add_query_arg('dich-vu', $bvsv_id, home_url('/dat-lich/'))); ?>">Đặt lịch</a></div>
 </div>
</article>
