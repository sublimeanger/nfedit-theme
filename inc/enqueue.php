<?php
/**
 * Stylesheet + script enqueue.
 * Self-host Google Fonts later via VeloPress font workflow if needed.
 */

defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', function () {
    // Google Fonts (Fraunces variable + Inter)
    wp_enqueue_style(
        'nfedit-fonts',
        'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght,SOFT,WONK@0,9..144,400..700,0..100,0..1;1,9..144,400..700,0..100,0..1&family=Inter:wght@400;500;600&display=swap',
        [],
        null
    );

    // Master stylesheet (cache-busted by filemtime)
    $main_css = NFEDIT_DIR . '/assets/css/main.css';
    wp_enqueue_style(
        'nfedit-main',
        NFEDIT_URI . '/assets/css/main.css',
        ['nfedit-fonts'],
        file_exists($main_css) ? filemtime($main_css) : NFEDIT_VERSION
    );

    // Main JS bundle
    $main_js = NFEDIT_DIR . '/assets/js/main.js';
    wp_enqueue_script(
        'nfedit-main',
        NFEDIT_URI . '/assets/js/main.js',
        [],
        file_exists($main_js) ? filemtime($main_js) : NFEDIT_VERSION,
        true
    );

    // Header sticky behaviour
    $header_js = NFEDIT_DIR . '/assets/js/header.js';
    wp_enqueue_script(
        'nfedit-header',
        NFEDIT_URI . '/assets/js/header.js',
        ['nfedit-main'],
        file_exists($header_js) ? filemtime($header_js) : NFEDIT_VERSION,
        true
    );


    // Property-page-only scripts
    if (is_singular("property")) {
        $pp_js = NFEDIT_DIR . "/assets/js/property-page.js";
        wp_enqueue_script(
            "nfedit-property-page",
            NFEDIT_URI . "/assets/js/property-page.js",
            ["nfedit-main"],
            file_exists($pp_js) ? filemtime($pp_js) : NFEDIT_VERSION,
            true
        );
        $gl_js = NFEDIT_DIR . "/assets/js/gallery-lightbox.js";
        wp_enqueue_script(
            "nfedit-gallery-lightbox",
            NFEDIT_URI . "/assets/js/gallery-lightbox.js",
            ["nfedit-main"],
            file_exists($gl_js) ? filemtime($gl_js) : NFEDIT_VERSION,
            true
        );
    }

    // Homepage-only Oracle JS
    if (is_front_page()) {
        $or_js = NFEDIT_DIR . "/assets/js/oracle.js";
        wp_enqueue_script(
            "nfedit-oracle",
            NFEDIT_URI . "/assets/js/oracle.js",
            ["nfedit-main"],
            file_exists($or_js) ? filemtime($or_js) : NFEDIT_VERSION,
            true
        );
    }


    // Cottages-archive-only filter JS (also handles collections tabs)
    if (is_post_type_archive("property") || is_tax("area_taxonomy") || is_tax("feature") || is_post_type_archive("collection")) {
        $cf_js = NFEDIT_DIR . "/assets/js/cottages-filter.js";
        wp_enqueue_script(
            "nfedit-cottages-filter",
            NFEDIT_URI . "/assets/js/cottages-filter.js",
            ["nfedit-main"],
            file_exists($cf_js) ? filemtime($cf_js) : NFEDIT_VERSION,
            true
        );
    }


    // The Edit index page template — cluster filter JS
    if (is_page_template("page-templates/page-the-edit-index.php")) {
        $te_js = NFEDIT_DIR . "/assets/js/the-edit-filter.js";
        wp_enqueue_script(
            "nfedit-the-edit-filter",
            NFEDIT_URI . "/assets/js/the-edit-filter.js",
            ["nfedit-main"],
            file_exists($te_js) ? filemtime($te_js) : NFEDIT_VERSION,
            true
        );
    }

});

// Preconnect for Google Fonts
add_action('wp_head', function () {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1);

// Phase 9: Guides index filter (post type archive only)
add_action('wp_enqueue_scripts', function () {
    if (is_post_type_archive('guide')) {
        wp_enqueue_script(
            'nfedit-guides-filter',
            get_stylesheet_directory_uri() . '/assets/js/guides-filter.js',
            ['nfedit-main'],
            filemtime(get_stylesheet_directory() . '/assets/js/guides-filter.js'),
            true
        );
    }
}, 30);

// Phase 10A: Contact form JS on /contact/ only
add_action('wp_enqueue_scripts', function () {
    if (is_page_template('page-templates/page-contact.php')) {
        $f = get_stylesheet_directory() . '/assets/js/contact-form.js';
        wp_enqueue_script(
            'nfedit-contact-form',
            get_stylesheet_directory_uri() . '/assets/js/contact-form.js',
            ['nfedit-main'],
            file_exists($f) ? filemtime($f) : null,
            true
        );
    }
}, 30);

// Phase 10A: Oracle form JS on /oracle/ only
add_action('wp_enqueue_scripts', function () {
    if (is_page_template('page-templates/page-oracle.php')) {
        $f = get_stylesheet_directory() . '/assets/js/oracle-form.js';
        wp_enqueue_script(
            'nfedit-oracle-form',
            get_stylesheet_directory_uri() . '/assets/js/oracle-form.js',
            ['nfedit-main'],
            file_exists($f) ? filemtime($f) : null,
            true
        );
    }
}, 30);

// Phase 10B: Legal TOC scroll-spy on legal pages only
add_action('wp_enqueue_scripts', function () {
    if (is_page_template('page-templates/page-legal.php')) {
        $f = get_stylesheet_directory() . '/assets/js/legal-toc.js';
        wp_enqueue_script(
            'nfedit-legal-toc',
            get_stylesheet_directory_uri() . '/assets/js/legal-toc.js',
            ['nfedit-main'],
            file_exists($f) ? filemtime($f) : null,
            true
        );
    }
}, 30);

// ─── Branding: SVG favicon + default OG image ─────────────────
add_action('wp_head', 'nfedit_branding_head', 2);
function nfedit_branding_head() {
    $img_uri = get_stylesheet_directory_uri() . '/assets/img';
    // SVG favicon (preferred by modern browsers — crisp at any size)
    echo '<link rel="icon" type="image/svg+xml" href="' . esc_url($img_uri . '/favicon.svg') . '">' . "\n";
    // PNG fallback (older browsers + WP Site Icon also injects its own variants)
    echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url($img_uri . '/favicon-32.png') . '">' . "\n";
    echo '<link rel="apple-touch-icon" href="' . esc_url($img_uri . '/favicon-192.png') . '">' . "\n";

    // Default OG image (RankMath / SEO plugins override per-page; this is site-wide fallback)
    if (!is_singular()) {
        $soc_id = (int) get_option('nfedit_social_square_id');
        $soc_url = $soc_id ? wp_get_attachment_url($soc_id) : ($img_uri . '/social-square-1080.png');
        if ($soc_url) {
            echo '<meta property="og:image" content="' . esc_url($soc_url) . '">' . "\n";
            echo '<meta property="og:image:width" content="1080">' . "\n";
            echo '<meta property="og:image:height" content="1080">' . "\n";
            echo '<meta name="twitter:image" content="' . esc_url($soc_url) . '">' . "\n";
            echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        }
    }
}
