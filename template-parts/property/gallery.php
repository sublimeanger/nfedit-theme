<?php
/**
 * 6.9 — Gallery (adaptive grid + lightbox).
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$gallery = get_field('gallery', $post_id);
if (!$gallery || empty($gallery)) return;

$total        = count($gallery);
$visible      = $total >= 30 ? array_slice($gallery, 0, 9) : $gallery;
$visible_n    = count($visible);
$has_overflow = $total > $visible_n;

$lightbox_urls = [];
foreach ($gallery as $att) {
    $att_id = is_array($att) ? (isset($att['ID']) ? (int) $att['ID'] : 0) : (int) $att;
    if ($att_id) {
        $url = wp_get_attachment_image_url($att_id, 'nfedit_gallery_full');
        if ($url) $lightbox_urls[] = $url;
    }
}
?>
<section id="gallery" class="nfedit-property-gallery">
    <div class="container-edit">
        <h2 class="nfedit-property-gallery__heading">Inside and around</h2>
        <div class="nfedit-property-gallery__grid" data-nfedit-gallery data-images='<?php echo esc_attr(wp_json_encode(array_values($lightbox_urls))); ?>'>
            <?php foreach ($visible as $i => $att):
                $att_id = is_array($att) ? (isset($att['ID']) ? (int) $att['ID'] : 0) : (int) $att;
                if (!$att_id) continue;
                $thumb = wp_get_attachment_image_url($att_id, 'nfedit_gallery_4_3');
                $alt   = (string) get_post_meta($att_id, '_wp_attachment_image_alt', true);
                if (!$alt) $alt = get_the_title($post_id) . ' — image ' . ($i + 1);
            ?>
                <button class="nfedit-property-gallery__item" data-gallery-index="<?php echo (int) $i; ?>" type="button" aria-label="Open image <?php echo (int) $i + 1; ?>">
                    <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" class="img-muted" />
                </button>
            <?php endforeach; ?>
        </div>
        <?php if ($has_overflow): ?>
            <div class="nfedit-property-gallery__overflow">
                <button class="btn-secondary" data-gallery-overflow type="button">See all <?php echo (int) $total; ?> photos</button>
            </div>
        <?php endif; ?>
    </div>
</section>
