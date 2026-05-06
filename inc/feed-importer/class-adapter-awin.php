<?php
/**
 * Awin Product Feed adapter (Legacy CSV).
 *
 * - Feed list:    https://productdata.awin.com/datafeed/list/apikey/{KEY}
 * - Feed download: per-advertiser URL from feed-list (gzipped CSV)
 *
 * PHP 7.4 compatible.
 */
defined('ABSPATH') || exit;

class NFEdit_Feed_Adapter_Awin extends NFEdit_Feed_Adapter_Base {

    const FEED_LIST_URL    = 'https://productdata.awin.com/datafeed/list/apikey/%s';
    const FETCH_TIMEOUT    = 60;
    const SLEEP_MIN_SECONDS = 10;
    const SLEEP_MAX_SECONDS = 60;

    private $api_key;
    private $publisher_id;

    public function __construct() {
        $this->api_key      = defined('NFEDIT_AWIN_API_KEY') ? NFEDIT_AWIN_API_KEY : '';
        $this->publisher_id = defined('NFEDIT_AWIN_PUBLISHER_ID') ? NFEDIT_AWIN_PUBLISHER_ID : '';
    }

    public function get_id(): string    { return 'awin'; }
    public function get_label(): string { return 'Awin Network (Legacy CSV)'; }

    public function test_connection() {
        if (!$this->api_key) {
            return new WP_Error('nfedit_awin_no_key', 'NFEDIT_AWIN_API_KEY not set in wp-config.php');
        }
        $url = sprintf(self::FEED_LIST_URL, urlencode($this->api_key));
        $resp = wp_remote_get($url, ['timeout' => 15, 'redirection' => 5]);
        if (is_wp_error($resp)) return $resp;
        $code = wp_remote_retrieve_response_code($resp);
        if ($code !== 200) {
            return new WP_Error('nfedit_awin_http', "Feed-list returned HTTP $code");
        }
        $body = wp_remote_retrieve_body($resp);
        if (strpos($body, 'Advertiser ID') === false) {
            return new WP_Error('nfedit_awin_format', 'Feed-list response did not look like Awin CSV');
        }
        return null;
    }

    public function list_sources(): array {
        if (!$this->api_key) {
            $this->log('error', null, 'API key missing', [], 'error');
            return [];
        }
        $url = sprintf(self::FEED_LIST_URL, urlencode($this->api_key));

        $start = microtime(true);
        $resp = wp_remote_get($url, ['timeout' => self::FETCH_TIMEOUT, 'redirection' => 5]);
        $duration_ms = (int) round((microtime(true) - $start) * 1000);

        if (is_wp_error($resp)) {
            $this->log('list_fetch_error', null, $resp->get_error_message(), [], 'error');
            return [];
        }
        $code = wp_remote_retrieve_response_code($resp);
        if ($code !== 200) {
            $this->log('list_fetch_error', null, "HTTP $code", [], 'error');
            return [];
        }
        $body = wp_remote_retrieve_body($resp);

        $rows = $this->parse_csv($body);
        $this->log('list_fetched', null, sprintf('Got %d advertisers', count($rows)), ['duration_ms' => $duration_ms], 'info');

        global $wpdb;
        $sources = [];
        foreach ($rows as $row) {
            $adv_id = (int) (isset($row['Advertiser ID']) ? $row['Advertiser ID'] : 0);
            if (!$adv_id) continue;

            $is_joined = strtolower(trim(isset($row['Membership Status']) ? $row['Membership Status'] : '')) === 'joined';

            $wpdb->replace($wpdb->prefix . 'nfedit_advertisers', [
                'advertiser_id'        => $adv_id,
                'advertiser_name'      => substr(isset($row['Advertiser Name']) ? $row['Advertiser Name'] : '', 0, 255),
                'primary_region'       => substr(isset($row['Primary Region']) ? $row['Primary Region'] : '', 0, 8),
                'membership_status'    => $is_joined ? 'Joined' : 'Not Joined',
                'feed_id'              => (int) (isset($row['Feed ID']) ? $row['Feed ID'] : 0) ?: null,
                'feed_name'            => substr(isset($row['Feed Name']) ? $row['Feed Name'] : '', 0, 255),
                'download_url'         => isset($row['URL']) ? $row['URL'] : null,
                'last_imported_remote' => self::parse_dt(isset($row['Last Imported']) ? $row['Last Imported'] : ''),
                'last_checked_remote'  => self::parse_dt(isset($row['Last Checked']) ? $row['Last Checked'] : ''),
                'no_of_products'       => (int) (isset($row['No of products']) ? $row['No of products'] : 0) ?: null,
            ]);

            $sources[] = [
                'source_id'     => "awin:$adv_id",
                'label'         => isset($row['Advertiser Name']) ? $row['Advertiser Name'] : "Advertiser $adv_id",
                'membership'    => $is_joined ? 'joined' : 'not_joined',
                'product_count' => (int) (isset($row['No of products']) ? $row['No of products'] : 0) ?: null,
                'last_remote'   => strtotime(isset($row['Last Imported']) ? $row['Last Imported'] : '') ?: null,
                'metadata'      => [
                    'advertiser_id' => $adv_id,
                    'feed_id'       => (int) (isset($row['Feed ID']) ? $row['Feed ID'] : 0),
                    'download_url'  => isset($row['URL']) ? $row['URL'] : null,
                ],
            ];
        }

        return $sources;
    }

