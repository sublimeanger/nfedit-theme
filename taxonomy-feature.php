<?php
/**
 * Taxonomy archive: cottages with a given feature.
 */
defined('ABSPATH') || exit;

$term         = get_queried_object();
$feature_slug = $term ? $term->slug : '';
$feature_name = $term ? $term->name : ucfirst(str_replace('-', ' ', $feature_slug));

$hero_meta = nfedit_feature_archive_hero_meta($feature_slug, $feature_name);

get_header(); ?>

<div class="nfedit-cottages-archive nfedit-cottages-archive--feature">
    <section class="nfedit-feature-hero">
        <?php if (!empty($hero_meta['image_url'])): ?>
            <img src="<?php echo esc_url($hero_meta['image_url']); ?>" alt="" class="nfedit-feature-hero__image" />
        <?php endif; ?>
        <div class="nfedit-feature-hero__gradient"></div>
        <div class="nfedit-feature-hero__caption container-edit">
            <nav class="nfedit-archive-breadcrumb nfedit-archive-breadcrumb--on-image" aria-label="Breadcrumb">
                <ol>
                    <li><a href="<?php echo esc_url(home_url('/cottages/')); ?>">Cottages</a></li>
                    <li class="nfedit-archive-breadcrumb__sep">/</li>
                    <li class="nfedit-archive-breadcrumb__current"><?php echo esc_html($hero_meta['title']); ?></li>
                </ol>
            </nav>
            <h1 class="nfedit-feature-hero__title"><?php echo esc_html($hero_meta['title']); ?></h1>
        </div>
    </section>

    <section class="container-edit nfedit-feature-intro">
        <p class="content-edit nfedit-feature-intro__body"><?php echo esc_html($hero_meta['intro']); ?></p>
    </section>

    <?php get_template_part('template-parts/components/cottages-grid', null, [
        'scope_feature' => [
            'slug'  => $feature_slug,
            'label' => $hero_meta['label'],
        ],
    ]); ?>

    <?php get_template_part('template-parts/global/oracle-promo-compact'); ?>
    <?php get_template_part('template-parts/global/newsletter-compact'); ?>
</div>

<?php get_footer();
