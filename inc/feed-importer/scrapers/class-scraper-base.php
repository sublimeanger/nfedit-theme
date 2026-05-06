<?php
/**
 * Abstract base for HTML scraper adapters.
 *
 * Implements the same NFEdit_Feed_Adapter_Base contract as the Awin adapter,
 * with scraping-specific concerns: domain validation, rate limiting, robots.txt
 * compliance, transient caching.
 *
 * PHP 7.4 compatible.
 */
defined('ABSPATH') || exit;

abstract class NFEdit_Scraper_Base extends NFEdit_Feed_Adapter_Base {

    const USER_AGENT = 'NFEdit-Research/1.0 (https://newforestedit.co.uk/contact/)';
    const REQUEST_DELAY_MIN_MS = 2000;
    const REQUEST_DELAY_MAX_MS = 4000;
    const REQUEST_TIMEOUT      = 60;
    const CACHE_TTL_HOURS      = 24;

    /** Domain this scraper is allowed to fetch. */
    abstract protected function get_allowed_domain(): string;

    /** Source URL groups (parks, listings) the scraper crawls. */
    abstract protected function get_target_url_groups(): array;

    /** Parse a single property detail page; return canonical-property array or null. */
    abstract protected function parse_property_page(string $url, string $html);

    /** Yield discovered detail-page URLs. */
    abstract protected function discover_property_urls(): iterable;

    /**
     * Cached HTTP GET with rate limiting + robots.txt + domain check.
     * Returns body string or null on failure.
     */
    protected function fetch_html(string $url) {
        $host = parse_url($url, PHP_URL_HOST);
        if ($host !== $this->get_allowed_domain()) {
            $this->log('scrape_domain_rejected', null, "URL outside allowed domain: $url", [], 'error');
            return null;
        }

        $cache_key = 'nfedit_scrape_' . md5($url);
        $cached    = get_transient($cache_key);
        if ($cached !== false) {
            $this->log('scrape_cache_hit', null, "Cache hit: $url", [], 'info');
            return $cached;
        }

        $delay = mt_rand(self::REQUEST_DELAY_MIN_MS, self::REQUEST_DELAY_MAX_MS);
        usleep($delay * 1000);

        if (!$this->is_allowed_by_robots($url)) {
            $this->log('scrape_robots_rejected', null, "Blocked by robots.txt: $url", [], 'warn');
            return null;
        }

        $start = microtime(true);
        $resp  = wp_remote_get($url, [
            'timeout'     => self::REQUEST_TIMEOUT,
            'redirection' => 5,
            'user-agent'  => self::USER_AGENT,
            'headers'     => [
                'Accept'          => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'en-GB,en;q=0.9',
            ],
        ]);
        $duration_ms = (int) round((microtime(true) - $start) * 1000);

        if (is_wp_error($resp)) {
            $this->log('scrape_fetch_error', null, $resp->get_error_message(), ['url' => $url], 'error');
            return null;
        }
        $code = wp_remote_retrieve_response_code($resp);
        if ($code !== 200) {
            $this->log('scrape_fetch_error', null, "HTTP $code", ['url' => $url, 'duration_ms' => $duration_ms], 'error');
            return null;
        }

        $body = wp_remote_retrieve_body($resp);
        set_transient($cache_key, $body, self::CACHE_TTL_HOURS * HOUR_IN_SECONDS);
        $this->log('scrape_fetched', null, "Fetched $url ({$duration_ms}ms, " . strlen($body) . "b)", [], 'info');
        return $body;
    }

    /**
     * robots.txt check, 24h cached. Conservative defaults.
     */
    protected function is_allowed_by_robots(string $url): bool {
        $host       = parse_url($url, PHP_URL_HOST);
        $cache_key  = 'nfedit_robots_' . md5($host);
        $rules      = get_transient($cache_key);

        if ($rules === false) {
            $robots_url = "https://$host/robots.txt";
            $resp = wp_remote_get($robots_url, [
                'timeout'    => 10,
                'user-agent' => self::USER_AGENT,
            ]);
            if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
                $rules = ['allow_all' => true];
            } else {
                $rules = $this->parse_robots_txt(wp_remote_retrieve_body($resp));
            }
            set_transient($cache_key, $rules, self::CACHE_TTL_HOURS * HOUR_IN_SECONDS);
        }

        if (!empty($rules['allow_all'])) return true;

        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) $path = '/';

        $applicable = isset($rules[self::USER_AGENT]) ? $rules[self::USER_AGENT]
                    : (isset($rules['*']) ? $rules['*'] : ['disallow' => []]);

        foreach ($applicable['disallow'] as $disallow_path) {
            if ($disallow_path === '') continue;
            if (strpos($path, $disallow_path) === 0) return false;
        }
        return true;
    }

    protected function parse_robots_txt(string $body): array {
        $rules           = [];
        $current_agents  = [];
        foreach (preg_split('/\r?\n/', $body) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (preg_match('/^User-agent:\s*(.+)$/i', $line, $m)) {
                $current_agents = [trim($m[1])];
                foreach ($current_agents as $a) {
                    if (!isset($rules[$a])) $rules[$a] = ['disallow' => []];
                }
            } elseif (preg_match('/^Disallow:\s*(.*)$/i', $line, $m)) {
                $path = trim($m[1]);
                foreach ($current_agents as $a) {
                    $rules[$a]['disallow'][] = $path;
                }
            }
        }
        if (isset($rules['*']['disallow'])
            && count($rules['*']['disallow']) === 1
            && $rules['*']['disallow'][0] === '') {
            $rules['allow_all'] = true;
        }
        return $rules;
    }

    /**
     * Default fetch_properties: discover URLs, fetch, parse, yield.
     */
    public function fetch_properties(string $source_id): iterable {
        foreach ($this->discover_property_urls() as $url) {
            $html = $this->fetch_html($url);
            if (!$html) continue;
            $canonical = $this->parse_property_page($url, $html);
            if ($canonical) yield $canonical;
        }
    }
}
