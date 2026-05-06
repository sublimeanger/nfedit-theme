<?php
/**
 * Surround card — wrapped in <a> link to single-surround template (Phase 12f).
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$sid = isset($args['post_id']) ? (int) $args['post_id'] : 0;
if (!$sid) return;

$dek      = function_exists('get_field') ? (string) get_field('dek', $sid) : '';
$image_id = function_exists('get_field') ? get_field('image', $sid) : 0;
$image_id = is_array($image_id) ? (isset($image_id['ID']) ? (int) $image_id['ID'] : 0) : (int) $image_id;
$image    = $image_id ? wp_get_attachment_image_url($image_id, 'nfedit_card_16_9') : '';
$kinds    = get_the_terms($sid, 'surround_kind');
$kind     = ($kinds && !is_wp_error($kinds)) ? strtoupper($kinds[0]->name) : '';
$title    = get_the_title($sid);
$permalink = get_permalink($sid);
?>
<a href="<?php echo esc_url($permalink); ?>" class="nfedit-surround-card-link" aria-label="<?php echo esc_attr(sprintf('Read about %s', $title)); ?>">
    <article class="nfedit-surround-card">
        <div class="nfedit-surround-card__media">
            <?php if ($image): ?>
                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy" class="img-muted" />
            <?php endif; ?>
        </div>
        <div class="nfedit-surround-card__body">
            <?php if ($kind): ?>
                <span class="eyebrow nfedit-surround-card__kind"><?php echo esc_html($kind); ?></span>
            <?php endif; ?>
            <h3 class="nfedit-surround-card__name"><?php echo esc_html($title); ?></h3>
            <?php if ($dek): ?>
                <p class="nfedit-surround-card__dek"><?php echo esc_html($dek); ?></p>
            <?php endif; ?>
        </div>
    </article>
</a>
