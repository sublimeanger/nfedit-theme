<?php
/**
 * Editorial cluster archive — /the-edit/in/{slug}/
 */
defined('ABSPATH') || exit;
get_header();

$term         = get_queried_object();
$cluster_name = $term ? $term->name : '';
$description  = $term ? (string) $term->description : '';

global $wp_query;
?>

<div class="nfedit-cluster-archive">
    <section class="container-edit nfedit-cluster-archive__intro">
        <nav class="nfedit-cluster-archive__breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="<?php echo esc_url(home_url('/the-edit/')); ?>">The Edit</a></li>
                <li class="nfedit-cluster-archive__sep">/</li>
                <li class="nfedit-cluster-archive__current"><?php echo esc_html($cluster_name); ?></li>
            </ol>
        </nav>
        <p class="eyebrow nfedit-cluster-archive__eyebrow">In the cluster</p>
        <h1 class="nfedit-cluster-archive__title">
            More from the <span class="nfedit-cluster-archive__italic"><?php echo esc_html($cluster_name); ?></span> file.
        </h1>
        <?php if ($description): ?>
            <p class="nfedit-cluster-archive__dek"><?php echo esc_html($description); ?></p>
        <?php endif; ?>
        <p class="nfedit-cluster-archive__count">
            <?php echo (int) $wp_query->found_posts; ?>
            stor<?php echo (int) $wp_query->found_posts === 1 ? 'y' : 'ies'; ?>
        </p>
    </section>

    <section class="container-edit nfedit-cluster-archive__grid-section">
        <?php if (have_posts()): ?>
            <div class="nfedit-cluster-archive__grid">
                <?php while (have_posts()): the_post(); ?>
                    <?php get_template_part('template-parts/components/editorial-card', null, [
                        'post_id' => get_the_ID(),
                        'aspect'  => 'portrait',
                        'large'   => false,
                    ]); ?>
                <?php endwhile; ?>
            </div>
            <?php
            the_posts_pagination([
                'prev_text' => '&larr; Previous',
                'next_text' => 'Next &rarr;',
            ]);
            ?>
        <?php else: ?>
            <p class="nfedit-cluster-archive__empty">Nothing in this cluster yet &mdash; check back soon.</p>
        <?php endif; ?>
    </section>

    <?php get_template_part('template-parts/global/newsletter-compact'); ?>
</div>

<?php get_footer();
