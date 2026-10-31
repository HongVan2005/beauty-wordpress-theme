<?php
/** Bố cục trang Dịch vụ: banner + sidebar (danh mục, lọc giá, nổi bật) + thanh công cụ + lưới 3 cột + phân trang. Dùng cho archive và taxonomy. */
if (!defined('ABSPATH')) exit;
$bvsv_p        = bvsv_params();
$bvsv_all_url  = get_post_type_archive_link('spa_service');
$bvsv_term     = is_tax('spa_service_cat') ? get_queried_object() : null;
if ($bvsv_term && is_wp_error($bvsv_term)) $bvsv_term = null;
$bvsv_base     = $bvsv_term ? get_term_link($bvsv_term) : $bvsv_all_url;
if (is_wp_error($bvsv_base) || !$bvsv_base) $bvsv_base = $bvsv_all_url;
$bvsv_heading  = $bvsv_term ? $bvsv_term->name : 'Tất cả dịch vụ';
$bvsv_cats     = get_terms(array('taxonomy' => 'spa_service_cat', 'hide_empty' => true));
$bvsv_all_cnt  = (int) wp_count_posts('spa_service')->publish;
$bvsv_bounds   = bvsv_price_bounds();
$bvsv_filtered = ($bvsv_p['min'] > 0 || $bvsv_p['max'] > 0);
$bvsv_searched = ('' !== $bvsv_p['q']);
$bvsv_feat     = bvsv_featured_ids((int) apply_filters('beauty_service_featured_count', 3));
?>
<div class="bv-shop bv-shop--service">

<section class="bvs-hero" aria-labelledby="bvsv-hero-title">
 <div class="bvs-wrap bvs-hero__inner">
  <div class="bvs-hero__copy">
   <h1 id="bvsv-hero-title" class="bvs-hero__title"><span>Dịch vụ Beauty Spa</span></h1>
   <p class="bvs-hero__lead">Tìm dịch vụ theo nhóm, xem chi tiết và gửi yêu cầu đặt lịch cho liệu trình phù hợp với bạn.</p>
   <a class="bvs-btn bvs-btn--solid bvs-hero__cta" href="#bvs-main">Khám phá dịch vụ</a>
  </div>
 </div>
 <div class="bvs-hero__media"><img src="<?php echo esc_url(bvsv_hero_image_url()); ?>" alt="Dịch vụ chăm sóc da tại Beauty Spa" width="504" height="504" fetchpriority="high" decoding="async"></div>
</section>

