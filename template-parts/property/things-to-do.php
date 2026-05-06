<?php
/**
 * 6.13 — Things to do (markdown bullets, T1).
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$rows = get_field('things_to_do', $post_id);
if (!$rows || empty($rows)) return;
?>
<section class="nfedit-property-things-to-do">
    <div class="container-edit content-edit">
        <h2 class="nfedit-property-things-to-do__heading">What we&rsquo;d do, if we were you</h2>
        <ul class="nfedit-property-things-to-do__list">
            <?php foreach ($rows as $r):
                $text = '';
                if (is_array($r)) {
                    $text = isset($r['item']) ? (string) $r['item'] : '';
                } elseif (is_string($r)) {
                    $text = $r;
                }
                if (!$text) continue;
            ?>
                <li>
                    <span class="nfedit-property-things-to-do__bullet" aria-hidden="true">&#9675;</span>
                    <span><?php echo nfedit_render_inline_markdown($text); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
