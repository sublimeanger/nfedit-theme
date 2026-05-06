<?php
/**
 * 5.6 — Collections grid.
 */
defined('ABSPATH') || exit;

$collections = new WP_Query([
    'post_type'      => 'collection',
    'posts_per_page' => 12,
    'orderby'        => ['menu_order' => 'ASC', 'date' => 'DESC'],
]);

if (!$collections->have_posts()) {
    wp_reset_postdata();
    return;
}
?>
<section class="nfedit-home-collections">
    <div class="container-edit">
        <div class="nfedit-home-section-head">
            <h2 class="nfedit-home-section-head__title">Browse by what you fancy</h2>
            <a href="<?php echo esc_url(get_post_type_archive_link('collection')); ?>" class="btn-tertiary">
                <span>All collections</span><span class="arrow">&rarr;</span>
            </a>
        </div>
        <div class="nfedit-home-collections__grid">
            <?php while ($collections->have_posts()): $collections->the_post(); ?>
                <?php get_template_part('template-parts/components/collection-card', null, [
                    'post_id' => get_the_ID(),
                ]); ?>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
