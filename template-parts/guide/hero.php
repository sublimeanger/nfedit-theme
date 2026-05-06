<?php
defined('ABSPATH') || exit;
$post_id          = $args['post_id'];
$hero_url         = $args['hero_url'];
$category         = $args['category'];
$dek              = $args['dek'];
$read_min         = $args['read_min'];
$last_upd_display = $args['last_upd_display'];
?>
<section class="nfedit-guide-hero">
    <?php if ($hero_url): ?>
        <img src="<?php echo esc_url($hero_url); ?>" alt="" class="nfedit-guide-hero__image" />
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
