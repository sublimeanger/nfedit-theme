<?php
/**
 * Archive: Guides index.
 */
defined('ABSPATH') || exit;
get_header();

$cats = get_terms([
    'taxonomy'   => 'guide_category',
    'hide_empty' => true,
    'orderby'    => 'count',
    'order'      => 'DESC',
]);

$all_guides = new WP_Query([
    'post_type'      => 'guide',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
?>

<div class="nfedit-guides-archive">

    <section class="container-edit nfedit-guides-archive__masthead">
        <p class="eyebrow nfedit-guides-archive__eyebrow">Practical, not romantic</p>
        <h1 class="nfedit-guides-archive__title">The guides desk.</h1>
        <p class="nfedit-guides-archive__dek">
            Everything we wish someone had told us. Cattle grids, cancellation policies, where to park, where the wifi works. Updated whenever we learn something new.
        </p>
    </section>

    <?php if (!empty($cats) && !is_wp_error($cats)): ?>
        <nav class="nfedit-guides-archive__filters" data-guide-filters aria-label="Filter guides by category">
            <div class="container-edit nfedit-guides-archive__filters-inner scroll-rail">
                <span class="eyebrow nfedit-guides-archive__filters-label">Browse by</span>
                <ul>
                    <li>
                        <button type="button" class="nfedit-guides-archive__filter is-active" data-cat-filter="all">Everything</button>
                    </li>
                    <?php foreach ($cats as $c): ?>
                        <li>
                            <button type="button" class="nfedit-guides-archive__filter" data-cat-filter="<?php echo esc_attr($c->slug); ?>"><?php echo esc_html($c->name); ?></button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
    <?php endif; ?>

    <section class="container-edit nfedit-guides-archive__grid-section" data-guide-grid>
        <p class="eyebrow nfedit-guides-archive__count" data-guide-count hidden>
            <span data-count-num>0</span> guide<span data-count-suffix>s</span>
            in <span data-count-label></span>
        </p>
        <?php if ($all_guides->have_posts()): ?>
            <div class="nfedit-guides-archive__grid">
                <?php while ($all_guides->have_posts()): $all_guides->the_post();
                    $gid = get_the_ID();
                    $g_cats = get_the_terms($gid, 'guide_category');
                    $g_cat_slug = ($g_cats && !is_wp_error($g_cats)) ? $g_cats[0]->slug : '';
                ?>
                    <div class="nfedit-guides-archive__item" data-guide-cat="<?php echo esc_attr($g_cat_slug); ?>">
                        <?php get_template_part('template-parts/components/guide-card', null, ['post_id' => $gid]); ?>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        <?php else: ?>
            <p class="nfedit-guides-archive__empty">No guides yet — check back soon.</p>
        <?php endif; ?>
        <p class="nfedit-guides-archive__no-results" data-no-results hidden>
            Nothing in this category yet — check back soon.
        </p>
    </section>

    <?php get_template_part('template-parts/global/newsletter-compact'); ?>
</div>

<?php get_footer();
