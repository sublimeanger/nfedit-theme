<?php
defined('ABSPATH') || exit;
$block = $args['block'];

$image = isset($block['image']) ? $block['image'] : null;
$att_id = 0;
if (is_array($image) && isset($image['ID'])) {
    $att_id = (int) $image['ID'];
} elseif (is_numeric($image)) {
    $att_id = (int) $image;
}
$caption = isset($block['caption']) ? $block['caption'] : '';
if (!$att_id) return;

$alt  = get_post_meta($att_id, '_wp_attachment_image_alt', true);
if (!$alt && $caption) $alt = $caption;
?>
<figure class="nfedit-guide-block-image">
    <?php echo wp_get_attachment_image($att_id, 'nfedit_card_16_9', false, [
        'alt'     => $alt,
        'loading' => 'lazy',
        'class'   => 'img-muted',
    ]); ?>
    <?php if ($caption): ?>
        <figcaption><?php echo esc_html($caption); ?></figcaption>
    <?php endif; ?>
</figure>
