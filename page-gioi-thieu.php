<?php
/**
 * Template Name: Giới thiệu (About Us)
 *
 * Tự động áp dụng cho trang có đường dẫn /gioi-thieu/ (page-{slug}.php).
 * Template này tự dựng 8 section nên KHÔNG gọi the_content():
 * nội dung cũ lưu trong database và các bộ lọc the_content sẽ không còn chen vào trang nữa.
 *
 * CSS: assets/css/about-page.css | JS (fade-up): assets/js/about-page.js
 */
get_header();

/* ---------- Dữ liệu dễ chỉnh ---------- */
$u = function ($file) { return esc_url(beauty_theme_image_url($file)); };

/* Ảnh Unsplash (tone be/nâu ấm). Nếu tải lỗi sẽ tự dùng ảnh dự phòng nằm trong theme. */
$img_face  = $u('spa-banner-selected.jpg');
$img_space = 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1100&h=1100&q=80';
$img_towel = 'https://images.unsplash.com/photo-1600334129128-685c5582fd35?auto=format&fit=crop&w=1200&h=900&q=80';

/* Số liệu: cập nhật theo số liệu thật của cơ sở trước khi công bố. */
$stats = array(
    array('5+',  'Năm kinh nghiệm'),
    array('10K+', 'Khách hàng hài lòng'),
    array('20+', 'Dịch vụ chuyên sâu'),
);

/* Icon SVG nét mảnh (màu theo currentColor) */
$icons = array(
    'leaf'    => '<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M38 10C20 9 10 18 10 30c0 3 .8 5.6 2.2 7.8C14 40 17 41 20 41c13 0 18-14 18-31Z"/><path d="M12 38c6-9 13-15 22-20"/></svg>',
    'shield'  => '<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M24 6 9 12v11c0 9.5 6.2 16.2 15 19 8.8-2.8 15-9.5 15-19V12L24 6Z"/><path d="m17.5 24 5 5 8-9"/></svg>',
    'user'    => '<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="24" cy="17" r="7.5"/><path d="M9 41c1.2-8 7-13 15-13s13.8 5 15 13"/></svg>',
    'sparkle' => '<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 8c1.4 8 4 10.6 12 12-8 1.4-10.6 4-12 12-1.4-8-4-10.6-12-12 8-1.4 10.6-4 12-12Z"/><path d="M36 28c.7 4 2 5.3 6 6-4 .7-5.3 2-6 6-.7-4-2-5.3-6-6 4-.7 5.3-2 6-6Z"/></svg>',
);
$leaf_small = '<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M38 10C20 9 10 18 10 30c0 3 .8 5.6 2.2 7.8C14 40 17 41 20 41c13 0 18-14 18-31Z"/><path d="M12 38c6-9 13-15 22-20"/></svg>';

/* Họa tiết cành lá mờ cho section Quote */
$branch = '<svg viewBox="0 0 200 320" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M100 312C96 240 104 160 92 8"/><path d="M98 270c-26-6-44-24-50-50 26 4 44 22 50 50Z"/><path d="M99 226c24-8 40-26 44-52-24 6-40 24-44 52Z"/><path d="M97 184c-24-8-40-26-44-52 24 6 40 24 44 52Z"/><path d="M96 142c22-8 38-24 42-48-22 6-38 22-42 48Z"/><path d="M94 100c-20-8-34-22-38-44 20 6 34 20 38 44Z"/><path d="M93 62c16-6 28-18 32-36-16 5-28 16-32 36Z"/></svg>';

/* Thuộc tính ảnh: ưu tiên ảnh Unsplash, lỗi thì dùng ảnh dự phòng trong theme */
$onerr = 'onerror="this.onerror=null;this.src=this.dataset.fb"';
?>
<main class="ba" id="ba-top">
<script>document.documentElement.className+=' ba-js';</script>

<!-- =========================================================
     【2】HERO — banner mềm mại, 2 cột (chữ trái / ảnh phải)
     (Section 【1】HEADER là header.php của theme: logo, menu, tìm kiếm, giỏ hàng, nút Đặt lịch)
========================================================== -->
<section class="ba-hero">
  <div class="ba-container ba-hero__grid">
    <div class="ba-hero__copy ba-fade">
      <span class="ba-label">VỀ BEAUTY SPA</span>
      <h1 class="ba-hero__title">Hành trình kiến tạo vẻ đẹp tự nhiên</h1>
      <p class="ba-hero__text">Beauty Spa là nơi bạn có thể tìm thấy sự thư giãn, chăm sóc và lấy lại năng lượng tích cực. Chúng tôi tin rằng, mỗi người phụ nữ đều xứng đáng được yêu thương và tỏa sáng theo cách riêng của mình.</p>
      <a class="ba-btn ba-btn--solid ba-btn--pill" href="<?php echo esc_url(home_url('/dich-vu/')); ?>">Khám phá ngay</a>
      <p class="ba-script ba-hero__script" aria-hidden="true">Natural beauty for a better you</p>
    </div>

    <div class="ba-hero__media ba-fade" style="--d:.15s">
      <div class="ba-zoom ba-hero__photo">
        <img src="<?php echo esc_url($img_face); ?>" data-fb="<?php echo $u('spa-album-facial.jpg'); ?>" <?php echo $onerr; ?> alt="Liệu trình chăm sóc da mặt thư giãn tại Beauty Spa" width="1000" height="1250" loading="eager" decoding="async" referrerpolicy="no-referrer">
      </div>
      <div class="ba-badge" aria-hidden="true">
        <span class="ba-badge__icon"><?php echo $leaf_small; ?></span>
        <span class="ba-badge__text">VẺ ĐẸP<br>TỰ NHIÊN</span>
      </div>
    </div>
  </div>
