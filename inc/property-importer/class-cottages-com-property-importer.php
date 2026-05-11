<?php
/**
 * Phase 13e: import 91 cottages.com NF properties as draft posts.
 *
 * Reads cottages.com_inventory + property_matches + snaptrip_listings,
 * builds canonical property arrays per the actual canonical template
 * (`nfedit_canonical_property_template()`), hands off to
 * `NFEdit_Feed_Field_Mapper::apply()` which performs wp_insert_post /
 * wp_update_post / update_field / taxonomy assignment / image sideloading.
 *
 * After mapper.apply() returns the post_id, this adapter performs
 * explicit `update_field()` calls for fields the canonical doesn't carry:
 *   - tier (mapped from staging.proposed_tier)
 *   - merchant_property_id (snaptrip ref OR cottages.com merchant_product_id)
 *   - price_unit = 'week' (when weekly_price_from > 0)
 * and forces post_status='draft' via wp_update_post.
 *
 * Idempotent via external_id ('cottages-com:{merchant_product_id}').
 *
 * PHP 7.4 compatible.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class NFEdit_Cottages_Com_Property_Importer {

    const EXTERNAL_ID_PREFIX = 'cottages-com';
    const PHASE_MARKER_META  = '_nfedit_phase_13e_imported';

    const ADVERTISER_ID_COTTAGES_COM = 118653;
    const ADVERTISER_ID_SNAPTRIP     = 10811;

    private static $tier_map = array(
        'T1'         => 1,
        'T2'         => 2,
        'below_bar'  => 3,
        'no_reviews' => 3,
    );

    private $area_term_names = null;

    public function import_all( $opts = array() ) {
        global $wpdb;

        $dry_run = ! empty( $opts['dry_run'] );
        $limit   = isset( $opts['limit'] ) ? (int) $opts['limit'] : 0;

        $this->load_area_term_names();

        $sql = "
            SELECT
                ci.*,
                m.auto_route,
                m.snaptrip_listing_id,
                m.confidence AS match_confidence,
                sl.detail_url        AS snaptrip_detail_url,
                sl.snaptrip_ref      AS snaptrip_ref,
                sl.title             AS snaptrip_title,
                sl.bedrooms          AS snaptrip_bedrooms
            FROM wp_nfedit_cottages_com_inventory ci
            LEFT JOIN wp_nfedit_property_matches m ON m.cottages_com_inventory_id = ci.id
            LEFT JOIN wp_nfedit_snaptrip_listings sl ON sl.id = m.snaptrip_listing_id
            ORDER BY ci.id
        ";
        if ( $limit > 0 ) {
            $sql .= " LIMIT " . (int) $limit;
        }
        $rows = $wpdb->get_results( $sql );

        $stats = array(
            'total'           => count( $rows ),
            'processed'       => 0,
            'created'         => 0,
            'updated'         => 0,
            'errors'          => 0,
            'route_snaptrip'  => 0,
            'route_cottages'  => 0,
            'tier_1'          => 0,
            'tier_2'          => 0,
            'tier_3'          => 0,
            'area_matched'    => 0,
            'area_unmatched'  => 0,
            'with_bedrooms'   => 0,
            'with_price'      => 0,
        );

        foreach ( $rows as $row ) {
            $result = $this->import_one( $row, $dry_run );
            $stats['processed']++;
            $stats[ 'route_' . ( $result['merchant_slug'] === 'snaptrip' ? 'snaptrip' : 'cottages' ) ]++;
            $stats[ 'tier_' . $result['tier'] ]++;
            if ( ! empty( $result['area_hint'] ) )       { $stats['area_matched']++; } else { $stats['area_unmatched']++; }
            if ( ! empty( $result['has_bedrooms'] ) )    { $stats['with_bedrooms']++; }
            if ( ! empty( $result['has_price'] ) )       { $stats['with_price']++; }

            if ( $result['outcome'] === 'created' )      { $stats['created']++; }
            elseif ( $result['outcome'] === 'updated' )  { $stats['updated']++; }
            elseif ( $result['outcome'] === 'error' )    { $stats['errors']++; }

            if ( $dry_run ) {
                printf(
                    "  %3d. [%s] cc#%-3d %-12s -> %-12s | tier=%d | area=%-18s | bedrooms=%s | price=%s | slug=%s\n",
                    $stats['processed'], $result['outcome'],
                    $row->id, $row->merchant_product_id,
                    $result['merchant_slug'], $result['tier'],
                    $result['area_hint'] ?: '(none)',
                    ! empty( $result['has_bedrooms'] ) ? 'yes' : 'no',
                    ! empty( $result['has_price'] ) ? 'yes' : 'no',
                    $result['slug']
                );
            }
        }
        return $stats;
    }

    private function import_one( $row, $dry_run ) {
        $merchant_slug = $this->resolve_merchant_slug( $row );
        $booking_url   = $this->resolve_booking_url( $row, $merchant_slug );
        $merchant_pid  = $this->resolve_merchant_property_id( $row, $merchant_slug );
        $tier          = $this->resolve_tier( $row->proposed_tier );
        $area_hint     = $this->resolve_area_hint( $row->town );
        $slug          = sanitize_title( $row->title );

        $external_id = self::EXTERNAL_ID_PREFIX . ':' . $row->merchant_product_id;

        $advertiser_id = ( $merchant_slug === 'snaptrip' )
            ? self::ADVERTISER_ID_SNAPTRIP
            : self::ADVERTISER_ID_COTTAGES_COM;

        $bedrooms = null;
        if ( $merchant_slug === 'snaptrip' && ! empty( $row->snaptrip_bedrooms ) ) {
            $bedrooms = (int) $row->snaptrip_bedrooms;
        }

        $has_price = ! empty( $row->weekly_price_from ) && (float) $row->weekly_price_from > 0;

        $summary = array(
            'merchant_slug' => $merchant_slug,
            'tier'          => $tier,
            'area_hint'     => $area_hint,
            'slug'          => $slug,
            'has_bedrooms'  => $bedrooms !== null,
            'has_price'     => $has_price,
        );

        if ( $dry_run ) {
            $existing = $this->find_existing_post( $external_id );
            $summary['outcome'] = $existing ? 'updated' : 'created';
            return $summary;
        }

        $canonical = nfedit_canonical_property_template();

        $canonical['external_id']   = $external_id;
        $canonical['_scraped']      = true;  // triggers NFEdit_Feed_Field_Mapper::apply_scraped_extras() — populates sleeps/bedrooms/lat/lng/area_taxonomy/hero_image
        $canonical['title']         = $row->title;
        $canonical['slug_seed']     = $slug;
        $canonical['merchant_slug'] = $merchant_slug;
        $canonical['advertiser_id'] = $advertiser_id;
        $canonical['last_seen']     = time();
        $canonical['booking_url']   = $booking_url;
        $canonical['sleeps']        = ! empty( $row->sleeps ) ? (int) $row->sleeps : null;
        $canonical['bedrooms']      = $bedrooms;
        $canonical['lat']           = ! empty( $row->latitude )  ? (float) $row->latitude  : null;
        $canonical['lng']           = ! empty( $row->longitude ) ? (float) $row->longitude : null;
        $canonical['address_line']  = $this->compose_address_line( $row );
        $canonical['area_hint']     = $area_hint;
        $canonical['image_urls']    = ! empty( $row->image_url ) ? array( $row->image_url ) : array();

        if ( $has_price ) {
            $canonical['price_from'] = (float) $row->weekly_price_from;
            $canonical['price_unit'] = 'week';
        }

        $canonical['raw_payload'] = array(
            'cottages_com_inventory_id' => (int) $row->id,
            'merchant_product_id'       => $row->merchant_product_id,
            'aw_product_id'             => $row->aw_product_id,
            'auto_route'                => $row->auto_route,
            'match_confidence'          => $row->match_confidence,
            'snaptrip_listing_id'       => $row->snaptrip_listing_id ? (int) $row->snaptrip_listing_id : null,
            'snaptrip_ref'              => $row->snaptrip_ref,
            'snaptrip_title'            => $row->snaptrip_title,
            'proposed_tier'             => $row->proposed_tier,
            'custom_1'                  => $row->custom_1,
            'custom_2'                  => $row->custom_2,
            'custom_3'                  => $row->custom_3,
            'custom_4'                  => $row->custom_4,
            'custom_5'                  => $row->custom_5,
            'custom_6'                  => $row->custom_6,
            'custom_7'                  => $row->custom_7,
            'review_avg'                => $row->review_avg,
            'star_rating'               => $row->star_rating,
            'imported_via_phase'        => '13e',
            'imported_at'               => current_time( 'mysql', 1 ),
        );

        $existing_id = $this->find_existing_post( $external_id );
        try {
            $mapper  = new NFEdit_Feed_Field_Mapper();
            $post_id = $mapper->apply( $canonical );

            if ( ! $post_id || is_wp_error( $post_id ) ) {
                $summary['outcome'] = 'error';
                $summary['error']   = is_wp_error( $post_id )
                    ? $post_id->get_error_message()
                    : 'mapper returned no post_id';
                return $summary;
            }

            update_field( 'tier', $tier, $post_id );
            if ( ! empty( $merchant_pid ) ) {
                update_field( 'merchant_property_id', $merchant_pid, $post_id );
            }

            if ( get_post_status( $post_id ) !== 'draft' ) {
                wp_update_post( array(
                    'ID'          => $post_id,
                    'post_status' => 'draft',
                ) );
            }

            update_post_meta( $post_id, self::PHASE_MARKER_META, 1 );

            $summary['outcome'] = $existing_id ? 'updated' : 'created';
            $summary['post_id'] = $post_id;
            return $summary;

        } catch ( Exception $e ) {
            $summary['outcome'] = 'error';
            $summary['error']   = $e->getMessage();
            return $summary;
        }
    }

    private function resolve_merchant_slug( $row ) {
        if ( $row->auto_route === 'snaptrip' && ! empty( $row->snaptrip_listing_id ) ) {
            return 'snaptrip';
        }
        return 'cottages-com';
    }

    private function resolve_booking_url( $row, $merchant_slug ) {
        if ( $merchant_slug === 'snaptrip' && ! empty( $row->snaptrip_detail_url ) ) {
            return $row->snaptrip_detail_url;
        }
        return $row->merchant_deep_link;
    }

    private function resolve_merchant_property_id( $row, $merchant_slug ) {
        if ( $merchant_slug === 'snaptrip' && ! empty( $row->snaptrip_ref ) ) {
            return $row->snaptrip_ref;
        }
        return $row->merchant_product_id;
    }

    private function resolve_tier( $proposed_tier ) {
        if ( isset( self::$tier_map[ $proposed_tier ] ) ) {
            return self::$tier_map[ $proposed_tier ];
        }
        return 3;
    }

    private function load_area_term_names() {
        if ( $this->area_term_names !== null ) { return; }
        $terms = get_terms( array(
            'taxonomy'   => 'area_taxonomy',
            'hide_empty' => false,
        ) );
        $this->area_term_names = array();
        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $t ) {
                $this->area_term_names[ strtolower( $t->name ) ] = $t->name;
            }
        }
    }

    private function resolve_area_hint( $town ) {
        if ( empty( $town ) ) { return ''; }
        $this->load_area_term_names();
        $key = strtolower( trim( $town ) );
        if ( isset( $this->area_term_names[ $key ] ) ) {
            return $this->area_term_names[ $key ];
        }
        $key_dashed = str_replace( ' ', '-', $key );
        if ( isset( $this->area_term_names[ $key_dashed ] ) ) {
            return $this->area_term_names[ $key_dashed ];
        }
        $key_spaced = str_replace( '-', ' ', $key );
        if ( isset( $this->area_term_names[ $key_spaced ] ) ) {
            return $this->area_term_names[ $key_spaced ];
        }
        return '';
    }

    private function compose_address_line( $row ) {
        if ( ! empty( $row->address_line ) ) {
            return $row->address_line;
        }
        $parts = array();
        if ( ! empty( $row->town ) )     { $parts[] = $row->town; }
        if ( ! empty( $row->postcode ) ) { $parts[] = $row->postcode; }
        return implode( ', ', $parts );
    }

    private function find_existing_post( $external_id ) {
        global $wpdb;
        $row = $wpdb->get_var( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = 'feed_source_id' AND meta_value = %s
             LIMIT 1",
            $external_id
        ) );
        if ( $row ) {
            $post = get_post( $row );
            if ( $post && $post->post_type === 'property' ) {
                return (int) $row;
            }
        }
        return 0;
    }
}
