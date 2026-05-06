<?php
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$cluster = isset($args['cluster']) ? $args['cluster'] : null;

$query_args = [
    'post_type'      => 'post',
    'posts_per_page' => 3,
    'post__not_in'   => [$post_id],
    'orderby'        => 'date',
    'order'          => 'DESC',
];

if ($cluster && !is_wp_error($cluster) && isset($cluster->term_id)) {
    $query_args['tax_query'] = [[
        'taxonomy' => 'cluster',
        'field'    => 'term_id',
        'terms'    => (int) $cluster->term_id,
    ]];
}

$related = new WP_Query($query_args);

// Fallback: any 3 latest, exclude self
if (!$related->have_posts() && !empty($query_args['tax_query'])) {
    wp_reset_postdata();
    unset($query_args['tax_query']);
    $related = new WP_Query($query_args);
}

if (!$related->have_posts()) {
    wp_reset_postdata();
    return;
}

$cluster_lower = ($cluster && isset($cluster->name)) ? strtolower($cluster->name) : 'The Edit';
?>
<section class="nfedit-editorial-related">
    <div class="container-edit">
        <p class="eyebrow nfedit-editorial-related__eyebrow">Keep reading</p>
        <h2 class="nfedit-editorial-related__heading">More in <?php echo esc_html($cluster_lower); ?>.</h2>
        <div class="nfedit-editorial-related__grid">
            <?php while ($related->have_posts()): $related->the_post(); ?>
                <?php get_template_part('template-parts/components/editorial-card', null, [
                    'post_id' => get_the_ID(),
                    'aspect'  => 'landscape',
                    'large'   => false,
                ]); ?>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
