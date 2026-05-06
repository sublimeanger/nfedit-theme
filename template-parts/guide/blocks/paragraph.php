<?php
/**
 * Paragraph block — wysiwyg field, contains its own <p> tags.
 */
defined('ABSPATH') || exit;
$block = $args['block'];
$text  = isset($block['text']) ? $block['text'] : '';
if (!$text) return;
?>
<div class="nfedit-guide-block-paragraph">
    <?php echo wp_kses_post($text); ?>
</div>
