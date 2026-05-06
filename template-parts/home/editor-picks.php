<?php
/**
 * 5.3 — Editor's picks rail (Tier 1 properties).
 */
defined('ABSPATH') || exit;

$picks = new WP_Query([
    'post_type'      => 'property',
    'posts_per_page' => 12,
    'meta_query'     => [
        ['key' => 'tier', 'value' => 1, 'compare' => '='],
    ],
    'orderby' => 'date',
    'order'   => 'DESC',
]);

if (!$picks->have_posts()) {
    wp_reset_postdata();
    return;
}
?>
<section class="nfedit-home-picks">
    <div class="container-edit">
        <div class="nfedit-home-section-head">
            <div>
                <h2 class="nfedit-home-section-head__title">Cottages we love right now</h2>
                <p class="nfedit-home-section-head__dek">A handful of the places we&rsquo;d send our friends. Updated as we find new favourites.</p>
            </div>
            <a href="<?php echo esc_url(get_post_type_archive_link('property')); ?>" class="btn-tertiary">
                <span>View all cottages</span><span class="arrow">&rarr;</span>
            </a>
        </div>
    </div>
    <div class="container-edit">
        <div class="nfedit-home-picks__rail scroll-rail">
            <?php while ($picks->have_posts()): $picks->the_post(); ?>
                <?php get_template_part('template-parts/components/property-card', null, [
                    'post_id' => get_the_ID(),
                    'variant' => 'standard',
                ]); ?>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
