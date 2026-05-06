<?php
/**
 * 6.1 — Sticky offer banner.
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$active  = (bool) get_field('offer_active', $post_id);
if (!$active) return;

$copy   = (string) get_field('offer_copy', $post_id);
$code   = (string) get_field('offer_code', $post_id);
$expiry = get_field('offer_expiry', $post_id);
if ($expiry) {
    $expiry_ts = strtotime((string) $expiry);
    if ($expiry_ts && $expiry_ts < time()) return;
}
?>
<div class="nfedit-offer-banner" data-nfedit-offer-banner role="status">
    <div class="container-edit nfedit-offer-banner__inner">
        <p class="nfedit-offer-banner__text">
            <?php echo esc_html($copy); ?>
            <?php if ($code): ?>
                Use code <span class="nfedit-offer-banner__code"><?php echo esc_html($code); ?></span>.
            <?php endif; ?>
        </p>
        <button class="nfedit-offer-banner__dismiss" data-nfedit-offer-dismiss aria-label="Dismiss">
            <?php echo nfedit_lucide_svg('x', ['width' => 16, 'height' => 16, 'stroke-width' => 2]); ?>
        </button>
    </div>
</div>
