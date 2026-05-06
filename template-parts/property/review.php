<?php
/**
 * 6.11 — Real review (T1+T2).
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$text    = (string) get_field('review_text', $post_id);
if (!$text) return;
$name    = (string) get_field('review_name', $post_id);
$date    = (string) get_field('review_date', $post_id);
$nights  = (string) get_field('review_nights_stayed', $post_id);
?>
<section id="reviews" class="nfedit-property-review">
    <div class="container-edit content-edit nfedit-property-review__inner">
        <p class="nfedit-property-review__text">&ldquo;<?php echo esc_html($text); ?>&rdquo;</p>
        <p class="nfedit-property-review__byline">
            <strong>&mdash; <?php echo esc_html($name); ?><?php if ($date): ?>, <?php echo esc_html($date); ?><?php endif; ?></strong>
            <?php if ($nights): ?>
                <span class="nfedit-property-review__nights">(<?php echo esc_html($nights); ?>)</span>
            <?php endif; ?>
        </p>
    </div>
</section>
