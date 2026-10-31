<?php
/** Bố cục trang Cửa hàng: banner + sidebar (danh mục, lọc giá, nổi bật) + danh sách sản phẩm. Dùng cho archive và taxonomy. */
if (!defined('ABSPATH')) exit;
global $wp_query;
$bvs_p        = bvs_params();
$bvs_shop_url = get_post_type_archive_link('spa_product');
$bvs_term     = is_tax('spa_product_cat') ? get_queried_object() : null;
if ($bvs_term && is_wp_error($bvs_term)) $bvs_term = null;
$bvs_base     = $bvs_term ? get_term_link($bvs_term) : $bvs_shop_url;
if (is_wp_error($bvs_base) || !$bvs_base) $bvs_base = $bvs_shop_url;
$bvs_heading  = $bvs_term ? $bvs_term->name : 'Tất cả sản phẩm';
$bvs_total    = (int) $wp_query->found_posts;
$bvs_cats     = get_terms(array('taxonomy' => 'spa_product_cat', 'hide_empty' => true));
$bvs_all_cnt  = (int) wp_count_posts('spa_product')->publish;
$bvs_bounds   = bvs_price_bounds();
$bvs_filtered = ($bvs_p['min'] > 0 || $bvs_p['max'] > 0);
$bvs_feat     = bvs_featured_ids(3);
?>
<div class="bv-shop">

<section class="bvs-hero" aria-labelledby="bvs-hero-title">
 <div class="bvs-wrap bvs-hero__inner">
  <div class="bvs-hero__copy">
   <h1 id="bvs-hero-title" class="bvs-hero__title"><span>Chăm sóc làn da,</span> <span>nâng niu vẻ đẹp</span></h1>
   <p class="bvs-hero__lead">Những sản phẩm chăm sóc da, tóc và cơ thể được chọn lọc cho thói quen làm đẹp nhẹ nhàng mỗi ngày.</p>
   <a class="bvs-btn bvs-btn--solid bvs-hero__cta" href="#bvs-main">Khám phá sản phẩm</a>
  </div>
 </div>
 <div class="bvs-hero__media"><img src="<?php echo esc_url(bvs_hero_image_url()); ?>" alt="Bộ sản phẩm chăm sóc da Beauty" width="504" height="504" fetchpriority="high" decoding="async"></div>
</section>

