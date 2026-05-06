<?php
/**
 * 6.4 — One-liner italic (T1+T2).
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$one_liner = (string) get_field('one_liner', $post_id);
if (!$one_liner) return;
?>
<section class="nfedit-property-oneliner">
    <p class="content-edit nfedit-property-oneliner__text">&ldquo;<?php echo esc_html($one_liner); ?>&rdquo;</p>
</section>
