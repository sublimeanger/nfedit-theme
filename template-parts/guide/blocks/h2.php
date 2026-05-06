<?php
defined('ABSPATH') || exit;
$block = $args['block'];
$text  = isset($block['text']) ? $block['text'] : '';
$id    = isset($block['id']) ? $block['id'] : '';
if (!$text) return;
if (!$id) $id = sanitize_title($text);
?>
<h2 id="<?php echo esc_attr($id); ?>" class="nfedit-guide-block-h2"><?php echo esc_html($text); ?></h2>
