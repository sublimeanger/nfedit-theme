<?php
/**
 * "What we'd do" bullets with markdown bold/italic via nfedit_render_inline_markdown().
 * Schema sub-field: 'item'.
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$things  = get_field('things_to_do', $post_id);
if (!is_array($things) || empty($things)) return;
?>
<section class="nfedit-area-things-to-do">
    <div class="container-edit content-edit">
        <h2 class="nfedit-area-things-to-do__heading">What we&rsquo;d do, if we were you</h2>
        <ul class="nfedit-area-things-to-do__list">
            <?php foreach ($things as $t):
                $text = '';
                if (is_array($t)) {
                    $text = isset($t['item']) ? (string) $t['item'] : '';
                } elseif (is_string($t)) {
                    $text = $t;
                }
                if (!$text) continue;
            ?>
                <li>
                    <span class="nfedit-area-things-to-do__bullet" aria-hidden="true">&#9675;</span>
                    <span><?php echo nfedit_render_inline_markdown($text); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
