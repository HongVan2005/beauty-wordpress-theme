<?php get_header(); ?>
<section class="page-hero"><div class="container"><span class="eyebrow">Tìm kiếm</span><h1 class="page-title"><?php echo esc_html(sprintf('Kết quả cho “%s”', get_search_query())); ?></h1></div></section>
<main class="content-area container">
<?php if (have_posts()) : ?><div class="cards"><?php while (have_posts()) : the_post(); ?>
 <article <?php post_class('card'); ?>><a href="<?php the_permalink(); ?>"><div class="card-image"><?php if (has_post_thumbnail()) { the_post_thumbnail('large'); } else { ?><img src="<?php echo esc_url(beauty_theme_image_url('spa-album-room.jpg')); ?>" alt="" width="504" height="504" loading="lazy" decoding="async"><?php } ?></div></a><div class="card-body"><div class="meta"><?php echo esc_html(get_post_type_object(get_post_type())->labels->singular_name); ?></div><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p><?php beaute_spa_excerpt(24); ?></p><a class="btn btn-outline" href="<?php the_permalink(); ?>">Xem chi tiết</a></div></article>
<?php endwhile; ?></div><?php beauty_pagination(); else : ?><p>Không tìm thấy kết quả phù hợp. Hãy thử từ khóa khác như “massage”, “chăm sóc da” hoặc “serum”.</p><?php endif; ?>
</main><?php get_footer(); ?>
