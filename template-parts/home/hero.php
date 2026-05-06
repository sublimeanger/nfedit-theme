<?php
/**
 * 5.1 — Homepage hero (full-bleed forest image + headline + Oracle widget).
 */
defined('ABSPATH') || exit;

$hero_id = function_exists('get_field') ? get_field('hero_fallback_image', 'option') : 0;
$hero_id = is_array($hero_id) ? (isset($hero_id['ID']) ? (int) $hero_id['ID'] : 0) : (int) $hero_id;

if (!$hero_id) {
    $cached = (int) get_option('nfedit_homepage_hero_fallback_id');
    if ($cached) {
        $hero_id = $cached;
    } else {
        $url = 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=2400&h=1600&q=85';
        if (function_exists('media_sideload_image')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $att_id = media_sideload_image($url, 0, 'New Forest hero', 'id');
            if (!is_wp_error($att_id) && $att_id) {
                update_option('nfedit_homepage_hero_fallback_id', (int) $att_id);
                $hero_id = (int) $att_id;
            }
        }
    }
}
$hero_url = $hero_id ? wp_get_attachment_image_url($hero_id, 'nfedit_hero_xl') : '';

$eyebrow  = function_exists('get_field') ? (string) get_field('hero_eyebrow', 'option') : '';
if (!$eyebrow)  $eyebrow  = 'The New Forest Edit';
$headline = function_exists('get_field') ? (string) get_field('hero_h1', 'option') : '';
if (!$headline) $headline = "Slow weekends in the forest, the way we'd plan them ourselves.";
$dek      = function_exists('get_field') ? (string) get_field('hero_sub', 'option') : '';
if (!$dek)      $dek      = "A curated guide to the New Forest's best cottages, places to walk, and pubs to find your way to. By Jamie & Lauren.";
?>
<section class="nfedit-home-hero">
    <?php if ($hero_url): ?>
        <img src="<?php echo esc_url($hero_url); ?>" alt="The New Forest at golden hour" class="nfedit-home-hero__image" />
    <?php endif; ?>
    <div class="nfedit-home-hero__gradient"></div>
    <div class="container-edit nfedit-home-hero__content">
        <p class="eyebrow nfedit-home-hero__eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h1 class="nfedit-home-hero__title"><?php echo esc_html($headline); ?></h1>
        <p class="nfedit-home-hero__dek"><?php echo esc_html($dek); ?></p>

        <div class="nfedit-home-hero__oracle-wrap">
            <?php get_template_part('template-parts/components/oracle', null, ['variant' => 'light']); ?>
        </div>
    </div>
    <div class="nfedit-home-hero__inspected">
        <span class="eyebrow">Inspected by us</span>
        <?php echo nfedit_lucide_svg('chevron-down', ['width' => 16, 'height' => 16, 'class' => 'nfedit-home-hero__chevron']); ?>
    </div>
</section>
