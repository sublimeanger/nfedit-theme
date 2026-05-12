<?php
/**
 * Phase 13e.2: scrape cottages.com property pages for full galleries
 * and sideload via NFEdit_Feed_Image_Sideloader.
 *
 * For each Phase 13e draft:
 *  1. Construct cottages.com URL from merchant_product_id
 *  2. Fetch page (retry-on-202 with exponential backoff + real-browser UA)
 *  3. Regex-parse img.chooseacottage.co.uk URLs
 *  4. Filter to dominant-owner images only (drops recommended-cottage thumbnails)
 *  5. Dedup by image_id, keeping the largest width per image
 *  6. Sideload each via NFEdit_Feed_Image_Sideloader (dedup'd by source URL)
 *  7. Update ACF gallery field with all attachment IDs
 *  8. Mark with _nfedit_phase_13e2_gallery_scraped post meta
 *
 * Idempotent: re-running re-fetches but sideloader dedups via
 * _nfedit_source_url; gallery field is rewritten with same content.
 *
 * PHP 7.4 compatible.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class NFEdit_Cottages_Com_Gallery_Scraper {

    const PHASE_13E_MARKER       = '_nfedit_phase_13e_imported';
    const PHASE_13E2_MARKER      = '_nfedit_phase_13e2_gallery_scraped';
    const REQUEST_USER_AGENT     = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    const REQUEST_TIMEOUT_SEC    = 30;
    const RETRY_BACKOFF_SEC      = array( 5, 10, 20 );
    const INTER_REQUEST_DELAY_SEC = 8;  // increased from 2 after PART D revealed ~62% fetch failure rate at 2-sec cadence (cottages.com bot blocking)

    public function scrape_all( $opts = array() ) {
        global $wpdb;

        $dry_run = ! empty( $opts['dry_run'] );
        $limit   = isset( $opts['limit'] )  ? (int) $opts['limit']  : 0;
        $offset  = isset( $opts['offset'] ) ? (int) $opts['offset'] : 0;

        $sql = "SELECT id, merchant_product_id, title FROM wp_nfedit_cottages_com_inventory ORDER BY id";
        if ( $limit > 0 ) {
            $sql .= " LIMIT " . (int) $limit;
            if ( $offset > 0 ) {
                $sql .= " OFFSET " . (int) $offset;
            }
        } elseif ( $offset > 0 ) {
            // OFFSET without LIMIT requires a LIMIT in MySQL — use a huge number
            $sql .= " LIMIT 1000 OFFSET " . (int) $offset;
        }
        $rows = $wpdb->get_results( $sql );

        $stats = array(
            'total'                 => count( $rows ),
            'processed'             => 0,
            'matched_draft'         => 0,
            'no_draft_found'        => 0,
            'fetched_ok'            => 0,
            'fetch_failed'          => 0,
            'fetch_blocked_202'     => 0,
            'images_discovered'     => 0,
            'images_sideloaded_new' => 0,
            'images_dedup_existing' => 0,
            'images_failed'         => 0,
            'gallery_field_updated' => 0,
            'errors'                => 0,
        );

        $sideloader = new NFEdit_Feed_Image_Sideloader();

        foreach ( $rows as $row ) {
            $stats['processed']++;
            $external_id = 'cottages-com:' . $row->merchant_product_id;
            $post_id = $this->find_draft_post( $external_id );

            if ( ! $post_id ) {
                $stats['no_draft_found']++;
                continue;
            }
            $stats['matched_draft']++;

            $url = 'https://www.cottages.com/cottages/' . rawurlencode( $row->merchant_product_id );

            $fetch_result = $this->fetch_page_with_retry( $url );
            if ( ! $fetch_result['ok'] ) {
                if ( $fetch_result['final_code'] == 202 ) {
                    $stats['fetch_blocked_202']++;
                } else {
                    $stats['fetch_failed']++;
                }
                printf( "  %3d. cc#%-3d post#%-4d %-30s | FETCH FAILED (HTTP %s, %d attempts)\n",
                    $stats['processed'], $row->id, $post_id,
                    substr( $row->title, 0, 30 ),
                    $fetch_result['final_code'], $fetch_result['attempts'] );
                if ( function_exists( 'fflush' ) ) { fflush( STDOUT ); }
                continue;
            }
            $stats['fetched_ok']++;

            $image_urls = $this->parse_image_urls( $fetch_result['body'] );
            $stats['images_discovered'] += count( $image_urls );

            if ( $dry_run ) {
                printf( "  %3d. cc#%-3d post#%-4d %-30s | %d images discovered (dry-run)\n",
                    $stats['processed'], $row->id, $post_id,
                    substr( $row->title, 0, 30 ), count( $image_urls ) );
                if ( function_exists( 'fflush' ) ) { fflush( STDOUT ); }
                if ( $stats['processed'] < count( $rows ) ) {
                    sleep( self::INTER_REQUEST_DELAY_SEC );
                }
                continue;
            }

            $hero_id = (int) get_post_thumbnail_id( $post_id );
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
                    $att_id = $sideloader->sideload( $img_url, basename( parse_url( $img_url, PHP_URL_PATH ) ), $post_id );
                    if ( $att_id && ! is_wp_error( $att_id ) ) {
                        $attachment_ids[] = (int) $att_id;
                        $stats['images_sideloaded_new']++;
                        $new_count++;
                    } else {
                        $stats['images_failed']++;
                    }
                } catch ( Exception $e ) {
                    $stats['images_failed']++;
                    error_log( "Phase 13e.2 sideload error for post {$post_id} url {$img_url}: " . $e->getMessage() );
                }
            }

            if ( $hero_id && ! in_array( $hero_id, $attachment_ids, true ) ) {
                array_unshift( $attachment_ids, $hero_id );
            }

            $attachment_ids = array_values( array_unique( array_filter( $attachment_ids ) ) );

            if ( ! empty( $attachment_ids ) ) {
                $update_result = update_field( 'field_nfedit_property_gallery', $attachment_ids, $post_id );
                if ( $update_result ) {
                    $stats['gallery_field_updated']++;
                }
            }

            update_post_meta( $post_id, self::PHASE_13E2_MARKER, count( $attachment_ids ) );

            printf( "  %3d. cc#%-3d post#%-4d %-30s | discovered=%d  new=%d  dedup=%d  failed=%d  gallery_size=%d\n",
                $stats['processed'], $row->id, $post_id,
                substr( $row->title, 0, 30 ),
                count( $image_urls ), $new_count, $dedup_count, $stats['images_failed'],
                count( $attachment_ids )
            );
            if ( function_exists( 'fflush' ) ) { fflush( STDOUT ); }

            if ( $stats['processed'] < count( $rows ) ) {
                sleep( self::INTER_REQUEST_DELAY_SEC );
            }
        }
        return $stats;
    }

    private function fetch_page_with_retry( $url ) {
        $final_code = 0;
        foreach ( array_merge( array( 0 ), self::RETRY_BACKOFF_SEC ) as $attempt_idx => $backoff ) {
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
                    return array( 'ok' => true, 'body' => $body, 'final_code' => $code, 'attempts' => $attempt_idx + 1 );
                }
            }
        }
        return array( 'ok' => false, 'body' => '', 'final_code' => $final_code, 'attempts' => 1 + count( self::RETRY_BACKOFF_SEC ) );
    }

    /**
     * Parse cottages.com image URLs from the page HTML.
     * - Filter to dominant-owner images (drops recommended-cottage thumbnails)
     * - Dedup by image_id, keeping the largest width per image
     */
    private function parse_image_urls( $html ) {
        preg_match_all(
            '#https://img\.chooseacottage\.co\.uk/property/(\d+)/(\d+)/(\d+)\.(jpg|jpeg|png|webp)#i',
            $html,
            $matches,
            PREG_SET_ORDER
        );
        if ( empty( $matches ) ) {
            return array();
        }

        // Count URL occurrences per owner_id to identify the page's primary cottage
        $owner_counts = array();
        foreach ( $matches as $m ) {
            $owner = $m[1];
            $owner_counts[ $owner ] = isset( $owner_counts[ $owner ] ) ? $owner_counts[ $owner ] + 1 : 1;
        }
        if ( empty( $owner_counts ) ) {
            return array();
        }
        arsort( $owner_counts );
        // The most-mentioned owner is the cottage's own; everything else is page chrome
        $primary_owner = (string) array_key_first( $owner_counts );

        // For each unique image_id under primary_owner, keep the largest-width URL
        $best_per_image = array();
        foreach ( $matches as $m ) {
            if ( (string) $m[1] !== $primary_owner ) {
                continue;
            }
            $width = (int) $m[2];
            $imgid = $m[3];
            if ( ! isset( $best_per_image[ $imgid ] ) || $width > $best_per_image[ $imgid ]['width'] ) {
                $best_per_image[ $imgid ] = array( 'url' => $m[0], 'width' => $width );
            }
        }

        $urls = array();
        foreach ( $best_per_image as $entry ) {
            $urls[] = $entry['url'];
        }
        return $urls;
    }

    private function find_draft_post( $external_id ) {
        global $wpdb;
        $post_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = 'feed_source_id' AND meta_value = %s
             LIMIT 1",
            $external_id
        ) );
        if ( $post_id ) {
            $marker = get_post_meta( $post_id, self::PHASE_13E_MARKER, true );
            if ( $marker ) {
                return (int) $post_id;
            }
        }
        return 0;
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
}
