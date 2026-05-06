<?php
/**
 * Shorefield Holidays scraper.
 *
 * Discovery: hits each /holidays/accommodation/{type} listing page,
 * extracts /holidays/accommodation/{type}/{slug} detail URLs.
 *
 * Park assignment: counts /holidays/locations/{slug} link occurrences on
 * the detail page and assigns to whichever park has the most references
 * (footer mentions all parks 2x; main content adds extras for the home park).
 * Filters to New Forest parks only (configurable via wp_option for
 * Oakdene Forest Park inclusion).
 *
 * PHP 7.4 compatible.
 */
defined('ABSPATH') || exit;

class NFEdit_Scraper_Shorefield extends NFEdit_Scraper_Base {

    const HOST = 'www.shorefield.co.uk';

    const PARK_NEW_FOREST = ['shorefield-country-park', 'lytton-lawn-touring-park'];
    const PARK_ADJACENT   = ['oakdene-forest-park'];

    /** New Forest area-taxonomy mapping per park slug. */
    const PARK_AREA_HINT = [
        'shorefield-country-park'   => 'Milford-on-Sea',
        'lytton-lawn-touring-park'  => 'Milford-on-Sea',
        'oakdene-forest-park'       => 'Ringwood',
    ];

    const ACCOMMODATION_TYPES = [
        'lodge-holidays',
        'houses-and-cottages',
        'treehouses',
    ];

    public function get_id(): string    { return 'shorefield-scraper'; }
    public function get_label(): string { return 'Shorefield Holidays (HTML scraper)'; }

    protected function get_allowed_domain(): string {
        return self::HOST;
    }

    public function test_connection() {
        $resp = wp_remote_head('https://' . self::HOST . '/holidays', [
            'timeout'    => 10,
            'user-agent' => self::USER_AGENT,
        ]);
        if (is_wp_error($resp)) return $resp;
        $code = wp_remote_retrieve_response_code($resp);
        if ($code !== 200) return new WP_Error('shorefield_unreachable', "HTTP $code");
        return null;
    }

    public function list_sources(): array {
        $sources           = [];
        $include_adjacent  = (bool) get_option('nfedit_shorefield_include_adjacent', false);
        $park_slugs        = self::PARK_NEW_FOREST;
        if ($include_adjacent) {
            $park_slugs = array_merge($park_slugs, self::PARK_ADJACENT);
        }
        foreach ($park_slugs as $slug) {
            $sources[] = [
                'source_id'     => "shorefield:$slug",
                'label'         => $this->humanise_slug($slug),
                'membership'    => 'joined',
                'product_count' => null,
                'last_remote'   => null,
                'metadata'      => ['park_slug' => $slug],
            ];
        }
        return $sources;
    }

    protected function get_target_url_groups(): array {
        $accommodation_urls = [];
        foreach (self::ACCOMMODATION_TYPES as $t) {
            $accommodation_urls[] = 'https://' . self::HOST . '/holidays/accommodation/' . $t;
        }
        $park_urls = [];
        foreach (self::PARK_NEW_FOREST as $s) {
            $park_urls[] = 'https://' . self::HOST . '/holidays/locations/' . $s;
        }
        return [
            'parks'         => $park_urls,
            'accommodation' => $accommodation_urls,
        ];
    }

    protected function discover_property_urls(): iterable {
        $seen_urls = [];
        foreach ($this->get_target_url_groups()['accommodation'] as $listing_url) {
            $html = $this->fetch_html($listing_url);
            if (!$html) continue;

            $urls = $this->extract_property_urls_from_listing($html);
            foreach ($urls as $url) {
                if (isset($seen_urls[$url])) continue;
                $seen_urls[$url] = true;
                yield $url;
            }
        }
    }

