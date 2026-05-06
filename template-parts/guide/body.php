<?php
/**
 * Guide Flexible Content body renderer.
 *
 * @var array $args { 'post_id' => int, 'body' => array of blocks }
 */
defined('ABSPATH') || exit;
$body = $args['body'];
if (empty($body) || !is_array($body)) return;
?>
<section class="nfedit-guide-body">
    <div class="container-edit nfedit-guide-body__inner">
        <?php foreach ($body as $block):
            $layout = isset($block['acf_fc_layout']) ? $block['acf_fc_layout'] : '';
            if (!$layout) continue;
            get_template_part('template-parts/guide/blocks/' . $layout, null, ['block' => $block]);
        endforeach; ?>
    </div>
</section>
