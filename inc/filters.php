<?php
/**
 * Cottages filter — canonical server-side query builder.
 * Used by archive templates AND the REST endpoint to ensure parity.
 */

defined('ABSPATH') || exit;

/**
 * @param array $params Filter params (typically $_GET on archive, JSON body on REST)
 * @return WP_Query
 */
function nfedit_property_filter_query($params) {
    $params = wp_parse_args($params, [
        'area'         => '',
        'feature'      => [],
        'forest_coast' => '',
        'dogs'         => '',
        'min_sleeps'   => 0,
        'sort'         => 'editor',
        'paged'        => 1,
        'per_page'     => 12,
    ]);

    // Normalise feature param — accept "log-burner,hot-tub" or array
    if (is_string($params['feature']) && $params['feature'] !== '') {
        $params['feature'] = array_filter(array_map('sanitize_title', explode(',', $params['feature'])));
    }
    if (!is_array($params['feature'])) {
        $params['feature'] = [];
    }

    $tax_query = [];
    if (!empty($params['area'])) {
        $tax_query[] = [
            'taxonomy' => 'area_taxonomy',
            'field'    => 'slug',
            'terms'    => sanitize_title($params['area']),
        ];
    }
    if (!empty($params['feature'])) {
        $tax_query[] = [
            'taxonomy' => 'feature',
            'field'    => 'slug',
            'terms'    => $params['feature'],
            'operator' => 'AND',
        ];
    }
    if (in_array($params['forest_coast'], ['forest', 'coast', 'both'], true)) {
        $tax_query[] = [
            'taxonomy' => 'forest_or_coast',
            'field'    => 'slug',
            'terms'    => $params['forest_coast'],
        ];
    }
    if (count($tax_query) > 1) {
        $tax_query['relation'] = 'AND';
    }

    $meta_query = [];
    if ($params['dogs'] === '1' || $params['dogs'] === 1 || $params['dogs'] === true) {
        $meta_query[] = [
            'key'     => 'dogs_welcome',
            'value'   => '1',
            'compare' => '=',
        ];
    }
    if ((int) $params['min_sleeps'] > 0) {
        $meta_query[] = [
            'key'     => 'sleeps',
            'value'   => (int) $params['min_sleeps'],
            'compare' => '>=',
            'type'    => 'NUMERIC',
        ];
    }
    if (count($meta_query) > 1) {
        $meta_query['relation'] = 'AND';
    }

    $orderby = 'date';
    $order   = 'DESC';
    $meta_key = '';

    switch ($params['sort']) {
        case 'editor':
            $orderby = 'meta_value_num date';
            $order   = 'ASC';
            $meta_key = 'tier';
            break;
        case 'newest':
            $orderby = 'date';
            $order   = 'DESC';
            break;
        case 'price_asc':
            $orderby = 'meta_value_num';
            $order   = 'ASC';
            $meta_key = 'price_from';
            break;
        case 'price_desc':
            $orderby = 'meta_value_num';
            $order   = 'DESC';
            $meta_key = 'price_from';
            break;
    }

    $args = [
        'post_type'      => 'property',
        'post_status'    => 'publish',
        'posts_per_page' => (int) $params['per_page'] > 0 ? (int) $params['per_page'] : 12,
        'paged'          => max(1, (int) $params['paged']),
        'orderby'        => $orderby,
        'order'          => $order,
    ];
    if ($meta_key)         $args['meta_key']  = $meta_key;
    if (!empty($tax_query))  $args['tax_query']  = $tax_query;
    if (!empty($meta_query)) $args['meta_query'] = $meta_query;

    return new WP_Query($args);
}

/**
 * Sanitise raw $_GET into clean filter params.
 */
function nfedit_property_filter_params_from_get($get) {
    $clean = [];
    if (!empty($get['area']))         $clean['area']         = sanitize_title($get['area']);
    if (!empty($get['feature']))      $clean['feature']      = $get['feature'];
    if (!empty($get['forest_coast'])) $clean['forest_coast'] = sanitize_text_field($get['forest_coast']);
    if (!empty($get['dogs']))         $clean['dogs']         = '1';
    if (!empty($get['min_sleeps']))   $clean['min_sleeps']   = (int) $get['min_sleeps'];
    if (!empty($get['sort']))         $clean['sort']         = sanitize_text_field($get['sort']);
    if (!empty($get['paged']))        $clean['paged']        = (int) $get['paged'];
    return $clean;
}

/**
 * Filter pills shown in the bar.
 */
function nfedit_property_filter_pills() {
    return [
        ['id' => 'forest_coast:forest',   'label' => 'Forest',         'param' => 'forest_coast', 'value' => 'forest'],
        ['id' => 'forest_coast:coast',    'label' => 'Coast',          'param' => 'forest_coast', 'value' => 'coast'],
        ['id' => 'dogs:1',                'label' => 'Dogs welcome',   'param' => 'dogs',         'value' => '1'],
        ['id' => 'feature:hot-tub',       'label' => 'Hot tub',        'param' => 'feature',      'value' => 'hot-tub'],
        ['id' => 'min_sleeps:6',          'label' => 'Sleeps 6+',      'param' => 'min_sleeps',   'value' => '6'],
        ['id' => 'feature:near-pub',      'label' => 'Walk to a pub',  'param' => 'feature',      'value' => 'near-pub'],
        ['id' => 'feature:log-burner',    'label' => 'Log burner',     'param' => 'feature',      'value' => 'log-burner'],
    ];
}

function nfedit_property_sort_options() {
    return [
        'editor'      => "Editor's picks first",
        'newest'      => 'Newest',
        'price_asc'   => 'Price: low to high',
        'price_desc'  => 'Price: high to low',
    ];
}
