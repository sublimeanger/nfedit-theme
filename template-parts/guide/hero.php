<?php
defined('ABSPATH') || exit;
$post_id          = $args['post_id'];
$hero_id          = isset($args['hero_id']) ? (int) $args['hero_id'] : 0;
$category         = $args['category'];
$dek              = $args['dek'];
$read_min         = $args['read_min'];
$last_upd_display = $args['last_upd_display'];
?>
<section class="nfedit-guide-hero">
    <?php if ($hero_id): ?>
        <?php echo wp_get_attachment_image($hero_id, 'nfedit_hero_xl', false, [
            'alt'           => '',
            'class'         => 'nfedit-guide-hero__image',
            'loading'       => 'eager',
            'fetchpriority' => 'high',
        ]); ?>
    <?php endif; ?>
    <div class="nfedit-guide-hero__gradient"></div>
    <div class="container-edit nfedit-guide-hero__caption">
        <?php if ($category): ?>
            <p class="eyebrow nfedit-guide-hero__category"><?php echo esc_html($category); ?></p>
        <?php endif; ?>
        <h1 class="nfedit-guide-hero__title"><?php echo esc_html(get_the_title($post_id)); ?></h1>
        <?php if ($dek): ?>
            <p class="nfedit-guide-hero__dek"><?php echo esc_html($dek); ?></p>
        <?php endif; ?>
        <p class="nfedit-guide-hero__meta">
            <?php if ($read_min): ?><?php echo (int) $read_min; ?> min read<?php endif; ?>
            <?php if ($read_min && $last_upd_display): ?> &middot; <?php endif; ?>
            <?php if ($last_upd_display): ?>Updated <?php echo esc_html($last_upd_display); ?><?php endif; ?>
        </p>
    </div>
</section>
