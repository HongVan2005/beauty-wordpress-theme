<?php get_header(); ?>
<main class="content-area container">
 <?php if (have_posts()): ?><div class="cards"><?php while(have_posts()):the_post(); ?>
 <article <?php post_class('card'); ?>><a href="<?php the_permalink(); ?>"><div class="card-image"><?php if(has_post_thumbnail()) the_post_thumbnail('large'); ?></div></a><div class="card-body"><div class="meta"><?php echo esc_html(get_the_date()); ?></div><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p><?php beaute_spa_excerpt(24); ?></p><a class="btn btn-outline" href="<?php the_permalink(); ?>">Đọc tiếp</a></div></article>
 <?php endwhile; ?></div><div class="pagination"><?php beauty_pagination(); ?></div><?php else: ?><h1>Chưa có nội dung</h1><p>Hãy thêm bài viết trong WordPress Admin.</p><?php endif; ?>
</main><?php get_footer(); ?>
