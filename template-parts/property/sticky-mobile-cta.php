<?php
/**
 * 6.20 — Sticky mobile CTA (mobile only, slides up past hero).
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id     = (int) $args['post_id'];
$price       = (float) get_field('price_from', $post_id);
$unit        = (string) get_field('price_unit', $post_id);
$caveat      = (string) get_field('price_caveat', $post_id);
$booking_url = function_exists('nfedit_property_booking_url') ? (string) nfedit_property_booking_url($post_id) : (string) get_field('booking_url', $post_id);
if (!$unit) $unit = 'night';
if ($price <= 0 && !$booking_url) return;
?>
<div class="nfedit-property-sticky-cta" data-property-sticky-cta hidden>
    <div class="nfedit-property-sticky-cta__inner">
        <div class="nfedit-property-sticky-cta__price">
            <?php if ($price > 0): ?>
                <p class="nfedit-property-sticky-cta__amount">From &pound;<?php echo (int) $price; ?>/<?php echo esc_html($unit); ?></p>
            <?php endif; ?>
            <?php if ($caveat): ?>
                <p class="nfedit-property-sticky-cta__caveat"><?php echo esc_html($caveat); ?></p>
            <?php endif; ?>
        </div>
        <?php if ($booking_url): ?>
            <a href="<?php echo esc_url($booking_url); ?>" class="nfedit-property-sticky-cta__btn" rel="nofollow sponsored noopener" target="_blank">
                Check availability
            </a>
        <?php endif; ?>
    </div>
</div>
