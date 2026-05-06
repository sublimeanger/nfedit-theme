<?php
/**
 * Nearby areas — auto-resolved by forest_or_coast taxonomy, exclude self, max 4.
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];

$fc_terms = wp_get_post_terms($post_id, 'forest_or_coast', ['fields' => 'slugs']);
$fc = (!empty($fc_terms) && !is_wp_error($fc_terms)) ? $fc_terms[0] : '';

$query_args = [
    'post_type'      => 'area',
    'posts_per_page' => 4,
    'post__not_in'   => [$post_id],
    'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
];

if ($fc === 'forest' || $fc === 'coast') {
    $query_args['tax_query'] = [[
        'taxonomy' => 'forest_or_coast',
        'field'    => 'slug',
        'terms'    => [$fc, 'both'],
    ]];
}

$nearby = new WP_Query($query_args);

if (!$nearby->have_posts()) {
    wp_reset_postdata();
    unset($query_args['tax_query']);
    $nearby = new WP_Query($query_args);
}

if (!$nearby->have_posts()) {
    wp_reset_postdata();
    return;
}
?>
<section class="nfedit-area-nearby">
    <div class="container-edit">
        <h2 class="nfedit-area-nearby__heading">Nearby areas</h2>
        <div class="nfedit-area-nearby__grid">
            <?php while ($nearby->have_posts()): $nearby->the_post();
                $aid   = get_the_ID();
                $kind  = (string) get_field('kind', $aid);
                $count = (int) get_field('count_label', $aid);
                if (!$count) {
                    $a_terms = wp_get_post_terms($aid, 'area_taxonomy', ['fields' => 'slugs']);
                    if (!empty($a_terms) && !is_wp_error($a_terms)) {
                        $cnt_q = new WP_Query([
                            'post_type'      => 'property',
                            'posts_per_page' => -1,
                            'fields'         => 'ids',
                            'tax_query'      => [[
                                'taxonomy' => 'area_taxonomy',
                                'field'    => 'slug',
                                'terms'    => $a_terms,
                            ]],
                        ]);
                        $count = (int) $cnt_q->found_posts;
                        wp_reset_postdata();
                    }
                }
            ?>
                <a href="<?php echo esc_url(get_permalink($aid)); ?>" class="nfedit-area-nearby__item">
                    <?php if ($kind): ?>
                        <p class="eyebrow nfedit-area-nearby__kind"><?php echo esc_html(strtoupper($kind)); ?></p>
                    <?php endif; ?>
                    <p class="nfedit-area-nearby__name"><?php echo esc_html(get_the_title($aid)); ?></p>
                    <?php if ($count > 0): ?>
                        <p class="nfedit-area-nearby__count"><?php echo (int) $count; ?> cottage<?php echo $count === 1 ? '' : 's'; ?></p>
                    <?php endif; ?>
                </a>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
