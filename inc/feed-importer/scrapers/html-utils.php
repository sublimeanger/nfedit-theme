<?php
/**
 * Shared DOMDocument helpers for scrapers.
 * PHP 7.4 compatible.
 */
defined('ABSPATH') || exit;

/**
 * Load HTML safely with libxml warning suppression. UTF-8 wrapped.
 */
function nfedit_scraper_load_html($html) {
    $doc = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    @$doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    return $doc;
}

/**
 * Extract first regex match from HTML.
 * @return string|null
 */
function nfedit_scraper_first_match($pattern, $html, $group = 1) {
    if (preg_match($pattern, $html, $m)) {
        return isset($m[$group]) ? trim($m[$group]) : null;
    }
    return null;
}

/**
 * Extract value of meta tag by name or property.
 */
function nfedit_scraper_meta_content($html, $key, $is_property = false) {
    $attr = $is_property ? 'property' : 'name';
    $key_q = preg_quote($key, '#');
    if (preg_match('#<meta\s+[^>]*' . $attr . '="' . $key_q . '"[^>]+content="([^"]+)"#i', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    if (preg_match('#<meta\s+[^>]*content="([^"]+)"[^>]+' . $attr . '="' . $key_q . '"#i', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    return null;
}

/**
 * Extract <title> contents (collapsed whitespace, decoded entities).
 */
function nfedit_scraper_page_title($html) {
    if (preg_match('#<title[^>]*>(.*?)</title>#is', $html, $m)) {
        $t = preg_replace('/\s+/', ' ', trim($m[1]));
        return html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    return null;
}

/**
 * Take "Coppertree House | Treehouses | Shorefield Holidays ®" and return "Coppertree House".
 */
function nfedit_scraper_first_title_segment($title, $sep = '|') {
    if (!$title) return '';
    $parts = explode($sep, $title);
    return trim($parts[0]);
}
