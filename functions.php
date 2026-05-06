<?php
/**
 * The New Forest Edit — theme functions
 *
 * Architecture: standalone theme. CSS architecture in assets/css/main.css
 * (imports tokens → base → utilities → components → pages).
 * No jQuery, no parent theme dependency, no page builder.
 *
 * Phase 1 = scaffold only. Phase 2 fills enqueue/header/footer.
 * Phase 3 fills inc/cpts.php + inc/taxonomies.php + inc/acf-json/.
 * Phases 4-10 fill page templates and template-parts.
 */

defined('ABSPATH') || exit;

define('NFEDIT_VERSION', '0.1.0');
define('NFEDIT_DIR', get_stylesheet_directory());
define('NFEDIT_URI', get_stylesheet_directory_uri());

// ─────────────────────────────────────────────────────────────────────
// Theme support
// ─────────────────────────────────────────────────────────────────────
function nfedit_theme_setup() {
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', ['comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');

    register_nav_menus([
        'primary'         => __('Primary', 'nfedit'),
        'footer-discover' => __('Footer — Discover', 'nfedit'),
        'footer-edit'     => __('Footer — The Edit', 'nfedit'),
        'footer-about'    => __('Footer — About', 'nfedit'),
    ]);
}
add_action('after_setup_theme', 'nfedit_theme_setup');

// ─────────────────────────────────────────────────────────────────────
// Enqueue (Phase 1 placeholder — Phase 2 replaces with full enqueue.php include)
// ─────────────────────────────────────────────────────────────────────
function nfedit_enqueue_styles() {
    wp_enqueue_style(
        'nfedit-theme',
        get_stylesheet_uri(),
        [],
        NFEDIT_VERSION
    );
}
add_action('wp_enqueue_scripts', 'nfedit_enqueue_styles');

// ─────────────────────────────────────────────────────────────────────
// inc/ includes — populated as later phases land
// ─────────────────────────────────────────────────────────────────────
$nfedit_includes = [
    "inc/enqueue.php",
    "inc/image-sizes.php",
    "inc/cpts.php",
    "inc/taxonomies.php",
    "inc/taxonomy-seeds.php",
    "inc/rewrites.php",
    "inc/seed-defaults.php",
    "inc/helpers.php",
    "inc/filters.php",
    "inc/rest-api.php",
    "inc/acf-fields-property.php",
    "inc/acf-fields-other.php",
    "inc/acf-options.php",
];
foreach ($nfedit_includes as $f) {
    $path = NFEDIT_DIR . '/' . $f;
    if (file_exists($path)) {
        require_once $path;
    }
}

// ─────────────────────────────────────────────────────────────────────
// ACF JSON sync (read/write to inc/acf-json/)
// ─────────────────────────────────────────────────────────────────────
add_filter('acf/settings/save_json', function () {
    return NFEDIT_DIR . '/inc/acf-json';
});
add_filter('acf/settings/load_json', function ($paths) {
    unset($paths[0]);
    $paths[] = NFEDIT_DIR . '/inc/acf-json';
    return $paths;
});

// ─────────────────────────────────────────────────────────────────────
// Disable Gutenberg block library CSS we don't use (perf)
// ─────────────────────────────────────────────────────────────────────
function nfedit_dequeue_block_library_css() {
    if (!is_admin()) {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('global-styles');
        wp_dequeue_style('classic-theme-styles');
    }
}
add_action('wp_enqueue_scripts', 'nfedit_dequeue_block_library_css', 100);

// ─────────────────────────────────────────────────────────────────────
// Remove emoji noise
// ─────────────────────────────────────────────────────────────────────
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');

// ─────────────────────────────────────────────────────────────────────
// Admin tweaks (lean dashboard)
// ─────────────────────────────────────────────────────────────────────
function nfedit_admin_remove_dashboard_widgets() {
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    remove_meta_box('dashboard_primary', 'dashboard', 'side');
    remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
    remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
}
add_action('wp_dashboard_setup', 'nfedit_admin_remove_dashboard_widgets');

// ─── Phase 12a: Feed importer ──────────────────────────────────────
require_once get_stylesheet_directory() . '/inc/feed-importer/logger.php';
require_once get_stylesheet_directory() . '/inc/feed-importer/canonical-property.php';
require_once get_stylesheet_directory() . '/inc/feed-importer/class-adapter-base.php';
require_once get_stylesheet_directory() . '/inc/feed-importer/class-adapter-awin.php';
require_once get_stylesheet_directory() . '/inc/feed-importer/class-image-sideloader.php';
require_once get_stylesheet_directory() . '/inc/feed-importer/class-lifecycle.php';
require_once get_stylesheet_directory() . '/inc/feed-importer/class-field-mapper.php';
require_once get_stylesheet_directory() . '/inc/feed-importer/class-importer.php';
require_once get_stylesheet_directory() . '/inc/feed-importer/admin-page.php';
require_once get_stylesheet_directory() . '/inc/feed-importer/scrapers/html-utils.php';
require_once get_stylesheet_directory() . '/inc/feed-importer/scrapers/class-scraper-base.php';
require_once get_stylesheet_directory() . '/inc/feed-importer/scrapers/class-scraper-shorefield.php';
require_once get_stylesheet_directory() . '/inc/surround-helpers.php';
require_once get_stylesheet_directory() . '/inc/awin-deeplink.php';
require_once get_stylesheet_directory() . '/inc/feed-importer/fixture-cleanup.php';
