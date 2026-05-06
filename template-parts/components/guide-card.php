<?php
/**
 * Guide card.
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$pid = isset($args['post_id']) ? (int) $args['post_id'] : 0;
if (!$pid) return;

$dek = (string) get_field('dek', $pid);
if (!$dek) $dek = get_the_excerpt($pid);
$img_id = get_field('hero_image', $pid);
$img_id = is_array($img_id) ? (isset($img_id['ID']) ? (int) $img_id['ID'] : 0) : (int) $img_id;
if (!$img_id) $img_id = (int) get_post_thumbnail_id($pid);
$img_url = $img_id ? wp_get_attachment_image_url($img_id, 'nfedit_card_16_9') : '';
?>
<a href="<?php echo esc_url(get_permalink($pid)); ?>" class="nfedit-guide-card">
    <div class="nfedit-guide-card__media">
        <?php if ($img_url): ?>
            <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr(get_the_title($pid)); ?>" loading="lazy" class="img-muted" />
        <?php endif; ?>
    </div>
    <h3 class="nfedit-guide-card__title"><?php echo esc_html(get_the_title($pid)); ?></h3>
    <?php if ($dek): ?>
        <p class="nfedit-guide-card__dek"><?php echo esc_html($dek); ?></p>
    <?php endif; ?>
</a>
