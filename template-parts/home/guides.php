<?php
/**
 * 5.8 — Guides rail.
 */
defined('ABSPATH') || exit;

$guides = new WP_Query([
    'post_type'      => 'guide',
    'posts_per_page' => 8,
    'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
]);

if (!$guides->have_posts()) {
    wp_reset_postdata();
    return;
}
?>
<section class="nfedit-home-guides">
    <div class="container-edit">
        <div class="nfedit-home-section-head nfedit-home-section-head--stacked">
            <h2 class="nfedit-home-section-head__title">Before you go</h2>
            <p class="nfedit-home-section-head__dek">What we wish someone had told us.</p>
        </div>
        <div class="nfedit-home-guides__rail scroll-rail">
            <?php while ($guides->have_posts()): $guides->the_post(); ?>
                <?php get_template_part('template-parts/components/guide-card', null, [
                    'post_id' => get_the_ID(),
                ]); ?>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
