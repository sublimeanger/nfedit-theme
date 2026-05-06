<?php
defined('ABSPATH') || exit;

$post_id      = (int) $args['post_id'];
$mid_cottages = isset($args['mid_cottages']) ? $args['mid_cottages'] : [];

$excluded = [];
foreach ($mid_cottages as $c) {
    $excluded[] = is_object($c) ? (int) $c->ID : (int) $c;
}
$excluded = array_filter($excluded);

$cottages = new WP_Query([
    'post_type'      => 'property',
    'posts_per_page' => 3,
    'post__not_in'   => !empty($excluded) ? $excluded : [0],
    'meta_query'     => [[
        'key'   => 'tier',
        'value' => 1,
    ]],
    'orderby' => 'rand',
]);

if (!$cottages->have_posts()) {
    wp_reset_postdata();
    return;
}
?>
<section class="nfedit-editorial-related-cottages">
    <div class="container-edit">
        <div class="nfedit-editorial-related-cottages__head">
            <div>
                <p class="eyebrow nfedit-editorial-related-cottages__eyebrow">Stay nearby</p>
                <h2 class="nfedit-editorial-related-cottages__heading">Cottages that fit the story.</h2>
            </div>
            <a href="<?php echo esc_url(get_post_type_archive_link('property')); ?>" class="btn-tertiary nfedit-editorial-related-cottages__link">
                <span>All cottages</span><span class="arrow">&rarr;</span>
            </a>
        </div>
        <div class="nfedit-editorial-related-cottages__grid">
            <?php while ($cottages->have_posts()): $cottages->the_post(); ?>
                <?php get_template_part('template-parts/components/property-card', null, [
                    'post_id' => get_the_ID(),
                    'variant' => 'standard',
                ]); ?>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
