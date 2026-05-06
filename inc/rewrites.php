<?php
/**
 * Native post permalinks rewritten to /the-edit/{slug}/.
 * Pretty cottages-archive URLs.
 * Editorial cluster archives at /the-edit/in/{slug}/.
 * Guide category archives at /guides/in/{slug}/.
 */

defined('ABSPATH') || exit;

// ─── Native post → /the-edit/{slug}/ (Phase 3) ──────────────────────────
add_filter('post_link', function ($url, $post) {
    if ($post && $post->post_type === 'post') {
        return home_url('/the-edit/' . $post->post_name . '/');
    }
    return $url;
}, 10, 2);

add_action('init', function () {
    add_rewrite_rule(
        '^the-edit/([^/]+)/?$',
        'index.php?name=$matches[1]',
        'top'
    );

    // ─── Phase 6: Pretty cottage-archive URLs ────────────────────────
    add_rewrite_rule(
        '^cottages/by-area/([^/]+)/?$',
        'index.php?taxonomy=area_taxonomy&term=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^cottages/by-feature/([^/]+)/?$',
        'index.php?taxonomy=feature&term=$matches[1]',
        'top'
    );
});

// Higher-priority archive rules (more specific go LAST so they prepend FIRST = land on TOP).
add_action('init', function () {
    // Phase 8: Editorial cluster archives at /the-edit/in/{slug}/
    add_rewrite_rule(
        '^the-edit/in/([^/]+)/?$',
        'index.php?taxonomy=cluster&term=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^the-edit/in/([^/]+)/page/([0-9]+)/?$',
        'index.php?taxonomy=cluster&term=$matches[1]&paged=$matches[2]',
        'top'
    );

    // Phase 9: Guide category archives at /guides/in/{slug}/
    add_rewrite_rule(
        '^guides/in/([^/]+)/?$',
        'index.php?taxonomy=guide_category&term=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^guides/in/([^/]+)/page/([0-9]+)/?$',
        'index.php?taxonomy=guide_category&term=$matches[1]&paged=$matches[2]',
        'top'
    );
}, 11);

// ─── Disable WP canonical redirect when targeting taxonomy archive URLs ────
add_filter('redirect_canonical', function ($redirect_url, $requested_url) {
    $req = parse_url($requested_url, PHP_URL_PATH);
    if ($req && (
        strpos($req, '/cottages/area/') === 0
        || strpos($req, '/cottages/by-area/') === 0
        || strpos($req, '/cottages/by-feature/') === 0
        || strpos($req, '/area-taxonomy/') === 0
        || strpos($req, '/feature/') === 0
        || strpos($req, '/the-edit/in/') === 0
        || strpos($req, '/cluster/') === 0
        || strpos($req, '/guides/in/') === 0
        || strpos($req, '/guides/category/') === 0
    )) {
        return false;
    }
    return $redirect_url;
}, 10, 2);

// ─── 301: native taxonomy archives → pretty URLs ──────────
add_action('template_redirect', function () {
    $req = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

    if (is_tax('area_taxonomy')) {
        $term = get_queried_object();
        if ($term && strpos($req, '/cottages/by-area/') === false) {
            wp_safe_redirect(home_url('/cottages/by-area/' . $term->slug . '/'), 301);
            exit;
        }
    }
    if (is_tax('feature')) {
        $term = get_queried_object();
        if ($term && strpos($req, '/cottages/by-feature/') === false) {
            wp_safe_redirect(home_url('/cottages/by-feature/' . $term->slug . '/'), 301);
            exit;
        }
    }
    if (is_tax('cluster')) {
        $term = get_queried_object();
        if ($term && strpos($req, '/the-edit/in/') === false) {
            wp_safe_redirect(home_url('/the-edit/in/' . $term->slug . '/'), 301);
            exit;
        }
    }
    if (is_tax('guide_category')) {
        $term = get_queried_object();
        if ($term && strpos($req, '/guides/in/') === false) {
            wp_safe_redirect(home_url('/guides/in/' . $term->slug . '/'), 301);
            exit;
        }
    }
}, 5);

// ─── Alternate native rewrite rules so the 301 hook actually fires ────
add_action('init', function () {
    add_rewrite_rule('^cottages/area/([^/]+)/?$',  'index.php?taxonomy=area_taxonomy&term=$matches[1]', 'top');
    add_rewrite_rule('^area-taxonomy/([^/]+)/?$',  'index.php?taxonomy=area_taxonomy&term=$matches[1]', 'top');
    add_rewrite_rule('^feature/([^/]+)/?$',        'index.php?taxonomy=feature&term=$matches[1]',       'top');
    add_rewrite_rule('^cluster/([^/]+)/?$',        'index.php?taxonomy=cluster&term=$matches[1]',       'top');
    add_rewrite_rule('^guides/category/([^/]+)/?$', 'index.php?taxonomy=guide_category&term=$matches[1]', 'top');
}, 11);

// ─── Phase 10B: /newsletter/ → /#newsletter (homepage anchor) ──────
add_action('template_redirect', function () {
    $req = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $req_path = parse_url($req, PHP_URL_PATH);
    if ($req_path === '/newsletter/' || $req_path === '/newsletter') {
        wp_safe_redirect(home_url('/#newsletter'), 301);
        exit;
    }
}, 4);
