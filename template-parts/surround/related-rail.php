<?php
/**
 * Related surrounds — same kind, same area, exclude self.
 */
defined('ABSPATH') || exit;
$post_id = isset($args['post_id']) ? (int) $args['post_id'] : (int) get_the_ID();

$kinds = get_the_terms($post_id, 'surround_kind');
$areas = get_the_terms($post_id, 'area_taxonomy');
$kind  = (!is_wp_error($kinds) && !empty($kinds)) ? (int) $kinds[0]->term_id : 0;
$area  = (!is_wp_error($areas) && !empty($areas)) ? (int) $areas[0]->term_id : 0;

if (!$kind && !$area) return;

$tax_query = ['relation' => 'OR'];
if ($kind) $tax_query[] = ['taxonomy' => 'surround_kind',  'field' => 'term_id', 'terms' => [$kind]];
if ($area) $tax_query[] = ['taxonomy' => 'area_taxonomy',  'field' => 'term_id', 'terms' => [$area]];

$q = new WP_Query([
    'post_type'      => 'surround',
    'post_status'    => 'publish',
    'posts_per_page' => 6,
    'post__not_in'   => [$post_id],
    'tax_query'      => $tax_query,
    'no_found_rows'  => true,
]);

if (!$q->have_posts()) { wp_reset_postdata(); return; }
?>
<section class="nfedit-surround__related">
    <div class="container-edit">
        <h2 class="nfedit-surround__related-heading">More like this</h2>
        <div class="nfedit-surround__related-rail scroll-rail">
            <?php while ($q->have_posts()): $q->the_post();
                get_template_part('template-parts/components/surround-card', null, ['post_id' => get_the_ID()]);
            endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