    public function fetch_properties(string $source_id): iterable {
        if (strpos($source_id, 'awin:') !== 0) {
            throw new InvalidArgumentException("Bad source_id: $source_id");
        }
        $advertiser_id = (int) substr($source_id, 5);

        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}nfedit_advertisers
            WHERE advertiser_id = %d
        ", $advertiser_id), ARRAY_A);

        if (!$row) {
            $this->log('feed_fetch_error', $advertiser_id, 'Advertiser not in cache; run list_sources first', [], 'error');
            return;
        }
        if (strtolower($row['membership_status']) !== 'joined') {
            $this->log('feed_fetch_skip', $advertiser_id, 'Not joined; skipping', [], 'warn');
            return;
        }
        if (!$row['download_url']) {
            $this->log('feed_fetch_error', $advertiser_id, 'No download_url cached', [], 'error');
            return;
        }

        $sleep = mt_rand(self::SLEEP_MIN_SECONDS, self::SLEEP_MAX_SECONDS);
        $this->log('feed_fetch_sleep', $advertiser_id, "Sleeping {$sleep}s before download", [], 'info');
        sleep($sleep);

        $start = microtime(true);
        $resp = wp_remote_get($row['download_url'], [
            'timeout'     => self::FETCH_TIMEOUT,
            'redirection' => 5,
        ]);
        $duration_ms = (int) round((microtime(true) - $start) * 1000);

        if (is_wp_error($resp)) {
            $this->log('feed_fetch_error', $advertiser_id, $resp->get_error_message(), [], 'error');
            return;
        }
        $code = wp_remote_retrieve_response_code($resp);
        if ($code !== 200) {
            $this->log('feed_fetch_error', $advertiser_id, "HTTP $code", [], 'error');
            return;
        }

        $body = wp_remote_retrieve_body($resp);

        if (substr($body, 0, 2) === "\x1f\x8b") {
            $body = gzdecode($body);
            if ($body === false) {
                $this->log('feed_fetch_error', $advertiser_id, 'gzdecode failed', [], 'error');
                return;
            }
        }

        $rows = $this->parse_csv($body);
        $this->log('feed_downloaded', $advertiser_id, sprintf('Got %d rows', count($rows)), [
            'duration_ms' => $duration_ms,
            'bytes'       => strlen($body),
        ], 'info');

        foreach ($rows as $row_data) {
            $canonical = $this->row_to_canonical($row_data, $advertiser_id);
            if ($canonical) {
                yield $canonical;
            }
        }

        $wpdb->update(
            $wpdb->prefix . 'nfedit_advertisers',
            ['last_imported_local' => current_time('mysql')],
            ['advertiser_id' => $advertiser_id]
        );
    }

    /**
     * Phase 12a stub. Phase 12b implements full mapping.
     */
    protected function row_to_canonical(array $row, int $advertiser_id) {
        return [
            '_phase_12a_stub' => true,
            '_advertiser_id'  => $advertiser_id,
            '_raw'            => $row,
        ];
    }

    /**
     * Parse Awin CSV. Comma delimiter, double-quote escaping, UTF-8.
     */
    protected function parse_csv($body): array {
        if (!$body) return [];

        $rows  = [];
        $lines = preg_split("/\r?\n/", $body);
        if (empty($lines)) return [];

        $headers = str_getcsv($lines[0]);
        $headers = array_map('trim', $headers);

        for ($i = 1; $i < count($lines); $i++) {
            $line = $lines[$i];
            if (trim($line) === '') continue;

            $values = str_getcsv($line);
            if (count($values) < count($headers)) continue;

            $row = [];
            foreach ($headers as $j => $header) {
                $row[$header] = isset($values[$j]) ? $values[$j] : '';
            }
            $rows[] = $row;
        }

        return $rows;
    }

    protected static function parse_dt($dt) {
        if (!$dt) return null;
        $ts = strtotime($dt);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }
}
