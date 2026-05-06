<?php
/**
 * Importer orchestrator.
 *
 * - run_full_sync() — for cron
 * - run_advertiser_sync($id) — for "import this one now" admin button
 * - refresh_feed_list() — for "refresh advertiser list" admin button
 */
defined('ABSPATH') || exit;

class NFEdit_Feed_Importer {

    /** @var NFEdit_Feed_Adapter_Awin */
    private $adapter;
    /** @var NFEdit_Feed_Field_Mapper */
    private $mapper;

    public function __construct() {
        $this->adapter = new NFEdit_Feed_Adapter_Awin();
        $this->mapper  = new NFEdit_Feed_Field_Mapper();
    }

    public function refresh_feed_list() {
        return $this->adapter->list_sources();
    }

    public function run_advertiser_sync($advertiser_id) {
        $advertiser_id = (int) $advertiser_id;
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

        nfedit_feed_log('run_started', $advertiser_id, "Manual sync started", []);
        $start = microtime(true);

        foreach ($this->adapter->fetch_properties("awin:$advertiser_id") as $canonical) {
            try {
                $result = $this->mapper->apply($canonical);
                if (is_wp_error($result)) {
                    $stats['failed']++;
                    nfedit_feed_log('property_failed', $advertiser_id, $result->get_error_message(), ['canonical' => $canonical], 'error');
                } else {
                    $stats['created']++;
                }
            } catch (Throwable $e) {
                $stats['failed']++;
                nfedit_feed_log('property_failed', $advertiser_id, $e->getMessage(), [], 'error');
            }
        }

        $duration = round(microtime(true) - $start, 2);
        nfedit_feed_log('run_completed', $advertiser_id, sprintf('Completed: %s in %ss', wp_json_encode($stats), $duration));

        return $stats;
    }

    public function run_full_sync() {
        $totals = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

        $this->refresh_feed_list();

        global $wpdb;
        $advertiser_ids = $wpdb->get_col("
            SELECT advertiser_id FROM {$wpdb->prefix}nfedit_advertisers
            WHERE enabled = 1
            AND membership_status = 'Joined'
            ORDER BY advertiser_id
        ");

        foreach ($advertiser_ids as $adv_id) {
            $stats = $this->run_advertiser_sync((int) $adv_id);
            foreach ($stats as $k => $v) $totals[$k] += $v;
        }

        return $totals;
    }
}

// Lifecycle scheduled check (daily) — Action Scheduler
add_action('nfedit_feed_lifecycle_check', function () {
    $lifecycle = new NFEdit_Feed_Lifecycle();
    $count = $lifecycle->check_sunset_transitions();
    nfedit_feed_log('lifecycle_run', null, "Checked sunset transitions: $count properties moved");
});

add_action('init', function () {
    if (function_exists('as_schedule_recurring_action') && function_exists('as_next_scheduled_action')) {
        if (false === as_next_scheduled_action('nfedit_feed_lifecycle_check')) {
            as_schedule_recurring_action(time() + HOUR_IN_SECONDS, DAY_IN_SECONDS, 'nfedit_feed_lifecycle_check');
        }
    }
}, 50);
