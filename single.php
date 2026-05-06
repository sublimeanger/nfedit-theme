<?php
/**
 * Single editorial article (native post type).
 *
 * Lovable source: src/pages/Editorial.tsx (115 lines)
 */
defined('ABSPATH') || exit;

if (!have_posts()) {
    get_header();
    echo '<p class="container-edit" style="padding: 6rem 1.5rem; text-align: center;">Article not found.</p>';
    get_footer();
    return;
}

the_post();
$post_id = get_the_ID();

$dek         = function_exists('get_field') ? (string) get_field('dek', $post_id) : '';
$read_min    = function_exists('get_field') ? (int) get_field('read_minutes', $post_id) : 0;
$author      = function_exists('get_field') ? (string) get_field('author', $post_id) : '';
$display_dt  = function_exists('get_field') ? (string) get_field('display_date', $post_id) : '';

$hero_id  = function_exists('get_field') ? get_field('hero_image', $post_id) : 0;
$hero_id  = is_array($hero_id) ? (isset($hero_id['ID']) ? (int) $hero_id['ID'] : 0) : (int) $hero_id;
if (!$hero_id) $hero_id = (int) get_post_thumbnail_id($post_id);
$hero_url = $hero_id ? wp_get_attachment_image_url($hero_id, 'nfedit_hero_xl') : '';

$clusters      = get_the_terms($post_id, 'cluster');
$cluster       = ($clusters && !is_wp_error($clusters)) ? $clusters[0] : null;
$cluster_label = $cluster ? strtoupper($cluster->name) : '';
$cluster_link  = $cluster ? home_url('/the-edit/in/' . $cluster->slug . '/') : '';

$mid_cottages = function_exists('get_field') ? get_field('embedded_cottages', $post_id) : [];
if (!is_array($mid_cottages)) $mid_cottages = [];

if (!$display_dt) {
    $display_dt = get_the_date('F Y', $post_id);
}

get_header(); ?>

<article class="nfedit-editorial" data-editorial-id="<?php echo esc_attr($post_id); ?>">

    <?php get_template_part('template-parts/editorial/hero', null, [
        'post_id'    => $post_id,
        'hero_url'   => $hero_url,
        'cluster'    => $cluster_label,
        'dek'        => $dek,
        'author'     => $author,
        'display_dt' => $display_dt,
        'read_min'   => $read_min,
    ]); ?>

    <?php get_template_part('template-parts/editorial/breadcrumbs', null, [
        'post_id'      => $post_id,
        'cluster_name' => $cluster ? $cluster->name : '',
        'cluster_link' => $cluster_link,
    ]); ?>

    <?php get_template_part('template-parts/editorial/body', null, [
        'post_id'      => $post_id,
        'mid_cottages' => $mid_cottages,
    ]); ?>

    <?php if ($author): ?>
        <?php get_template_part('template-parts/editorial/author', null, ['author' => $author]); ?>
    <?php endif; ?>

    <?php get_template_part('template-parts/editorial/related-cottages', null, [
        'post_id'      => $post_id,
        'mid_cottages' => $mid_cottages,
    ]); ?>

    <?php get_template_part('template-parts/editorial/related-editorials', null, [
        'post_id' => $post_id,
        'cluster' => $cluster,
    ]); ?>

    <?php get_template_part('template-parts/global/newsletter-compact'); ?>

</article>

<?php get_footer();
