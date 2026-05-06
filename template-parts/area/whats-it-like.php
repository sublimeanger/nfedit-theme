<?php
/**
 * "What it's actually like" — paragraph editorial.
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$body = (string) get_field('whats_it_like', $post_id);
if (!$body) return;
?>
<section class="nfedit-area-whats">
    <div class="container-edit content-edit">
        <h2 class="nfedit-area-whats__heading">What it&rsquo;s actually like</h2>
        <p class="nfedit-area-whats__body"><?php echo esc_html($body); ?></p>
    </div>
</section>
