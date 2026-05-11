<?php
/**
 * Cottages.com feed importer CLI entry point.
 *
 * Usage:
 *   cd /home/master/applications/vbzbzzugvp/public_html
 *   wp eval-file wp-content/themes/nfedit/inc/feed-importer/cottages-com-feed-cli.php all
 *   wp eval-file wp-content/themes/nfedit/inc/feed-importer/cottages-com-feed-cli.php smoke
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once dirname( __FILE__ ) . '/class-cottages-com-feed.php';

$mode = isset( $args[0] ) ? strtolower( trim( $args[0] ) ) : 'all';

$importer = new NFEdit_Cottages_Com_Feed();

try {
    switch ( $mode ) {
        case 'smoke':
            $importer->log( 'SMOKE MODE: download + extract + sample, no upsert' );
            $importer->download_feed();
            $importer->extract_feed();

            $fh = fopen( '/tmp/nfedit-cottages-com-feed.csv', 'r' );
            $header = fgetcsv( $fh );
            echo "CSV header columns: " . count( $header ) . "\n";

            $col = array_flip( $header );
            $nf_pcs = NFEdit_Cottages_Com_Feed::NF_POSTCODES;
            $nf_count = 0;
            $total = 0;
            $first_nf_samples = array();
            while ( ( $row = fgetcsv( $fh ) ) !== false ) {
                $total++;
                $pc_raw = isset( $col['Travel:destination_zipcode'] ) ? $row[ $col['Travel:destination_zipcode'] ] : '';
                $pc = strtoupper( trim( $pc_raw ) );
                $outward = '';
                $parts = preg_split( '/\s+/', $pc );
                if ( count( $parts ) >= 2 && preg_match( '/^[A-Z]{1,2}\d{1,2}[A-Z]?$/', $parts[0] ) ) {
                    $outward = $parts[0];
                } else {
                    $no_space = str_replace( ' ', '', $pc );
                    if ( strlen( $no_space ) >= 5 ) {
                        $cand = substr( $no_space, 0, -3 );
                        if ( preg_match( '/^[A-Z]{1,2}\d{1,2}[A-Z]?$/', $cand ) ) {
                            $outward = $cand;
                        }
                    }
                }
                if ( $outward !== '' ) {
                    if ( in_array( $outward, $nf_pcs, true ) ) {
                        $nf_count++;
                        if ( count( $first_nf_samples ) < 3 ) {
                            $first_nf_samples[] = array(
                                'id'   => $row[ $col['merchant_product_id'] ],
                                'name' => $row[ $col['product_name'] ],
                                'pc'   => $row[ $col['Travel:destination_zipcode'] ],
                                'town' => $row[ $col['Travel:destination_city'] ],
                                'rev'  => $row[ $col['reviews'] ],
                                'pax'  => $row[ $col['Travel:travel_pax_max'] ],
                            );
                        }
                    }
                }
            }
            fclose( $fh );
            echo "Total CSV rows: $total\n";
            echo "NF rows (by postcode): $nf_count\n";
            echo "First 3 NF samples:\n";
            foreach ( $first_nf_samples as $s ) {
                echo "  [{$s['id']}] {$s['name']} | {$s['pc']} | {$s['town']} | reviews={$s['rev']} | pax={$s['pax']}\n";
            }
            $importer->cleanup_tmp();
            break;

        case 'all':
        default:
            $importer->download_feed();
            $importer->extract_feed();
            $importer->import_csv();
            $importer->cleanup_tmp();
            break;
    }

    $stats = $importer->get_stats();
    echo "\n=== FINAL STATS ===\n";
    foreach ( $stats as $k => $v ) {
        echo str_pad( $k, 26 ) . $v . "\n";
    }
} catch ( Exception $e ) {
    echo "ERROR: " . $e->getMessage() . "\n";
    $importer->cleanup_tmp();
    exit( 1 );
}
