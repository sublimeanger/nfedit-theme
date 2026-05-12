<?php
/**
 * Phase 13e.3 CLI — Snaptrip gallery scrape + sideload
 *
 * Usage:
 *   wp eval-file wp-content/themes/nfedit/inc/property-importer/snaptrip-gallery-scraper-cli.php dry-run
 *   wp eval-file wp-content/themes/nfedit/inc/property-importer/snaptrip-gallery-scraper-cli.php run-sample
 *   wp eval-file wp-content/themes/nfedit/inc/property-importer/snaptrip-gallery-scraper-cli.php run
 *   wp eval-file wp-content/themes/nfedit/inc/property-importer/snaptrip-gallery-scraper-cli.php run-from 3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$args = isset( $args ) ? $args : array();
$mode = isset( $args[0] ) ? $args[0] : 'dry-run';

require_once get_template_directory() . '/inc/property-importer/class-snaptrip-gallery-scraper.php';

$scraper = new NFEdit_Snaptrip_Gallery_Scraper();

switch ( $mode ) {
    case 'run-sample':
        echo "=== Phase 13e.3: Snaptrip gallery scrape (LIVE — sample 3) ===\n";
        $stats = $scraper->scrape_all( array( 'dry_run' => false, 'limit' => 3 ) );
        break;
    case 'run':
        echo "=== Phase 13e.3: Snaptrip gallery scrape (LIVE — all) ===\n";
        $stats = $scraper->scrape_all( array( 'dry_run' => false ) );
        break;
    case 'run-from':
        $offset = isset( $args[1] ) ? (int) $args[1] : 0;
        echo "=== Phase 13e.3: Snaptrip gallery scrape (LIVE — from offset $offset) ===\n";
        $stats = $scraper->scrape_all( array( 'dry_run' => false, 'offset' => $offset ) );
        break;
    case 'dry-run':
    default:
        echo "=== Phase 13e.3: Snaptrip gallery scrape (DRY RUN) ===\n";
        $stats = $scraper->scrape_all( array( 'dry_run' => true ) );
        break;
}

echo "\n=== STATS ===\n";
foreach ( $stats as $k => $v ) {
    echo sprintf( "  %-25s %d\n", $k, $v );
}
