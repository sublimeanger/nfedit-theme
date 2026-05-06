<?php
/**
 * Surround helpers — utility functions for surround rendering.
 */
defined('ABSPATH') || exit;

/**
 * Returns the surround_kind label for a surround post, or empty string.
 */
function nfedit_surround_kind_label($post_id) {
    $kinds = get_the_terms($post_id, 'surround_kind');
    if (is_wp_error($kinds) || empty($kinds)) return '';
    return $kinds[0]->name;
}

/**
 * Returns the primary area name for a surround, or empty string.
 */
function nfedit_surround_area_name($post_id) {
    $areas = get_the_terms($post_id, 'area_taxonomy');
    if (is_wp_error($areas) || empty($areas)) return '';
    return $areas[0]->name;
}

/**
 * Counts cottages that link this surround via their `surrounds` relationship field.
 * Returns 0 for unknown surrounds. Uses serialised-LIKE match (same approach as cottages-rail).
 *
 * @param int $surround_id
 * @return int
 */
function nfedit_surround_cottage_count($surround_id) {
    $surround_id = (int) $surround_id;
    if (!$surround_id) return 0;
    global $wpdb;
    $needle = '"' . (string) $surround_id . '"';
    $count = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT pm.post_id)
           FROM {$wpdb->postmeta} pm
           JOIN {$wpdb->posts} p ON p.ID = pm.post_id
          WHERE pm.meta_key = 'surrounds'
            AND pm.meta_value LIKE %s
            AND p.post_type = 'property'
            AND p.post_status = 'publish'",
        '%' . $wpdb->esc_like($needle) . '%'
    ));
    return $count;
}
