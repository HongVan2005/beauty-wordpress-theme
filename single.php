<?php get_header(); ?>
<section class="page-hero"><div class="container"><?php while(have_posts()):the_post(); ?><span class="eyebrow"><?php echo esc_html(get_the_date()); ?></span><h1 class="page-title"><?php the_title(); ?></h1></div></section>
<main class="content-area container beauty-single-layout"><article <?php post_class('entry-content beauty-single-article'); ?>><?php if(has_post_thumbnail()) : ?><figure class="beauty-featured-image"><?php the_post_thumbnail('large',array('alt'=>the_title_attribute(array('echo'=>false)))); ?></figure><?php endif; ?><div class="beauty-article-body"><?php the_content(); ?></div></article><aside class="beauty-single-sidebar"><div class="beauty-sidebar-card"><span class="eyebrow">BEAUTY SPA</span><h2>Chăm sóc bản thân mỗi ngày</h2><p>Tìm dịch vụ phù hợp hoặc đặt lịch để được tư vấn.</p><a class="btn" href="<?php echo esc_url(home_url('/dat-lich/')); ?>">Đặt lịch ngay</a><hr><a href="<?php echo esc_url(home_url('/tin-tuc/')); ?>">Đọc thêm kiến thức làm đẹp →</a></div>
<div class="beauty-latest-posts">
    <span class="beauty-latest-posts__eyebrow">BEAUTY JOURNAL</span>
    <h3>Bài viết mới nhất</h3>
    <p class="beauty-latest-posts__intro">Khám phá những chia sẻ và kiến thức làm đẹp từ Beauty Spa.</p>

    <div class="beauty-latest-posts__list">
        <?php
        $beauty_latest_query = new WP_Query(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 3,
            'post__not_in' => array(get_the_ID()),
            'ignore_sticky_posts' => true
        ));

        if ($beauty_latest_query->have_posts()) :
            while ($beauty_latest_query->have_posts()) :
                $beauty_latest_query->the_post();
        ?>
            <article class="beauty-latest-post">
                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                    <?php echo esc_html(get_the_date('d/m/Y')); ?>
                </time>
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 12)); ?></p>
            </article>
        <?php
            endwhile;
        else :
        ?>
            <p class="beauty-latest-posts__empty">Chưa có bài viết mới. Hãy quay lại khám phá sau nhé!</p>
        <?php
        endif;
        wp_reset_postdata();
        ?>
    </div>

    <a class="beauty-latest-posts__link" href="<?php echo esc_url(home_url('/tin-tuc/')); ?>">
        Xem tất cả bài viết <span>→</span>
    </a>
</div>
</aside><?php endwhile; ?></main><?php get_footer(); ?>
