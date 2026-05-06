<?php
/**
 * 5.2 — Trust strip (4 columns).
 */
defined('ABSPATH') || exit;

$items = [
    ['icon' => 'compass', 'label' => 'Curated',         'headline' => 'Every cottage chosen by us.'],
    ['icon' => 'trees',   'label' => 'Forest + Coast',  'headline' => 'Heath, oak woods, Solent shoreline. All in one trip.'],
    ['icon' => 'heart',   'label' => 'Known by heart',  'headline' => 'We grew up here. Recommendations that mean something.'],
    ['icon' => 'tag',     'label' => 'No booking fees', 'headline' => "Book direct via our trusted partners. We take a small affiliate fee, you don't pay extra."],
];
?>
<section class="nfedit-home-trust">
    <div class="container-edit nfedit-home-trust__grid">
        <?php foreach ($items as $i): ?>
            <div class="nfedit-home-trust__item">
                <?php echo nfedit_lucide_svg($i['icon'], ['width' => 24, 'height' => 24, 'stroke-width' => 1.25, 'class' => 'nfedit-home-trust__icon']); ?>
                <span class="eyebrow nfedit-home-trust__label"><?php echo esc_html($i['label']); ?></span>
                <p class="nfedit-home-trust__headline"><?php echo esc_html($i['headline']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>
