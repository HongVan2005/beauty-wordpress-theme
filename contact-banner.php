<?php
if (!defined('ABSPATH')) exit;

/* Chi ap dung tren trang Lien he */
add_filter('body_class', function($classes) {
    if (is_page('lien-he')) {
        $classes[] = 'beauty-contact-page';
    }
    return $classes;
});

function beauty_contact_banner_html() {
    $image = get_stylesheet_directory_uri() . '/assets/images/contact-banner.png';

    return '
    <div class="beauty-contact-image-banner">
        <img src="' . esc_url($image) . '" alt="Beauty Spa - Liên hệ">
    </div>

    <style>
    /* An tieu de trang Lien he phia tren */
    body.beauty-contact-page .page-title-area,
    body.beauty-contact-page .page-title-section,
    body.beauty-contact-page .page-title-wrapper,
    body.beauty-contact-page .page-header,
    body.beauty-contact-page .page-hero,
    body.beauty-contact-page .entry-header,
    body.beauty-contact-page .breadcrumb-area {
        display: none !important;
    }

    /* Banner tran vien man hinh */
    body.beauty-contact-page .beauty-contact-image-banner {
        position: relative;
        width: 100vw;
        max-width: none;
        margin: 0 calc(50% - 50vw) 36px;
        padding: 0;
        overflow: hidden;
        border-radius: 0;
        box-shadow: none;
        line-height: 0;
    }

    body.beauty-contact-page .beauty-contact-image-banner img {
        display: block;
        width: 100%;
        height: auto;
        aspect-ratio: 3 / 1;
        object-fit: cover;
        object-position: center;
    }

    @media(max-width: 768px) {
        body.beauty-contact-page .beauty-contact-image-banner {
            margin-bottom: 24px;
        }

        body.beauty-contact-page .beauty-contact-image-banner img {
            aspect-ratio: 4 / 3;
        }
    }
    
/* FIX KHOANG TRANG PHIA TREN BANNER - CHI TRANG LIEN HE */
body.beauty-contact-page .page-title,
body.beauty-contact-page .page-title-wrap,
body.beauty-contact-page .page-title-container,
body.beauty-contact-page .page-header-area,
body.beauty-contact-page .breadcrumb-wrapper,
body.beauty-contact-page .breadcrumbs {
    display: none !important;
    height: 0 !important;
    min-height: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
}

body.beauty-contact-page #content,
body.beauty-contact-page .site-content,
body.beauty-contact-page .site-main,
body.beauty-contact-page main {
    padding-top: 0 !important;
    margin-top: 0 !important;
}

body.beauty-contact-page .beauty-contact-image-banner {
    margin-top: 0 !important;
}
</style>';
}

function beauty_contact_banner_filter($content) {
    static $done = false;

    if ($done || is_admin() || !is_main_query() || !in_the_loop() || !is_page('lien-he')) {
        return $content;
    }

    $done = true;
    return beauty_contact_banner_html() . $content;
}

add_filter('the_content', 'beauty_contact_banner_filter', 30);