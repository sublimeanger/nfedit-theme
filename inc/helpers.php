<?php
/**
 * Reusable helpers — the contracts later phases (4-10) consume.
 */

defined('ABSPATH') || exit;

/**
 * Render inline markdown — ports Lovable's renderInline() (Cottage.tsx L36-49).
 * Supports **bold** and *italic*. Escapes HTML first.
 */
function nfedit_render_inline_markdown($text) {
    $text = esc_html((string) $text);
    $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*([^*]+)\*/',     '<em>$1</em>',         $text);
    return $text;
}

/**
 * Should a tier-gated section be shown for a property?
 *
 * @param int    $post_id
 * @param string $section_key  doctrine|owner_quote|things_to_do|owner_recs|sustainability|one_liner|review|surrounds|policy
 * @return bool
 */
function nfedit_property_show_section($post_id, $section_key) {
    $tier = function_exists('get_field') ? (int) get_field('tier', $post_id) : 1;
    if ($tier < 1) $tier = 1;

    // tier_required = highest tier value at which the section is still shown
    $tier_required = [
        'doctrine'       => 1,
        'owner_quote'    => 1,
        'things_to_do'   => 1,
        'owner_recs'     => 1,
        'sustainability' => 1,
        'one_liner'      => 2, // T1+T2
        'review'         => 2,
        'surrounds'      => 2,
        'policy'         => 2,
    ];

    if (!isset($tier_required[$section_key])) return true;
    return $tier <= $tier_required[$section_key];
}

/**
 * Property card data shape — matches Lovable's Cottage type.
 * Used by template-parts/components/property-card.php (Phase 4+).
 *
 * @param int $post_id
 * @return array
 */
function nfedit_get_property_card_data($post_id) {
    $tier        = function_exists('get_field') ? (int) get_field('tier', $post_id) : 0;
    $area_terms  = get_the_terms($post_id, 'area_taxonomy');
    $area        = ($area_terms && !is_wp_error($area_terms)) ? $area_terms[0]->name : '';
    $price_unit  = function_exists('get_field') ? get_field('price_unit', $post_id) : '';

    return [
        'id'        => get_post_field('post_name', $post_id),
        'tier'      => $tier,
        'name'      => get_the_title($post_id),
        'area'      => $area,
        'oneLiner'  => function_exists('get_field') ? (string) get_field('one_liner', $post_id) : '',
        'image'     => get_the_post_thumbnail_url($post_id, 'nfedit_card_4_3'),
        'sleeps'    => function_exists('get_field') ? (int) get_field('sleeps', $post_id) : 0,
        'bedrooms'  => function_exists('get_field') ? (int) get_field('bedrooms', $post_id) : 0,
        'dogs'      => function_exists('get_field') ? (bool) get_field('dogs_welcome', $post_id) : false,
        'priceFrom' => function_exists('get_field') ? (float) get_field('price_from', $post_id) : 0.0,
        'priceUnit' => $price_unit ? $price_unit : 'night',
        'permalink' => get_permalink($post_id),
    ];
}

/**
 * Count properties tagged with a given area term.
 *
 * @param int $term_id
 * @return int
 */
function nfedit_count_properties_in_area($term_id) {
    $q = new WP_Query([
        'post_type'      => 'property',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'tax_query'      => [[
            'taxonomy' => 'area_taxonomy',
            'field'    => 'term_id',
            'terms'    => (int) $term_id,
        ]],
    ]);
    return (int) $q->found_posts;
}

/**
 * Resolve a feature taxonomy term slug to its Lucide icon slug.
 * Returns 'trees' as fallback if unmapped.
 */
function nfedit_feature_to_icon($feature_slug) {
    $map = [
        'log-burner'      => 'flame',
        'open-fire'       => 'flame',
        'hot-tub'         => 'droplets',
        'dog-friendly'    => 'dog',
        'parking'         => 'car',
        'forest-access'   => 'trees',
        'walk-from-door'  => 'trees',
        'wifi'            => 'wifi',
        'ev-charging'     => 'plug',
        'accessible'      => 'accessibility',
        'enclosed-garden' => 'trees',
        'aga'             => 'flame',
        'near-pub'        => 'beer',
        'big-group'       => 'users',
        'couples'         => 'heart',
        'sleeps-6'        => 'users',
    ];
    return isset($map[$feature_slug]) ? $map[$feature_slug] : 'trees';
}

/**
 * Render an inline Lucide SVG. PHP 7.4 compatible.
 *
 * @param string $name  Icon slug
 * @param array  $attrs Override class/width/height/stroke-width
 * @return string HTML
 */
