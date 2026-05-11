<?php
/**
 * Cottages.com Awin product feed importer (NF subset to staging table).
 *
 * Downloads the cottages.com Awin product feed (FID 113722), filters to the
 * New Forest postcode set, and upserts into wp_nfedit_cottages_com_inventory.
 *
 * Reads the Awin API key from the NFEDIT_AWIN_API_KEY wp-config constant.
 * Never logs the key.
 *
 * PHP 7.4 compatible.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class NFEdit_Cottages_Com_Feed {

    const FEED_ID       = 113722;
    const TABLE_NAME    = 'wp_nfedit_cottages_com_inventory';
    const TMP_ZIP_PATH  = '/tmp/nfedit-cottages-com-feed.zip';
    const TMP_CSV_PATH  = '/tmp/nfedit-cottages-com-feed.csv';

    const NF_POSTCODES = array(
        'SO40','SO41','SO42','SO43','SO45','SO51',
        'BH23','BH24','BH25','BH31',
        'SP5','SP6',
    );

    /**
     * NF geographic bounding box.
     * The postcode-only filter let through Wiltshire / Salisbury / Mere / Cranborne-Chase
     * properties that share an outward postcode with a Forest-edge district
     * (e.g. SP5, SO51) but are geographically not New Forest.
     * Confirmed bounds with Jamie 2026-05-11.
     */
    const NF_LAT_MAX = 51.00;
    const NF_LAT_MIN = 50.65;
    const NF_LNG_MIN = -1.91;
    const NF_LNG_MAX = -1.30;

    const FEED_COLUMNS = array(
        'aw_deep_link',
        'product_name',
        'aw_product_id',
        'merchant_product_id',
        'merchant_image_url',
        'search_price',
        'merchant_deep_link',
        'last_updated',
        'in_stock',
        'is_for_sale',
        'reviews',
        'rating',
        'custom_1','custom_2','custom_3','custom_4','custom_5','custom_6','custom_7',
        'Travel:longitude',
        'Travel:latitude',
        'Travel:destination_address',
        'Travel:destination_zipcode',
        'Travel:destination_city',
        'Travel:destination_region',
        'Travel:travel_pax_max',
    );

    private $log_file;
    private $stats = array(
        'feed_bytes_downloaded' => 0,
        'csv_rows_total'        => 0,
        'rows_with_postcode'    => 0,
        'rows_matched_nf'       => 0,
        'rows_upserted'         => 0,
        'rows_skipped_no_id'    => 0,
        'rows_outside_bounds'   => 0,
        'tier_T1'               => 0,
        'tier_T2'               => 0,
        'tier_below_bar'        => 0,
        'tier_no_reviews'       => 0,
    );

    public function __construct() {
        $upload = wp_upload_dir();
        $this->log_file = trailingslashit( $upload['basedir'] ) . 'cottages-com-feed.log';
    }

    public function log( $msg ) {
        $line = '[' . gmdate( 'Y-m-d H:i:s' ) . 'Z] ' . $msg . "\n";
        file_put_contents( $this->log_file, $line, FILE_APPEND );
        echo $line;
    }

    public function get_stats() {
        return $this->stats;
    }

    private function get_feed_url() {
        if ( ! defined( 'NFEDIT_AWIN_API_KEY' ) ) {
            throw new RuntimeException( 'NFEDIT_AWIN_API_KEY constant not defined' );
        }
        $api_key = NFEDIT_AWIN_API_KEY;
        if ( strlen( $api_key ) !== 32 ) {
            throw new RuntimeException( 'NFEDIT_AWIN_API_KEY appears malformed (length != 32)' );
        }

        $encoded_cols = array_map( 'rawurlencode', self::FEED_COLUMNS );
        $cols_param   = implode( ',', $encoded_cols );

        return 'https://productdata.awin.com/datafeed/download'
             . '/apikey/' . $api_key
             . '/language/en'
             . '/fid/' . self::FEED_ID
             . '/rid/0'
             . '/hasEnhancedFeeds/0'
             . '/columns/' . $cols_param
             . '/format/csv'
             . '/delimiter/%2C'
             . '/compression/zip/';
    }

    public function download_feed() {
        $url = $this->get_feed_url();
        $this->log( 'Downloading feed (FID ' . self::FEED_ID . ')' );

        if ( file_exists( self::TMP_ZIP_PATH ) ) { @unlink( self::TMP_ZIP_PATH ); }
        if ( file_exists( self::TMP_CSV_PATH ) ) { @unlink( self::TMP_CSV_PATH ); }

        $fh = fopen( self::TMP_ZIP_PATH, 'wb' );
        if ( ! $fh ) {
            throw new RuntimeException( 'Failed to open ' . self::TMP_ZIP_PATH . ' for writing' );
        }

        $ch = curl_init( $url );
        curl_setopt_array( $ch, array(
            CURLOPT_FILE           => $fh,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_TIMEOUT        => 300,
            CURLOPT_USERAGENT      => 'NewForestEditFeedImporter/1.0 (+https://newforestedit.co.uk)',
            CURLOPT_SSL_VERIFYPEER => true,
        ) );
        $ok   = curl_exec( $ch );
        $code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        $err  = curl_error( $ch );
        curl_close( $ch );
        fclose( $fh );

        if ( ! $ok || $code >= 400 ) {
            throw new RuntimeException( "Feed download failed: HTTP {$code} {$err}" );
        }

        $bytes = filesize( self::TMP_ZIP_PATH );
        $this->stats['feed_bytes_downloaded'] = $bytes;
        $this->log( "Downloaded {$bytes} bytes to " . self::TMP_ZIP_PATH );

        if ( $bytes < 1024 * 1024 ) {
            throw new RuntimeException( 'Feed file suspiciously small: ' . $bytes . ' bytes' );
        }
    }

    public function extract_feed() {
        if ( ! class_exists( 'ZipArchive' ) ) {
            throw new RuntimeException( 'PHP ZipArchive class not available' );
        }
        $zip = new ZipArchive();
        if ( $zip->open( self::TMP_ZIP_PATH ) !== true ) {
            throw new RuntimeException( 'Failed to open ' . self::TMP_ZIP_PATH );
        }
        if ( $zip->numFiles < 1 ) {
            $zip->close();
            throw new RuntimeException( 'Zip is empty' );
        }
        $csv_name_in_zip = $zip->getNameIndex( 0 );
        $zip->extractTo( '/tmp/', array( $csv_name_in_zip ) );
        $zip->close();

        $extracted = '/tmp/' . $csv_name_in_zip;
        if ( ! file_exists( $extracted ) ) {
            throw new RuntimeException( 'Extracted CSV not found at ' . $extracted );
        }
        if ( $extracted !== self::TMP_CSV_PATH ) {
            rename( $extracted, self::TMP_CSV_PATH );
        }
        $this->log( 'Extracted CSV to ' . self::TMP_CSV_PATH . ' (' . filesize( self::TMP_CSV_PATH ) . ' bytes)' );
    }

    public function import_csv() {
        $fh = fopen( self::TMP_CSV_PATH, 'r' );
        if ( ! $fh ) {
            throw new RuntimeException( 'Failed to open extracted CSV' );
        }

        $header = fgetcsv( $fh );
        if ( ! $header ) {
            fclose( $fh );
            throw new RuntimeException( 'CSV header missing' );
        }
        $col = array_flip( $header );
        $this->log( 'CSV header: ' . count( $header ) . ' columns' );

        $required = array(
            'merchant_product_id', 'product_name',
            'Travel:destination_zipcode', 'Travel:latitude', 'Travel:longitude',
        );
        foreach ( $required as $rc ) {
            if ( ! isset( $col[ $rc ] ) ) {
                fclose( $fh );
                throw new RuntimeException( "Required CSV column missing: {$rc}" );
            }
        }

        while ( ( $row = fgetcsv( $fh ) ) !== false ) {
            $this->stats['csv_rows_total']++;

            if ( count( $row ) < count( $header ) ) {
                continue;
            }

            $get = function( $key ) use ( $row, $col ) {
                if ( ! isset( $col[ $key ] ) ) { return ''; }
                $idx = $col[ $key ];
                return isset( $row[ $idx ] ) ? trim( $row[ $idx ] ) : '';
            };

            $postcode = strtoupper( $get( 'Travel:destination_zipcode' ) );
            $outward  = $this->extract_postcode_outward( $postcode );
            if ( $outward !== '' ) {
                $this->stats['rows_with_postcode']++;
            }
            if ( ! in_array( $outward, self::NF_POSTCODES, true ) ) {
                continue;
            }
            $this->stats['rows_matched_nf']++;

            // NF geographic bounding-box veto (the postcode filter alone lets
            // through Wiltshire / Salisbury / Mere properties that share an
            // outward code with Forest-edge districts but aren't NF).
            $lat_check = $this->to_decimal( $get( 'Travel:latitude' ) );
            $lng_check = $this->to_decimal( $get( 'Travel:longitude' ) );
            if ( $lat_check === null || $lng_check === null
                 || $lat_check > self::NF_LAT_MAX || $lat_check < self::NF_LAT_MIN
                 || $lng_check < self::NF_LNG_MIN || $lng_check > self::NF_LNG_MAX ) {
                $this->stats['rows_outside_bounds']++;
                continue;
            }

            $mpid = $get( 'merchant_product_id' );
            if ( $mpid === '' ) {
                $this->stats['rows_skipped_no_id']++;
                continue;
            }

            $review_avg = $this->to_decimal( $get( 'reviews' ) );
            $tier       = $this->assign_tier( $review_avg );
            $this->stats[ 'tier_' . $tier ]++;

            $data = array(
                'merchant_product_id' => $mpid,
                'aw_product_id'       => $get( 'aw_product_id' ),
                'title'               => $get( 'product_name' ),
                'merchant_deep_link'  => $get( 'merchant_deep_link' ),
                'aw_deep_link'        => $get( 'aw_deep_link' ),
                'sleeps'              => $this->to_int( $get( 'Travel:travel_pax_max' ) ),
                'weekly_price_from'   => $this->to_decimal( $get( 'search_price' ) ),
                'image_url'           => $get( 'merchant_image_url' ),
                'review_avg'          => $review_avg,
                'star_rating'         => $this->to_int( $get( 'rating' ) ),
                'postcode'            => $postcode !== '' ? $postcode : null,
                'postcode_outward'    => $outward !== '' ? $outward : null,
                'address_line'        => $get( 'Travel:destination_address' ),
                'town'                => $get( 'Travel:destination_city' ),
                'region'              => $get( 'Travel:destination_region' ),
                'latitude'            => $this->to_decimal( $get( 'Travel:latitude' ) ),
                'longitude'           => $this->to_decimal( $get( 'Travel:longitude' ) ),
                'custom_1'            => $this->yesno_to_int( $get( 'custom_1' ) ),
                'custom_2'            => $this->yesno_to_int( $get( 'custom_2' ) ),
                'custom_3'            => $this->yesno_to_int( $get( 'custom_3' ) ),
                'custom_4'            => $this->yesno_to_int( $get( 'custom_4' ) ),
                'custom_5'            => $this->yesno_to_int( $get( 'custom_5' ) ),
                'custom_6'            => $this->yesno_to_int( $get( 'custom_6' ) ),
                'custom_7'            => $this->yesno_to_int( $get( 'custom_7' ) ),
                'proposed_tier'       => $tier,
                'feed_last_updated'   => $get( 'last_updated' ),
                'in_stock'            => $this->yesno_to_int( $get( 'in_stock' ) ),
                'imported_at'         => current_time( 'mysql', 1 ),
            );

            $this->upsert_row( $data );
            $this->stats['rows_upserted']++;
        }
        fclose( $fh );
        $this->log( 'Import complete' );
    }

    private function extract_postcode_outward( $postcode ) {
        // UK postcode = OUTWARD INWARD where INWARD is always exactly 3 chars
        // (digit + two letters). Splitting on space gives the canonical outward;
        // for space-less postcodes, stripping the trailing 3-char inward also
        // works. This avoids the "BH2 5DY" -> "BH25DY" -> greedy match -> "BH25"
        // false-positive (Bournemouth misclassified as New Milton).
        $pc = strtoupper( trim( $postcode ) );
        if ( $pc === '' ) { return ''; }
        $parts = preg_split( '/\s+/', $pc );
        if ( count( $parts ) >= 2 ) {
            $outward = $parts[0];
            if ( preg_match( '/^[A-Z]{1,2}\d{1,2}[A-Z]?$/', $outward ) ) {
                return $outward;
            }
        }
        $no_space = str_replace( ' ', '', $pc );
        if ( strlen( $no_space ) >= 5 ) {
            $candidate = substr( $no_space, 0, -3 );
            if ( preg_match( '/^[A-Z]{1,2}\d{1,2}[A-Z]?$/', $candidate ) ) {
                return $candidate;
            }
        }
        return '';
    }

    private function to_int( $s ) {
        if ( $s === '' || $s === null ) { return null; }
        if ( ! is_numeric( $s ) ) { return null; }
        return (int) $s;
    }

    private function to_decimal( $s ) {
        if ( $s === '' || $s === null ) { return null; }
        if ( ! is_numeric( $s ) ) { return null; }
        return (float) $s;
    }

    private function yesno_to_int( $s ) {
        $s = strtolower( trim( (string) $s ) );
        if ( $s === 'yes' || $s === '1' || $s === 'true' ) { return 1; }
        if ( $s === 'no'  || $s === '0' || $s === 'false' ) { return 0; }
        return null;
    }

    private function assign_tier( $review_avg ) {
        if ( $review_avg === null || $review_avg <= 0 ) { return 'no_reviews'; }
        if ( $review_avg >= 4.8 ) { return 'T1'; }
        if ( $review_avg >= 4.3 ) { return 'T2'; }
        return 'below_bar';
    }

    private function upsert_row( $data ) {
        global $wpdb;
        $mpid = $data['merchant_product_id'];

        $existing_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM " . self::TABLE_NAME . " WHERE merchant_product_id = %s",
            $mpid
        ) );

        if ( $existing_id ) {
            $wpdb->update( self::TABLE_NAME, $data, array( 'id' => (int) $existing_id ) );
        } else {
            $wpdb->insert( self::TABLE_NAME, $data );
        }
    }

    public function cleanup_tmp() {
        if ( file_exists( self::TMP_ZIP_PATH ) ) { @unlink( self::TMP_ZIP_PATH ); }
        if ( file_exists( self::TMP_CSV_PATH ) ) { @unlink( self::TMP_CSV_PATH ); }
        $this->log( 'Cleaned up temp files' );
    }
}
