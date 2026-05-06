<?php
defined('ABSPATH') || exit;

$post_id    = (int) $args['post_id'];
$hero_url   = isset($args['hero_url']) ? (string) $args['hero_url'] : '';
$cluster    = isset($args['cluster']) ? (string) $args['cluster'] : '';
$dek        = isset($args['dek']) ? (string) $args['dek'] : '';
$author     = isset($args['author']) ? (string) $args['author'] : '';
$display_dt = isset($args['display_dt']) ? (string) $args['display_dt'] : '';
$read_min   = isset($args['read_min']) ? (int) $args['read_min'] : 0;
?>
<section class="nfedit-editorial-hero">
    <?php if ($hero_url): ?>
        <img src="<?php echo esc_url($hero_url); ?>" alt="" class="nfedit-editorial-hero__image" />
    <?php endif; ?>
    <div class="nfedit-editorial-hero__gradient"></div>
    <div class="container-edit nfedit-editorial-hero__caption">
        <?php if ($cluster): ?>
            <p class="eyebrow nfedit-editorial-hero__cluster"><?php echo esc_html($cluster); ?></p>
        <?php endif; ?>
        <h1 class="nfedit-editorial-hero__title"><?php echo esc_html(get_the_title($post_id)); ?></h1>
        <?php if ($dek): ?>
            <p class="nfedit-editorial-hero__dek"><?php echo esc_html($dek); ?></p>
        <?php endif; ?>
        <?php if ($author || $display_dt || $read_min): ?>
            <p class="nfedit-editorial-hero__byline">
                <?php if ($author): ?>By <?php echo esc_html($author); ?> &middot; <?php endif; ?>
                <?php if ($display_dt): ?><?php echo esc_html($display_dt); ?><?php endif; ?>
                <?php if ($read_min > 0): ?><?php echo $display_dt ? ' &middot; ' : ''; ?><?php echo (int) $read_min; ?> min read<?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
</section>
