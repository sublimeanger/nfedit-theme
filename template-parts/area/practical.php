<?php
/**
 * Practical info — h2 + paragraph on bone-warm band.
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$body = (string) get_field('practical', $post_id);
if (!$body) return;
?>
<section class="nfedit-area-practical">
    <div class="container-edit content-edit">
        <h2 class="nfedit-area-practical__heading">Practical info</h2>
        <p class="nfedit-area-practical__body"><?php echo esc_html($body); ?></p>
    </div>
</section>
