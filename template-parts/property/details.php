<?php
/**
 * 6.15 — Details (label/value 2-col grid).
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$rows = get_field('details', $post_id);
if (!$rows || empty($rows)) return;

// Filter empty rows so blank seeded values don't leave gap rows
$filtered = [];
foreach ($rows as $r) {
    $label = isset($r['label']) ? (string) $r['label'] : '';
    $value = isset($r['value']) ? (string) $r['value'] : '';
    if ($label && $value) $filtered[] = ['label' => $label, 'value' => $value];
}
if (empty($filtered)) return;
?>
<section id="details" class="nfedit-property-details">
    <div class="container-edit">
        <h2 class="nfedit-property-details__heading">The details</h2>
        <div class="nfedit-property-details__grid">
            <?php foreach ($filtered as $r): ?>
                <div class="nfedit-property-details__row">
                    <span class="nfedit-property-details__label"><?php echo esc_html($r['label']); ?></span>
                    <span class="nfedit-property-details__value">&mdash; <?php echo esc_html($r['value']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
