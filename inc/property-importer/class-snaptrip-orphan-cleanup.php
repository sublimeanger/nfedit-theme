<?php
/**
 * Phase 13e.3.2 Snaptrip orphan attachment cleanup.
 * PHP 7.4.33 compatible.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class NFEdit_Snaptrip_Orphan_Cleanup {

    /** Build a map of every attachment ID currently referenced by any property. */
    public function find_used_attachments() {
        $used = array();
        $pids = get_posts( array(
            'post_type'      => 'property',
            'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ) );
        foreach ( $pids as $pid ) {
            $featured = get_post_thumbnail_id( $pid );
            if ( $featured ) { $used[ (int) $featured ] = true; }

            $gallery = get_field( 'field_nfedit_property_gallery', $pid );
            if ( is_array( $gallery ) ) {
                foreach ( $gallery as $item ) {
                    $att_id = is_array( $item ) ? ( isset( $item['ID'] ) ? $item['ID'] : 0 ) : (int) $item;
                    if ( $att_id ) { $used[ (int) $att_id ] = true; }
                }
            }
        }
        return $used;
    }

    /** Snaptrip-sourced attachments NOT in $used_atts. */
    public function find_snaptrip_orphans( $used_atts ) {
        global $wpdb;
        $rows = $wpdb->get_results(
            "SELECT post_id AS att_id, meta_value AS src_url
             FROM {$wpdb->postmeta}
             WHERE meta_key = '_nfedit_source_url'
               AND meta_value LIKE 'https://images.snaptrip.com/optim/image/file/%'"
        );
        $orphans = array();
        foreach ( $rows as $row ) {
            if ( ! isset( $used_atts[ (int) $row->att_id ] ) ) {
                $orphans[] = $row;
            }
        }
        return $orphans;
    }

    /** Categorize an orphan by its URL prefix pattern. */
    public function categorize( $src_url ) {
        if ( preg_match( '#/file/\d+/normal_#', $src_url ) )    { return 'normal'; }
        if ( preg_match( '#/file/\d+/hero_#', $src_url ) )      { return 'hero'; }
        if ( preg_match( '#/file/\d+/thumb_#', $src_url ) )     { return 'thumb'; }
        if ( preg_match( '#/file/\d+/cap_#', $src_url ) )       { return 'cap'; }
        if ( preg_match( '#/file/\d+/floorplan_#', $src_url ) ) { return 'floorplan'; }
        if ( preg_match( '#/file/\d+/[a-f0-9-]+\.#i', $src_url ) ) { return 'no_prefix'; }
        return 'other';
    }
}
