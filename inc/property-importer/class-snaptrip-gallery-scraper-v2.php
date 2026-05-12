<?php
/**
 * NFEdit_Snaptrip_Gallery_Scraper_V2 — Phase 13e.3.1 patched.
 *
 * Differences vs v1:
 *  - data-images parsing extracts UUID + listing_id (regex was numeric in v1, but
 *    image IDs are actually UUIDs; that error is benign in v1 because v1 only
 *    deduped by full URL string).
 *  - URL synthesis drops the `normal_` prefix entirely. Yields 2598x1949 source.
 *  - Group entries by listing_id; if multiple groups, keep only the LARGEST,
 *    tie-break on highest-numbered listing_id (newest re-list).
 *  - Per-image dimension log captured into stats for runtime sanity checks.
 *
 * PHP 7.4.33 compatible.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class NFEdit_Snaptrip_Gallery_Scraper_V2 {

    /** Fetch the snaptrip property page HTML. Returns null on failure. */
    private function fetch_html( $url ) {
        $args = array(
            'timeout'    => 30,
            'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
            'headers'    => array(
                'Accept'          => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'en-GB,en;q=0.9',
            ),
        );
        $resp = wp_remote_get( $url, $args );
        if ( is_wp_error( $resp ) ) { return null; }
        $code = (int) wp_remote_retrieve_response_code( $resp );
        if ( 200 !== $code ) { return null; }
        return wp_remote_retrieve_body( $resp );
    }

    /**
     * Parse data-images JSON from page HTML.
     * Returns [ [ 'listing_id'=>str, 'uuid'=>str, 'ext'=>str ], ... ] after group dedup.
     */
    public function parse_data_images( $html, &$telemetry_ref = null ) {
        $telemetry_ref = array(
            'raw_entries'      => 0,
            'parsed_entries'   => 0,
            'group_count'      => 0,
            'group_sizes'      => array(),
            'chosen_group_lid' => null,
        );

        if ( ! preg_match( '/data-images=([\'"])(.+?)\1/s', $html, $m ) ) {
            return array();
        }
        $json = html_entity_decode( $m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        $entries = json_decode( $json, true );
        if ( ! is_array( $entries ) ) { return array(); }

        $telemetry_ref['raw_entries'] = count( $entries );

        $parsed = array();
        $re = '#/file/(\d+)/(?:normal|hero|thumb|cap|floorplan)_([a-f0-9-]+)\.(jpe?g|png|webp)$#i';
        foreach ( $entries as $entry ) {
            if ( empty( $entry['normal'] ) ) { continue; }
            if ( ! preg_match( $re, $entry['normal'], $mm ) ) { continue; }
            $parsed[] = array(
                'listing_id' => $mm[1],
                'uuid'       => $mm[2],
                'ext'        => strtolower( $mm[3] ),
            );
        }
        $telemetry_ref['parsed_entries'] = count( $parsed );
        if ( empty( $parsed ) ) { return array(); }

        // Cluster by listing_id proximity. The numeric in /file/{n}/ is per-image-asset,
        // not per-property-listing; properties uploaded in one batch get sequential IDs.
        // When Snaptrip re-listed a property, the new batch lives millions of IDs higher.
        // Sort ascending, split at any gap > 1,000,000.
        usort( $parsed, function( $a, $b ) {
            return ( (int) $a['listing_id'] ) - ( (int) $b['listing_id'] );
        } );

        $clusters = array( array() );
        $prev_lid = null;
        foreach ( $parsed as $row ) {
            $lid_int = (int) $row['listing_id'];
            if ( $prev_lid !== null && ( $lid_int - $prev_lid ) > 1000000 ) {
                $clusters[] = array();
            }
            $clusters[ count( $clusters ) - 1 ][] = $row;
            $prev_lid = $lid_int;
        }

        $telemetry_ref['group_count'] = count( $clusters );
        $telemetry_ref['group_sizes'] = array();
        foreach ( $clusters as $cluster ) {
            $sz = count( $cluster );
            $first_lid = ! empty( $cluster ) ? $cluster[0]['listing_id'] : '?';
            $last_lid  = ! empty( $cluster ) ? end( $cluster )['listing_id'] : '?';
            $telemetry_ref['group_sizes'][] = sprintf( '%s-%s:%d', $first_lid, $last_lid, $sz );
        }

        // Pick largest cluster; tie-break on max listing_id in cluster (newest re-list).
        $best_idx = 0;
        $best_count = -1;
        $best_max_lid = -1;
        foreach ( $clusters as $i => $cluster ) {
            $count = count( $cluster );
            $cluster_max = 0;
            foreach ( $cluster as $r ) {
                $cl = (int) $r['listing_id'];
                if ( $cl > $cluster_max ) { $cluster_max = $cl; }
            }
            if ( $count > $best_count || ( $count === $best_count && ( $best_max_lid === -1 || $cluster_max < $best_max_lid ) ) ) {
                $best_idx = $i;
                $best_count = $count;
                $best_max_lid = $cluster_max;
            }
        }
        $telemetry_ref['chosen_group_lid'] = (string) $best_max_lid;

        return $this->dedup_by_uuid( $clusters[ $best_idx ] );
    }

    private function dedup_by_uuid( $rows ) {
        $seen = array();
        $out  = array();
        foreach ( $rows as $row ) {
            if ( isset( $seen[ $row['uuid'] ] ) ) { continue; }
            $seen[ $row['uuid'] ] = true;
            $out[] = $row;
        }
        return $out;
    }

    /** No-prefix URL — yields full-size source. */
    public function build_image_url( $listing_id, $uuid, $ext ) {
        return sprintf(
            'https://images.snaptrip.com/optim/image/file/%s/%s.%s',
            $listing_id,
            $uuid,
            $ext
        );
    }

    /** Unwrap Awin cread.php ued= parameter to recover raw URL. */
    private function unwrap_booking_url( $url ) {
        if ( empty( $url ) ) { return null; }
        if ( strpos( $url, 'awin1.com' ) !== false ) {
            $parts = wp_parse_url( $url );
            if ( ! empty( $parts['query'] ) ) {
                parse_str( $parts['query'], $q );
                if ( ! empty( $q['ued'] ) ) { return $q['ued']; }
            }
        }
        return $url;
    }

    /**
     * Process one cottage. Returns stats array (never throws — caller logs).
     */
    public function process_cottage( $post_id ) {
        $stats = array(
            'post_id'           => (int) $post_id,
            'fetch_ok'          => false,
            'gallery_size'      => 0,
            'raw_entries'       => 0,
            'parsed_entries'    => 0,
            'group_count'       => 0,
            'chosen_group_lid'  => null,
            'dropped_count'     => 0,
            'sideload_fails'    => 0,
            'min_width'         => null,
            'max_width'         => null,
        );

        // Snaptrip drafts store the booking URL as `booking_url` (not `awin_booking_url`,
        // which is the Shorefield published-cottage key). Verified across all 46 13e.3
        // markers via 13e.3.1 PART A pre-flight (booking_url=46, awin_booking_url=0).
        $url = get_post_meta( $post_id, 'booking_url', true );
        $raw_url = $this->unwrap_booking_url( $url );
        if ( empty( $raw_url ) ) { return $stats; }

        $html = $this->fetch_html( $raw_url );
        if ( null === $html ) { return $stats; }
        $stats['fetch_ok'] = true;

        $telemetry = array();
        $kept = $this->parse_data_images( $html, $telemetry );

        $stats['raw_entries']      = $telemetry['raw_entries'];
        $stats['parsed_entries']   = $telemetry['parsed_entries'];
        $stats['group_count']      = $telemetry['group_count'];
        $stats['chosen_group_lid'] = $telemetry['chosen_group_lid'];
        $stats['dropped_count']    = max( 0, $telemetry['parsed_entries'] - count( $kept ) );

        if ( empty( $kept ) ) { return $stats; }

        $sideloader = new NFEdit_Feed_Image_Sideloader();
        $attachment_ids = array();
        $widths = array();
        foreach ( $kept as $row ) {
            $url    = $this->build_image_url( $row['listing_id'], $row['uuid'], $row['ext'] );
            $att_id = $sideloader->sideload( $url, basename( parse_url( $url, PHP_URL_PATH ) ), $post_id );

            if ( ! $att_id || is_wp_error( $att_id ) ) {
                $stats['sideload_fails']++;
                fflush( STDOUT );
                continue;
            }
            $attachment_ids[] = (int) $att_id;

            $meta = wp_get_attachment_metadata( $att_id );
            if ( is_array( $meta ) && isset( $meta['width'] ) ) {
                $widths[] = (int) $meta['width'];
            }
            fflush( STDOUT );
        }

        if ( ! empty( $widths ) ) {
            $stats['min_width'] = min( $widths );
            $stats['max_width'] = max( $widths );
        }

        if ( ! empty( $attachment_ids ) ) {
            update_field( 'field_nfedit_property_gallery', $attachment_ids, $post_id );
        }

        update_post_meta( $post_id, '_nfedit_phase_13e3_snaptrip_gallery_scraped', count( $attachment_ids ) );
        update_post_meta( $post_id, '_nfedit_phase_13e3_1_snaptrip_gallery_fixed', count( $attachment_ids ) );

        $stats['gallery_size'] = count( $attachment_ids );
        return $stats;
    }
}
