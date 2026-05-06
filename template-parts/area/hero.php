<?php
/**
 * Area hero — large image, gradient overlay, title block bottom-left.
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];

$hero_id = get_field('hero_image', $post_id);
$hero_id = is_array($hero_id) ? (isset($hero_id['ID']) ? (int) $hero_id['ID'] : 0) : (int) $hero_id;
if (!$hero_id) $hero_id = (int) get_post_thumbnail_id($post_id);
$hero_url = $hero_id ? wp_get_attachment_image_url($hero_id, 'nfedit_hero_xl') : '';

$kind = (string) get_field('kind', $post_id);

$count = (int) get_field('count_label', $post_id);
if (!$count) {
    $area_terms = wp_get_post_terms($post_id, 'area_taxonomy', ['fields' => 'slugs']);
    if (!empty($area_terms) && !is_wp_error($area_terms)) {
        $cnt_q = new WP_Query([
            'post_type'      => 'property',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => [[
                'taxonomy' => 'area_taxonomy',
                'field'    => 'slug',
                'terms'    => $area_terms,
            ]],
        ]);
        $count = (int) $cnt_q->found_posts;
        wp_reset_postdata();
    }
}
?>
<section class="nfedit-area-hero">
    <?php if ($hero_url): ?>
        <img src="<?php echo esc_url($hero_url); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" class="nfedit-area-hero__image" />
    <?php endif; ?>
    <div class="nfedit-area-hero__gradient"></div>
    <div class="container-edit nfedit-area-hero__caption">
        <?php if ($kind): ?>
            <p class="eyebrow nfedit-area-hero__kind"><?php echo esc_html(strtoupper($kind)); ?></p>
        <?php endif; ?>
        <h1 class="nfedit-area-hero__title"><?php echo esc_html(get_the_title($post_id)); ?></h1>
        <?php if ($count > 0): ?>
            <p class="nfedit-area-hero__count"><?php echo (int) $count; ?> cottage<?php echo $count === 1 ? '' : 's'; ?></p>
        <?php endif; ?>
    </div>
</section>
