<?php
/**
 * 6.19 — Related cottages.
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];

// Tier 1 curated relationship first
$related = get_field('related_cottages', $post_id);
$related_ids = [];
if (is_array($related)) {
    foreach ($related as $r) {
        $rid = is_object($r) ? (int) $r->ID : (int) $r;
        if ($rid) $related_ids[] = $rid;
    }
}

// Fallback: same area, exclude self
if (empty($related_ids)) {
    $area_terms = wp_get_post_terms($post_id, 'area_taxonomy', ['fields' => 'ids']);
    $q_args = [
        'post_type'      => 'property',
        'post_status'    => 'publish',
        'posts_per_page' => 4,
        'post__not_in'   => [$post_id],
        'orderby'        => 'rand',
        'fields'         => 'ids',
    ];
    if (!empty($area_terms) && !is_wp_error($area_terms)) {
        $q_args['tax_query'] = [[
            'taxonomy' => 'area_taxonomy',
            'field'    => 'term_id',
            'terms'    => $area_terms,
        ]];
    }
    $q = new WP_Query($q_args);
    $related_ids = $q->posts;
}

if (empty($related_ids)) return;
?>
<section class="nfedit-property-related">
    <div class="container-edit">
        <h2 class="nfedit-property-related__heading">Other places we love nearby</h2>
        <div class="nfedit-property-related__rail scroll-rail">
            <?php foreach ($related_ids as $rid):
                get_template_part('template-parts/components/property-card', null, [
                    'post_id' => (int) $rid,
                    'variant' => 'minimal',
                ]);
            endforeach; ?>
        </div>
    </div>
</section>
