<?php
/**
 * Awin deeplink wrapping — central helper.
 *
 * Wraps a raw merchant URL into an Awin tracked deeplink based on:
 *   - the merchant slug stored on the property post (ACF `merchant` field)
 *   - the merchant's advertiser_id in wp_nfedit_advertisers (matched by merchant_slug)
 *   - the publisher ID stored in wp_options (nfedit_awin_publisher_id)
 *
 * Schema notes (Phase 12a):
 *   wp_nfedit_advertisers columns used: merchant_slug, advertiser_id, enabled
 *   "approved" = enabled=1
 *
 * Returns the Awin URL on success, or the raw URL on any failure.
 */
defined('ABSPATH') || exit;

/**
 * Look up a merchant's advertiser_id (Awin merchant ID) by slug.
 * Returns string awinmid or null if not found / not enabled.
 */
function nfedit_awin_get_mid($merchant_slug) {
    if (empty($merchant_slug) || $merchant_slug === 'direct') {
        return null;
    }
    global $wpdb;
    $mid = $wpdb->get_var($wpdb->prepare(
        "SELECT advertiser_id FROM {$wpdb->prefix}nfedit_advertisers
          WHERE merchant_slug = %s AND enabled = 1 LIMIT 1",
        $merchant_slug
    ));
    return $mid ? (string) $mid : null;
}

/**
 * Get the display label for a merchant slug, used by the CTA template.
 *
 * Resolution order:
 *   1. cta_label column (if non-empty)
 *   2. advertiser_name column
 *   3. The bare slug (fallback — matches pre-Phase-13d behaviour)
 *
 * Only considers enabled = 1 rows. Returns empty string if slug is empty.
 *
 * @param string $merchant_slug
 * @return string
 */
function nfedit_get_merchant_cta_label( $merchant_slug ) {
    global $wpdb;
    $merchant_slug = (string) $merchant_slug;
    if ( $merchant_slug === '' ) {
        return '';
    }
    $row = $wpdb->get_row( $wpdb->prepare(
        "SELECT cta_label, advertiser_name FROM wp_nfedit_advertisers WHERE merchant_slug = %s AND enabled = 1 LIMIT 1",
        $merchant_slug
    ) );
    if ( ! $row ) {
        return $merchant_slug;
    }
    if ( ! empty( $row->cta_label ) ) {
        return $row->cta_label;
    }
    if ( ! empty( $row->advertiser_name ) ) {
        return $row->advertiser_name;
    }
    return $merchant_slug;
}


/**
 * Wrap a raw URL into an Awin deeplink.
 */
function nfedit_awin_wrap_url($raw_url, $merchant_slug, $clickref = 'nfedit') {
    if (empty($raw_url)) return '';
    $publisher_id = get_option('nfedit_awin_publisher_id', '');
    if (empty($publisher_id)) return $raw_url;
    $awinmid = nfedit_awin_get_mid($merchant_slug);
    if (empty($awinmid)) return $raw_url;

    return sprintf(
        'https://www.awin1.com/cread.php?awinmid=%s&awinaffid=%s&ued=%s&clickref=%s',
        rawurlencode($awinmid),
        rawurlencode($publisher_id),
        rawurlencode($raw_url),
        rawurlencode($clickref)
    );
}

/**
 * Publish-time hook: when a property post transitions to 'publish',
 * compute the Awin URL and store it in `awin_booking_url` post meta.
 */
function nfedit_awin_property_publish_hook($new_status, $old_status, $post) {
    if (empty($post) || $post->post_type !== 'property') return;
    if ($new_status !== 'publish') return;

    $post_id  = (int) $post->ID;
    $raw_url  = get_post_meta($post_id, 'booking_url', true);
    $merchant = get_post_meta($post_id, 'merchant', true);
    if (empty($raw_url) || empty($merchant)) return;

    $clickref = !empty($post->post_name) ? $post->post_name : 'p' . $post_id;
    $clickref = substr($clickref, 0, 50);

    $awin_url = nfedit_awin_wrap_url($raw_url, $merchant, $clickref);

    if ($awin_url !== $raw_url) {
        update_post_meta($post_id, 'awin_booking_url', $awin_url);
    } else {
        delete_post_meta($post_id, 'awin_booking_url');
    }
}
add_action('transition_post_status', 'nfedit_awin_property_publish_hook', 10, 3);

/**
 * Frontend helper — return the booking CTA URL (Awin-wrapped if available).
 */
function nfedit_property_booking_url($post_id) {
    $awin = get_post_meta($post_id, 'awin_booking_url', true);
    if (!empty($awin)) return $awin;
    return get_post_meta($post_id, 'booking_url', true);
}
