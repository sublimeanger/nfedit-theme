<?php
/**
 * 6.12 — Around here (surrounds rail).
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id   = (int) $args['post_id'];
$surrounds = get_field('surrounds', $post_id);
if (!$surrounds || empty($surrounds)) return;
?>
<section id="around" class="nfedit-property-surrounds">
    <div class="container-edit">
        <div class="nfedit-property-surrounds__head">
            <h2 class="nfedit-property-surrounds__heading">Around here</h2>
            <p class="nfedit-property-surrounds__dek">What&rsquo;s worth the detour.</p>
        </div>
        <div class="nfedit-property-surrounds__rail scroll-rail">
            <?php foreach ($surrounds as $surround):
                $sid = is_object($surround) ? (int) $surround->ID : (int) $surround;
                if (!$sid) continue;
                get_template_part('template-parts/components/surround-card', null, ['post_id' => $sid]);
            endforeach; ?>
        </div>
    </div>
</section>
