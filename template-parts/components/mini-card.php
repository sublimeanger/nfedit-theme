<?php
/**
 * Mini card — used in owner-recs columns.
 * @var array $args { 'name' => string, 'dek' => string, 'image_id' => int }
 */
defined('ABSPATH') || exit;

$name     = isset($args['name']) ? (string) $args['name'] : '';
$dek      = isset($args['dek']) ? (string) $args['dek'] : '';
$image_id = isset($args['image_id']) ? (int) $args['image_id'] : 0;
$image    = $image_id ? wp_get_attachment_image_url($image_id, 'nfedit_card_4_3') : '';
?>
<article class="nfedit-mini-card">
    <div class="nfedit-mini-card__media">
        <?php if ($image): ?>
            <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy" class="img-muted" />
        <?php endif; ?>
    </div>
    <h4 class="nfedit-mini-card__name"><?php echo esc_html($name); ?></h4>
    <?php if ($dek): ?>
        <p class="nfedit-mini-card__dek"><?php echo esc_html($dek); ?></p>
    <?php endif; ?>
</article>