function nfedit_lucide_svg($name, $attrs = []) {
    $defaults = [
        'class'        => 'nfedit-icon',
        'width'        => 16,
        'height'       => 16,
        'stroke-width' => 1.5,
    ];
    $a = array_merge($defaults, $attrs);

    $paths = [
        'users'         => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'bed-double'    => '<path d="M2 20v-8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8"/><path d="M4 10V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"/><path d="M12 4v6"/><path d="M2 18h20"/>',
        'bath'          => '<path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-.5C4.683 3 4 3.683 4 4.5V17a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5h2v-2H4"/><line x1="10" y1="5" x2="8" y2="7"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="7" y1="19" x2="7" y2="21"/><line x1="17" y1="19" x2="17" y2="21"/>',
        'dog'           => '<path d="M11.25 16.25h1.5L12 17z"/><path d="M16 14v.5"/><path d="M4.42 11.247A13.152 13.152 0 0 0 4 14.556C4 18.728 7.582 21 12 21s8-2.272 8-6.444a11.702 11.702 0 0 0-.493-3.309"/><path d="M8 14v.5"/><path d="M8.5 8.5c-.384 1.05-1.083 2.028-2.344 2.5-1.931.722-3.576-.297-3.656-1-.113-.994 1.177-6.53 4-7 1.923-.321 3.651.845 3.651 2.235A7.497 7.497 0 0 1 14 5c0-1.39 1.844-2.598 3.767-2.277 2.823.47 4.113 6.006 4 7-.08.703-1.725 1.722-3.656 1-1.261-.472-1.96-1.45-2.344-2.5"/>',
        'map-pin'       => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'bookmark'      => '<path d="m19 21-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/>',
        'share-2'       => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>',
        'flame'         => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
        'wifi'          => '<path d="M5 13a10 10 0 0 1 14 0"/><path d="M8.5 16.5a5 5 0 0 1 7 0"/><path d="M2 8.82a15 15 0 0 1 20 0"/><line x1="12" y1="20" x2="12.01" y2="20"/>',
        'car'           => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>',
        'trees'         => '<path d="M10 10v.2A3 3 0 0 1 8.9 16v0H5v0h0a3 3 0 0 1-1-5.8V10a3 3 0 0 1 6 0Z"/><path d="M7 16v6"/><path d="M13 19h6"/><path d="M16 16v6"/><path d="m22 13-1.296-.644a6.43 6.43 0 0 1-3.4-5.625v-.071A1.66 1.66 0 0 0 15.654 5H15a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1h-1a1 1 0 0 0-1 1v.143a3.86 3.86 0 0 1-1.93 3.342L8 14"/>',
        'plug'          => '<path d="M12 22v-5"/><path d="M9 7V2"/><path d="M15 7V2"/><path d="M6 13V8h12v5a4 4 0 0 1-4 4h-4a4 4 0 0 1-4-4Z"/>',
        'accessibility' => '<circle cx="16" cy="4" r="1"/><path d="m18 19 1-7-6 1"/><path d="m5 8 3-3 5.5 3-2.36 3.5"/><path d="M4.24 14.5a5 5 0 0 0 6.88 6"/><path d="M13.76 17.5a5 5 0 0 0-6.88-6"/>',
        'droplets'      => '<path d="M7 16.3c2.2 0 4-1.83 4-4.05 0-1.16-.57-2.26-1.71-3.19S7.29 6.75 7 5.3c-.29 1.45-1.14 2.84-2.29 3.76S3 11.1 3 12.25c0 2.22 1.8 4.05 4 4.05Z"/><path d="M12.56 6.6A10.97 10.97 0 0 0 14 3.02c.5 2.5 2 4.9 4 6.5s3 3.5 3 5.5a6.98 6.98 0 0 1-11.91 4.97"/>',
        'x'             => '<path d="M18 6L6 18M6 6l12 12"/>',
        'chevron-left'  => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'plus'          => '<path d="M5 12h14M12 5v14"/>',
        'minus'         => '<path d="M5 12h14"/>',
        'beer'          => '<path d="M17 11h1a3 3 0 0 1 0 6h-1"/><path d="M9 12v6"/><path d="M13 12v6"/><path d="M14 7.5c-1 0-1.44.5-3 .5s-2-.5-3-.5-1.72.5-2.5.5a2.5 2.5 0 0 1 0-5c.78 0 1.57.5 2.5.5C9.44 3.5 10 3 12 3s2.56.5 4 .5c.93 0 1.72-.5 2.5-.5a2.5 2.5 0 0 1 0 5c-.78 0-1.5-.5-2.5-.5Z"/><path d="M5 8v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8"/>',
        'heart'         => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'compass'      => '<circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>',
        'tag'          => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
        'sparkles'     => '<path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/>',
        'instagram'    => '<rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>',
        'mail'         => '<rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,6 12,13 2,6"/>',
        'train'         => '<rect x="4" y="3" width="16" height="16" rx="2"/><path d="M4 11h16"/><path d="M12 3v8"/><path d="m8 19-2 3"/><path d="m18 22-2-3"/><path d="M8 15h.01"/><path d="M16 15h.01"/>',
    ];

    $path = isset($paths[$name]) ? $paths[$name] : $paths['trees'];

    return sprintf(
        '<svg class="%s" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="%s" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>',
        esc_attr($a['class']),
        (int) $a['width'],
        (int) $a['height'],
        esc_attr((string) $a['stroke-width']),
        $path
    );
}

/**
 * Resolve a feature taxonomy archive's hero meta — title, intro, label, image.
 * Curated copy for known features; generic fallback for the rest.
 */
