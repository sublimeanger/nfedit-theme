<?php
/**
 * Phase 13e.3.1 CLI runner for the v2 patched Snaptrip gallery scraper.
 *
 * Usage (positional arg — wp-cli intercepts --flags):
 *   wp eval-file ...snaptrip-gallery-scraper-v2-cli.php sample
 *   wp eval-file ...snaptrip-gallery-scraper-v2-cli.php full
 */

require_once __DIR__ . '/class-snaptrip-gallery-scraper-v2.php';

$arglist = isset( $args ) && is_array( $args ) ? $args : array();
$mode = ! empty( $arglist[0] ) ? $arglist[0] : 'sample';

$sample_ids = array( 513, 515, 335 );

if ( 'sample' === $mode ) {
    $post_ids = $sample_ids;
} else {
    global $wpdb;
    $rows = $wpdb->get_col(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_nfedit_phase_13e3_snaptrip_gallery_scraped' ORDER BY CAST(post_id AS UNSIGNED) ASC"
    );
    $post_ids = array_map( 'intval', $rows );
}

$scraper = new NFEdit_Snaptrip_Gallery_Scraper_V2();
$totals = array(
    'cottages'      => 0,
    'attachments'   => 0,
    'sideload_fail' => 0,
    'fetch_fail'    => 0,
    'multi_group'   => 0,
);

echo "Mode: $mode | Cottages: " . count( $post_ids ) . "\n";
echo str_repeat( '-', 80 ) . "\n";

foreach ( $post_ids as $pid ) {
    $t0 = microtime( true );
    $s  = $scraper->process_cottage( $pid );
    $dt = round( microtime( true ) - $t0, 1 );

    $title = get_the_title( $pid );
    $title_short = substr( $title, 0, 40 );

    $totals['cottages']++;
    $totals['attachments']   += $s['gallery_size'];
    $totals['sideload_fail'] += $s['sideload_fails'];
    if ( ! $s['fetch_ok'] )   { $totals['fetch_fail']++; }
    if ( $s['group_count'] > 1 ) { $totals['multi_group']++; }

    printf(
        "#%d %s | raw=%d groups=%d lid=%s kept=%d drop=%d sfail=%d w=%s-%s %.1fs\n",
        $pid,
        str_pad( $title_short, 40 ),
        $s['raw_entries'],
        $s['group_count'],
        $s['chosen_group_lid'] ? $s['chosen_group_lid'] : 'n/a',
        $s['gallery_size'],
        $s['dropped_count'],
        $s['sideload_fails'],
        $s['min_width'] !== null ? $s['min_width'] : 'n/a',
        $s['max_width'] !== null ? $s['max_width'] : 'n/a',
        $dt
    );
    fflush( STDOUT );

    sleep( 1 );
}

echo str_repeat( '-', 80 ) . "\n";
echo "=== TOTALS ===\n";
echo "Cottages processed: {$totals['cottages']}\n";
echo "Attachments created (gallery items): {$totals['attachments']}\n";
echo "Sideload fails: {$totals['sideload_fail']}\n";
echo "Fetch fails: {$totals['fetch_fail']}\n";
echo "Multi-group cottages (dedupped): {$totals['multi_group']}\n";
