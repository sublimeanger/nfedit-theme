<?php
/**
 * Archive: Collections index — sticky tabs by axis.
 */
defined('ABSPATH') || exit;
get_header();

$tabs = [
    'feature'  => 'By feature',
    'size'     => 'By size',
    'location' => 'By location',
    'occasion' => 'By occasion',
    'style'    => 'By style',
    'audience' => 'By audience',
];

$default_tab = 'feature';

// Pre-render all tab grids; JS shows/hides
$collections_by_axis = [];
foreach (array_keys($tabs) as $axis) {
    $collections_by_axis[$axis] = new WP_Query([
        'post_type'      => 'collection',
        'posts_per_page' => -1,
        'meta_key'       => 'axis',
        'meta_value'     => $axis,
        'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
    ]);
}
?>

<div class="nfedit-collections-archive">

    <?php get_template_part('template-parts/global/page-hero', null, [
        'eyebrow'   => 'Browse by',
        'title'     => 'Cottages, sliced six different ways.',
        'dek'       => 'Same collection, different doorways in. Find what you actually want.',
        'image_url' => 'https://images.unsplash.com/photo-1418065460487-3e41a6c84dc5?auto=format&fit=crop&w=1920&q=80',
        'height'    => '40vh',
    ]); ?>

    <nav class="nfedit-collections-archive__tabs" data-collections-tabs aria-label="Collection axes">
        <div class="container-edit nfedit-collections-archive__tabs-inner scroll-rail">
            <ul>
                <?php foreach ($tabs as $axis => $label):
                    $is_active = ($axis === $default_tab);
                ?>
                    <li>
                        <button
                            type="button"
                            data-tab-id="<?php echo esc_attr($axis); ?>"
                            class="nfedit-collections-archive__tab<?php echo $is_active ? ' is-active' : ''; ?>"
                        ><?php echo esc_html($label); ?></button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>

    <section class="nfedit-collections-archive__panels">
        <div class="container-edit">
            <?php foreach ($tabs as $axis => $label):
                $q = $collections_by_axis[$axis];
                $is_active = ($axis === $default_tab);
            ?>
                <div
                    class="nfedit-collections-archive__panel<?php echo $is_active ? ' is-active' : ''; ?>"
                    data-tab-panel="<?php echo esc_attr($axis); ?>"
                    <?php if (!$is_active): ?>hidden<?php endif; ?>
                >
                    <?php if ($q->have_posts()): ?>
                        <div class="nfedit-collections-archive__grid">
                            <?php while ($q->have_posts()): $q->the_post(); ?>
                                <?php get_template_part('template-parts/components/collection-card', null, ['post_id' => get_the_ID()]); ?>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </div>
                    <?php else: ?>
                        <p class="nfedit-collections-archive__empty">No collections in this category yet.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php get_template_part('template-parts/global/oracle-promo-compact'); ?>
    <?php get_template_part('template-parts/global/newsletter-compact'); ?>
</div>

<?php get_footer();
