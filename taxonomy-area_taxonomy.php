<?php
/**
 * Taxonomy archive: cottages in a given area.
 */
defined('ABSPATH') || exit;

$term      = get_queried_object();
$area_slug = $term ? $term->slug : '';
$area_name = $term ? $term->name : ucfirst($area_slug);

// Find matching Area CPT post for the dek + "More about" link
$area_post = null;
if ($area_slug) {
    $q = new WP_Query([
        'post_type'      => 'area',
        'posts_per_page' => 1,
        'tax_query'      => [[
            'taxonomy' => 'area_taxonomy',
            'field'    => 'slug',
            'terms'    => $area_slug,
        ]],
    ]);
    if ($q->have_posts()) {
        $q->the_post();
        $area_post = get_post();
        wp_reset_postdata();
    }
}
$area_dek = $area_post ? (string) get_field('dek', $area_post->ID) : '';

get_header(); ?>

<div class="nfedit-cottages-archive">
    <div class="nfedit-archive-intro container-edit nfedit-archive-intro--area">
        <nav class="nfedit-archive-breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="<?php echo esc_url(home_url('/cottages/')); ?>">Cottages</a></li>
                <li class="nfedit-archive-breadcrumb__sep">/</li>
                <li class="nfedit-archive-breadcrumb__current"><?php echo esc_html($area_name); ?></li>
            </ol>
        </nav>
        <div class="nfedit-archive-intro__row">
            <div>
                <h1 class="nfedit-archive-intro__title nfedit-archive-intro__title--left">
                    Cottages in <?php echo esc_html($area_name); ?>
                </h1>
                <?php if ($area_dek): ?>
                    <p class="nfedit-archive-intro__dek nfedit-archive-intro__dek--left"><?php echo esc_html($area_dek); ?></p>
                <?php endif; ?>
            </div>
            <?php if ($area_post): ?>
                <a href="<?php echo esc_url(get_permalink($area_post)); ?>" class="btn-tertiary">
                    <span>More about <?php echo esc_html($area_name); ?></span><span class="arrow">&rarr;</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php get_template_part('template-parts/components/cottages-grid', null, [
        'scope_area' => $area_slug,
    ]); ?>

    <?php get_template_part('template-parts/global/oracle-promo-compact'); ?>
    <?php get_template_part('template-parts/global/newsletter-compact'); ?>
</div>

<?php get_footer();
