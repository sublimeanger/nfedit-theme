<?php
/**
 * Cottages that send guests here (reverse lookup).
 *
 * Queries property posts where the surrounds relationship field includes this surround's ID.
 * ACF stores relationship values as serialised arrays in postmeta — direct LIKE match is the
 * canonical reverse-lookup approach (ACF meta_query is unreliable for relationships).
 */
defined('ABSPATH') || exit;
$post_id = isset($args['post_id']) ? (int) $args['post_id'] : (int) get_the_ID();
if (!$post_id) return;

global $wpdb;

// ACF relationship serialised: a:N:{i:0;s:3:"201";...} — match the post ID as quoted string
$needle = '"' . (string) $post_id . '"';

$cottage_ids = $wpdb->get_col($wpdb->prepare(
    "SELECT DISTINCT pm.post_id
       FROM {$wpdb->postmeta} pm
       JOIN {$wpdb->posts} p ON p.ID = pm.post_id
      WHERE pm.meta_key = 'surrounds'
        AND pm.meta_value LIKE %s
        AND p.post_type = 'property'
        AND p.post_status = 'publish'
      ORDER BY p.post_date DESC
      LIMIT 12",
    '%' . $wpdb->esc_like($needle) . '%'
));

if (empty($cottage_ids)) return;
?>
<section class="nfedit-surround__cottages">
    <div class="container-edit">
        <h2 class="nfedit-surround__cottages-heading">Cottages that send guests here</h2>
        <p class="nfedit-surround__cottages-dek">The places we recommend nearby for an overnight.</p>
        <div class="nfedit-surround__cottages-rail scroll-rail">
            <?php foreach ($cottage_ids as $cid):
                get_template_part('template-parts/components/property-card', null, [
                    'post_id' => (int) $cid,
                    'variant' => 'standard',
                ]);
            endforeach; ?>
        </div>
    </div>
</section>
