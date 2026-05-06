<?php
/**
 * At-a-glance facts: 4 columns (or fewer if some empty).
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$facts   = isset($args['facts']) ? $args['facts'] : [];

$fact_meta = [
    'from_london'     => ['icon' => 'map-pin', 'eyebrow' => 'FROM LONDON'],
    'nearest_station' => ['icon' => 'train',   'eyebrow' => 'NEAREST STATION'],
    'pony_density'    => ['icon' => 'trees',   'eyebrow' => 'PONY DENSITY'],
    'best_for'        => ['icon' => 'heart',   'eyebrow' => 'BEST FOR'],
];
?>
<section class="nfedit-area-facts">
    <div class="container-edit nfedit-area-facts__grid">
        <?php foreach ($fact_meta as $key => $meta):
            $value = isset($facts[$key]) ? (string) $facts[$key] : '';
            if (!$value) continue;
        ?>
            <div class="nfedit-area-facts__item">
                <?php echo nfedit_lucide_svg($meta['icon'], ['width' => 24, 'height' => 24, 'stroke-width' => 1.25, 'class' => 'nfedit-area-facts__icon']); ?>
                <span class="eyebrow nfedit-area-facts__eyebrow"><?php echo esc_html($meta['eyebrow']); ?></span>
                <p class="nfedit-area-facts__value"><?php echo esc_html($value); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>
