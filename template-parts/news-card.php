<article <?php post_class('bv-card'); ?>>
 <a class="bv-card-media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php if (has_post_thumbnail()) { the_post_thumbnail('medium_large', array('loading' => 'lazy', 'decoding' => 'async')); } else { ?><img class="is-placeholder" src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/placeholder.svg'); ?>" alt="" width="800" height="600" loading="lazy" decoding="async"><?php } ?></a>
 <div class="bv-card-body">
  <div class="bv-meta"><?php echo esc_html(get_the_date('d/m/Y')); $beauty_cats = get_the_category(); if ($beauty_cats) echo ' · ' . esc_html($beauty_cats[0]->name); ?></div>
  <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
  <p class="bv-excerpt"><?php beaute_spa_excerpt(22); ?></p>
  <a class="bv-more" href="<?php the_permalink(); ?>">Đọc tiếp →</a>
 </div>
</article>
