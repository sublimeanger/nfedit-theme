<?php
/**
 * 6.7 — What we love / What you may not love (T1).
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id  = (int) $args['post_id'];
$love     = get_field('what_we_love', $post_id);
$not_love = get_field('what_you_may_not_love', $post_id);

$extract = function ($rows) {
    if (!is_array($rows)) return [];
    $out = [];
    foreach ($rows as $r) {
        $val = '';
        if (is_array($r)) {
            $val = isset($r['text']) ? (string) $r['text'] : (isset($r['point']) ? (string) $r['point'] : '');
        } elseif (is_string($r)) {
            $val = $r;
        }
        if ($val) $out[] = $val;
    }
    return $out;
};
$love_pts     = $extract($love);
$not_love_pts = $extract($not_love);
if (empty($love_pts) && empty($not_love_pts)) return;
?>
<section class="nfedit-property-doctrine">
    <div class="container-edit nfedit-property-doctrine__grid">
        <?php if (!empty($love_pts)): ?>
            <div class="nfedit-property-doctrine__col nfedit-property-doctrine__col--love">
                <p class="eyebrow nfedit-property-doctrine__heading">What we love</p>
                <ul class="nfedit-property-doctrine__list">
                    <?php foreach ($love_pts as $p): ?>
                        <li>
                            <span class="nfedit-property-doctrine__bullet" aria-hidden="true">&#9656;</span>
                            <span><?php echo esc_html($p); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if (!empty($not_love_pts)): ?>
            <div class="nfedit-property-doctrine__col nfedit-property-doctrine__col--not-love">
                <p class="eyebrow nfedit-property-doctrine__heading nfedit-property-doctrine__heading--alt">What you may not love</p>
                <ul class="nfedit-property-doctrine__list nfedit-property-doctrine__list--alt">
                    <?php foreach ($not_love_pts as $p): ?>
                        <li>
                            <span class="nfedit-property-doctrine__bullet" aria-hidden="true">&mdash;</span>
                            <span><?php echo esc_html($p); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</section>
