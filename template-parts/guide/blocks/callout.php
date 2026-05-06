<?php
defined('ABSPATH') || exit;
$block = $args['block'];
$text  = isset($block['text']) ? $block['text'] : '';
if (!$text) return;
?>
<aside class="nfedit-guide-block-callout">
    <div class="nfedit-guide-block-callout__rule" aria-hidden="true"></div>
    <p class="nfedit-guide-block-callout__text"><?php echo esc_html($text); ?></p>
</aside>
