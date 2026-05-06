<?php
/**
 * Image sizes — match Lovable component aspect ratios exactly.
 * Image production: VeloPress workflow processes uploads to WebP via ~/vp-process-images.sh
 */

defined('ABSPATH') || exit;

add_action('after_setup_theme', function () {
    add_image_size('nfedit_hero_xl',       1920, 1080, true);  // 16:9 hero
    add_image_size('nfedit_hero_md',       1600, 1067, true);  // 3:2 editorial post hero
    add_image_size('nfedit_card_4_3',       800,  600, true);  // PropertyCard standard, MiniCard
    add_image_size('nfedit_card_4_5',       800, 1000, true);  // PropertyCard featured, EditorialCard portrait
    add_image_size('nfedit_card_16_9',     1200,  675, true);  // AreaCard, GuideCard, SurroundCard
    add_image_size('nfedit_card_1_1',       800,  800, true);  // CollectionCard desktop
    add_image_size('nfedit_portrait',       200,  200, true);  // Owner portrait (circle crop in CSS)
    add_image_size('nfedit_team_portrait',  600,  600, true);  // About page team portraits
    add_image_size('nfedit_gallery_4_3',   1200,  900, true);  // Gallery grid
    add_image_size('nfedit_gallery_full',  2400, 1600, false); // Lightbox (soft crop)
});

add_filter('image_size_names_choose', function ($sizes) {
    return array_merge($sizes, [
        'nfedit_hero_xl'      => 'Hero (1920×1080)',
        'nfedit_card_4_3'     => 'Card 4:3',
        'nfedit_card_4_5'     => 'Card 4:5 portrait',
        'nfedit_card_16_9'    => 'Card 16:9',
        'nfedit_gallery_4_3'  => 'Gallery 4:3',
    ]);
});
