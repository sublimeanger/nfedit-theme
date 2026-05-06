<?php
/**
 * Surround hero — image, eyebrow (kind + area), title, dek
 */
defined('ABSPATH') || exit;
$post_id = isset($args['post_id']) ? (int) $args['post_id'] : (int) get_the_ID();
$image   = get_field('image', $post_id);
$dek     = (string) get_field('dek', $post_id);
$kinds   = get_the_terms($post_id, 'surround_kind');
$areas   = get_the_terms($post_id, 'area_taxonomy');
$kind    = (!is_wp_error($kinds) && !empty($kinds)) ? $kinds[0] : null;
$area    = (!is_wp_error($areas) && !empty($areas)) ? $areas[0] : null;

$img_url    = is_array($image) && !empty($image['url']) ? $image['url'] : '';
$img_alt    = is_array($image) && !empty($image['alt']) ? $image['alt'] : get_the_title($post_id);
$img_w      = is_array($image) && !empty($image['width'])  ? (int) $image['width']  : 1600;
$img_h      = is_array($image) && !empty($image['height']) ? (int) $image['height'] : 900;
?>
<header class="nfedit-surround__hero">
    <?php if ($img_url): ?>
        <div class="nfedit-surround__hero-image">
            <img src="<?php echo esc_url($img_url); ?>"
                 alt="<?php echo esc_attr($img_alt); ?>"
                 loading="eager"
                 width="<?php echo esc_attr($img_w); ?>"
                 height="<?php echo esc_attr($img_h); ?>">
        </div>
    <?php endif; ?>
    <div class="container-edit nfedit-surround__hero-text">
        <?php if ($kind || $area): ?>
            <p class="nfedit-surround__eyebrow eyebrow">
                <?php if ($kind): ?>
                    <span class="nfedit-surround__kind"><?php echo esc_html($kind->name); ?></span>
                <?php endif; ?>
                <?php if ($kind && $area): ?> &middot; <?php endif; ?>
                <?php if ($area):
                    $area_link = get_term_link($area);
                    if (!is_wp_error($area_link)): ?>
                        <a href="<?php echo esc_url($area_link); ?>" class="nfedit-surround__area"><?php echo esc_html($area->name); ?></a>
                    <?php else: ?>
                        <span class="nfedit-surround__area"><?php echo esc_html($area->name); ?></span>
                    <?php endif;
                endif; ?>
            </p>
        <?php endif; ?>
        <h1 class="nfedit-surround__title"><?php echo esc_html(get_the_title($post_id)); ?></h1>
        <?php if ($dek): ?>
            <p class="nfedit-surround__dek"><?php echo esc_html($dek); ?></p>
        <?php endif; ?>
    </div>
</header>