    protected function extract_property_urls_from_listing(string $html): array {
        $urls = [];
        if (preg_match_all(
            '#href="((?:https?://www\.shorefield\.co\.uk)?(/holidays/accommodation/[a-z0-9-]+/[a-z0-9-]+))"#i',
            $html,
            $m
        )) {
            foreach ($m[2] as $path) {
                $urls['https://' . self::HOST . $path] = true;
            }
        }
        return array_keys($urls);
    }

    protected function parse_property_page(string $url, string $html) {
        $canonical = nfedit_canonical_property_template();

        // Title — use og:title (cleanest), strip the brand suffix
        $og_title = nfedit_scraper_meta_content($html, 'og:title', true);
        if ($og_title) {
            $canonical['title'] = nfedit_scraper_first_title_segment($og_title, '|');
        }
        if (!$canonical['title']) {
            $page_title = nfedit_scraper_page_title($html);
            if ($page_title) {
                $canonical['title'] = nfedit_scraper_first_title_segment($page_title, '|');
            }
        }

        // Description — meta description
        $desc = nfedit_scraper_meta_content($html, 'description');
        if (!$desc) {
            $desc = nfedit_scraper_meta_content($html, 'og:description', true);
        }
        if ($desc) $canonical['description'] = $desc;

        // Slug from URL
        if (preg_match('#/([a-z0-9-]+)/?$#i', $url, $m)) {
            $canonical['slug_seed'] = $m[1];
        }

        // Determine accommodation type from URL
        $accommodation_type = '';
        if (preg_match('#/holidays/accommodation/([a-z0-9-]+)/[a-z0-9-]+#i', $url, $m)) {
            $accommodation_type = $m[1];
            // Skip caravan/camping properties even if they slip through discovery
            if (in_array($accommodation_type, ['caravan-holidays', 'camping-and-touring-pitches'], true)) {
                $this->log('scrape_skipped_type', null, "Skipping non-cottage type '$accommodation_type': $url", [], 'info');
                return null;
            }
        }

        // Sleeps
        $sleeps = nfedit_scraper_first_match('/sleeps?\s+(\d+)/i', $html);
        if ($sleeps !== null) $canonical['sleeps'] = (int) $sleeps;

        // Bedrooms
        $bedrooms = nfedit_scraper_first_match('/(\d+)\s+bedrooms?/i', $html);
        if ($bedrooms !== null) $canonical['bedrooms'] = (int) $bedrooms;

        // Pets
        $canonical['pets_welcome'] = preg_match('/pet[\s-]?friendly|muddy paws|dogs welcome|dog[\s-]?friendly/i', $html) === 1;

        // Park assignment via link-counting on detail page
        $park_slug = $this->infer_park_slug($html);
        if (!$park_slug) {
            $this->log('scrape_skipped_no_park', null, "Could not assign park: $url", [], 'warn');
            return null;
        }
        $include_adjacent = (bool) get_option('nfedit_shorefield_include_adjacent', false);
        $allowed_parks    = self::PARK_NEW_FOREST;
        if ($include_adjacent) {
            $allowed_parks = array_merge($allowed_parks, self::PARK_ADJACENT);
        }
        if (!in_array($park_slug, $allowed_parks, true)) {
            $this->log('scrape_skipped_off_target', null, "Park '$park_slug' not in target set: $url", [], 'info');
            return null;
        }
        $canonical['area_hint'] = isset(self::PARK_AREA_HINT[$park_slug]) ? self::PARK_AREA_HINT[$park_slug] : '';

        // Feature hints
        $canonical['feature_hints'] = $this->extract_features($html);

        // Image gallery — filter to URLs containing this property's accommodation/{type}/{slug}/ segment
        $canonical['image_urls'] = $this->extract_image_urls($html, $accommodation_type, $canonical['slug_seed']);

        // Identity + provenance
        $canonical['external_id']   = "shorefield:" . ($canonical['slug_seed'] ?: md5($url));
        $canonical['merchant_slug'] = 'direct';  // Shorefield: no Awin product feed; direct merchant slug pending
        $canonical['advertiser_id'] = 6349;
        $canonical['booking_url']   = $url;       // Lauren replaces with Awin-tagged URL during review
        $canonical['raw_payload']   = [
            'source_url'         => $url,
            'scraped_at'         => time(),
            'park_slug'          => $park_slug,
            'accommodation_type' => $accommodation_type,
        ];
        $canonical['last_seen']     = time();

        return $canonical;
    }

