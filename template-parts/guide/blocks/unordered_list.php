<?php
defined('ABSPATH') || exit;
$block = $args['block'];
$items = isset($block['items']) ? $block['items'] : [];
if (empty($items)) return;
?>
<ul class="nfedit-guide-block-ul">
    <?php foreach ($items as $row):
        $item = isset($row['item']) ? $row['item'] : '';
        if (!$item) continue;
    ?>
        <li><?php echo wp_kses_post($item); ?></li>
    <?php endforeach; ?>
</ul>
