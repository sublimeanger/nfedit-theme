<?php
/**
 * From The Edit — latest 3 editorials, landscape cards.
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];

$editorials = new WP_Query([
    'post_type'      => 'post',
    'posts_per_page' => 3,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post_status'    => 'publish',
]);

if (!$editorials->have_posts()) {
    wp_reset_postdata();
    return;
}
?>
<section class="nfedit-area-edit">
    <div class="container-edit">
        <div class="nfedit-area-edit__head">
            <h2 class="nfedit-area-edit__heading">From the <span class="nfedit-area-edit__italic">Edit</span></h2>
            <a href="<?php echo esc_url(home_url('/the-edit/')); ?>" class="btn-tertiary">
                <span>More stories</span><span class="arrow">&rarr;</span>
            </a>
        </div>
        <div class="nfedit-area-edit__grid">
            <?php while ($editorials->have_posts()): $editorials->the_post(); ?>
                <?php get_template_part('template-parts/components/editorial-card', null, [
                    'post_id' => get_the_ID(),
                    'aspect'  => 'landscape',
                    'large'   => false,
                ]); ?>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
