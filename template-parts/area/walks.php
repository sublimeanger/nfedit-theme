<?php
/**
 * Top dog walks — name + grade · distance · pram OK.
 * Schema sub-fields: name, grade, distance, pram_friendly.
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$walks   = get_field('walks', $post_id);
if (!is_array($walks) || empty($walks)) return;
?>
<section class="nfedit-area-walks">
    <div class="container-edit">
        <h2 class="nfedit-area-walks__heading">Top dog walks</h2>
        <div class="nfedit-area-walks__grid">
            <?php foreach ($walks as $w):
                $name     = isset($w['name'])     ? (string) $w['name']     : '';
                $grade    = isset($w['grade'])    ? (string) $w['grade']    : '';
                $distance = isset($w['distance']) ? (string) $w['distance'] : '';
                $pram     = !empty($w['pram_friendly']);
                if (!$name) continue;

                $meta_parts = [];
                if ($grade)    $meta_parts[] = ucfirst($grade);
                if ($distance) $meta_parts[] = $distance;
                if ($pram)     $meta_parts[] = 'pram OK';
            ?>
                <div class="nfedit-area-walks__item">
                    <span class="nfedit-area-walks__name"><?php echo esc_html($name); ?></span>
                    <?php if (!empty($meta_parts)): ?>
                        <span class="nfedit-area-walks__meta"><?php echo esc_html(implode(' · ', $meta_parts)); ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
