<?php
/**
 * Collection card.
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$pid = isset($args['post_id']) ? (int) $args['post_id'] : 0;
if (!$pid) return;

// Auto-compute count if available
$count = 0;
$rule = (string) get_field('auto_populate_by', $pid);
if ($rule === 'feature') {
    $term_id = get_field('auto_feature', $pid);
    $term_id = is_array($term_id) ? (isset($term_id['term_id']) ? (int) $term_id['term_id'] : 0) : (int) $term_id;
    if ($term_id) {
        $cnt_q = new WP_Query([
            'post_type'      => 'property',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => [[
                'taxonomy' => 'feature',
                'field'    => 'term_id',
                'terms'    => $term_id,
            ]],
        ]);
        $count = (int) $cnt_q->found_posts;
        wp_reset_postdata();
    }
} elseif ($rule === 'size_min') {
    $sleeps_min = (int) get_field('auto_size_min', $pid);
    if ($sleeps_min > 0) {
        $cnt_q = new WP_Query([
            'post_type'      => 'property',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [[
                'key'     => 'sleeps',
                'value'   => $sleeps_min,
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ]],
        ]);
        $count = (int) $cnt_q->found_posts;
        wp_reset_postdata();
    }
} else {
    $hand = get_field('hand_picked_cottages', $pid);
    if (is_array($hand)) $count = count($hand);
}

$img_id  = get_field('hero_image', $pid);
$img_id  = is_array($img_id) ? (isset($img_id['ID']) ? (int) $img_id['ID'] : 0) : (int) $img_id;
if (!$img_id) $img_id = (int) get_post_thumbnail_id($pid);
?>
<a href="<?php echo esc_url(get_permalink($pid)); ?>" class="nfedit-collection-card">
    <div class="nfedit-collection-card__media">
        <?php if ($img_id): ?>
            <?php echo wp_get_attachment_image($img_id, 'nfedit_card_4_5', false, [
                'alt'     => get_the_title($pid),
                'loading' => 'lazy',
                'class'   => 'img-muted',
            ]); ?>
        <?php endif; ?>
        <div class="nfedit-collection-card__gradient"></div>
        <h3 class="nfedit-collection-card__name"><?php echo esc_html(get_the_title($pid)); ?></h3>
    </div>
    <?php if ($count > 0): ?>
        <p class="nfedit-collection-card__count"><?php echo (int) $count; ?> cottages</p>
    <?php endif; ?>
</a>