</section>

<!-- =========================================================
     【3】CÂU CHUYỆN / SỨ MỆNH — nền trắng, ảnh trái / chữ phải
========================================================== -->
<section class="ba-story">
  <div class="ba-container ba-story__grid">
    <div class="ba-story__media ba-fade">
      <div class="ba-zoom ba-story__photo">
        <img src="<?php echo esc_url($img_space); ?>" data-fb="<?php echo $u('spa-album-room.jpg'); ?>" <?php echo $onerr; ?> alt="Không gian thư giãn tại Beauty Spa" width="1100" height="1100" loading="lazy" decoding="async" referrerpolicy="no-referrer">
      </div>
      <span class="ba-script ba-story__script" aria-hidden="true">Không gian thư giãn</span>
    </div>

    <div class="ba-story__copy ba-fade" style="--d:.15s">
      <span class="ba-label">SỨ MỆNH CỦA BEAUTY SPA</span>
      <h2 class="ba-h2">Chăm sóc vẻ đẹp, nuôi dưỡng sự tự tin</h2>
      <p>Chúng tôi không chỉ mang đến những dịch vụ làm đẹp chất lượng, mà còn lan tỏa lối sống lành mạnh, tích cực. Beauty Spa mong muốn trở thành người bạn đồng hành đáng tin cậy trên hành trình chăm sóc và tôn vinh vẻ đẹp của bạn.</p>
      <p>Mỗi buổi chăm sóc đều bắt đầu bằng việc lắng nghe, để bạn hiểu rõ quy trình, thời lượng, chi phí và chủ động lựa chọn trải nghiệm phù hợp với mình.</p>
      <a class="ba-btn ba-btn--outline ba-btn--pill" href="#ba-journey">Tìm hiểu thêm <span aria-hidden="true">→</span></a>
    </div>
  </div>
</section>

<!-- =========================================================
     【4】GIÁ TRỊ CỐT LÕI — nền be nhạt, cột trái 30% / lưới 4 icon 70%
========================================================== -->
<section class="ba-values">
  <div class="ba-container ba-values__grid">
    <div class="ba-values__intro ba-fade">
      <span class="ba-label">GIÁ TRỊ CỐT LÕI</span>
      <h2 class="ba-h2">Điều tạo nên sự khác biệt</h2>
      <p>Tại Beauty Spa, chúng tôi luôn đặt trải nghiệm của bạn lên hàng đầu.</p>
    </div>

    <ul class="ba-values__list">
      <?php
      $values = array(
          array('leaf',    'Tự nhiên',     'Ưu tiên sản phẩm thiên nhiên, lành tính.'),
          array('shield',  'An toàn',      'Quy trình rõ ràng, vệ sinh và an toàn.'),
          array('user',    'Chuyên nghiệp', 'Đội ngũ tận tâm, được hướng dẫn bài bản.'),
          array('sparkle', 'Hiệu quả',     'Cá nhân hóa liệu trình phù hợp từng nhu cầu.'),
      );
      foreach ($values as $i => $v) : ?>
      <li class="ba-value ba-fade" style="--d:<?php echo esc_attr(0.1 * ($i + 1)); ?>s">
        <span class="ba-value__icon"><?php echo $icons[$v[0]]; ?></span>
        <h3 class="ba-value__title"><?php echo esc_html($v[1]); ?></h3>
        <p><?php echo esc_html($v[2]); ?></p>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- =========================================================
     【5】QUY TRÌNH DỊCH VỤ — 4 bước nằm ngang
========================================================== -->
<section class="ba-process">
  <div class="ba-container">
    <div class="ba-process__head ba-fade">
      <span class="ba-label">TRẢI NGHIỆM TẠI BEAUTY</span>
      <h2 class="ba-h2">Quy trình phục vụ</h2>
    </div>
    <ol class="ba-steps">
      <?php
      $steps = array(
          array('Lắng nghe nhu cầu',     'Tiếp nhận mong muốn và những lưu ý riêng mà bạn muốn chia sẻ.'),
          array('Tư vấn rõ ràng',        'Giới thiệu lựa chọn, thời lượng, chi phí và lưu ý trước khi thực hiện.'),
          array('Chăm sóc chu đáo',      'Chuẩn bị không gian, dụng cụ và thực hiện từng bước nhẹ nhàng, thoải mái.'),
          array('Đồng hành sau dịch vụ', 'Hướng dẫn chăm sóc tại nhà và tiếp nhận phản hồi của bạn.'),
      );
      foreach ($steps as $i => $s) : ?>
      <li class="ba-step ba-fade" style="--d:<?php echo esc_attr(0.12 * $i); ?>s">
        <span class="ba-step__num"><?php echo esc_html(sprintf('%02d', $i + 1)); ?></span>
        <h3 class="ba-step__title"><?php echo esc_html($s[0]); ?></h3>
        <p><?php echo esc_html($s[1]); ?></p>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<!-- =========================================================
     【6】HÀNH TRÌNH — nền trắng, ảnh trái / chữ phải + 3 ô số liệu
