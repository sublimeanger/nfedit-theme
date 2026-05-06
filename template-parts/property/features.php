<?php
/**
 * 6.5 — Feature pills.
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$features = get_the_terms($post_id, 'feature');
if (!$features || is_wp_error($features) || empty($features)) return;
?>
<section class="nfedit-property-features">
    <div class="container-edit content-edit nfedit-property-features__list">
        <?php foreach ($features as $f):
            $icon = nfedit_feature_to_icon($f->slug);
        ?>
            <span class="nfedit-feature-pill">
                <?php echo nfedit_lucide_svg($icon, ['width' => 16, 'height' => 16]); ?>
                <?php echo esc_html($f->name); ?>
            </span>
        <?php endforeach; ?>
    </div>
</section>