function nfedit_feature_archive_hero_meta($slug, $name) {
    $defaults = [
        'hot-tub' => [
            'title' => 'Cottages with hot tubs',
            'intro' => "We only list a hot tub if it's properly maintained, regularly serviced, and either covered or in a spot the hosts actually clean. We've turned away properties whose 'hot tub' was a Lidl inflatable that hadn't been used in eighteen months.",
            'label' => 'Hot tub',
            'image' => 'https://images.unsplash.com/photo-1571902943202-507ec2618e8f?auto=format&fit=crop&w=1600&q=80',
        ],
        'log-burner' => [
            'title' => 'Cottages with log burners',
            'intro' => "A log burner needs to be more than a feature in the listing. We check that there's a flue that's been swept this year, a basket of seasoned logs included in the rate, and instructions left for guests who haven't lit one before.",
            'label' => 'Log burner',
            'image' => 'https://images.unsplash.com/photo-1483794344563-d27a8d18014e?auto=format&fit=crop&w=1600&q=80',
        ],
        'dog-friendly' => [
            'title' => 'Dog-friendly cottages',
            'intro' => "'Dog friendly' has to mean more than 'pets tolerated for £50/dog/night'. We look for enclosed gardens (or at least honestly-described ones), water bowls, dog towels, and a treat on arrival. We mark the ones whose hosts genuinely seem pleased to meet your dog.",
            'label' => 'Dogs welcome',
            'image' => 'https://images.unsplash.com/photo-1530549387789-4c1017266635?auto=format&fit=crop&w=1600&q=80',
        ],
        'forest-access' => [
            'title' => 'Walk-from-the-door forest access',
            'intro' => "These cottages give you a walk that starts at the back gate, with no road-crossing required. We've measured each one. Anything more than 200 metres of pavement to the first hoof-print and we won't list it under this label.",
            'label' => 'Forest access',
            'image' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1600&q=80',
        ],
    ];

    if (isset($defaults[$slug])) {
        $d = $defaults[$slug];
        return [
            'title'     => $d['title'],
            'intro'     => $d['intro'],
            'label'     => $d['label'],
            'image_url' => $d['image'],
        ];
    }

    return [
        'title'     => sprintf('Cottages with %s', $name),
        'intro'     => 'Our criteria for this feature are explicit. We don&rsquo;t list properties that mention it loosely in a description; only those where the host has confirmed it and we&rsquo;ve seen it in the photos.',
        'label'     => $name,
        'image_url' => 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=1600&q=80',
    ];
}

/**
 * Render the mid-article cottage rail HTML for editorial body injection.
 *
 * @param array $cottages Array of post objects or post IDs from ACF Relationship field.
 * @return string HTML
 */
function nfedit_render_mid_article_rail($cottages) {
    if (empty($cottages) || !is_array($cottages)) return '';

    ob_start();
    ?>
    <aside class="nfedit-mid-article-rail" aria-label="Cottages mentioned in this article">
        <p class="eyebrow nfedit-mid-article-rail__eyebrow">We&rsquo;d stay at one of these, ourselves.</p>
        <div class="nfedit-mid-article-rail__grid">
            <?php foreach ($cottages as $c):
                $cid = is_object($c) ? (int) $c->ID : (int) $c;
                if (!$cid) continue;
                get_template_part('template-parts/components/property-card', null, [
                    'post_id' => $cid,
                    'variant' => 'standard',
                ]);
            endforeach; ?>
        </div>
    </aside>
    <?php
    return ob_get_clean();
}

/**
 * Phase 10B: Parse a wysiwyg HTML body, inject `id` attributes onto every <h2>,
 * and return the modified HTML plus an array of TOC items.
 *
 * @param string $html Raw wysiwyg body content.
 * @return array [ string $html_with_ids, array $toc_items ]
 */
function nfedit_legal_build_toc_and_body($html) {
    $html = (string) $html;
    if (!$html) return ['', []];

    $allowed = wp_kses_allowed_html('post');
    if (isset($allowed['h2']) && !isset($allowed['h2']['id'])) {
        $allowed['h2']['id'] = true;
    }
    $html = wp_kses($html, $allowed);

    $toc_items = [];
    $doc = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $wrapped = '<?xml encoding="UTF-8"><div>' . $html . '</div>';
    $doc->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $h2s = $doc->getElementsByTagName('h2');
    $used_ids = [];
    foreach ($h2s as $h2) {
        $text = trim($h2->textContent);
        if (!$text) continue;
        $base_id = sanitize_title($text);
        if (!$base_id) $base_id = 'section';
        $id = $base_id;
        $i = 2;
        while (in_array($id, $used_ids, true)) {
            $id = $base_id . '-' . $i;
            $i++;
        }
        $used_ids[] = $id;
        $h2->setAttribute('id', $id);
        $toc_items[] = ['id' => $id, 'text' => $text];
    }

    $body_html = '';
    $root = $doc->documentElement;
    if ($root) {
        foreach ($root->childNodes as $child) {
            $body_html .= $doc->saveHTML($child);
        }
    }

    return [$body_html, $toc_items];
}
