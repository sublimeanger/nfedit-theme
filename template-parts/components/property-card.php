<?php
/**
 * Property card — 3 variants: standard / featured / minimal.
 * @var array $args { 'post_id' => int, 'variant' => string }
 */
defined('ABSPATH') || exit;

$pid     = isset($args['post_id']) ? (int) $args['post_id'] : 0;
$variant = isset($args['variant']) ? $args['variant'] : 'standard';
if (!$pid) return;

$data = nfedit_get_property_card_data($pid);
if (empty($data)) return;

$variant_class = 'nfedit-property-card--' . $variant;
?>
<a href="<?php echo esc_url($data['permalink']); ?>" class="nfedit-property-card <?php echo esc_attr($variant_class); ?>">
    <div class="nfedit-property-card__media">
        <?php if (!empty($data['image_id'])): ?>
            <?php echo wp_get_attachment_image((int) $data['image_id'], 'nfedit_card_4_3', false, [
                'alt'     => $data['name'],
                'loading' => 'lazy',
                'class'   => 'img-muted',
            ]); ?>
        <?php elseif (!empty($data['image'])): ?>
            <img src="<?php echo esc_url($data['image']); ?>" alt="<?php echo esc_attr($data['name']); ?>" loading="lazy" class="img-muted" />
        <?php endif; ?>
    </div>
    <div class="nfedit-property-card__body">
        <?php if ($variant === 'minimal'): ?>
            <span class="eyebrow nfedit-property-card__area"><?php echo esc_html($data['area']); ?></span>
        <?php else: ?>
            <div class="nfedit-property-card__meta">
                <span class="eyebrow nfedit-property-card__area"><?php echo esc_html($data['area']); ?></span>
                <?php if ((int) $data['tier'] === 1): ?>
                    <span class="nfedit-property-card__tier">Editor&rsquo;s Pick</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <h3 class="nfedit-property-card__name"><?php echo esc_html($data['name']); ?></h3>
        <?php if ($variant !== 'minimal' && !empty($data['oneLiner'])): ?>
            <p class="nfedit-property-card__oneliner"><?php echo esc_html($data['oneLiner']); ?></p>
        <?php endif; ?>
        <?php if ($variant !== 'minimal'): ?>
            <p class="nfedit-property-card__stats">
                Sleeps <?php echo (int) $data['sleeps']; ?>
                &middot; <?php echo (int) $data['bedrooms']; ?> bed<?php echo (int) $data['bedrooms'] === 1 ? '' : 's'; ?>
                <?php if ($data['dogs']): ?>&middot; Dogs OK<?php endif; ?>
                <?php if ((float) $data['priceFrom'] > 0): ?>
                    &middot; From &pound;<?php echo (int) $data['priceFrom']; ?>/<?php echo esc_html($data['priceUnit']); ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
</a>
