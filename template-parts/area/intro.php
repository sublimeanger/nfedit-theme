<?php
/**
 * Area intro — italic dek + body paragraph.
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$dek   = (string) get_field('dek', $post_id);
$intro = (string) get_field('intro_paragraph', $post_id);
?>
<section class="nfedit-area-intro">
    <div class="container-edit content-edit">
        <?php if ($dek): ?>
            <p class="nfedit-area-intro__dek"><?php echo esc_html($dek); ?></p>
        <?php endif; ?>
        <?php if ($intro): ?>
            <p class="nfedit-area-intro__body"><?php echo esc_html($intro); ?></p>
        <?php endif; ?>
    </div>
</section>
