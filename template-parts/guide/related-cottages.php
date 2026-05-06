<?php
defined('ABSPATH') || exit;
$post_id  = $args['post_id'];
$cottages = $args['cottages'];

$ids = [];
foreach ($cottages as $c) {
    if (is_object($c)) $ids[] = $c->ID;
    elseif (is_numeric($c)) $ids[] = (int) $c;
}
if (empty($ids)) return;
?>
<section class="nfedit-guide-related-cottages">
    <div class="container-edit">
        <div class="nfedit-guide-related-cottages__head">
            <p class="eyebrow nfedit-guide-related-cottages__eyebrow">Pair this guide with</p>
            <h2 class="nfedit-guide-related-cottages__heading">Cottages where this guide pays off.</h2>
        </div>
        <div class="nfedit-guide-related-cottages__grid">
            <?php foreach ($ids as $cid): ?>
                <?php get_template_part('template-parts/components/property-card', null, [
                    'post_id' => $cid,
                    'variant' => 'standard',
                ]); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
