<?php
/**
 * Top pubs — 4-col grid (top-border list style).
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$pubs    = get_field('pubs', $post_id);
if (!is_array($pubs) || empty($pubs)) return;
?>
<section class="nfedit-area-pubs">
    <div class="container-edit">
        <h2 class="nfedit-area-pubs__heading">Top pubs</h2>
        <div class="nfedit-area-pubs__grid">
            <?php foreach ($pubs as $p):
                $name = isset($p['name']) ? (string) $p['name'] : '';
                $dek  = isset($p['dek'])  ? (string) $p['dek']  : '';
                if (!$name) continue;
            ?>
                <div class="nfedit-area-pubs__item">
                    <h3 class="nfedit-area-pubs__name"><?php echo esc_html($name); ?></h3>
                    <?php if ($dek): ?>
                        <p class="nfedit-area-pubs__dek"><?php echo esc_html($dek); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