    /**
     * Count /holidays/locations/{slug} link occurrences on the detail page,
     * return the slug with the most references (likely the property's home park).
     * Returns null if no park links found.
     */
    protected function infer_park_slug(string $html) {
        if (!preg_match_all('#href="/holidays/locations/([a-z0-9-]+)"#i', $html, $m)) {
            return null;
        }
        $counts = [];
        foreach ($m[1] as $slug) {
            $counts[$slug] = isset($counts[$slug]) ? $counts[$slug] + 1 : 1;
        }
        if (empty($counts)) return null;
        arsort($counts);
        return key($counts);
    }

    protected function extract_features(string $html): array {
        $hints = [];
        $feature_keywords = [
            'hot tub'         => 'Hot tub',
            'log burner'      => 'Log burner',
            'wood burner'     => 'Log burner',
            'dog friendly'    => 'Dog friendly',
            'dog-friendly'    => 'Dog friendly',
            'pet friendly'    => 'Dog friendly',
            'pet-friendly'    => 'Dog friendly',
            'enclosed garden' => 'Enclosed garden',
            'wifi'            => 'WiFi',
            'parking'         => 'Parking',
            'aga'             => 'Aga',
            'open fire'       => 'Open fire',
            'ev charging'     => 'EV charging',
            'forest access'   => 'Forest access',
        ];
        $lower = mb_strtolower($html);
        foreach ($feature_keywords as $needle => $label) {
            if (strpos($lower, $needle) !== false) $hints[] = $label;
        }
        return array_values(array_unique($hints));
    }

    /**
     * Extract gallery images. Filters to URLs containing this property's
     * accommodation/{type}/{slug}/ path segment so navigation thumbnails
     * for OTHER properties are excluded. Dedupes by basename (multiple
     * URLs with different size-hashes for the same logical image).
     */
    protected function extract_image_urls(string $html, string $accommodation_type, string $slug): array {
        if (!$accommodation_type || !$slug) return [];

        // Match src="..." where path contains /accommodation/{type}/{slug}/...{ext}
        $pattern = '#src="(https://fls-[^"]*/accommodation/' . preg_quote($accommodation_type, '#') . '/' . preg_quote($slug, '#') . '/[^"]+\.(?:webp|jpg|jpeg|png))"#i';
        if (!preg_match_all($pattern, $html, $m)) return [];

        // Dedupe by basename (Glide produces N size-variants of the same image)
        $by_basename = [];
        foreach ($m[1] as $url) {
            $base = basename(parse_url($url, PHP_URL_PATH));
            // Glide URL format: .../{filename.ext}/{hash}/{filename.ext} — basename is the hash file
            // The actual image identifier is the last path segment before the hash
            $parts = explode('/', parse_url($url, PHP_URL_PATH));
            // Walk backwards, find the first segment that ends in .webp/.jpg/.png — that's the canonical name
            $canonical_name = '';
            foreach (array_reverse($parts) as $seg) {
                if (preg_match('/\.(?:webp|jpg|jpeg|png)$/i', $seg)) {
                    $canonical_name = $seg;
                    break;
                }
            }
            if (!$canonical_name) $canonical_name = $base;
            // Prefer the largest URL we see for this canonical name (just keep first occurrence — they're equivalent)
            if (!isset($by_basename[$canonical_name])) {
                $by_basename[$canonical_name] = $url;
            }
        }
        return array_values($by_basename);
    }

    protected function humanise_slug(string $slug): string {
        return ucwords(str_replace('-', ' ', $slug));
    }
}
