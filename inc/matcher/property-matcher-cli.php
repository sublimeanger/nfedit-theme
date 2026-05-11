<?php
/**
 * Property matcher CLI entry point.
 *
 * Usage:
 *   wp eval-file wp-content/themes/nfedit/inc/matcher/property-matcher-cli.php run
 *   wp eval-file wp-content/themes/nfedit/inc/matcher/property-matcher-cli.php smoke
 *   wp eval-file wp-content/themes/nfedit/inc/matcher/property-matcher-cli.php export-csv
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once dirname( __FILE__ ) . '/class-property-matcher.php';

$mode = isset( $args[0] ) ? strtolower( trim( $args[0] ) ) : 'run';

$m = new NFEdit_Property_Matcher();

try {
    switch ( $mode ) {
        case 'smoke':
            global $wpdb;
            $cottage = $wpdb->get_row(
                "SELECT id, merchant_product_id, title, latitude, longitude, postcode, sleeps, review_avg, weekly_price_from
                 FROM wp_nfedit_cottages_com_inventory
                 WHERE merchant_product_id = 'DDDF' LIMIT 1"
            );
            if ( ! $cottage ) {
                echo "SMOKE FAIL: Salters Cottage (DDDF) not found in staging\n";
                exit( 1 );
            }
            echo "Smoke target: {$cottage->title} ({$cottage->merchant_product_id}) "
               . "{$cottage->postcode} sleeps={$cottage->sleeps} "
               . "lat={$cottage->latitude} lng={$cottage->longitude}\n";

            $snaptrip = $wpdb->get_results(
                "SELECT id, snaptrip_ref, title, latitude, longitude, postcode, sleeps, bedrooms
                 FROM wp_nfedit_snaptrip_listings
                 WHERE latitude IS NOT NULL AND longitude IS NOT NULL"
            );

            $result = $m->find_best_match( $cottage, $snaptrip );
            if ( ! $result ) {
                echo "No match found within 500m\n";
                exit( 0 );
            }

            $best = $result['best'];
            echo "Best match: Snaptrip #{$best['snaptrip']->id} ref={$best['snaptrip']->snaptrip_ref}\n";
            echo "  title: {$best['snaptrip']->title}\n";
            echo "  postcode: {$best['snaptrip']->postcode}  sleeps: {$best['snaptrip']->sleeps}\n";
            echo "  distance: " . round( $best['distance'], 1 ) . "m\n";
            echo "  confidence: {$best['confidence']}\n";
            echo "  method: {$best['method']}\n";
            echo "  candidates considered: {$result['candidates_considered']}\n";
            echo "  auto_route: " . $m->resolve_route( $best['confidence'] ) . "\n";
            break;

        case 'export-csv':
            $path = WP_CONTENT_DIR . '/uploads/property-matches-manual-review.csv';
            $count = $m->export_review_csv( $path );
            echo "Exported {$count} review-needed rows to {$path}\n";
            break;

        case 'disambiguate':
            echo "=== Running disambiguation pass on existing matches ===\n";
            $m->resolve_many_to_one();
            $path = WP_CONTENT_DIR . '/uploads/property-matches-manual-review-v2.csv';
            $count = $m->export_review_csv( $path );
            echo "\nExported {$count} post-disambiguation review-needed rows to {$path}\n";
            break;

        case 'apply-review':
            $csv_path = isset( $args[1] ) ? $args[1] : '';
            if ( $csv_path === '' ) {
                echo "ERROR: apply-review requires a CSV path as second argument\n";
                echo "Usage: wp eval-file ...cli.php apply-review /path/to/decided.csv\n";
                exit( 1 );
            }
            $result = $m->apply_review_decisions( $csv_path );
            echo "Processed: {$result['processed']}, Skipped: {$result['skipped']}\n";
            break;

        case 'run':
        default:
            $m->run();
            $path = WP_CONTENT_DIR . '/uploads/property-matches-manual-review.csv';
            $count = $m->export_review_csv( $path );
            echo "\nExported {$count} review-needed rows to {$path}\n";
            break;
    }

    $stats = $m->get_stats();
    echo "\n=== FINAL STATS ===\n";
    foreach ( $stats as $k => $v ) {
        echo str_pad( $k, 26 ) . $v . "\n";
    }
} catch ( Exception $e ) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit( 1 );
}
