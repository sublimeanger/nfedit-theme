<?php
/**
 * Field mapper — Phase 12a skeleton + Phase 12c scraper extensions.
 *
 * Always-overwrite policy: booking_url, price_from, price_unit
 * Editorial-override-respect: everything else when editorial_override_active=true
 *
 * Phase 12c additions for scraped properties (when canonical has `_scraped` flag):
 *   - Mark _nfedit_scrape_review_required = 1 (gates publishing)
 *   - Resolve feature_hints to existing `feature` taxonomy terms
 *   - Resolve area_hint to existing `area_taxonomy` term (warn if no match)
 *   - Sideload images (cap 10), set hero_image + post thumbnail + gallery
 *
 * PHP 7.4 compatible.
 */
defined('ABSPATH') || exit;

class NFEdit_Feed_Field_Mapper {

    const ALWAYS_OVERWRITE_FIELDS = [
        'booking_url',
        'price_from',
        'price_unit',
    ];

    const MAX_GALLERY_IMAGES = 10;

    /**
     * Apply canonical to a (new or existing) property post.
     *
     * @param array $canonical
     * @return int|WP_Error
     */
    public function apply(array $canonical) {
        $external_id = isset($canonical['external_id']) ? $canonical['external_id'] : '';
        if (!$external_id) {
            return new WP_Error('nfedit_no_external_id', 'Canonical property has no external_id');
        }

        $post_id = $this->find_post_by_external_id($external_id);
        $is_new  = !$post_id;

        if ($is_new) {
            $post_id = wp_insert_post([
                'post_type'    => 'property',
                'post_status'  => 'draft',
                'post_title'   => !empty($canonical['title']) ? $canonical['title'] : "Imported property (Phase 12 stub)",
                'post_content' => '',
            ]);
            if (is_wp_error($post_id)) return $post_id;
        }

        update_field('feed_source_id',    $external_id, $post_id);
        update_field('feed_last_updated', date('Y-m-d'), $post_id);
        update_field('feed_raw_payload',  wp_json_encode(isset($canonical['raw_payload']) ? $canonical['raw_payload'] : []), $post_id);

        $override_active = (bool) get_field('editorial_override_active', $post_id);

        $always_map = [
            'booking_url' => 'booking_url',
            'price_from'  => 'price_from',
            'price_unit'  => 'price_unit',
        ];
        foreach ($always_map as $field_name => $canon_key) {
            $val = isset($canonical[$canon_key]) ? $canonical[$canon_key] : null;
            if ($val !== null && $val !== '') {
                update_field($field_name, $val, $post_id);
            }
        }

        if ($override_active && !$is_new) {
            nfedit_feed_log('property_skipped_override', null, "Skipped #$post_id ($external_id) — editorial_override_active", ['post_id' => $post_id, 'external_id' => $external_id]);
            return $post_id;
        }

        if (!empty($canonical['merchant_slug'])) {
            update_field('merchant', $canonical['merchant_slug'], $post_id);
        }
        if ($is_new) {
            update_field('tier', 3, $post_id);
        }

        // ─── Phase 12c: scraper-specific handling ──────────────────────
        if (!empty($canonical['_scraped'])) {
            $this->apply_scraped_extras($canonical, $post_id);
        }

        nfedit_feed_log(
            $is_new ? 'property_created' : 'property_updated',
            null,
            $is_new ? "Created #$post_id ($external_id)" : "Updated #$post_id ($external_id)",
            ['post_id' => $post_id, 'external_id' => $external_id]
        );

        return $post_id;
    }

