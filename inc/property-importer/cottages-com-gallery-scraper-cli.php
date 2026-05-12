<?php
/**
 * Phase 13e.2 CLI — cottages.com gallery scrape + sideload
 *
 * Usage:
 *   wp eval-file wp-content/themes/nfedit/inc/property-importer/cottages-com-gallery-scraper-cli.php dry-run
 *   wp eval-file wp-content/themes/nfedit/inc/property-importer/cottages-com-gallery-scraper-cli.php run-sample
 *   wp eval-file wp-content/themes/nfedit/inc/property-importer/cottages-com-gallery-scraper-cli.php run
 *   wp eval-file wp-content/themes/nfedit/inc/property-importer/cottages-com-gallery-scraper-cli.php run-from 4
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$args = isset( $args ) ? $args : array();
$mode = isset( $args[0] ) ? $args[0] : 'dry-run';

require_once get_template_directory() . '/inc/property-importer/class-cottages-com-gallery-scraper.php';

$scraper = new NFEdit_Cottages_Com_Gallery_Scraper();

switch ( $mode ) {
    case 'run-sample':
        echo "=== Phase 13e.2: gallery scrape (LIVE — sample 3) ===\n";
        $stats = $scraper->scrape_all( array( 'dry_run' => false, 'limit' => 3 ) );
        break;
    case 'run':
        echo "=== Phase 13e.2: gallery scrape (LIVE — all) ===\n";
        $stats = $scraper->scrape_all( array( 'dry_run' => false ) );
        break;
    case 'run-from':
        $offset = isset( $args[1] ) ? (int) $args[1] : 0;
        echo "=== Phase 13e.2: gallery scrape (LIVE — from offset $offset) ===\n";
        $stats = $scraper->scrape_all( array( 'dry_run' => false, 'offset' => $offset ) );
        break;
    case 'dry-run':
    default:
        echo "=== Phase 13e.2: gallery scrape (DRY RUN) ===\n";
        $stats = $scraper->scrape_all( array( 'dry_run' => true ) );
        break;
}

echo "\n=== STATS ===\n";
foreach ( $stats as $k => $v ) {
    echo sprintf( "  %-25s %d\n", $k, $v );
}
