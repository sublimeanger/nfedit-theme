<?php
/**
 * "Where to stay" — cottages with this area_taxonomy term, 3-up grid.
 */
defined('ABSPATH') || exit;

$post_id   = (int) $args['post_id'];
$area_slug = isset($args['area_slug']) ? (string) $args['area_slug'] : '';
$area_name = get_the_title($post_id);

if (!$area_slug) return;

$cottages = new WP_Query([
    'post_type'      => 'property',
    'posts_per_page' => 6,
    'tax_query'      => [[
        'taxonomy' => 'area_taxonomy',
        'field'    => 'slug',
        'terms'    => $area_slug,
    ]],
    'meta_key' => 'tier',
    'orderby'  => 'meta_value_num date',
    'order'    => 'ASC',
]);

if (!$cottages->have_posts()) {
    wp_reset_postdata();
    return;
}
?>
<section class="nfedit-area-where-to-stay">
    <div class="container-edit">
        <div class="nfedit-area-where-to-stay__head">
            <h2 class="nfedit-area-where-to-stay__heading">Where to stay</h2>
            <a href="<?php echo esc_url(home_url('/cottages/by-area/' . $area_slug . '/')); ?>" class="btn-tertiary">
                <span>All cottages in <?php echo esc_html($area_name); ?></span><span class="arrow">&rarr;</span>
            </a>
        </div>
        <div class="nfedit-area-where-to-stay__grid">
            <?php while ($cottages->have_posts()): $cottages->the_post(); ?>
                <?php get_template_part('template-parts/components/property-card', null, [
                    'post_id' => get_the_ID(),
                    'variant' => 'standard',
                ]); ?>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
