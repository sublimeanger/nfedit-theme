<?php
/**
 * 6.18 — Sustainability (T1).
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$body = (string) get_field('sustainability', $post_id);
if (!$body) return;
?>
<section class="nfedit-property-sustainability">
    <div class="container-edit content-edit nfedit-property-sustainability__inner">
        <p class="eyebrow nfedit-property-sustainability__eyebrow">Sustainability</p>
        <p class="nfedit-property-sustainability__body"><?php echo esc_html($body); ?></p>
    </div>
</section>
