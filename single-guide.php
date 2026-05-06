<?php
/**
 * Single Guide template.
 */
defined('ABSPATH') || exit;

if (!have_posts()) { get_header(); echo '<p class="container-edit">Guide not found.</p>'; get_footer(); return; }
the_post();
$post_id = get_the_ID();

// Meta
$dek         = (string) get_field('dek', $post_id);
$read_min    = (int) get_field('read_minutes', $post_id);
$last_upd    = (string) get_field('last_updated', $post_id);
if ($last_upd) {
    $ts = strtotime($last_upd);
    $last_upd_display = $ts ? date_i18n('F Y', $ts) : '';
} else {
    $last_upd_display = '';
}

// Hero
$hero_id = get_field('hero_image', $post_id);
if (is_array($hero_id) && isset($hero_id['ID'])) $hero_id = $hero_id['ID'];
if (!$hero_id) $hero_id = get_post_thumbnail_id($post_id);
$hero_url = $hero_id ? wp_get_attachment_image_url($hero_id, 'nfedit_hero_xl') : '';

// Category
$cats = get_the_terms($post_id, 'guide_category');
$cat = ($cats && !is_wp_error($cats)) ? $cats[0] : null;
$cat_label = $cat ? strtoupper($cat->name) : '';
$cat_link  = $cat ? home_url('/guides/in/' . $cat->slug . '/') : '';

// Body (Flex)
$body = get_field('body', $post_id);

// Related cottages
$related_cottages = get_field('related_cottages', $post_id);

get_header(); ?>

<article class="nfedit-guide" data-guide-id="<?php echo esc_attr($post_id); ?>">

    <?php
    get_template_part('template-parts/guide/hero', null, [
        'post_id'          => $post_id,
        'hero_url'         => $hero_url,
        'category'         => $cat_label,
        'dek'              => $dek,
        'read_min'         => $read_min,
        'last_upd_display' => $last_upd_display,
    ]);
    ?>

    <?php
    get_template_part('template-parts/guide/breadcrumbs', null, [
        'post_id'      => $post_id,
        'category'     => $cat ? $cat->name : '',
        'category_link'=> $cat_link,
    ]);
    ?>

    <?php
    if (!empty($body)) {
        get_template_part('template-parts/guide/body', null, [
            'post_id' => $post_id,
            'body'    => $body,
        ]);
    }
    ?>

    <?php
    if (!empty($related_cottages)) {
        get_template_part('template-parts/guide/related-cottages', null, [
            'post_id'  => $post_id,
            'cottages' => $related_cottages,
        ]);
    }
    ?>

    <?php
    get_template_part('template-parts/guide/related-guides', null, [
        'post_id'  => $post_id,
        'category' => $cat,
    ]);
    ?>

    <?php get_template_part('template-parts/global/newsletter-compact'); ?>

</article>

<?php get_footer();
