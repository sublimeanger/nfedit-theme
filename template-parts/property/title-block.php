<?php
/**
 * 6.3 — Title block (stats + price) + sticky sub-nav.
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id   = (int) $args['post_id'];
$sleeps    = (int) get_field('sleeps', $post_id);
$bedrooms  = (int) get_field('bedrooms', $post_id);
$bathrooms = (int) get_field('bathrooms', $post_id);
$dogs      = (bool) get_field('dogs_welcome', $post_id);
$price     = (float) get_field('price_from', $post_id);
$unit      = (string) get_field('price_unit', $post_id);
$caveat    = (string) get_field('price_caveat', $post_id);
if (!$unit) $unit = 'night';

$area_terms = get_the_terms($post_id, 'area_taxonomy');
$area = ($area_terms && !is_wp_error($area_terms)) ? $area_terms[0]->name : '';

$sections = [
    ['id' => 'overview', 'label' => 'Overview'],
    ['id' => 'gallery',  'label' => 'Gallery'],
    ['id' => 'around',   'label' => 'Location'],
    ['id' => 'details',  'label' => 'Details'],
    ['id' => 'reviews',  'label' => 'Reviews'],
    ['id' => 'faqs',     'label' => 'FAQs'],
];
?>
<section id="overview" class="nfedit-property-title-block">
    <div class="container-edit nfedit-property-title-block__inner">
        <div class="nfedit-property-title-block__stats">
            <span class="nfedit-stat"><?php echo nfedit_lucide_svg('users', ['width' => 16, 'height' => 16]); ?> Sleeps <?php echo (int) $sleeps; ?></span>
            <span class="nfedit-stat-divider">&middot;</span>
            <span class="nfedit-stat"><?php echo nfedit_lucide_svg('bed-double', ['width' => 16, 'height' => 16]); ?> <?php echo (int) $bedrooms; ?> bedrooms</span>
            <span class="nfedit-stat-divider">&middot;</span>
            <span class="nfedit-stat"><?php echo nfedit_lucide_svg('bath', ['width' => 16, 'height' => 16]); ?> <?php echo (int) $bathrooms; ?> bathrooms</span>
            <?php if ($dogs): ?>
                <span class="nfedit-stat-divider">&middot;</span>
                <span class="nfedit-stat"><?php echo nfedit_lucide_svg('dog', ['width' => 16, 'height' => 16]); ?> Dogs welcome</span>
            <?php endif; ?>
            <?php if ($area): ?>
                <span class="nfedit-stat-divider">&middot;</span>
                <span class="nfedit-stat"><?php echo nfedit_lucide_svg('map-pin', ['width' => 16, 'height' => 16]); ?> <?php echo esc_html($area); ?></span>
            <?php endif; ?>
        </div>
        <?php if ($price > 0): ?>
            <div class="nfedit-property-title-block__price">
                <p class="nfedit-property-title-block__price-amount">
                    From &pound;<?php echo (int) $price; ?><span class="nfedit-property-title-block__price-unit">/<?php echo esc_html($unit); ?></span>
                </p>
                <?php if ($caveat): ?>
                    <p class="nfedit-property-title-block__price-caveat"><?php echo esc_html($caveat); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <nav class="nfedit-property-subnav" data-property-subnav aria-label="Section navigation">
        <div class="container-edit nfedit-property-subnav__inner">
            <ul>
                <?php foreach ($sections as $s): ?>
                    <li>
                        <a href="#<?php echo esc_attr($s['id']); ?>" data-subnav-link="<?php echo esc_attr($s['id']); ?>">
                            <?php echo esc_html($s['label']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
</section>
