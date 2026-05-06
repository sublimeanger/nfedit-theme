<?php
/**
 * 6.14 — Owner recommendations (3 columns: eat / do / see, T1).
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$eat = get_field('owner_recs_eat', $post_id);
$do  = get_field('owner_recs_do',  $post_id);
$see = get_field('owner_recs_see', $post_id);

if (empty($eat) && empty($do) && empty($see)) return;

$cols = [
    ['label' => 'TO EAT', 'items' => $eat],
    ['label' => 'TO DO',  'items' => $do],
    ['label' => 'TO SEE', 'items' => $see],
];
?>
<section class="nfedit-property-owner-recs">
    <div class="container-edit">
        <div class="nfedit-property-owner-recs__head">
            <h2 class="nfedit-property-owner-recs__heading">From the owner</h2>
            <p class="nfedit-property-owner-recs__dek">The local list &mdash; what they actually do on their days off.</p>
        </div>
        <div class="nfedit-property-owner-recs__grid">
            <?php foreach ($cols as $col):
                if (empty($col['items'])) continue;
            ?>
                <div class="nfedit-property-owner-recs__col">
                    <p class="eyebrow nfedit-property-owner-recs__col-label"><?php echo esc_html($col['label']); ?></p>
                    <div class="nfedit-property-owner-recs__items">
                        <?php foreach ($col['items'] as $item):
                            $name   = isset($item['name']) ? (string) $item['name'] : '';
                            $dek    = isset($item['dek']) ? (string) $item['dek'] : '';
                            $img_id = isset($item['image']) ? $item['image'] : 0;
                            $img_id = is_array($img_id) ? (isset($img_id['ID']) ? (int) $img_id['ID'] : 0) : (int) $img_id;
                            get_template_part('template-parts/components/mini-card', null, [
                                'name'     => $name,
                                'dek'      => $dek,
                                'image_id' => $img_id,
                            ]);
                        endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
