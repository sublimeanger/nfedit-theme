<?php
/**
 * Canonical property array shape consumed by the field mapper.
 *
 * Phase 12a defines the shape; Phase 12b fills it from real Awin feed data.
 */
defined('ABSPATH') || exit;

function nfedit_canonical_property_template() {
    return [
        // Identity / dedup
        'external_id'    => '',
        'merchant_slug'  => '',
        'advertiser_id'  => 0,
        'last_seen'      => time(),
        // Title / slug / description
        'title'          => '',
        'slug_seed'      => '',
        'description'    => '',
        // Booking
        'booking_url'    => '',
        'price_from'     => null,
        'currency'       => 'GBP',
        // Capacity
        'sleeps'         => null,
        'bedrooms'       => null,
        'bathrooms'      => null,
        'pets_welcome'   => null,
        // Location
        'lat'            => null,
        'lng'            => null,
        'address_line'   => '',
        'area_hint'      => '',
        // Features
        'feature_hints'  => [],
        // Media
        'image_urls'     => [],
        // Raw payload (debug / re-mapping)
        'raw_payload'    => null,
    ];
}