<div class="bvs-wrap bvs-layout">

 <aside class="bvs-side" aria-label="Bộ lọc dịch vụ">

  <nav class="bvs-box bvs-box--cats" aria-labelledby="bvsv-cats-title">
   <h2 id="bvsv-cats-title" class="bvs-box__title">Danh mục dịch vụ</h2>
   <ul class="bvs-cats">
    <li><a class="bvs-cat<?php echo $bvsv_term ? '' : ' is-active'; ?>" href="<?php echo esc_url(bvsv_link($bvsv_all_url)); ?>"<?php echo $bvsv_term ? '' : ' aria-current="page"'; ?>><?php echo bvsv_cat_icon('tat-ca'); ?><span class="bvs-cat__name">Tất cả dịch vụ</span><span class="bvs-cat__count"><?php echo (int) $bvsv_all_cnt; ?></span></a></li>
    <?php if ($bvsv_cats && !is_wp_error($bvsv_cats)) : foreach ($bvsv_cats as $bvsv_c) :
        $bvsv_cl = get_term_link($bvsv_c);
        if (is_wp_error($bvsv_cl)) continue;
        $bvsv_on = $bvsv_term && (int) $bvsv_term->term_id === (int) $bvsv_c->term_id; ?>
    <li><a class="bvs-cat<?php echo $bvsv_on ? ' is-active' : ''; ?>" href="<?php echo esc_url(bvsv_link($bvsv_cl)); ?>"<?php echo $bvsv_on ? ' aria-current="page"' : ''; ?>><?php echo bvsv_cat_icon($bvsv_c->slug); ?><span class="bvs-cat__name"><?php echo esc_html($bvsv_c->name); ?></span><span class="bvs-cat__count"><?php echo (int) $bvsv_c->count; ?></span></a></li>
    <?php endforeach; endif; ?>
   </ul>
  </nav>

  <?php if ($bvsv_bounds['max'] > 0) : ?>
  <section class="bvs-box bvs-box--price" aria-labelledby="bvsv-price-title">
   <h2 id="bvsv-price-title" class="bvs-box__title">Lọc theo giá</h2>
   <form class="bvs-pricef" method="get" action="<?php echo esc_url($bvsv_base); ?>" data-min="<?php echo (int) $bvsv_bounds['min']; ?>" data-max="<?php echo (int) $bvsv_bounds['max']; ?>" data-step="<?php echo (int) $bvsv_bounds['step']; ?>">
    <?php if ('' !== $bvsv_p['sort']) : ?><input type="hidden" name="sap_xep" value="<?php echo esc_attr($bvsv_p['sort']); ?>"><?php endif; ?>
    <?php if ($bvsv_searched) : ?><input type="hidden" name="tu_khoa" value="<?php echo esc_attr($bvsv_p['q']); ?>"><?php endif; ?>
    <div class="bvs-range" aria-hidden="false"></div>
    <div class="bvs-price__fields">
     <label><span>Giá thấp nhất (đ)</span><input type="number" name="gia_tu" inputmode="numeric" min="<?php echo (int) $bvsv_bounds['min']; ?>" max="<?php echo (int) $bvsv_bounds['max']; ?>" step="1000" placeholder="<?php echo (int) $bvsv_bounds['min']; ?>" value="<?php echo $bvsv_p['min'] > 0 ? (int) $bvsv_p['min'] : ''; ?>"></label>
     <label><span>Giá cao nhất (đ)</span><input type="number" name="gia_den" inputmode="numeric" min="<?php echo (int) $bvsv_bounds['min']; ?>" max="<?php echo (int) $bvsv_bounds['max']; ?>" step="1000" placeholder="<?php echo (int) $bvsv_bounds['max']; ?>" value="<?php echo $bvsv_p['max'] > 0 ? (int) $bvsv_p['max'] : ''; ?>"></label>
    </div>
    <button class="bvs-btn bvs-btn--solid bvs-price__submit" type="submit">Lọc giá</button>
    <?php if ($bvsv_filtered) : ?><a class="bvs-price__reset" href="<?php
        $bvsv_reset = $bvsv_p;
        $bvsv_reset_args = array();
        if ('' !== $bvsv_reset['sort']) $bvsv_reset_args['sap_xep'] = $bvsv_reset['sort'];
        if ('' !== $bvsv_reset['q'])    $bvsv_reset_args['tu_khoa'] = $bvsv_reset['q'];
        echo esc_url($bvsv_reset_args ? add_query_arg($bvsv_reset_args, $bvsv_base) : $bvsv_base);
    ?>">Xóa lọc giá</a><?php endif; ?>
   </form>
  </section>
  <?php endif; ?>

  <?php if ($bvsv_feat) : ?>
  <section class="bvs-box bvs-box--feat" aria-labelledby="bvsv-feat-title">
   <h2 id="bvsv-feat-title" class="bvs-box__title">Dịch vụ nổi bật</h2>
   <ul class="bvs-feat">
    <?php foreach ($bvsv_feat as $bvsv_fid) : ?>
    <li class="bvs-feat__item">
     <a class="bvs-feat__img" href="<?php echo esc_url(get_permalink($bvsv_fid)); ?>" tabindex="-1" aria-hidden="true"><?php echo bvsv_image($bvsv_fid, 'thumbnail'); ?></a>
     <div class="bvs-feat__info"><a class="bvs-feat__name" href="<?php echo esc_url(get_permalink($bvsv_fid)); ?>"><?php echo esc_html(get_the_title($bvsv_fid)); ?></a><div class="bvs-price bvs-price--sm"><?php echo bvsv_price_html($bvsv_fid); ?></div></div>
    </li>
    <?php endforeach; ?>
   </ul>
  </section>
  <?php endif; ?>

 </aside>

 <main class="bvs-main" id="bvs-main">
  <nav class="bvs-crumbs" aria-label="Điều hướng"><a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a><span aria-hidden="true">›</span><?php if ($bvsv_term) : ?><a href="<?php echo esc_url($bvsv_all_url); ?>">Dịch vụ</a><span aria-hidden="true">›</span><span aria-current="page"><?php echo esc_html($bvsv_term->name); ?></span><?php else : ?><span aria-current="page">Dịch vụ</span><?php endif; ?></nav>

  <div class="bvs-toolbar bvsv-toolbar">
   <div class="bvs-toolbar__title">
    <h2 class="bvs-heading"><?php echo esc_html($bvsv_heading); ?></h2>
    <p class="bvs-count" role="status"><?php echo esc_html(bvsv_range_text()); ?><?php if ($bvsv_searched) : ?> · từ khóa “<?php echo esc_html($bvsv_p['q']); ?>”<?php endif; ?></p>
   </div>
   <form class="bvs-sort bvsv-tools" method="get" action="<?php echo esc_url($bvsv_base); ?>" role="search" aria-label="Tìm kiếm và sắp xếp dịch vụ">
    <?php if ($bvsv_p['min'] > 0) : ?><input type="hidden" name="gia_tu" value="<?php echo (int) $bvsv_p['min']; ?>"><?php endif; ?>
    <?php if ($bvsv_p['max'] > 0) : ?><input type="hidden" name="gia_den" value="<?php echo (int) $bvsv_p['max']; ?>"><?php endif; ?>
    <div class="bvsv-search">
     <label class="screen-reader-text" for="bvsv-search-input">Tìm dịch vụ</label>
     <input id="bvsv-search-input" class="bvsv-search__input" type="search" name="tu_khoa" placeholder="Tìm dịch vụ..." value="<?php echo esc_attr($bvsv_p['q']); ?>" maxlength="80" autocomplete="off">
     <button class="bvs-btn bvs-btn--solid bvsv-search__btn" type="submit">Tìm kiếm</button>
    </div>
    <div class="bvsv-sortbox">
     <label for="bvs-sort-select">Sắp xếp theo</label>
     <select id="bvs-sort-select" name="sap_xep"><?php foreach (bvsv_sort_options() as $bvsv_k => $bvsv_label) : ?><option value="<?php echo esc_attr($bvsv_k); ?>"<?php selected($bvsv_p['sort'], $bvsv_k); ?>><?php echo esc_html($bvsv_label); ?></option><?php endforeach; ?></select>
     <button class="bvs-btn bvs-btn--ghost bvs-sort__submit" type="submit">Áp dụng</button>
    </div>
   </form>
  </div>

  <?php if (have_posts()) : ?>
   <div class="bvs-grid"><?php while (have_posts()) : the_post(); get_template_part('template-parts/service-shop-card'); endwhile; ?></div>
   <?php bvsv_pagination(); ?>
  <?php else : ?>
   <div class="bvs-empty">
    <h3>Chưa có dịch vụ phù hợp</h3>
    <p><?php
      if ($bvsv_searched) echo 'Không tìm thấy dịch vụ nào khớp với từ khóa “' . esc_html($bvsv_p['q']) . '”. Hãy thử từ khóa khác hoặc xem tất cả dịch vụ.';
      elseif ($bvsv_filtered) echo 'Không có dịch vụ nào trong khoảng giá này. Hãy thử mở rộng khoảng giá.';
      else echo 'Nhóm này chưa có dịch vụ. Quản trị viên thêm dịch vụ trong WordPress Admin → Dịch vụ Spa.';
    ?></p>
    <a class="bvs-btn bvs-btn--solid" href="<?php echo esc_url($bvsv_all_url); ?>">Xem tất cả dịch vụ</a>
   </div>
  <?php endif; ?>
 </main>

</div>
</div>
