<?php
/**
 * 5.10 — Press strip.
 */
defined('ABSPATH') || exit;

$publications = [
    'Condé Nast Traveller',
    'The Times',
    'Country Living',
    'House & Garden',
    'Suitcase',
    'Monocle',
];
?>
<section class="nfedit-home-press">
    <div class="container-edit nfedit-home-press__inner">
        <p class="eyebrow nfedit-home-press__eyebrow">As seen in</p>
        <div class="nfedit-home-press__list">
            <?php foreach ($publications as $p): ?>
                <span class="nfedit-home-press__item"><?php echo esc_html($p); ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</section>
