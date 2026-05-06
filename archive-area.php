<?php
/**
 * Archive: Areas index — forest/coast split, map placeholder, editorial rail.
 */
defined('ABSPATH') || exit;
get_header();

$areas_q = new WP_Query([
    'post_type'      => 'area',
    'posts_per_page' => -1,
    'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
]);

$forest_ids = [];
$coast_ids  = [];
if ($areas_q->have_posts()) {
    while ($areas_q->have_posts()) {
        $areas_q->the_post();
        $kind = strtolower((string) get_field('kind', get_the_ID()));
        if ($kind === 'coast') {
            $coast_ids[] = get_the_ID();
        } else {
            // 'forest' or 'both' or empty → forest column (default)
            $forest_ids[] = get_the_ID();
        }
    }
    wp_reset_postdata();
}

$editorials_q = new WP_Query([
    'post_type'      => 'post',
    'posts_per_page' => 4,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
?>

<div class="nfedit-areas-archive">

    <?php get_template_part('template-parts/global/page-hero', null, [
        'eyebrow'   => 'Explore',
        'title'     => 'Ten places to base yourself in the New Forest.',
        'dek'       => "From Brockenhurst high street to Mudeford spit. Where each place sits, what it's like, and what's worth staying for.",
        'image_url' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1920&q=80',
        'height'    => '50vh',
    ]); ?>

    <section class="nfedit-areas-archive__intro">
        <div class="container-edit nfedit-areas-archive__intro-grid">
            <div>
                <p class="eyebrow nfedit-areas-archive__intro-eyebrow">Forest</p>
                <p class="nfedit-areas-archive__intro-body">
                    The inland villages &mdash; Brockenhurst, Lyndhurst, Burley, Beaulieu, Fordingbridge, Ringwood &mdash; share oak woods, heath, and the inevitable ponies in the lanes. Brockenhurst is the most useful base; Lyndhurst the busiest; Burley the most quietly weird. Fordingbridge and Ringwood feel less touristy and reward staying longer.
                </p>
            </div>
            <div>
                <p class="eyebrow nfedit-areas-archive__intro-eyebrow">Coast</p>
                <p class="nfedit-areas-archive__intro-body">
                    Lymington, Milford-on-Sea, Hythe, and Mudeford trade ponies for boatyards and shingle. Lymington has the cobbles and the Saturday market. Milford has the Needles on the horizon. Hythe is for ferry-watchers and Mudeford is for the spit and the chip queue. From any of them, the Forest is twenty minutes inland.
                </p>
            </div>
        </div>
    </section>

    <section class="nfedit-areas-archive__map">
        <div class="container-edit">
            <h2 class="nfedit-areas-archive__map-heading">Where they sit</h2>
            <p class="nfedit-areas-archive__map-dek">Tap any pin to open an area.</p>
        </div>
        <?php get_template_part('template-parts/components/forest-map-placeholder'); ?>
    </section>

    <?php if (!empty($forest_ids)): ?>
        <section class="nfedit-areas-archive__group">
            <div class="container-edit">
                <p class="eyebrow nfedit-areas-archive__group-eyebrow">Forest</p>
                <div class="nfedit-areas-archive__grid">
                    <?php foreach ($forest_ids as $aid): ?>
                        <?php get_template_part('template-parts/components/area-card', null, ['post_id' => $aid]); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <div class="container-edit"><div class="nfedit-areas-archive__divider"></div></div>

    <?php if (!empty($coast_ids)): ?>
        <section class="nfedit-areas-archive__group">
            <div class="container-edit">
                <p class="eyebrow nfedit-areas-archive__group-eyebrow">Coast</p>
                <div class="nfedit-areas-archive__grid">
                    <?php foreach ($coast_ids as $aid): ?>
                        <?php get_template_part('template-parts/components/area-card', null, ['post_id' => $aid]); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php get_template_part('template-parts/global/oracle-promo-compact'); ?>

    <?php if ($editorials_q->have_posts()): ?>
        <section class="nfedit-areas-archive__editorials">
            <div class="container-edit">
                <div class="nfedit-areas-archive__editorials-head">
                    <h2 class="nfedit-areas-archive__editorials-heading">From the <span class="nfedit-areas-archive__italic">Edit</span></h2>
                    <a href="<?php echo esc_url(home_url('/the-edit/')); ?>" class="btn-tertiary">
                        <span>More stories</span><span class="arrow">&rarr;</span>
                    </a>
                </div>
                <div class="nfedit-areas-archive__editorials-grid">
                    <?php while ($editorials_q->have_posts()): $editorials_q->the_post(); ?>
                        <?php get_template_part('template-parts/components/editorial-card', null, [
                            'post_id' => get_the_ID(),
                            'aspect'  => 'landscape',
                            'large'   => false,
                        ]); ?>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php get_template_part('template-parts/global/newsletter-compact'); ?>
</div>

<?php get_footer();
