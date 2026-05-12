<?php
/**
 * Phase 13e.1 (revised): assign feature taxonomy terms to the 91
 * cottages.com drafts based on the decoded custom_X flags PLUS direct
 * sleeps-column reads for size tiers.
 *
 * Flag mappings (custom_1 dropped — correlation gate failed):
 *   custom_2 = Luxury             -> "Luxury" feature term
 *   custom_3 = Hot tub            -> "Hot tub" feature term
 *   custom_4 = Swimming Pool      -> "Swimming pool" feature term
 *   (custom_5 OR custom_6) = Dog friendly -> "Dog friendly" feature term
 *   custom_7 unused
 *
 * Direct sleeps-column mappings (more reliable than flag decode):
 *   sleeps>=6  -> "Sleeps 6+"
 *   sleeps>=8  -> "Sleeps 8+"
 *   sleeps>=12 -> "Sleeps 12+"
 *   sleeps>=14 -> "Sleeps 14+"
 *
 * Idempotent via wp_set_object_terms with $append=true.
 *
 * PHP 7.4 compatible.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class NFEdit_Custom_Flag_Applier {

    const PHASE_MARKER_META = '_nfedit_phase_13e_imported';

    private static $term_names = array(
        'luxury'       => 'Luxury',
        'hot_tub'      => 'Hot tub',
        'swimming'     => 'Swimming pool',
        'dog_friendly' => 'Dog friendly',
        'sleeps_6'     => 'Sleeps 6+',
        'sleeps_8'     => 'Sleeps 8+',
        'sleeps_12'    => 'Sleeps 12+',
        'sleeps_14'    => 'Sleeps 14+',
    );

    private $term_ids = array();

    public function apply_all( $opts = array() ) {
        $dry_run = ! empty( $opts['dry_run'] );

        $this->load_term_ids();

        global $wpdb;
        $rows = $wpdb->get_results(
            "SELECT id, merchant_product_id, title, sleeps,
                    custom_2, custom_3, custom_4, custom_5, custom_6
             FROM wp_nfedit_cottages_com_inventory
             ORDER BY id"
        );

        $stats = array(
            'total_inventory_rows'   => count( $rows ),
            'processed'              => 0,
            'matched_draft'          => 0,
            'no_draft_found'         => 0,
            'errors'                 => 0,
            'assigned_luxury'        => 0,
            'assigned_hot_tub'       => 0,
            'assigned_swimming'      => 0,
            'assigned_dog_friendly'  => 0,
            'assigned_sleeps_6'      => 0,
            'assigned_sleeps_8'      => 0,
            'assigned_sleeps_12'     => 0,
            'assigned_sleeps_14'     => 0,
            'total_term_assignments' => 0,
        );

        foreach ( $rows as $row ) {
            $stats['processed']++;
            $external_id = 'cottages-com:' . $row->merchant_product_id;
            $post_id = $this->find_draft_post( $external_id );

            if ( ! $post_id ) {
                $stats['no_draft_found']++;
                if ( $dry_run ) {
                    printf( "  WARN cc#%-3d %s — no matching draft for external_id=%s\n",
                        $row->id, $row->merchant_product_id, $external_id );
                }
                continue;
            }
            $stats['matched_draft']++;

            $term_ids_to_assign = array();
            $assigned_labels    = array();

            if ( (int) $row->custom_2 === 1 && $this->term_ids['luxury'] ) {
                $term_ids_to_assign[] = $this->term_ids['luxury'];
                $assigned_labels[]    = 'Luxury';
                $stats['assigned_luxury']++;
            }
            if ( (int) $row->custom_3 === 1 && $this->term_ids['hot_tub'] ) {
                $term_ids_to_assign[] = $this->term_ids['hot_tub'];
                $assigned_labels[]    = 'Hot tub';
                $stats['assigned_hot_tub']++;
            }
            if ( (int) $row->custom_4 === 1 && $this->term_ids['swimming'] ) {
                $term_ids_to_assign[] = $this->term_ids['swimming'];
                $assigned_labels[]    = 'Swimming pool';
                $stats['assigned_swimming']++;
            }
            if ( ( (int) $row->custom_5 === 1 || (int) $row->custom_6 === 1 ) && $this->term_ids['dog_friendly'] ) {
                $term_ids_to_assign[] = $this->term_ids['dog_friendly'];
                $assigned_labels[]    = 'Dog friendly';
                $stats['assigned_dog_friendly']++;
            }

            $sleeps = (int) $row->sleeps;
            if ( $sleeps >= 6 && $this->term_ids['sleeps_6'] ) {
                $term_ids_to_assign[] = $this->term_ids['sleeps_6'];
                $assigned_labels[]    = 'Sleeps 6+';
                $stats['assigned_sleeps_6']++;
            }
            if ( $sleeps >= 8 && $this->term_ids['sleeps_8'] ) {
                $term_ids_to_assign[] = $this->term_ids['sleeps_8'];
                $assigned_labels[]    = 'Sleeps 8+';
                $stats['assigned_sleeps_8']++;
            }
            if ( $sleeps >= 12 && $this->term_ids['sleeps_12'] ) {
                $term_ids_to_assign[] = $this->term_ids['sleeps_12'];
                $assigned_labels[]    = 'Sleeps 12+';
                $stats['assigned_sleeps_12']++;
            }
            if ( $sleeps >= 14 && $this->term_ids['sleeps_14'] ) {
                $term_ids_to_assign[] = $this->term_ids['sleeps_14'];
                $assigned_labels[]    = 'Sleeps 14+';
                $stats['assigned_sleeps_14']++;
            }

            $stats['total_term_assignments'] += count( $term_ids_to_assign );

            if ( $dry_run ) {
                printf( "  %3d. cc#%-3d post#%-4d %-30s | sleeps=%-2d flags=%d%d%d%d%d | assign: %s\n",
                    $stats['processed'], $row->id, $post_id,
                    substr( $row->title, 0, 30 ),
                    $sleeps,
                    $row->custom_2, $row->custom_3, $row->custom_4, $row->custom_5, $row->custom_6,
                    empty( $assigned_labels ) ? '(none)' : implode( ', ', $assigned_labels )
                );
                continue;
            }

            if ( empty( $term_ids_to_assign ) ) {
                continue;
            }

            $result = wp_set_object_terms(
                $post_id,
                $term_ids_to_assign,
                'feature',
                true
            );

            if ( is_wp_error( $result ) ) {
                $stats['errors']++;
                error_log( "Phase 13e.1 error on post {$post_id}: " . $result->get_error_message() );
            }
        }

        return $stats;
    }

    private function load_term_ids() {
        foreach ( self::$term_names as $key => $name ) {
            $term = get_term_by( 'name', $name, 'feature' );
            if ( $term && ! is_wp_error( $term ) ) {
                $this->term_ids[ $key ] = (int) $term->term_id;
            } else {
                $this->term_ids[ $key ] = 0;
            }
        }
    }

    public function get_term_ids() {
        if ( empty( $this->term_ids ) ) { $this->load_term_ids(); }
        return $this->term_ids;
    }

    private function find_draft_post( $external_id ) {
        global $wpdb;
        $post_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key = 'feed_source_id' AND meta_value = %s
             LIMIT 1",
            $external_id
        ) );
        if ( $post_id ) {
            $post = get_post( $post_id );
            if ( $post && $post->post_type === 'property' ) {
                $marker = get_post_meta( $post_id, self::PHASE_MARKER_META, true );
                if ( $marker ) {
                    return (int) $post_id;
                }
            }
        }
        return 0;
    }
}
