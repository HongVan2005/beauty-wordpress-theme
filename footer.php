<?php
/* [FIX 2] FOOTER - nền tối, lưới 4 cột, thanh bản quyền riêng */
$beauty_f_phone    = beauty_contact_info('phone');
$beauty_f_tel      = preg_replace('/[^0-9+]/', '', $beauty_f_phone);
$beauty_f_email    = beauty_contact_info('email');
$beauty_f_zalo     = beauty_contact_info('zalo');
if (!$beauty_f_zalo && $beauty_f_tel) $beauty_f_zalo = 'https://zalo.me/' . ltrim($beauty_f_tel, '+');
$beauty_f_fb       = beauty_contact_info('messenger'); // chỉ hiện icon khi đã nhập link thật trong Tùy biến
$beauty_f_map      = beauty_map_url();
$beauty_f_services = get_terms(array('taxonomy' => 'spa_service_cat', 'hide_empty' => false, 'number' => 6));
?>
<footer class="site-footer bvf">
 <div class="container">
  <div class="bvf-grid">

   <div class="bvf-col bvf-about">
    <a class="bvf-brand" href="<?php echo esc_url(home_url('/')); ?>">BEAUTY<small>Spa • Skincare • Wellness</small></a>
    <p>Không gian thư giãn và chăm sóc vẻ đẹp của bạn. Website giới thiệu dịch vụ chăm sóc da, thư giãn và làm đẹp.</p>
    <div class="bvf-social" aria-label="Mạng xã hội">
     <?php if ($beauty_f_fb) : ?><a href="<?php echo esc_url($beauty_f_fb); ?>" target="_blank" rel="noopener" aria-label="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14 8h2.5V4.5H14A3.5 3.5 0 0 0 10.5 8v2H8v3.5h2.5V21H14v-7.5h2.4l.6-3.5H14V8.6c0-.4.2-.6.6-.6z"/></svg></a><?php endif; ?>
     <?php if ($beauty_f_zalo) : ?><a href="<?php echo esc_url($beauty_f_zalo); ?>" target="_blank" rel="noopener" aria-label="Zalo"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 5h16v11H9l-5 4z"/><path d="M8 9h8M8 12h5"/></svg></a><?php endif; ?>
     <?php if ($beauty_f_tel) : ?><a href="tel:<?php echo esc_attr($beauty_f_tel); ?>" aria-label="Gọi hotline"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg></a><?php endif; ?>
     <?php if ($beauty_f_map) : ?><a href="<?php echo esc_url($beauty_f_map); ?>" target="_blank" rel="noopener" aria-label="Google Map"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 21s7-6 7-11a7 7 0 0 0-14 0c0 5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></a><?php endif; ?>
    </div>
   </div>

   <div class="bvf-col">
    <h4>Liên kết nhanh</h4>
    <ul class="bvf-links">
     <li><a href="<?php echo esc_url(get_post_type_archive_link('spa_service')); ?>">Dịch vụ</a></li>
     <li><a href="<?php echo esc_url(home_url('/bang-gia/')); ?>">Bảng giá</a></li>
     <li><a href="<?php echo esc_url(beauty_shop_url()); ?>">Sản phẩm</a></li>
     <li><a href="<?php echo esc_url(home_url('/hinh-anh/')); ?>">Hình ảnh</a></li>
     <li><a href="<?php echo esc_url(home_url('/tin-tuc/')); ?>">Tin tức</a></li>
     <li><a href="<?php echo esc_url(home_url('/dat-lich/')); ?>">Đặt lịch</a></li>
     <li><a href="<?php echo esc_url(home_url('/lien-he/')); ?>">Liên hệ</a></li>
    </ul>
   </div>

   <div class="bvf-col">
    <h4>Dịch vụ</h4>
    <ul class="bvf-links">
     <li><a href="<?php echo esc_url(get_post_type_archive_link('spa_service')); ?>">Tất cả dịch vụ</a></li>
     <?php if (!is_wp_error($beauty_f_services) && $beauty_f_services) : foreach ($beauty_f_services as $beauty_f_term) :
      $beauty_f_link = get_term_link($beauty_f_term);
      if (is_wp_error($beauty_f_link)) continue; ?>
      <li><a href="<?php echo esc_url($beauty_f_link); ?>"><?php echo esc_html($beauty_f_term->name); ?></a></li>
     <?php endforeach; endif; ?>
    </ul>
   </div>

   <div class="bvf-col">
    <h4>Liên hệ</h4>
    <ul class="bvf-contact">
     <li><span>Hotline</span><?php if ($beauty_f_tel) : ?><a href="tel:<?php echo esc_attr($beauty_f_tel); ?>"><?php echo esc_html($beauty_f_phone); ?></a><?php else : echo esc_html($beauty_f_phone); endif; ?></li>
     <li><span>Email</span><?php if ($beauty_f_email) : ?><a href="mailto:<?php echo esc_attr($beauty_f_email); ?>"><?php echo esc_html($beauty_f_email); ?></a><?php endif; ?></li>
     <li><span>Địa chỉ</span><?php echo esc_html(beauty_contact_info('address')); ?></li>
     <li><span>Giờ mở cửa</span><?php echo esc_html(beauty_contact_info('hours')); ?></li>
    </ul>
   </div>

  </div>
  <div class="bvf-bottom">© <?php echo esc_html(wp_date('Y')); ?> <?php bloginfo('name'); ?>. Nội dung thuộc về chủ sở hữu website.</div>
 </div>
</footer>
<?php
$beauty_phone = preg_replace('/[^0-9+]/', '', beauty_contact_info('phone'));
$beauty_zalo = beauty_contact_info('zalo');
if (!$beauty_zalo && $beauty_phone) $beauty_zalo = 'https://zalo.me/' . ltrim($beauty_phone, '+');
$beauty_facebook = beauty_contact_info('messenger');
if (!$beauty_facebook) $beauty_facebook = 'https://www.facebook.com/';
$beauty_map = beauty_map_url();
?>
<nav class="bv-dock" aria-label="Liên hệ nhanh">
 <?php if ($beauty_phone) : ?><a class="bv-dock-call" href="tel:<?php echo esc_attr($beauty_phone); ?>"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg><span>Hotline</span></a><?php endif; ?>
 <a class="bv-dock-book" href="<?php echo esc_url(home_url('/dat-lich/')); ?>"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/></svg><span>Đặt lịch</span></a>
 <?php if ($beauty_zalo) : ?><a class="bv-dock-zalo" href="<?php echo esc_url($beauty_zalo); ?>" target="_blank" rel="noopener"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 5h16v11H9l-5 4z"/><path d="M8 9h8M8 12h5"/></svg><span>Zalo</span></a><?php endif; ?>
 <?php if ($beauty_facebook) : ?><a class="bv-dock-fb" href="<?php echo esc_url($beauty_facebook); ?>" target="_blank" rel="noopener"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14 8h2.5V4.5H14A3.5 3.5 0 0 0 10.5 8v2H8v3.5h2.5V21H14v-7.5h2.4l.6-3.5H14V8.6c0-.4.2-.6.6-.6z"/></svg><span>Facebook</span></a><?php endif; ?>
 <?php if ($beauty_map) : ?><a class="bv-dock-map" href="<?php echo esc_url($beauty_map); ?>" target="_blank" rel="noopener"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 21s7-6 7-11a7 7 0 0 0-14 0c0 5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg><span>Google Map</span></a><?php endif; ?>
</nav>
<?php wp_footer(); ?></body></html>
