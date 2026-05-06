<?php
/**
 * 6.17 — FAQs.
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$rows = get_field('faqs', $post_id);
if (!$rows || empty($rows)) return;

$has_content = false;
foreach ($rows as $r) {
    if (!empty($r['q']) && !empty($r['a'])) { $has_content = true; break; }
}
if (!$has_content) return;
?>
<section id="faqs" class="nfedit-property-faqs">
    <div class="container-edit">
        <h2 class="nfedit-property-faqs__heading">Frequently asked</h2>
        <div class="content-edit nfedit-property-faqs__list">
            <?php foreach ($rows as $r):
                $q = isset($r['q']) ? (string) $r['q'] : '';
                $a = isset($r['a']) ? (string) $r['a'] : '';
                if (!$q || !$a) continue;
                get_template_part('template-parts/components/faq-item', null, ['q' => $q, 'a' => $a]);
            endforeach; ?>
        </div>
    </div>
</section>
