<?php
/**
 * 6.8 — Booking CTA.
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id     = (int) $args['post_id'];
$merchant    = (string) get_field('merchant', $post_id);
$booking_url = function_exists('nfedit_property_booking_url') ? (string) nfedit_property_booking_url($post_id) : (string) get_field('booking_url', $post_id);
$override    = (string) get_field('booking_cta_override_copy', $post_id);

$merchant_label = $merchant ? $merchant : 'our partner';
$default_copy = sprintf(
    "We don&rsquo;t take bookings directly &mdash; you&rsquo;ll book with %s at the best available price. Our affiliate link helps keep The Edit running, at no extra cost to you.",
    esc_html($merchant_label)
);
?>
<section class="nfedit-property-booking-cta">
    <div class="container-edit content-edit nfedit-property-booking-cta__inner">
        <h2 class="nfedit-property-booking-cta__heading">Check availability</h2>
        <p class="nfedit-property-booking-cta__body"><?php echo $override ? esc_html($override) : $default_copy; ?></p>
        <div class="nfedit-property-booking-cta__actions">
            <?php if ($booking_url): ?>
                <a href="<?php echo esc_url($booking_url); ?>" class="btn-primary" rel="nofollow sponsored noopener" target="_blank">
                    View on <?php echo esc_html($merchant_label); ?> &rarr;
                </a>
            <?php endif; ?>
            <button class="btn-tertiary nfedit-property-booking-cta__save" data-nfedit-save="<?php echo esc_attr($post_id); ?>">
                <?php echo nfedit_lucide_svg('bookmark', ['width' => 16, 'height' => 16]); ?>
                Or save for later
            </button>
        </div>
    </div>
</section>
