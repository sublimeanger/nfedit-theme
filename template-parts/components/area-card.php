<?php
/**
 * Area card.
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$pid = isset($args['post_id']) ? (int) $args['post_id'] : 0;
if (!$pid) return;

$kind  = (string) get_field('kind', $pid);
$dek   = (string) get_field('dek', $pid);
$count = get_field('count_label', $pid);

if (!$count) {
    $area_terms = wp_get_post_terms($pid, 'area_taxonomy', ['fields' => 'slugs']);
    if (!empty($area_terms) && !is_wp_error($area_terms)) {
        $cnt_q = new WP_Query([
            'post_type'      => 'property',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => [[
                'taxonomy' => 'area_taxonomy',
                'field'    => 'slug',
                'terms'    => $area_terms,
            ]],
        ]);
        $count = (int) $cnt_q->found_posts;
        wp_reset_postdata();
    }
}

$img_id  = get_field('hero_image', $pid);
$img_id  = is_array($img_id) ? (isset($img_id['ID']) ? (int) $img_id['ID'] : 0) : (int) $img_id;
if (!$img_id) $img_id = (int) get_post_thumbnail_id($pid);
$img_url = $img_id ? wp_get_attachment_image_url($img_id, 'nfedit_card_16_9') : '';
?>
<a href="<?php echo esc_url(get_permalink($pid)); ?>" class="nfedit-area-card">
    <div class="nfedit-area-card__media">
        <?php if ($img_url): ?>
            <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr(get_the_title($pid)); ?>" loading="lazy" class="img-muted" />
        <?php endif; ?>
    </div>
    <div class="nfedit-area-card__body">
        <?php if ($kind): ?>
            <span class="eyebrow nfedit-area-card__kind"><?php echo esc_html(strtoupper($kind)); ?></span>
        <?php endif; ?>
        <h3 class="nfedit-area-card__name"><?php echo esc_html(get_the_title($pid)); ?></h3>
        <?php if ($dek): ?>
            <p class="nfedit-area-card__dek"><?php echo esc_html($dek); ?></p>
        <?php endif; ?>
        <?php if ($count): ?>
            <p class="nfedit-area-card__count"><?php echo (int) $count; ?> cottages</p>
        <?php endif; ?>
    </div>
</a>
