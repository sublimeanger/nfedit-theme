<?php
/**
 * Editorial card.
 * @var array $args { 'post_id' => int, 'large' => bool, 'aspect' => 'portrait'|'landscape' }
 */
defined('ABSPATH') || exit;

$pid    = isset($args['post_id']) ? (int) $args['post_id'] : 0;
if (!$pid) return;
$large  = !empty($args['large']);
$aspect = isset($args['aspect']) ? $args['aspect'] : 'portrait';

$img_id = get_field('hero_image', $pid);
$img_id = is_array($img_id) ? (isset($img_id['ID']) ? (int) $img_id['ID'] : 0) : (int) $img_id;
if (!$img_id) $img_id = (int) get_post_thumbnail_id($pid);
$size   = $aspect === 'portrait' ? 'nfedit_card_4_5' : 'nfedit_card_16_9';
$img_url = $img_id ? wp_get_attachment_image_url($img_id, $size) : '';

$dek = (string) get_field('dek', $pid);
if (!$dek) $dek = get_the_excerpt($pid);
$author  = (string) get_field('author', $pid);
$mins    = (int) get_field('read_minutes', $pid);

$clusters = get_the_terms($pid, 'cluster');
$cluster  = ($clusters && !is_wp_error($clusters)) ? strtoupper($clusters[0]->name) : '';

$aspect_class = 'nfedit-editorial-card--' . $aspect;
$size_class   = $large ? 'nfedit-editorial-card--large' : 'nfedit-editorial-card--small';
?>
<a href="<?php echo esc_url(get_permalink($pid)); ?>" class="nfedit-editorial-card <?php echo esc_attr("$aspect_class $size_class"); ?>">
    <div class="nfedit-editorial-card__media">
        <?php if ($img_url): ?>
            <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr(get_the_title($pid)); ?>" loading="lazy" class="img-muted" />
        <?php endif; ?>
    </div>
    <div class="nfedit-editorial-card__body">
        <?php if ($cluster): ?>
            <span class="eyebrow nfedit-editorial-card__cluster"><?php echo esc_html($cluster); ?></span>
        <?php endif; ?>
        <h3 class="nfedit-editorial-card__title"><?php echo esc_html(get_the_title($pid)); ?></h3>
        <?php if ($dek): ?>
            <p class="nfedit-editorial-card__dek"><?php echo esc_html($dek); ?></p>
        <?php endif; ?>
        <?php if ($author || $mins > 0): ?>
            <p class="nfedit-editorial-card__byline">
                <?php if ($author): ?>by <?php echo esc_html($author); ?><?php endif; ?>
                <?php if ($author && $mins > 0): ?> &middot; <?php endif; ?>
                <?php if ($mins > 0): ?><?php echo (int) $mins; ?> min read<?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
</a>