<div class="bvs-wrap bvs-layout">

 <aside class="bvs-side" aria-label="Bộ lọc cửa hàng">

  <nav class="bvs-box bvs-box--cats" aria-labelledby="bvs-cats-title">
   <h2 id="bvs-cats-title" class="bvs-box__title">Danh mục sản phẩm</h2>
   <ul class="bvs-cats">
    <li><a class="bvs-cat<?php echo $bvs_term ? '' : ' is-active'; ?>" href="<?php echo esc_url(bvs_link($bvs_shop_url)); ?>"<?php echo $bvs_term ? '' : ' aria-current="page"'; ?>><?php echo bvs_cat_icon('tat-ca'); ?><span class="bvs-cat__name">Tất cả sản phẩm</span><span class="bvs-cat__count"><?php echo (int) $bvs_all_cnt; ?></span></a></li>
    <?php if ($bvs_cats && !is_wp_error($bvs_cats)) : foreach ($bvs_cats as $bvs_c) :
        $bvs_cl = get_term_link($bvs_c);
        if (is_wp_error($bvs_cl)) continue;
        $bvs_on = $bvs_term && (int) $bvs_term->term_id === (int) $bvs_c->term_id; ?>
    <li><a class="bvs-cat<?php echo $bvs_on ? ' is-active' : ''; ?>" href="<?php echo esc_url(bvs_link($bvs_cl)); ?>"<?php echo $bvs_on ? ' aria-current="page"' : ''; ?>><?php echo bvs_cat_icon($bvs_c->slug); ?><span class="bvs-cat__name"><?php echo esc_html($bvs_c->name); ?></span><span class="bvs-cat__count"><?php echo (int) $bvs_c->count; ?></span></a></li>
    <?php endforeach; endif; ?>
   </ul>
  </nav>

  <?php if ($bvs_bounds['max'] > 0) : ?>
  <section class="bvs-box bvs-box--price" aria-labelledby="bvs-price-title">
   <h2 id="bvs-price-title" class="bvs-box__title">Lọc theo giá</h2>
   <form class="bvs-pricef" method="get" action="<?php echo esc_url($bvs_base); ?>" data-min="<?php echo (int) $bvs_bounds['min']; ?>" data-max="<?php echo (int) $bvs_bounds['max']; ?>" data-step="<?php echo (int) $bvs_bounds['step']; ?>">
    <?php if ('' !== $bvs_p['sort']) : ?><input type="hidden" name="sap_xep" value="<?php echo esc_attr($bvs_p['sort']); ?>"><?php endif; ?>
    <div class="bvs-range" aria-hidden="false"></div>
    <div class="bvs-price__fields">
     <label><span>Giá thấp nhất (đ)</span><input type="number" name="gia_tu" inputmode="numeric" min="<?php echo (int) $bvs_bounds['min']; ?>" max="<?php echo (int) $bvs_bounds['max']; ?>" step="1000" placeholder="<?php echo (int) $bvs_bounds['min']; ?>" value="<?php echo $bvs_p['min'] > 0 ? (int) $bvs_p['min'] : ''; ?>"></label>
     <label><span>Giá cao nhất (đ)</span><input type="number" name="gia_den" inputmode="numeric" min="<?php echo (int) $bvs_bounds['min']; ?>" max="<?php echo (int) $bvs_bounds['max']; ?>" step="1000" placeholder="<?php echo (int) $bvs_bounds['max']; ?>" value="<?php echo $bvs_p['max'] > 0 ? (int) $bvs_p['max'] : ''; ?>"></label>
    </div>
    <button class="bvs-btn bvs-btn--solid bvs-price__submit" type="submit">Lọc giá</button>
    <?php if ($bvs_filtered) : ?><a class="bvs-price__reset" href="<?php echo esc_url('' !== $bvs_p['sort'] ? add_query_arg('sap_xep', $bvs_p['sort'], $bvs_base) : $bvs_base); ?>">Xóa lọc giá</a><?php endif; ?>
   </form>
  </section>
  <?php endif; ?>

  <?php if ($bvs_feat) : ?>
  <section class="bvs-box bvs-box--feat" aria-labelledby="bvs-feat-title">
   <h2 id="bvs-feat-title" class="bvs-box__title">Sản phẩm nổi bật</h2>
   <ul class="bvs-feat">
    <?php foreach ($bvs_feat as $bvs_fid) : ?>
    <li class="bvs-feat__item">
     <a class="bvs-feat__img" href="<?php echo esc_url(get_permalink($bvs_fid)); ?>" tabindex="-1" aria-hidden="true"><?php echo beauty_product_image($bvs_fid, 'thumbnail'); ?></a>
     <div class="bvs-feat__info"><a class="bvs-feat__name" href="<?php echo esc_url(get_permalink($bvs_fid)); ?>"><?php echo esc_html(get_the_title($bvs_fid)); ?></a><div class="bvs-price bvs-price--sm"><?php echo beauty_product_price_html($bvs_fid); ?></div></div>
    </li>
    <?php endforeach; ?>
   </ul>
  </section>
  <?php endif; ?>

 </aside>

 <main class="bvs-main" id="bvs-main">
  <nav class="bvs-crumbs" aria-label="Điều hướng"><a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a><span aria-hidden="true">›</span><?php if ($bvs_term) : ?><a href="<?php echo esc_url($bvs_shop_url); ?>">Cửa hàng</a><span aria-hidden="true">›</span><span aria-current="page"><?php echo esc_html($bvs_term->name); ?></span><?php else : ?><span aria-current="page">Cửa hàng</span><?php endif; ?></nav>

  <div class="bvs-toolbar">
   <div class="bvs-toolbar__title">
    <h2 class="bvs-heading"><?php echo esc_html($bvs_heading); ?></h2>
    <p class="bvs-count" role="status">Tổng số <?php echo esc_html(number_format_i18n($bvs_total)); ?> sản phẩm</p>
   </div>
      <form class="bvs-sort bvsv-tools" method="get" action="<?php echo esc_url($bvs_base); ?>" role="search">
    <?php if ($bvs_p['min'] > 0) : ?><input type="hidden" name="gia_tu" value="<?php echo (int) $bvs_p['min']; ?>"><?php endif; ?>
    <?php if ($bvs_p['max'] > 0) : ?><input type="hidden" name="gia_den" value="<?php echo (int) $bvs_p['max']; ?>"><?php endif; ?>
    <div class="bvsv-search">
     <label class="screen-reader-text" for="bvs-product-search">Tìm sản phẩm</label>
     <input id="bvs-product-search" class="bvsv-search__input" type="search" name="tu_khoa" placeholder="Tìm sản phẩm..." value="<?php echo esc_attr($bvs_p['q']); ?>" maxlength="80">
     <button class="bvs-btn bvs-btn--solid bvsv-search__btn" type="submit" style="display:inline-flex !important;align-items:center !important;justify-content:center !important;flex-shrink:0 !important;width:auto !important;min-width:100px !important;height:42px !important;padding:0 20px !important;border:1px solid #49372e !important;border-radius:999px !important;background:#49372e !important;background-image:none !important;color:#ffffff !important;font-size:12px !important;font-weight:600 !important;line-height:1 !important;box-shadow:0 3px 8px rgba(73,55,46,.15) !important;white-space:nowrap !important;cursor:pointer !important;">Tìm kiếm</button>
    </div>
    <div class="bvsv-sortbox">
     <label for="bvs-sort-select">Sắp xếp theo</label>
     <select id="bvs-sort-select" name="sap_xep"><?php foreach (bvs_sort_options() as $bvs_k => $bvs_label) : ?><option value="<?php echo esc_attr($bvs_k); ?>"<?php selected($bvs_p['sort'], $bvs_k); ?>><?php echo esc_html($bvs_label); ?></option><?php endforeach; ?></select>
     <button class="bvs-btn bvs-btn--ghost bvs-sort__submit" type="submit">Áp dụng</button>
    </div>
   </form>
  </div>

  <?php if (have_posts()) : ?>
   <div class="bvs-grid"><?php while (have_posts()) : the_post(); get_template_part('template-parts/shop-card'); endwhile; ?></div>
   <?php bvs_pagination(); ?>
  <?php else : ?>
   <div class="bvs-empty">
    <h3>Chưa có sản phẩm phù hợp</h3>
    <p><?php echo $bvs_filtered ? 'Không có sản phẩm nào trong khoảng giá này. Hãy thử mở rộng khoảng giá.' : 'Danh mục này chưa có sản phẩm. Quản trị viên thêm sản phẩm trong WordPress Admin → Sản phẩm (cửa hàng Beauty).'; ?></p>
    <a class="bvs-btn bvs-btn--solid" href="<?php echo esc_url($bvs_shop_url); ?>">Xem tất cả sản phẩm</a>
   </div>
  <?php endif; ?>
 </main>

</div>
</div>
