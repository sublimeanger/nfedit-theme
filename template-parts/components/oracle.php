<?php
/**
 * Oracle widget — visual + chip behaviour only. Backend wiring in Phase 11.
 * @var array $args { 'variant' => 'light'|'dark' }
 */
defined('ABSPATH') || exit;

$variant = isset($args['variant']) && in_array($args['variant'], ['light', 'dark'], true) ? $args['variant'] : 'light';
$chips = [
    'Family of 4 + dog, October half-term',
    'Walk to the coast, hot tub, sleeps 6',
    'Two nights near Brockenhurst, fire to sit by',
    'Big group, room for 12, near a pub',
];
?>
<div class="nfedit-oracle nfedit-oracle--<?php echo esc_attr($variant); ?>" data-nfedit-oracle data-variant="<?php echo esc_attr($variant); ?>">
    <form class="nfedit-oracle__form" onsubmit="return false;" data-oracle-form>
        <div class="nfedit-oracle__icon-slot">
            <?php echo nfedit_lucide_svg('sparkles', ['width' => 24, 'height' => 24, 'class' => 'nfedit-oracle__icon']); ?>
        </div>
        <textarea
            class="nfedit-oracle__textarea"
            data-oracle-textarea
            rows="1"
            placeholder="A forest cottage, walk to a pub, dogs welcome, half-term&hellip;"
        ></textarea>
        <button type="submit" class="nfedit-oracle__submit" data-oracle-submit>Ask &rarr;</button>
    </form>
    <div class="nfedit-oracle__chips" data-oracle-chips hidden>
        <?php foreach ($chips as $c): ?>
            <button type="button" class="nfedit-oracle__chip" data-oracle-chip><?php echo esc_html($c); ?></button>
        <?php endforeach; ?>
    </div>
</div>