========================================================== -->
<section class="ba-journey" id="ba-journey">
  <div class="ba-container ba-journey__grid">
    <div class="ba-journey__media ba-fade">
      <div class="ba-zoom ba-journey__photo">
        <img src="<?php echo esc_url($img_towel); ?>" data-fb="<?php echo $u('spa-album-products.jpg'); ?>" <?php echo $onerr; ?> alt="Khăn tắm và tinh dầu thiên nhiên tại Beauty Spa" width="1200" height="900" loading="lazy" decoding="async" referrerpolicy="no-referrer">
      </div>
    </div>

    <div class="ba-journey__copy ba-fade" style="--d:.15s">
      <span class="ba-label">HÀNH TRÌNH CỦA CHÚNG TÔI</span>
      <h2 class="ba-h2">Từ thiên nhiên đến làn da rạng rỡ</h2>
      <p>Mỗi liệu trình tại Beauty Spa là sự kết hợp hài hòa giữa thiên nhiên, khoa học và sự tận tâm. Chúng tôi luôn nỗ lực để mang đến cho bạn trải nghiệm thư giãn trọn vẹn và làn da khỏe đẹp dài lâu.</p>

      <ul class="ba-stats">
        <?php foreach ($stats as $st) : ?>
        <li class="ba-stat">
          <strong class="ba-stat__num"><?php echo esc_html($st[0]); ?></strong>
          <span class="ba-stat__label"><?php echo esc_html($st[1]); ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<!-- =========================================================
     【7】CAM KẾT & KHÔNG GIAN — gallery 3 ảnh, bo góc 12px, hover zoom
========================================================== -->
<section class="ba-gallery">
  <div class="ba-container">
    <div class="ba-gallery__head ba-fade">
      <span class="ba-label">CAM KẾT &amp; KHÔNG GIAN</span>
      <h2 class="ba-h2">Một không gian để nghỉ ngơi</h2>
      <p>Không gian ấm áp với sắc kem, nâu và ánh vàng dịu, cùng cam kết minh bạch về dịch vụ, chi phí và quyền riêng tư của bạn.</p>
    </div>
    <div class="ba-gallery__grid">
      <?php
      $gallery = array(
          array('spa-album-room.jpg',     'Không gian phòng chờ',  'Khu đón tiếp thư thái, nhẹ nhàng'),
          array('spa-album-massage.jpg',  'Phòng trị liệu',        'Yên tĩnh, riêng tư và sạch sẽ'),
          array('spa-album-products.jpg', 'Sản phẩm thiên nhiên',  'Lành tính, rõ nguồn gốc thành phần'),
      );
      foreach ($gallery as $i => $g) : ?>
      <figure class="ba-shot ba-fade" style="--d:<?php echo esc_attr(0.12 * $i); ?>s">
        <div class="ba-zoom ba-shot__photo">
          <img src="<?php echo $u($g[0]); ?>" alt="<?php echo esc_attr($g[1]); ?> tại Beauty Spa" width="800" height="1000" loading="lazy" decoding="async">
        </div>
        <figcaption><strong><?php echo esc_html($g[1]); ?></strong><span><?php echo esc_html($g[2]); ?></span></figcaption>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =========================================================
     【8】TRÍCH DẪN (QUOTE) & CTA — nền hồng nhạt/be, họa tiết lá mờ 2 bên
========================================================== -->
<section class="ba-quote">
  <span class="ba-quote__leaf ba-quote__leaf--l" aria-hidden="true"><?php echo $branch; ?></span>
  <span class="ba-quote__leaf ba-quote__leaf--r" aria-hidden="true"><?php echo $branch; ?></span>

  <div class="ba-container ba-quote__inner">
    <blockquote class="ba-quote__text ba-script ba-fade">
      “Vẻ đẹp thật sự không chỉ đến từ ngoại hình, mà còn từ sự an yên trong tâm hồn.”
    </blockquote>
    <p class="ba-quote__by ba-fade">BEAUTY SPA</p>

    <div class="ba-cta ba-fade">
      <h2 class="ba-cta__title">Sẵn sàng dành thời gian cho bản thân?</h2>
      <a class="ba-btn ba-btn--solid ba-btn--pill" href="<?php echo esc_url(home_url('/dat-lich/')); ?>">Đặt lịch ngay</a>
    </div>
  </div>
</section>
</main>
<?php get_footer(); ?>
