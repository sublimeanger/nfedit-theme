<?php
/**
 * 5.5 — From The Edit (1 featured + 2 secondary editorials).
 */
defined('ABSPATH') || exit;

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

$ids = [];
foreach ($editorials->posts as $p) { $ids[] = (int) $p->ID; }
$featured_id = isset($ids[0]) ? $ids[0] : 0;
$rest_ids    = array_slice($ids, 1);
?>
<section class="nfedit-home-edit">
    <div class="container-edit">
        <div class="nfedit-home-section-head">
            <div>
                <h2 class="nfedit-home-section-head__title">From the <span class="nfedit-home-edit__italic">Edit</span></h2>
                <p class="nfedit-home-section-head__dek">Stories, walks, and where we send our friends.</p>
            </div>
            <a href="<?php echo esc_url(home_url('/the-edit/')); ?>" class="btn-tertiary">
                <span>More from The Edit</span><span class="arrow">&rarr;</span>
            </a>
        </div>

        <div class="nfedit-home-edit__grid">
            <?php if ($featured_id): ?>
                <div class="nfedit-home-edit__featured">
                    <?php get_template_part('template-parts/components/editorial-card', null, [
                        'post_id' => $featured_id,
                        'large'   => true,
                        'aspect'  => 'portrait',
                    ]); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($rest_ids)): ?>
                <div class="nfedit-home-edit__rest">
                    <?php foreach ($rest_ids as $id): ?>
                        <?php get_template_part('template-parts/components/editorial-card', null, [
                            'post_id' => $id,
                            'large'   => false,
                            'aspect'  => 'landscape',
                        ]); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php wp_reset_postdata(); ?>
