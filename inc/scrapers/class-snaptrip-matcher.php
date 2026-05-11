<?php
/**
 * Snaptrip New Forest matcher scraper.
 *
 * Permission to scrape granted in writing by David Ellis, Head of Marketing,
 * Snaptrip, on 11 May 2026.
 *
 * Two-pass scraper:
 *   1. crawl_index() — paginates /holiday-cottages/new-forest?page=N,
 *      extracts result-card data (incl. lat/lng from data-lat/data-lng on the
 *      card root div), upserts to wp_nfedit_snaptrip_listings with
 *      detail_fetched_at = NULL.
 *   2. crawl_details() — for each row where detail_fetched_at IS NULL,
 *      fetches the detail page, extracts postcode (and lat/lng as fallback if
 *      somehow missing from index), updates the row and sets detail_fetched_at.
 *
 * PHP 7.4 compatible.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class NFEdit_Snaptrip_Matcher {

    const BASE_URL          = 'https://www.snaptrip.com';
    const INDEX_PATH        = '/holiday-cottages/new-forest';
    const TABLE_NAME        = 'wp_nfedit_snaptrip_listings';
    const REQUEST_DELAY_MIN = 2;
    const REQUEST_DELAY_MAX = 3;
    const MAX_PAGES         = 30;
    const USER_AGENT        = 'NewForestEditMatcher/1.0 (+https://newforestedit.co.uk; permission: David Ellis, Snaptrip, 2026-05-11; contact: jamie@searchflare.co.uk)';

    private $log_file;
    private $stats = array(
        'pages_fetched'    => 0,
        'cards_seen'       => 0,
        'cards_upserted'   => 0,
        'details_fetched'  => 0,
        'details_failed'   => 0,
        'http_errors'      => 0,
    );

    public function __construct() {
        $upload = wp_upload_dir();
        $this->log_file = trailingslashit( $upload['basedir'] ) . 'snaptrip-matcher.log';
    }

    public function log( $msg ) {
        $line = '[' . gmdate( 'Y-m-d H:i:s' ) . 'Z] ' . $msg . "\n";
        file_put_contents( $this->log_file, $line, FILE_APPEND );
        echo $line;
    }

    public function get_stats() {
        return $this->stats;
    }

    /* ------------------------------------------------------------------
     * HTTP
     * ------------------------------------------------------------------ */

    private function polite_delay() {
        $min = (int) ( self::REQUEST_DELAY_MIN * 1000 );
        $max = (int) ( self::REQUEST_DELAY_MAX * 1000 );
        $ms  = mt_rand( $min, $max );
        usleep( $ms * 1000 );
    }

    private function fetch_url( $url ) {
        $this->polite_delay();
        $ch = curl_init( $url );
        curl_setopt_array( $ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_USERAGENT      => self::USER_AGENT,
            CURLOPT_HTTPHEADER     => array(
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-GB,en;q=0.9',
                'Accept-Encoding: gzip, deflate',
            ),
            CURLOPT_ENCODING       => '',
            CURLOPT_SSL_VERIFYPEER => true,
        ) );
        $body = curl_exec( $ch );
        $code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        $err  = curl_error( $ch );
        curl_close( $ch );

        if ( $body === false || $code >= 400 ) {
            $this->stats['http_errors']++;
            $this->log( "HTTP ERROR [{$code}] {$url} — {$err}" );
            return null;
        }
        return $body;
    }

    /* ------------------------------------------------------------------
     * Pass 1: crawl listing pages
     * ------------------------------------------------------------------ */

    public function crawl_index() {
        $this->log( '=== INDEX PASS START ===' );
        $page = 1;
        $cards_last_page = -1;
        $consecutive_empty = 0;

        while ( $page <= self::MAX_PAGES ) {
            $url = self::BASE_URL . self::INDEX_PATH . '?page=' . $page;
            $this->log( "Fetching index page {$page}: {$url}" );
            $html = $this->fetch_url( $url );
            $this->stats['pages_fetched']++;

            if ( ! $html ) {
                $this->log( "Page {$page} fetch failed — skipping" );
                $page++;
                continue;
            }

            $cards = $this->parse_index_page( $html );
            $this->log( "Page {$page}: parsed " . count( $cards ) . ' cards' );

            if ( empty( $cards ) ) {
                $consecutive_empty++;
                if ( $consecutive_empty >= 2 ) {
                    $this->log( "Two consecutive empty pages — assuming pagination exhausted" );
                    break;
                }
            } else {
                $consecutive_empty = 0;
            }

            foreach ( $cards as $card ) {
                $this->upsert_index_card( $card );
            }

            if ( count( $cards ) > 0 && count( $cards ) < 10 && $cards_last_page >= 20 ) {
                $this->log( 'Short page — likely final page reached' );
                $page++;
                break;
            }
            $cards_last_page = count( $cards );

            $page++;
        }
        $this->log( '=== INDEX PASS DONE ===' );
    }

    private function parse_index_page( $html ) {
        $cards = array();

        libxml_use_internal_errors( true );
        $dom = new DOMDocument();
        $dom->loadHTML( '<?xml encoding="UTF-8">' . $html );
        libxml_clear_errors();
        $xp = new DOMXPath( $dom );

        $links = $xp->query( '//a[starts-with(@href, "/properties/")]' );
        if ( ! $links || $links->length === 0 ) {
            return $cards;
        }

        $seen = array();
        foreach ( $links as $a ) {
            $href = $a->getAttribute( 'href' );
            if ( ! preg_match( '#^/properties/united-kingdom/england/south-east/hampshire/new-forest/#i', $href ) ) {
                continue;
            }
            if ( isset( $seen[ $href ] ) ) { continue; }
            $seen[ $href ] = true;

            $card_root = $this->ascend_to_card( $a );
            if ( ! $card_root ) { continue; }

            $card_text = $card_root->textContent;

            $ref = null;
            if ( preg_match( '/Ref:\s*(S\d+)/', $card_text, $m ) ) {
                $ref = $m[1];
            }
            if ( ! $ref ) {
                continue;
            }

            $title = trim( $a->getAttribute( 'title' ) );
            if ( ! $title ) {
                $h = $xp->query( './/h2|.//h3|.//h4', $card_root );
                if ( $h && $h->length ) {
                    // Skip the "Ref: S..." h4 — pick the longest non-ref heading
                    $best = '';
                    foreach ( $h as $hn ) {
                        $t = trim( $hn->textContent );
                        if ( strpos( $t, 'Ref:' ) === 0 ) { continue; }
                        if ( strlen( $t ) > strlen( $best ) ) { $best = $t; }
                    }
                    $title = $best;
                }
            }

            $town_slug = null;
            if ( preg_match( '#/new-forest/([^/]+)(?:/([^/]+))?/[^/]+$#', $href, $m ) ) {
                $town_slug = ( isset( $m[2] ) && $m[2] !== '' ) ? $m[2] : $m[1];
            }

            // Lat/lng — Snaptrip exposes these on the card root div as
            // data-lat / data-lng. No detail fetch needed for coordinates.
            $latitude  = null;
            $longitude = null;
            if ( $card_root->hasAttribute( 'data-lat' ) ) {
                $v = $card_root->getAttribute( 'data-lat' );
                if ( is_numeric( $v ) ) { $latitude = (float) $v; }
            }
            if ( $card_root->hasAttribute( 'data-lng' ) ) {
                $v = $card_root->getAttribute( 'data-lng' );
                if ( is_numeric( $v ) ) { $longitude = (float) $v; }
            }

            $image_url = null;
            $partner_brand = null;
            $imgs = $xp->query( './/img', $card_root );
            if ( $imgs && $imgs->length ) {
                $first = $imgs->item( 0 );
                $src = $first->getAttribute( 'src' );
                if ( ! $src ) {
                    $src = $first->getAttribute( 'data-src' );
                }
                if ( $src ) { $image_url = $src; }
                $alt = $first->getAttribute( 'alt' );
                if ( $alt && preg_match( '/^([^-]+?)\s*-\s*/', $alt, $am ) ) {
                    $partner_brand = trim( $am[1] );
                }
            }

            $bedrooms = null;
            $sleeps   = null;
            // Prefer explicit Snaptrip card_beds / card_sleeps divs.
            $beds_nodes = $xp->query( './/*[contains(concat(" ", normalize-space(@class), " "), " card_beds ")]', $card_root );
            if ( $beds_nodes && $beds_nodes->length ) {
                if ( preg_match( '/(\d+)/', $beds_nodes->item( 0 )->textContent, $bm ) ) {
                    $bedrooms = (int) $bm[1];
                }
            }
            $sleep_nodes = $xp->query( './/*[contains(concat(" ", normalize-space(@class), " "), " card_sleeps ")]', $card_root );
            if ( $sleep_nodes && $sleep_nodes->length ) {
                if ( preg_match( '/(\d+)/', $sleep_nodes->item( 0 )->textContent, $sm ) ) {
                    $sleeps = (int) $sm[1];
                }
            }
            // Fallback: integer heuristic on cleaned card text, excluding the
            // card_maxpet (pets) and any review count we already pattern out.
            if ( $bedrooms === null || $sleeps === null ) {
                $clean_text = $card_text;
                // Strip the pet count by stripping the entire card_maxpet block's text
                $pet_nodes = $xp->query( './/*[contains(concat(" ", normalize-space(@class), " "), " card_maxpet ")]', $card_root );
                if ( $pet_nodes && $pet_nodes->length ) {
                    $clean_text = str_replace( $pet_nodes->item( 0 )->textContent, ' ', $clean_text );
                }
                $clean_text = preg_replace( '/Ref:\s*S\d+/i', ' ', $clean_text );
                $clean_text = preg_replace( '/Reviews\s*\*?\*?\d+/i', ' ', $clean_text );
                $clean_text = preg_replace( '/\xC2\xA3\d[\d,\.]*/', ' ', $clean_text );
                $clean_text = preg_replace( '/per night/i', ' ', $clean_text );
                if ( preg_match_all( '/(?<![\d.])([1-9]\d?)(?![\d.])/', $clean_text, $nm ) ) {
                    $nums = array_values( array_unique( array_map( 'intval', $nm[1] ) ) );
                    if ( count( $nums ) >= 2 ) {
                        if ( $bedrooms === null ) { $bedrooms = $nums[0]; }
                        if ( $sleeps   === null ) { $sleeps   = $nums[1]; }
                        if ( $sleeps !== null && $bedrooms !== null && $sleeps < $bedrooms ) {
                            $tmp = $sleeps; $sleeps = $bedrooms; $bedrooms = $tmp;
                        }
                    }
                }
            }

            $price = null;
            if ( preg_match( '/From\s*£\s*([\d,]+)(?:\.\d+)?\s*per night/i', $card_text, $m ) ) {
                $price = (float) str_replace( ',', '', $m[1] );
            }

            $review_count = null;
            if ( preg_match( '/Reviews\s*\*?\*?\s*(\d+)/i', $card_text, $m ) ) {
                $review_count = (int) $m[1];
            }

            $snippet = null;
            $ps = $xp->query( './/text()[normalize-space(.)]', $card_root );
            $longest = '';
            if ( $ps ) {
                foreach ( $ps as $tn ) {
                    $t = trim( $tn->textContent );
                    if ( strlen( $t ) > strlen( $longest ) ) {
                        $longest = $t;
                    }
                }
            }
            if ( $longest && strlen( $longest ) > 40 ) {
                $snippet = substr( $longest, 0, 400 );
            }

            $cards[] = array(
                'snaptrip_ref'         => $ref,
                'title'                => $title,
                'detail_url'           => self::BASE_URL . $href,
                'town_slug'            => $town_slug,
                'bedrooms'             => $bedrooms,
                'sleeps'               => $sleeps,
                'price_per_night_from' => $price,
                'review_count'         => $review_count,
                'image_url'            => $image_url,
                'description_snippet'  => $snippet,
                'partner_brand'        => $partner_brand,
                'latitude'             => $latitude,
                'longitude'            => $longitude,
            );
            $this->stats['cards_seen']++;
        }

        return $cards;
    }

    private function ascend_to_card( $node ) {
        // Snaptrip's result-card root is a <div class="card njCard ..." data-lat data-lng>.
        // Prefer ancestors with data-lat (the canonical card root). Fall back to
        // the first ancestor block containing "Ref:" text.
        $depth = 0;
        $cur = $node;
        while ( $cur && $depth < 15 ) {
            if ( $cur->nodeType === XML_ELEMENT_NODE && $cur->hasAttribute( 'data-lat' ) ) {
                return $cur;
            }
            $cur = $cur->parentNode;
            $depth++;
        }
        // Fallback: walk again, look for any block element containing "Ref:"
        $depth = 0;
        $cur = $node;
        while ( $cur && $depth < 12 ) {
            if ( $cur->nodeType === XML_ELEMENT_NODE ) {
                $name = strtolower( $cur->nodeName );
                if ( in_array( $name, array( 'li', 'article', 'div', 'section' ), true ) ) {
                    if ( strpos( $cur->textContent, 'Ref:' ) !== false ) {
                        return $cur;
                    }
                }
            }
            $cur = $cur->parentNode;
            $depth++;
        }
        return null;
    }

    private function upsert_index_card( $card ) {
        global $wpdb;
        $now = current_time( 'mysql', 1 );

        $existing_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM " . self::TABLE_NAME . " WHERE snaptrip_ref = %s",
            $card['snaptrip_ref']
        ) );

        $data = array(
            'snaptrip_ref'         => $card['snaptrip_ref'],
            'title'                => $card['title'],
            'detail_url'           => $card['detail_url'],
            'town_slug'            => $card['town_slug'],
            'bedrooms'             => $card['bedrooms'],
            'sleeps'               => $card['sleeps'],
            'price_per_night_from' => $card['price_per_night_from'],
            'review_count'         => $card['review_count'],
            'image_url'            => $card['image_url'],
            'description_snippet'  => $card['description_snippet'],
            'partner_brand'        => $card['partner_brand'],
            'latitude'             => $card['latitude'],
            'longitude'            => $card['longitude'],
            'index_scraped_at'     => $now,
        );

        if ( $existing_id ) {
            $wpdb->update( self::TABLE_NAME, $data, array( 'id' => (int) $existing_id ) );
        } else {
            $wpdb->insert( self::TABLE_NAME, $data );
        }
        $this->stats['cards_upserted']++;
    }

    /* ------------------------------------------------------------------
     * Pass 2: crawl detail pages
     * ------------------------------------------------------------------ */

    public function crawl_details( $limit = 0 ) {
        global $wpdb;
        $this->log( '=== DETAIL PASS START ===' );

        $sql = "SELECT id, snaptrip_ref, detail_url FROM " . self::TABLE_NAME .
               " WHERE detail_fetched_at IS NULL ORDER BY id";
        if ( $limit > 0 ) {
            $sql .= " LIMIT " . (int) $limit;
        }
        $rows = $wpdb->get_results( $sql );
        $this->log( 'Detail queue size: ' . count( $rows ) );

        foreach ( $rows as $row ) {
            $html = $this->fetch_url( $row->detail_url );
            if ( ! $html ) {
                $this->stats['details_failed']++;
                continue;
            }
            $parsed = $this->parse_detail_page( $html );
            $update = array(
                'postcode'            => $parsed['postcode'],
                'latitude'            => $parsed['latitude'],
                'longitude'           => $parsed['longitude'],
                'partner_brand'       => $parsed['partner_brand'],
                'detail_html_excerpt' => $parsed['html_excerpt'],
                'detail_fetched_at'   => current_time( 'mysql', 1 ),
            );
            // Don't overwrite already-set fields with NULL — index pass may
            // already have set lat/lng (from data-lat) and partner_brand (from
            // image alt).
            if ( $parsed['partner_brand'] === null ) { unset( $update['partner_brand'] ); }
            if ( $parsed['latitude']      === null ) { unset( $update['latitude'] ); }
            if ( $parsed['longitude']     === null ) { unset( $update['longitude'] ); }
            $wpdb->update( self::TABLE_NAME, $update, array( 'id' => (int) $row->id ) );
            $this->stats['details_fetched']++;
            if ( $this->stats['details_fetched'] % 25 === 0 ) {
                $this->log( "Details progress: {$this->stats['details_fetched']} fetched / {$this->stats['details_failed']} failed" );
            }
        }
        $this->log( '=== DETAIL PASS DONE ===' );
    }

    private function parse_detail_page( $html ) {
        $out = array(
            'postcode'      => null,
            'latitude'      => null,
            'longitude'     => null,
            'partner_brand' => null,
            'html_excerpt'  => null,
        );

        if ( preg_match( '/\b([A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2})\b/', $html, $m ) ) {
            $out['postcode'] = strtoupper( trim( $m[1] ) );
            $out['postcode'] = preg_replace( '/\s*(\d[A-Z]{2})$/', ' $1', $out['postcode'] );
        }

        // Lat/lng — Snaptrip's JSON-LD GeoCoordinates lists longitude first,
        // then latitude. Extract each independently so ordering doesn't matter.
        if ( preg_match( '/"latitude"\s*:\s*"?(-?\d+\.\d+)"?/', $html, $m ) ) {
            $out['latitude'] = (float) $m[1];
        } elseif ( preg_match( '/data-lat(?:itude)?=["\'](-?\d+\.\d+)["\']/i', $html, $m ) ) {
            $out['latitude'] = (float) $m[1];
        }
        if ( preg_match( '/"longitude"\s*:\s*"?(-?\d+\.\d+)"?/', $html, $m ) ) {
            $out['longitude'] = (float) $m[1];
        } elseif ( preg_match( '/data-l(?:ng|on|ongitude)=["\'](-?\d+\.\d+)["\']/i', $html, $m ) ) {
            $out['longitude'] = (float) $m[1];
        }

        $brand_patterns = array(
            'Cottages.com'              => '/\bcottages\.com\b/i',
            'Sykes Cottages'            => '/\bsykes\b/i',
            'Hoseasons'                 => '/\bhoseasons\b/i',
            'Original Cottage Company'  => '/\boriginal cottage(?:s)?\b/i',
            'Holiday Cottages'          => '/\bholiday[\s-]?cottages\.co\.uk\b/i',
            'Last Minute Cottages'      => '/\blast[\s-]?minute[\s-]?cottages\b/i',
            'Big Cottages'              => '/\bbig[\s-]?cottages\b/i',
            'Dog Friendly Cottages'     => '/\bdog[\s-]?friendly[\s-]?cottages\b/i',
        );
        foreach ( $brand_patterns as $brand => $pattern ) {
            if ( preg_match( $pattern, $html ) ) {
                $out['partner_brand'] = $brand;
                break;
            }
        }

        $stripped = preg_replace( '#<script\b[^>]*>.*?</script>#is', '', $html );
        $stripped = preg_replace( '#<style\b[^>]*>.*?</style>#is', '', $stripped );
        $out['html_excerpt'] = substr( $stripped, 0, 8000 );

        return $out;
    }
}
