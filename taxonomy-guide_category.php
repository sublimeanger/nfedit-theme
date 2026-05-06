<?php
/**
 * Guide category archive.
 */
defined('ABSPATH') || exit;
get_header();

$term = get_queried_object();
$cat_name = $term ? $term->name : '';

global $wp_query;
?>

<div class="nfedit-guide-cat-archive">
    <section class="container-edit nfedit-guide-cat-archive__intro">
        <nav class="nfedit-guide-cat-archive__breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="<?php echo esc_url(get_post_type_archive_link('guide')); ?>">Guides</a></li>
                <li class="nfedit-guide-cat-archive__sep">/</li>
                <li class="nfedit-guide-cat-archive__current"><?php echo esc_html($cat_name); ?></li>
            </ol>
        </nav>
        <p class="eyebrow nfedit-guide-cat-archive__eyebrow">In the guides desk</p>
        <h1 class="nfedit-guide-cat-archive__title">
            Guides for <span class="nfedit-guide-cat-archive__italic"><?php echo esc_html(strtolower($cat_name)); ?></span>.
        </h1>
        <p class="nfedit-guide-cat-archive__count">
            <?php echo (int) $wp_query->found_posts; ?>
            guide<?php echo $wp_query->found_posts === 1 ? '' : 's'; ?>
        </p>
    </section>

    <section class="container-edit nfedit-guide-cat-archive__grid-section">
        <?php if (have_posts()): ?>
            <div class="nfedit-guide-cat-archive__grid">
                <?php while (have_posts()): the_post(); ?>
                    <?php get_template_part('template-parts/components/guide-card', null, ['post_id' => get_the_ID()]); ?>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination(['prev_text' => '&larr; Previous', 'next_text' => 'Next &rarr;']); ?>
        <?php else: ?>
            <p class="nfedit-guide-cat-archive__empty">Nothing in this category yet — check back soon.</p>
        <?php endif; ?>
    </section>

    <?php get_template_part('template-parts/global/newsletter-compact'); ?>
</div>

<?php get_footer();
