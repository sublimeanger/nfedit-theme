<?php
/**
 * ACF field groups for the Property CPT.
 *
 * Wrapped in acf/init per WP 6.7+ textdomain notice (lesson from greenscapes.md).
 * Tier-gated fields use conditional_logic referencing field_nfedit_property_tier.
 */

defined('ABSPATH') || exit;

add_action('acf/init', function () {

    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    $location_property = [[['param' => 'post_type', 'operator' => '==', 'value' => 'property']]];

    // ─── Conditional-logic helpers ──────────────────────────────────────
    $tier_eq_1     = [[['field' => 'field_nfedit_property_tier', 'operator' => '==', 'value' => '1']]];
    $tier_eq_1or2  = [
        [['field' => 'field_nfedit_property_tier', 'operator' => '==', 'value' => '1']],
        [['field' => 'field_nfedit_property_tier', 'operator' => '==', 'value' => '2']],
    ];

    // ─── A.1 Hero / identity ────────────────────────────────────────────
    acf_add_local_field_group([
        'key'      => 'group_nfedit_property_hero',
        'title'    => 'Hero & Identity',
        'menu_order' => 1,
        'position' => 'normal',
        'location' => $location_property,
        'fields'   => [
            ['key' => 'field_nfedit_property_tier', 'label' => 'Tier', 'name' => 'tier', 'type' => 'select', 'instructions' => 'Drives section visibility throughout the template.', 'required' => 1, 'choices' => [1 => "1 — Editor's Pick", 2 => '2 — Verified', 3 => '3 — Long-tail'], 'default_value' => 2, 'return_format' => 'value'],
            ['key' => 'field_nfedit_property_hero_image', 'label' => 'Hero image', 'name' => 'hero_image', 'type' => 'image', 'instructions' => '1920×1080 (16:9). Tier 1: hand-picked. T2/3: feed first image.', 'return_format' => 'array', 'preview_size' => 'medium'],
            ['key' => 'field_nfedit_property_signature_shot', 'label' => 'Signature shot', 'name' => 'signature_shot', 'type' => 'image', 'instructions' => 'Optional alternative to hero for cards (Tier 1 only).', 'return_format' => 'array', 'preview_size' => 'medium', 'conditional_logic' => $tier_eq_1],
            ['key' => 'field_nfedit_property_merchant', 'label' => 'Merchant', 'name' => 'merchant', 'type' => 'select', 'choices' => ['holidaycottages' => 'Holidaycottages.co.uk', 'sykes' => 'Sykes Holiday Cottages', 'cottages-com' => 'Cottages.com', 'snaptrip' => 'Snaptrip', 'cotswolds-hideaways' => 'Cotswolds Hideaways', 'rural-retreats' => 'Rural Retreats', 'luxury-cottages' => 'Luxury Cottages', 'toad-hall' => 'Toad Hall Cottages', 'new-forest-cottages' => 'New Forest Cottages', 'booking-com' => 'Booking.com', 'vrbo' => 'Vrbo', 'direct' => 'Direct (no platform)'], 'allow_null' => 1, 'return_format' => 'value'],
            ['key' => 'field_nfedit_property_merchant_property_id', 'label' => 'Merchant property ID', 'name' => 'merchant_property_id', 'type' => 'text', 'instructions' => 'Feed-side ID for de-duping & sync.'],
            ['key' => 'field_nfedit_property_booking_url', 'label' => 'Booking URL', 'name' => 'booking_url', 'type' => 'url', 'instructions' => 'Affiliate deep link.'],
        ],
    ]);

    // ─── A.2 Title block ────────────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_title_block', 'title' => 'Title Block', 'menu_order' => 2, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_sleeps', 'label' => 'Sleeps', 'name' => 'sleeps', 'type' => 'number', 'min' => 1, 'wrapper' => ['width' => 25]],
            ['key' => 'field_nfedit_property_bedrooms', 'label' => 'Bedrooms', 'name' => 'bedrooms', 'type' => 'number', 'min' => 0, 'wrapper' => ['width' => 25]],
            ['key' => 'field_nfedit_property_bathrooms', 'label' => 'Bathrooms', 'name' => 'bathrooms', 'type' => 'number', 'min' => 0, 'wrapper' => ['width' => 25]],
            ['key' => 'field_nfedit_property_dogs_welcome', 'label' => 'Dogs welcome', 'name' => 'dogs_welcome', 'type' => 'true_false', 'ui' => 1, 'wrapper' => ['width' => 25]],
            ['key' => 'field_nfedit_property_price_from', 'label' => 'Price from (£)', 'name' => 'price_from', 'type' => 'number', 'min' => 0, 'instructions' => 'Numeric for sorting.', 'wrapper' => ['width' => 33]],
            ['key' => 'field_nfedit_property_price_unit', 'label' => 'Price unit', 'name' => 'price_unit', 'type' => 'select', 'choices' => ['night' => 'per night', 'week' => 'per week'], 'default_value' => 'night', 'wrapper' => ['width' => 33]],
            ['key' => 'field_nfedit_property_price_caveat', 'label' => 'Price caveat', 'name' => 'price_caveat', 'type' => 'text', 'default_value' => 'subject to availability', 'wrapper' => ['width' => 34]],
        ],
    ]);

    // ─── A.3 One-liner (T1+T2) ──────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_oneliner', 'title' => 'One-liner', 'menu_order' => 3, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_one_liner', 'label' => 'One-liner', 'name' => 'one_liner', 'type' => 'textarea', 'maxlength' => 200, 'rows' => 2, 'instructions' => 'Max 200 chars. Italic display in template. Hidden on Tier 3.', 'conditional_logic' => $tier_eq_1or2],
        ],
    ]);

    // ─── A.5 Editorial copy ─────────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_editorial', 'title' => 'Editorial Copy', 'menu_order' => 5, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_editorial_paragraphs', 'label' => 'Editorial paragraphs', 'name' => 'editorial_paragraphs', 'type' => 'repeater', 'instructions' => 'Tier 1: hand-written, ~4 paragraphs. T2: cleaned feed. T3: raw feed.', 'button_label' => 'Add paragraph', 'sub_fields' => [
                ['key' => 'field_nfedit_property_editorial_paragraph', 'label' => 'Paragraph', 'name' => 'paragraph', 'type' => 'textarea', 'rows' => 4],
            ]],
        ],
    ]);

    // ─── A.6 Doctrine (T1) — What we love / may not love ────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_doctrine', 'title' => 'What we love / may not love (T1)', 'menu_order' => 6, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_what_we_love', 'label' => 'What we love', 'name' => 'what_we_love', 'type' => 'repeater', 'min' => 0, 'max' => 5, 'button_label' => 'Add row', 'conditional_logic' => $tier_eq_1, 'sub_fields' => [
                ['key' => 'field_nfedit_property_what_we_love_text', 'label' => 'Text', 'name' => 'text', 'type' => 'text'],
            ]],
            ['key' => 'field_nfedit_property_what_you_may_not_love', 'label' => 'What you may not love', 'name' => 'what_you_may_not_love', 'type' => 'repeater', 'min' => 0, 'max' => 4, 'button_label' => 'Add row', 'conditional_logic' => $tier_eq_1, 'sub_fields' => [
                ['key' => 'field_nfedit_property_what_you_may_not_love_text', 'label' => 'Text', 'name' => 'text', 'type' => 'text'],
            ]],
        ],
    ]);

    // ─── A.7 Booking CTA override (optional) ────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_booking_cta', 'title' => 'Booking CTA Override', 'menu_order' => 7, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_booking_cta_override_copy', 'label' => 'Override copy', 'name' => 'booking_cta_override_copy', 'type' => 'textarea', 'rows' => 3, 'instructions' => 'Optional. Overrides the default "We don\'t take bookings directly..." paragraph. T1 only.', 'conditional_logic' => $tier_eq_1],
        ],
    ]);

    // ─── A.8 Gallery ────────────────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_gallery', 'title' => 'Gallery', 'menu_order' => 8, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_gallery', 'label' => 'Gallery', 'name' => 'gallery', 'type' => 'gallery', 'instructions' => 'Adapts: <5 = rail, 5-9 = 2-col, 10-29 = 3-col, 30+ = 9 + lightbox.', 'preview_size' => 'medium', 'return_format' => 'array'],
        ],
    ]);

    // ─── A.9 Owner quote (T1) ───────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_owner_quote', 'title' => 'Owner Quote (T1)', 'menu_order' => 9, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_owner_quote_text', 'label' => 'Quote', 'name' => 'owner_quote_text', 'type' => 'textarea', 'rows' => 4, 'conditional_logic' => $tier_eq_1],
            ['key' => 'field_nfedit_property_owner_quote_name', 'label' => 'Owner name', 'name' => 'owner_quote_name', 'type' => 'text', 'wrapper' => ['width' => 50], 'conditional_logic' => $tier_eq_1],
            ['key' => 'field_nfedit_property_owner_quote_role', 'label' => 'Owner role', 'name' => 'owner_quote_role', 'type' => 'text', 'default_value' => 'Owner', 'wrapper' => ['width' => 50], 'conditional_logic' => $tier_eq_1],
            ['key' => 'field_nfedit_property_owner_portrait', 'label' => 'Owner portrait', 'name' => 'owner_portrait', 'type' => 'image', 'instructions' => '200×200, will be circle-cropped in CSS.', 'preview_size' => 'thumbnail', 'return_format' => 'array', 'conditional_logic' => $tier_eq_1],
        ],
    ]);

    // ─── A.10 Real review (T1+T2) ───────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_review', 'title' => 'Real Review (T1+T2)', 'menu_order' => 10, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_review_text', 'label' => 'Review text', 'name' => 'review_text', 'type' => 'textarea', 'rows' => 4, 'conditional_logic' => $tier_eq_1or2],
            ['key' => 'field_nfedit_property_review_name', 'label' => 'Reviewer name', 'name' => 'review_name', 'type' => 'text', 'wrapper' => ['width' => 33], 'conditional_logic' => $tier_eq_1or2],
            ['key' => 'field_nfedit_property_review_date', 'label' => 'Date', 'name' => 'review_date', 'type' => 'text', 'placeholder' => 'April 2026', 'wrapper' => ['width' => 33], 'conditional_logic' => $tier_eq_1or2],
            ['key' => 'field_nfedit_property_review_nights_stayed', 'label' => 'Nights stayed', 'name' => 'review_nights_stayed', 'type' => 'text', 'placeholder' => 'stayed two nights', 'wrapper' => ['width' => 34], 'conditional_logic' => $tier_eq_1or2],
        ],
    ]);

    // ─── A.11 Surrounds — Relationship to surround CPT ──────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_surrounds', 'title' => 'Surrounds (T1+T2)', 'menu_order' => 11, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_surrounds', 'label' => 'Surrounds', 'name' => 'surrounds', 'type' => 'relationship', 'post_type' => ['surround'], 'min' => 0, 'max' => 8, 'return_format' => 'id', 'instructions' => '6-8 surrounds (eat/drink/walk/see/shop/swim).', 'conditional_logic' => $tier_eq_1or2],
        ],
    ]);

    // ─── A.12 Things to do (T1) ─────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_things_to_do', 'title' => 'Things to Do (T1)', 'menu_order' => 12, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_things_to_do', 'label' => 'Things to do', 'name' => 'things_to_do', 'type' => 'repeater', 'instructions' => 'Each row supports markdown bold/italic — `**place**` and `*pub*`. Bullet point per row.', 'button_label' => 'Add row', 'conditional_logic' => $tier_eq_1, 'sub_fields' => [
                ['key' => 'field_nfedit_property_things_to_do_text', 'label' => 'Item', 'name' => 'item', 'type' => 'textarea', 'rows' => 2],
            ]],
        ],
    ]);

    // ─── A.13 Owner recs (T1, 3 columns) ────────────────────────────────
    $owner_recs_subfields = [
        ['key' => '__SUBKEY_NAME__', 'label' => 'Name', 'name' => 'name', 'type' => 'text'],
        ['key' => '__SUBKEY_DEK__',  'label' => 'Dek',  'name' => 'dek',  'type' => 'text'],
        ['key' => '__SUBKEY_IMG__',  'label' => 'Image','name' => 'image','type' => 'image', 'preview_size' => 'thumbnail', 'return_format' => 'array'],
    ];
    $make_recs_subfields = function ($prefix) use ($owner_recs_subfields) {
        $out = [];
        foreach ($owner_recs_subfields as $sf) {
            $sf['key'] = str_replace(['__SUBKEY_NAME__', '__SUBKEY_DEK__', '__SUBKEY_IMG__'], [
                'field_nfedit_property_owner_recs_' . $prefix . '_name',
                'field_nfedit_property_owner_recs_' . $prefix . '_dek',
                'field_nfedit_property_owner_recs_' . $prefix . '_image',
            ], $sf['key']);
            $out[] = $sf;
        }
        return $out;
    };
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_owner_recs', 'title' => "Owner's Recs (T1)", 'menu_order' => 13, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_owner_recs_eat', 'label' => 'To eat', 'name' => 'owner_recs_eat', 'type' => 'repeater', 'min' => 0, 'max' => 3, 'button_label' => 'Add', 'conditional_logic' => $tier_eq_1, 'sub_fields' => $make_recs_subfields('eat')],
            ['key' => 'field_nfedit_property_owner_recs_do', 'label' => 'To do', 'name' => 'owner_recs_do', 'type' => 'repeater', 'min' => 0, 'max' => 3, 'button_label' => 'Add', 'conditional_logic' => $tier_eq_1, 'sub_fields' => $make_recs_subfields('do')],
            ['key' => 'field_nfedit_property_owner_recs_see', 'label' => 'To see', 'name' => 'owner_recs_see', 'type' => 'repeater', 'min' => 0, 'max' => 3, 'button_label' => 'Add', 'conditional_logic' => $tier_eq_1, 'sub_fields' => $make_recs_subfields('see')],
        ],
    ]);

    // ─── A.14 Details (label/value) ─────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_details', 'title' => 'Details', 'menu_order' => 14, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_details', 'label' => 'Details', 'name' => 'details', 'type' => 'repeater', 'instructions' => 'Auto-seeded with 11 default labels on new property creation.', 'button_label' => 'Add row', 'sub_fields' => [
                ['key' => 'field_nfedit_property_details_label', 'label' => 'Label', 'name' => 'label', 'type' => 'text', 'wrapper' => ['width' => 30]],
                ['key' => 'field_nfedit_property_details_value', 'label' => 'Value', 'name' => 'value', 'type' => 'textarea', 'rows' => 2, 'wrapper' => ['width' => 70]],
            ]],
        ],
    ]);

    // ─── A.15 Policy (T1+T2) ────────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_policy', 'title' => 'Booking & Cancellation Policy (T1+T2)', 'menu_order' => 15, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_policy', 'label' => 'Policy', 'name' => 'policy', 'type' => 'textarea', 'rows' => 4, 'instructions' => 'Plain English. Hidden on T3.', 'conditional_logic' => $tier_eq_1or2],
        ],
    ]);

    // ─── A.16 FAQs ──────────────────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_faqs', 'title' => 'FAQs', 'menu_order' => 16, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_faqs', 'label' => 'FAQs', 'name' => 'faqs', 'type' => 'repeater', 'instructions' => 'Auto-seeded with 8 standard questions on new property creation.', 'button_label' => 'Add FAQ', 'sub_fields' => [
                ['key' => 'field_nfedit_property_faqs_q', 'label' => 'Question', 'name' => 'q', 'type' => 'text'],
                ['key' => 'field_nfedit_property_faqs_a', 'label' => 'Answer',   'name' => 'a', 'type' => 'textarea', 'rows' => 3],
            ]],
        ],
    ]);

    // ─── A.17 Sustainability (T1) ───────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_sustainability', 'title' => 'Sustainability (T1)', 'menu_order' => 17, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_sustainability', 'label' => 'Sustainability', 'name' => 'sustainability', 'type' => 'textarea', 'rows' => 4, 'instructions' => 'Single paragraph. Hidden if empty.', 'conditional_logic' => $tier_eq_1],
        ],
    ]);

    // ─── A.18 Sticky offer banner ───────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_offer', 'title' => 'Sticky Offer Banner', 'menu_order' => 18, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_offer_active', 'label' => 'Offer active', 'name' => 'offer_active', 'type' => 'true_false', 'ui' => 1, 'wrapper' => ['width' => 25]],
            ['key' => 'field_nfedit_property_offer_copy',   'label' => 'Copy',         'name' => 'offer_copy',   'type' => 'text', 'wrapper' => ['width' => 50], 'conditional_logic' => [[['field' => 'field_nfedit_property_offer_active', 'operator' => '==', 'value' => '1']]]],
            ['key' => 'field_nfedit_property_offer_code',   'label' => 'Code',         'name' => 'offer_code',   'type' => 'text', 'wrapper' => ['width' => 25], 'conditional_logic' => [[['field' => 'field_nfedit_property_offer_active', 'operator' => '==', 'value' => '1']]]],
            ['key' => 'field_nfedit_property_offer_expiry', 'label' => 'Expiry',       'name' => 'offer_expiry', 'type' => 'date_picker', 'display_format' => 'd/m/Y', 'return_format' => 'Y-m-d', 'instructions' => 'Auto-hides past this date.', 'conditional_logic' => [[['field' => 'field_nfedit_property_offer_active', 'operator' => '==', 'value' => '1']]]],
        ],
    ]);

    // ─── A.19 Geo ───────────────────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_geo', 'title' => 'Geographic', 'menu_order' => 19, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_latitude',    'label' => 'Latitude',  'name' => 'latitude',  'type' => 'number', 'wrapper' => ['width' => 33]],
            ['key' => 'field_nfedit_property_longitude',   'label' => 'Longitude', 'name' => 'longitude', 'type' => 'number', 'wrapper' => ['width' => 33]],
            ['key' => 'field_nfedit_property_address_line','label' => 'Address line', 'name' => 'address_line', 'type' => 'text', 'instructions' => 'Approximate (for map context, never exact).', 'wrapper' => ['width' => 34]],
        ],
    ]);

    // ─── A.20 Related cottages (T1) ─────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_related', 'title' => 'Related Cottages (T1 hand-curated)', 'menu_order' => 20, 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_related_cottages', 'label' => 'Related cottages', 'name' => 'related_cottages', 'type' => 'relationship', 'post_type' => ['property'], 'min' => 0, 'max' => 6, 'return_format' => 'id', 'instructions' => 'T2/3: auto-resolved by area or feature in template.', 'conditional_logic' => $tier_eq_1],
        ],
    ]);

    // ─── A.21 Feed metadata (importer) ──────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_property_feed_meta', 'title' => 'Feed Sync Metadata', 'menu_order' => 21, 'position' => 'side', 'location' => $location_property,
        'fields' => [
            ['key' => 'field_nfedit_property_feed_source_id',     'label' => 'Source ID',     'name' => 'feed_source_id',     'type' => 'text', 'instructions' => 'Composite: {merchant}:{merchant_property_id}.'],
            ['key' => 'field_nfedit_property_feed_last_updated',  'label' => 'Last updated',  'name' => 'feed_last_updated',  'type' => 'date_picker', 'display_format' => 'd/m/Y', 'return_format' => 'Y-m-d', 'instructions' => 'Set by importer.'],
            ['key' => 'field_nfedit_property_feed_raw_payload',   'label' => 'Raw payload',   'name' => 'feed_raw_payload',   'type' => 'textarea', 'rows' => 3, 'instructions' => 'JSON of last feed payload — for debugging.'],
            ['key' => 'field_nfedit_property_editorial_override_active', 'label' => 'Editorial override active', 'name' => 'editorial_override_active', 'type' => 'true_false', 'ui' => 1, 'instructions' => 'If on, importer skips this property on next sync.'],
        ],
    ]);

});
