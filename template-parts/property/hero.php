<?php
/**
 * 6.2 — Hero (16:9 image with title overlay).
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id  = (int) $args['post_id'];
$hero_id  = get_field('hero_image', $post_id);
$hero_id  = is_array($hero_id) ? (isset($hero_id['ID']) ? (int) $hero_id['ID'] : 0) : (int) $hero_id;
if (!$hero_id) {
    $hero_id = (int) get_post_thumbnail_id($post_id);
}
$name     = get_the_title($post_id);

$area_terms = get_the_terms($post_id, 'area_taxonomy');
$area = ($area_terms && !is_wp_error($area_terms)) ? $area_terms[0]->name : '';
?>
<section class="nfedit-property-hero" data-property-hero>
    <div class="nfedit-property-hero__media">
        <?php if ($hero_id): ?>
            <?php echo wp_get_attachment_image($hero_id, 'nfedit_hero_xl', false, [
                'alt'           => $name,
                'loading'       => 'eager',
                'fetchpriority' => 'high',
            ]); ?>
        <?php endif; ?>
        <div class="nfedit-property-hero__gradient"></div>
        <div class="nfedit-property-hero__caption">
            <?php if ($area): ?>
                <p class="eyebrow nfedit-property-hero__eyebrow"><?php echo esc_html(strtoupper($area)); ?></p>
            <?php endif; ?>
            <h1 class="nfedit-property-hero__title"><?php echo esc_html($name); ?></h1>
        </div>
        <div class="nfedit-property-hero__actions">
            <button class="nfedit-property-hero__action" aria-label="Save" data-nfedit-save="<?php echo esc_attr($post_id); ?>">
                <?php echo nfedit_lucide_svg('bookmark', ['width' => 20, 'height' => 20]); ?>
            </button>
            <button class="nfedit-property-hero__action" aria-label="Share" data-nfedit-share>
                <?php echo nfedit_lucide_svg('share-2', ['width' => 20, 'height' => 20]); ?>
            </button>
        </div>
    </div>
</section>
