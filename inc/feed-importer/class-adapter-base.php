<?php
/**
 * Abstract base for feed adapters.
 *
 * One adapter per source type (Awin, future Cool Stays, future direct-owner).
 * PHP 7.4 compatible.
 */
defined('ABSPATH') || exit;

abstract class NFEdit_Feed_Adapter_Base {

    /** Stable adapter ID, e.g. 'awin'. */
    abstract public function get_id(): string;

    /** Human-readable name for admin UI. */
    abstract public function get_label(): string;

    /**
     * Test connectivity / credentials.
     * @return WP_Error|null  null on success.
     */
    abstract public function test_connection();

    /**
     * Returns array of source descriptors.
     * @return array
     */
    abstract public function list_sources(): array;

    /**
     * Yields canonical property arrays from one source.
     * @param string $source_id
     * @return iterable
     */
    abstract public function fetch_properties(string $source_id): iterable;

    /** Helper: log an event. */
    protected function log($event_type, $advertiser_id, $message, $payload = [], $severity = 'info') {
        nfedit_feed_log($event_type, $advertiser_id, $message, $payload, $severity);
    }
}
