<?php
/**
 * Phase 13e.3: scrape Snaptrip property pages for gallery images via
 * the `data-images` HTML attribute (HTML-encoded JSON array), and
 * sideload via NFEdit_Feed_Image_Sideloader.
 *
 * For each Snaptrip-routed Phase 13e draft that DOESN'T already have
 * a 13e.2 cottages.com gallery:
 *
 *  1. Pull booking_url from ACF, un-wrap Awin's `ued=` parameter if present
 *  2. Fetch the raw Snaptrip URL (retry-on-non-200 + real-browser UA)
 *  3. Regex-extract the data-images attribute value
 *  4. html_entity_decode + json_decode
 *  5. For each entry, take the 'normal' URL (skip floorplans)
 *  6. Sideload (dedup'd via _nfedit_source_url)
 *  7. Update ACF gallery field with [hero, ...new_ids]
 *  8. Mark _nfedit_phase_13e3_snaptrip_gallery_scraped post meta
 *
 * Idempotent: re-running re-fetches but the image sideloader dedups,
 * so no duplicate attachments.
 *
 * PHP 7.4 compatible.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class NFEdit_Snaptrip_Gallery_Scraper {

    const PHASE_13E_MARKER       = '_nfedit_phase_13e_imported';
    const PHASE_13E2_MARKER      = '_nfedit_phase_13e2_gallery_scraped';
    const PHASE_13E3_MARKER      = '_nfedit_phase_13e3_snaptrip_gallery_scraped';
    const REQUEST_USER_AGENT     = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    const REQUEST_TIMEOUT_SEC    = 30;
    const RETRY_BACKOFF_SEC      = array( 5, 10, 20 );
    const INTER_REQUEST_DELAY_SEC = 3;

    public function scrape_all( $opts = array() ) {
        global $wpdb;

        $dry_run = ! empty( $opts['dry_run'] );
        $limit   = isset( $opts['limit'] )  ? (int) $opts['limit']  : 0;
        $offset  = isset( $opts['offset'] ) ? (int) $opts['offset'] : 0;

        $limit_clause = "";
        if ( $limit > 0 ) {
            $limit_clause = " LIMIT " . (int) $limit;
            if ( $offset > 0 ) {
                $limit_clause .= " OFFSET " . (int) $offset;
            }
        } elseif ( $offset > 0 ) {
            $limit_clause = " LIMIT 1000 OFFSET " . (int) $offset;
        }

        $rows = $wpdb->get_results( $wpdb->prepare(
            "SELECT p.ID AS post_id, p.post_title, pm_book.meta_value AS booking_url
             FROM {$wpdb->posts} p
             JOIN {$wpdb->postmeta} pm_merch    ON pm_merch.post_id    = p.ID AND pm_merch.meta_key    = 'merchant'                    AND pm_merch.meta_value    = 'snaptrip'
             JOIN {$wpdb->postmeta} pm_book     ON pm_book.post_id     = p.ID AND pm_book.meta_key     = 'booking_url'
             JOIN {$wpdb->postmeta} pm_marker   ON pm_marker.post_id   = p.ID AND pm_marker.meta_key   = %s                            AND pm_marker.meta_value   = '1'
             LEFT JOIN {$wpdb->postmeta} pm_g13e2 ON pm_g13e2.post_id = p.ID AND pm_g13e2.meta_key = %s
             WHERE p.post_type   = 'property'
               AND p.post_status = 'draft'
               AND pm_g13e2.post_id IS NULL
             ORDER BY p.ID" . $limit_clause,
            self::PHASE_13E_MARKER,
            self::PHASE_13E2_MARKER
        ) );

        $stats = array(
            'total'                 => count( $rows ),
            'processed'             => 0,
            'fetched_ok'            => 0,
            'fetch_failed'          => 0,
            'no_data_images_attr'   => 0,
            'json_parse_failed'     => 0,
            'images_in_data_attr'   => 0,
            'images_filtered_floor' => 0,
            'images_sideloaded_new' => 0,
            'images_dedup_existing' => 0,
            'images_failed'         => 0,
            'gallery_field_updated' => 0,
            'errors'                => 0,
        );

        $sideloader = new NFEdit_Feed_Image_Sideloader();

        foreach ( $rows as $row ) {
            $stats['processed']++;

            $raw_url = $this->unwrap_awin( $row->booking_url );
            if ( empty( $raw_url ) ) {
                $stats['errors']++;
                printf( "  %3d. post#%-4d %-30s | ERROR: empty booking_url\n",
                    $stats['processed'], $row->post_id, substr( $row->post_title, 0, 30 ) );
                if ( function_exists( 'fflush' ) ) { @fflush( STDOUT ); }
                continue;
            }

            $fetch = $this->fetch_page_with_retry( $raw_url );
            if ( ! $fetch['ok'] ) {
                $stats['fetch_failed']++;
                printf( "  %3d. post#%-4d %-30s | FETCH FAILED (HTTP %d, %d attempts)\n",
                    $stats['processed'], $row->post_id, substr( $row->post_title, 0, 30 ),
                    $fetch['final_code'], $fetch['attempts'] );
                if ( function_exists( 'fflush' ) ) { @fflush( STDOUT ); }
                $this->sleep_between();
                continue;
            }
            $stats['fetched_ok']++;

            $image_urls = $this->parse_data_images( $fetch['body'], $stats );

            $stats['images_in_data_attr'] += count( $image_urls );

            if ( $dry_run ) {
                printf( "  %3d. post#%-4d %-30s | %d gallery images discovered (dry-run)\n",
                    $stats['processed'], $row->post_id, substr( $row->post_title, 0, 30 ),
                    count( $image_urls ) );
                if ( function_exists( 'fflush' ) ) { @fflush( STDOUT ); }
                $this->sleep_between();
                continue;
            }

            $attachment_ids = array();
            $new_count = 0;
            $dedup_count = 0;
            foreach ( $image_urls as $img_url ) {
                $existing_id = $this->find_existing_attachment_by_source( $img_url );
                if ( $existing_id ) {
                    $attachment_ids[] = $existing_id;
                    $stats['images_dedup_existing']++;
                    $dedup_count++;
                    continue;
                }
                try {
                    $att_id = $sideloader->sideload(
                        $img_url,
                        basename( parse_url( $img_url, PHP_URL_PATH ) ),
                        $row->post_id
                    );
                    if ( $att_id && ! is_wp_error( $att_id ) ) {
                        $attachment_ids[] = (int) $att_id;
                        $stats['images_sideloaded_new']++;
                        $new_count++;
                    } else {
                        $stats['images_failed']++;
                    }
                } catch ( Exception $e ) {
                    $stats['images_failed']++;
                    error_log( "Phase 13e.3 sideload error post {$row->post_id} url {$img_url}: " . $e->getMessage() );
                }
            }

            $hero_id = (int) get_post_thumbnail_id( $row->post_id );
            if ( $hero_id && ! in_array( $hero_id, $attachment_ids, true ) ) {
                array_unshift( $attachment_ids, $hero_id );
            }

            $attachment_ids = array_values( array_unique( array_filter( $attachment_ids ) ) );

            if ( ! empty( $attachment_ids ) ) {
                $ok = update_field( 'field_nfedit_property_gallery', $attachment_ids, $row->post_id );
                if ( $ok ) {
                    $stats['gallery_field_updated']++;
                }
            }

            update_post_meta( $row->post_id, self::PHASE_13E3_MARKER, count( $attachment_ids ) );

            printf( "  %3d. post#%-4d %-30s | data-images=%d  new=%d  dedup=%d  fail=%d  gallery=%d\n",
                $stats['processed'], $row->post_id, substr( $row->post_title, 0, 30 ),
                count( $image_urls ), $new_count, $dedup_count, $stats['images_failed'],
                count( $attachment_ids )
            );
            if ( function_exists( 'fflush' ) ) { @fflush( STDOUT ); }

            $this->sleep_between();
        }
        return $stats;
    }

    private function unwrap_awin( $url ) {
        if ( strpos( $url, 'awin1.com' ) === false ) {
            return $url;
        }
        $q = parse_url( $url, PHP_URL_QUERY );
        if ( empty( $q ) ) { return $url; }
        parse_str( $q, $params );
        if ( ! empty( $params['ued'] ) ) {
            return urldecode( $params['ued'] );
        }
        return $url;
    }

    private function fetch_page_with_retry( $url ) {
        $final_code = 0;
        $attempts = 0;
        foreach ( array_merge( array( 0 ), self::RETRY_BACKOFF_SEC ) as $idx => $backoff ) {
            $attempts++;
            if ( $backoff > 0 ) {
                sleep( $backoff );
            }
            $response = wp_remote_get( $url, array(
                'timeout'     => self::REQUEST_TIMEOUT_SEC,
                'user-agent'  => self::REQUEST_USER_AGENT,
                'redirection' => 5,
                'sslverify'   => true,
                'headers'     => array(
                    'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-GB,en;q=0.5',
                ),
            ) );
            if ( is_wp_error( $response ) ) {
                $final_code = 0;
                continue;
            }
            $code = wp_remote_retrieve_response_code( $response );
            $final_code = $code;
            if ( $code == 200 ) {
                $body = wp_remote_retrieve_body( $response );
                if ( strlen( $body ) > 50000 ) {
                    return array( 'ok' => true, 'body' => $body, 'final_code' => $code, 'attempts' => $attempts );
                }
            }
        }
        return array( 'ok' => false, 'body' => '', 'final_code' => $final_code, 'attempts' => $attempts );
    }

    private function parse_data_images( $html, &$stats ) {
        if ( ! preg_match( "#data-images=(['\"])(.+?)\\1#s", $html, $m ) ) {
            $stats['no_data_images_attr']++;
            return array();
        }
        $encoded = $m[2];
        $decoded = html_entity_decode( $encoded, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        $arr     = json_decode( $decoded, true );
        if ( ! is_array( $arr ) ) {
            $stats['json_parse_failed']++;
            return array();
        }
        $urls = array();
        foreach ( $arr as $entry ) {
            if ( ! is_array( $entry ) ) { continue; }
            if ( ! empty( $entry['floorplan'] ) ) {
                $stats['images_filtered_floor']++;
                continue;
            }
            if ( ! empty( $entry['normal'] ) ) {
                $urls[] = $entry['normal'];
            } elseif ( ! empty( $entry['hero'] ) ) {
                $urls[] = $entry['hero'];
            } elseif ( ! empty( $entry['thumb'] ) ) {
                $urls[] = $entry['thumb'];
            }
        }
        return array_values( array_unique( $urls ) );
    }

    private function find_existing_attachment_by_source( $url ) {
        global $wpdb;
        $url_stripped = preg_replace( '/\?.*$/', '', $url );
        $id = $wpdb->get_var( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = '_nfedit_source_url' AND meta_value = %s
             LIMIT 1",
            $url_stripped
        ) );
        return $id ? (int) $id : 0;
    }

    private function sleep_between() {
        sleep( self::INTER_REQUEST_DELAY_SEC );
    }
}
