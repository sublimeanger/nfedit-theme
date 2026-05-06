<?php
defined('ABSPATH') || exit;
$post_id  = $args['post_id'];
$category = $args['category'];

$query_args = [
    'post_type'      => 'guide',
    'posts_per_page' => 3,
    'post__not_in'   => [$post_id],
    'orderby'        => 'date',
    'order'          => 'DESC',
];

if ($category && !is_wp_error($category)) {
    $query_args['tax_query'] = [[
        'taxonomy' => 'guide_category',
        'field'    => 'term_id',
        'terms'    => $category->term_id,
    ]];
}

$related = new WP_Query($query_args);

if (!$related->have_posts() && !empty($query_args['tax_query'])) {
    wp_reset_postdata();
    unset($query_args['tax_query']);
    $related = new WP_Query($query_args);
}

if (!$related->have_posts()) { wp_reset_postdata(); return; }
?>
<section class="nfedit-guide-related">
    <div class="container-edit">
        <p class="eyebrow nfedit-guide-related__eyebrow">More from the guides desk</p>
        <h2 class="nfedit-guide-related__heading">Keep reading.</h2>
        <div class="nfedit-guide-related__grid">
            <?php while ($related->have_posts()): $related->the_post(); ?>
                <?php get_template_part('template-parts/components/guide-card', null, [
                    'post_id' => get_the_ID(),
                ]); ?>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
