<?php
/**
 * Surround body — the editorial post_content
 */
defined('ABSPATH') || exit;
$post_id = isset($args['post_id']) ? (int) $args['post_id'] : (int) get_the_ID();
$content = apply_filters('the_content', get_post_field('post_content', $post_id));
if (!$content) return;
?>
<div class="nfedit-surround__body editorial-prose">
    <?php echo $content; // already filtered ?>
</div>
