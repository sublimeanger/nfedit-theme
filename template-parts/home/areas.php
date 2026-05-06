<?php
/**
 * 5.4 — Areas grid (4-up desktop).
 */
defined('ABSPATH') || exit;

$areas = new WP_Query([
    'post_type'      => 'area',
    'posts_per_page' => 12,
    'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
]);

if (!$areas->have_posts()) {
    wp_reset_postdata();
    return;
}
?>
<section class="nfedit-home-areas">
    <div class="container-edit">
        <div class="nfedit-home-section-head nfedit-home-section-head--stacked">
            <h2 class="nfedit-home-section-head__title">By village, hamlet, and stretch of coast</h2>
            <p class="nfedit-home-section-head__dek">Ten distinct places to base yourself, and what each one&rsquo;s actually like.</p>
        </div>
        <div class="nfedit-home-areas__grid">
            <?php while ($areas->have_posts()): $areas->the_post(); ?>
                <?php get_template_part('template-parts/components/area-card', null, [
                    'post_id' => get_the_ID(),
                ]); ?>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
