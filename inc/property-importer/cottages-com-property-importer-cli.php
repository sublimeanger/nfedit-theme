<?php
/**
 * Phase 13e CLI — cottages.com property importer
 *
 * Usage:
 *   wp eval-file wp-content/themes/nfedit/inc/property-importer/cottages-com-property-importer-cli.php dry-run
 *   wp eval-file wp-content/themes/nfedit/inc/property-importer/cottages-com-property-importer-cli.php run
 *   wp eval-file wp-content/themes/nfedit/inc/property-importer/cottages-com-property-importer-cli.php run 5
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$args  = isset( $args ) ? $args : array();
$mode  = isset( $args[0] ) ? $args[0] : 'dry-run';
$limit = isset( $args[1] ) ? (int) $args[1] : 0;

require_once get_template_directory() . '/inc/property-importer/class-cottages-com-property-importer.php';

$importer = new NFEdit_Cottages_Com_Property_Importer();

switch ( $mode ) {
    case 'run':
        echo "=== Phase 13e: cottages.com property import (LIVE) ===\n";
        if ( $limit > 0 ) { echo "Limit: {$limit} rows\n"; }
        $stats = $importer->import_all( array( 'dry_run' => false, 'limit' => $limit ) );
        break;

    case 'dry-run':
    default:
        echo "=== Phase 13e: cottages.com property import (DRY RUN) ===\n";
        if ( $limit > 0 ) { echo "Limit: {$limit} rows\n"; }
        $stats = $importer->import_all( array( 'dry_run' => true, 'limit' => $limit ) );
        break;
}

echo "\n=== STATS ===\n";
foreach ( $stats as $k => $v ) {
    echo sprintf( "  %-22s %d\n", $k, $v );
}