    /**
     * Phase 12c: apply scraper-specific fields.
     */
    protected function apply_scraped_extras(array $canonical, $post_id) {
        update_post_meta($post_id, '_nfedit_scrape_review_required', 1);

        // Always set sleeps/bedrooms/dogs_welcome from canonical when scraped (they're rarely curated separately)
        if (isset($canonical['sleeps']) && $canonical['sleeps']) {
            update_field('sleeps', (int) $canonical['sleeps'], $post_id);
        }
        if (isset($canonical['bedrooms']) && $canonical['bedrooms']) {
            update_field('bedrooms', (int) $canonical['bedrooms'], $post_id);
        }
        if (isset($canonical['pets_welcome'])) {
            update_field('dogs_welcome', (bool) $canonical['pets_welcome'], $post_id);
        }
        if (!empty($canonical['description'])) {
            // Store in one_liner if short, else first editorial paragraph
            if (mb_strlen($canonical['description']) <= 200) {
                update_field('one_liner', $canonical['description'], $post_id);
            } else {
                update_field('editorial_paragraphs', [
                    ['paragraph' => $canonical['description']],
                ], $post_id);
            }
        }
        if (!empty($canonical['address_line'])) {
            update_field('address_line', $canonical['address_line'], $post_id);
        }
        if (isset($canonical['lat']) && $canonical['lat']) {
            update_field('latitude', (float) $canonical['lat'], $post_id);
        }
        if (isset($canonical['lng']) && $canonical['lng']) {
            update_field('longitude', (float) $canonical['lng'], $post_id);
        }

        // Resolve feature_hints to taxonomy terms
        if (!empty($canonical['feature_hints']) && is_array($canonical['feature_hints'])) {
            $term_ids = [];
            $unmapped = [];
            foreach ($canonical['feature_hints'] as $hint) {
                $term = get_term_by('name', $hint, 'feature');
                if (!$term) $term = get_term_by('slug', sanitize_title($hint), 'feature');
                if ($term) {
                    $term_ids[] = (int) $term->term_id;
                } else {
                    $unmapped[] = $hint;
                }
            }
            if ($term_ids) {
                wp_set_object_terms($post_id, $term_ids, 'feature');
            }
            if ($unmapped) {
                nfedit_feed_log('mapper_feature_unmapped', null, "Unmapped features for #$post_id: " . implode(', ', $unmapped), [], 'info');
            }
        }

        // Resolve area_hint to area_taxonomy
        if (!empty($canonical['area_hint'])) {
            $area_term = get_term_by('name', $canonical['area_hint'], 'area_taxonomy');
            if (!$area_term) $area_term = get_term_by('slug', sanitize_title($canonical['area_hint']), 'area_taxonomy');
            if ($area_term) {
                wp_set_object_terms($post_id, [(int) $area_term->term_id], 'area_taxonomy');
            } else {
                nfedit_feed_log('mapper_area_unmapped', null, "Couldn't resolve area_hint '{$canonical['area_hint']}' for #$post_id", [], 'warn');
            }
        }

        // Sideload images
        if (!empty($canonical['image_urls']) && is_array($canonical['image_urls'])) {
            $sideloader = new NFEdit_Feed_Image_Sideloader();
            $att_ids    = [];
            $i          = 0;
            foreach ($canonical['image_urls'] as $img_url) {
                if ($i++ >= self::MAX_GALLERY_IMAGES) break;
                $att_id = $sideloader->sideload($img_url, isset($canonical['title']) ? $canonical['title'] : '', $post_id);
                if ($att_id) {
                    $att_ids[] = $att_id;
                    if (count($att_ids) === 1) {
                        // First successful image becomes hero + post thumbnail
                        set_post_thumbnail($post_id, $att_id);
                        // Phase 10A lesson: use field KEY not name to avoid collision
                        // with site-settings-newsletter's field_nfedit_global_nl_hero_image
                        update_field('field_nfedit_property_hero_image', $att_id, $post_id);
                    }
                }
            }
            if ($att_ids) {
                update_field('gallery', $att_ids, $post_id);
            }
        }
    }

    protected function find_post_by_external_id($external_id) {
        global $wpdb;
        $found = $wpdb->get_var($wpdb->prepare("
            SELECT post_id FROM {$wpdb->postmeta}
            WHERE meta_key = 'feed_source_id'
            AND meta_value = %s
            LIMIT 1
        ", $external_id));
        return (int) $found;
    }
}
