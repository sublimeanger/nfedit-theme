<?php
/**
 * Phase 13e.1 CLI — assign feature terms from custom_X flags
 *
 * Usage:
 *   wp eval-file wp-content/themes/nfedit/inc/property-importer/custom-flag-applier-cli.php dry-run
 *   wp eval-file wp-content/themes/nfedit/inc/property-importer/custom-flag-applier-cli.php run
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$args = isset( $args ) ? $args : array();
$mode = isset( $args[0] ) ? $args[0] : 'dry-run';

require_once get_template_directory() . '/inc/property-importer/class-custom-flag-applier.php';

$applier = new NFEdit_Custom_Flag_Applier();

echo "Term IDs resolved:\n";
foreach ( $applier->get_term_ids() as $key => $term_id ) {
    echo sprintf( "  %-15s term_id=%d\n", $key, $term_id );
}
echo "\n";

switch ( $mode ) {
    case 'run':
        echo "=== Phase 13e.1: custom flag → feature term assignment (LIVE) ===\n";
        $stats = $applier->apply_all( array( 'dry_run' => false ) );
        break;
    case 'dry-run':
    default:
        echo "=== Phase 13e.1: custom flag → feature term assignment (DRY RUN) ===\n";
        $stats = $applier->apply_all( array( 'dry_run' => true ) );
        break;
}

echo "\n=== STATS ===\n";
foreach ( $stats as $k => $v ) {
    echo sprintf( "  %-25s %d\n", $k, $v );
}
