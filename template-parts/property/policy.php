<?php
/**
 * 6.16 — Booking & cancellation policy (T1+T2).
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$policy = (string) get_field('policy', $post_id);
if (!$policy) return;
?>
<section class="nfedit-property-policy">
    <div class="container-edit content-edit">
        <h3 class="nfedit-property-policy__heading">Booking &amp; cancellation</h3>
        <p class="nfedit-property-policy__body"><?php echo esc_html($policy); ?></p>
    </div>
</section>
