<?php
/**
 * 6.6 — Editorial body (drop-cap on first paragraph).
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$paragraphs = get_field('editorial_paragraphs', $post_id);
if (!$paragraphs || empty($paragraphs)) return;
?>
<section class="nfedit-property-editorial">
    <div class="container-edit content-edit editorial-prose">
        <?php $idx = 0; foreach ($paragraphs as $row):
            $p = '';
            if (is_array($row)) {
                $p = isset($row['paragraph']) ? (string) $row['paragraph'] : '';
            } elseif (is_string($row)) {
                $p = $row;
            }
            if (!$p) continue;
            $class = $idx === 0 ? ' class="drop-cap"' : '';
            $idx++;
        ?>
            <p<?php echo $class; ?>><?php echo esc_html($p); ?></p>
        <?php endforeach; ?>
    </div>
</section>
