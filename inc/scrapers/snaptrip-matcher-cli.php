<?php
/**
 * Snaptrip matcher CLI entry point.
 *
 * Usage:
 *   wp eval-file wp-content/themes/nfedit/inc/scrapers/snaptrip-matcher-cli.php index
 *   wp eval-file wp-content/themes/nfedit/inc/scrapers/snaptrip-matcher-cli.php details
 *   wp eval-file wp-content/themes/nfedit/inc/scrapers/snaptrip-matcher-cli.php all
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once dirname( __FILE__ ) . '/class-snaptrip-matcher.php';

$mode = isset( $args[0] ) ? $args[0] : 'all';
$mode = strtolower( trim( $mode ) );

$matcher = new NFEdit_Snaptrip_Matcher();

switch ( $mode ) {
    case 'index':
        $matcher->crawl_index();
        break;
    case 'details':
        $matcher->crawl_details();
        break;
    case 'details-limit-5':
        $matcher->crawl_details( 5 );
        break;
    case 'all':
    default:
        $matcher->crawl_index();
        $matcher->crawl_details();
        break;
}

$stats = $matcher->get_stats();
echo "\n=== FINAL STATS ===\n";
foreach ( $stats as $k => $v ) {
    echo str_pad( $k, 22 ) . $v . "\n";
}
