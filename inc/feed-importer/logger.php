<?php
/**
 * Feed importer logger — writes to wp_nfedit_feed_log.
 *
 * Phase 12a. PHP 7.4 compatible.
 */
defined('ABSPATH') || exit;

/**
 * Insert a row into wp_nfedit_feed_log. Cheap and silent — must not throw.
 */
function nfedit_feed_log($event_type, $advertiser_id, $message, $payload = [], $severity = 'info', $run_id = null) {
    global $wpdb;
    static $current_run_id = null;
    if ($run_id) {
        $current_run_id = $run_id;
    } elseif (!$current_run_id) {
        $current_run_id = wp_generate_uuid4();
    }

    @$wpdb->insert($wpdb->prefix . 'nfedit_feed_log', [
        'run_id'        => $current_run_id,
        'advertiser_id' => $advertiser_id,
        'event_type'    => $event_type,
        'severity'      => $severity,
        'message'       => mb_substr((string) $message, 0, 65000),
        'payload'       => !empty($payload) ? wp_json_encode($payload) : null,
        'created'       => current_time('mysql'),
    ]);
}

function nfedit_feed_log_set_run_id($run_id) {
    nfedit_feed_log('run_id_anchor', null, '', [], 'info', $run_id);
}
