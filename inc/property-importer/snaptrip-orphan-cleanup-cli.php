<?php
/**
 * Phase 13e.3.2 CLI.
 *
 * Usage:
 *   wp eval-file ...snaptrip-orphan-cleanup-cli.php dry
 *   wp eval-file ...snaptrip-orphan-cleanup-cli.php live
 */

require_once __DIR__ . '/class-snaptrip-orphan-cleanup.php';

$mode = 'dry';
$arglist = isset( $args ) && is_array( $args ) ? $args : array();
foreach ( $arglist as $arg ) {
    if ( $arg === 'live' || $arg === 'dry' ) { $mode = $arg; }
}

$cleanup = new NFEdit_Snaptrip_Orphan_Cleanup();

echo "=== Phase 13e.3.2 — mode=$mode ===\n";

echo "\n[1/3] Collecting in-use attachments across all property posts...\n";
$t0 = microtime( true );
$used = $cleanup->find_used_attachments();
printf( "    %d unique in-use attachment IDs (%.1fs)\n", count( $used ), microtime( true ) - $t0 );

echo "\n[2/3] Finding Snaptrip orphans...\n";
$t0 = microtime( true );
$orphans = $cleanup->find_snaptrip_orphans( $used );
printf( "    %d orphans found (%.1fs)\n", count( $orphans ), microtime( true ) - $t0 );

$buckets = array();
foreach ( $orphans as $row ) {
    $cat = $cleanup->categorize( $row->src_url );
    if ( ! isset( $buckets[ $cat ] ) ) { $buckets[ $cat ] = 0; }
    $buckets[ $cat ]++;
}
echo "\n    Orphans by category:\n";
foreach ( $buckets as $cat => $count ) {
    echo "      $cat: $count\n";
}

echo "\n    Sample (first 5):\n";
foreach ( array_slice( $orphans, 0, 5 ) as $row ) {
    echo "      att=$row->att_id  $row->src_url\n";
}

echo "\n    Sample (last 5):\n";
foreach ( array_slice( $orphans, -5 ) as $row ) {
    echo "      att=$row->att_id  $row->src_url\n";
}

if ( 'dry' === $mode ) {
    echo "\n=== DRY RUN — no deletion. Re-run with 'live' to delete. ===\n";
    return;
}

echo "\n[3/3] Deleting orphans (force, no trash)...\n";
$deleted = 0;
$failed  = 0;
$total   = count( $orphans );
$t0      = microtime( true );
foreach ( $orphans as $idx => $row ) {
    $result = wp_delete_attachment( (int) $row->att_id, true );
    if ( false === $result || null === $result ) {
        $failed++;
        echo "    FAIL att=$row->att_id\n";
    } else {
        $deleted++;
    }
    if ( 0 === ( $deleted % 100 ) && $deleted > 0 ) {
        printf( "    %d/%d deleted...\n", $deleted, $total );
        fflush( STDOUT );
    }
}
printf( "\n    Done: deleted=%d failed=%d in %.1fs\n", $deleted, $failed, microtime( true ) - $t0 );
