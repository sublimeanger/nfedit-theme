<?php
/**
 * Single Area template — The New Forest Edit
 *
 * 11 sections, each conditional on data presence:
 *   1.  Hero (always)
 *   2.  Intro (uses dek + intro_paragraph — both optional individually)
 *   3.  At-a-glance (renders any subset of 4 facts)
 *   4.  What's it actually like (whats_it_like)
 *   5.  Where to stay (cottages with this area_taxonomy term)
 *   6.  Top pubs (pubs repeater)
 *   7.  Top dog walks (walks repeater)
 *   8.  What we'd do (things_to_do repeater)
 *   9.  Practical info (practical text)
 *   10. From the Edit (latest editorials)
 *   11. Nearby areas (auto-resolved from forest_or_coast)
 */

defined('ABSPATH') || exit;

if (have_posts()) the_post();

$post_id    = get_the_ID();
// area posts dont have area_taxonomy terms; use post slug which matches term slug
$area_slug = get_post_field("post_name", $post_id);

$dek           = (string) get_field('dek', $post_id);
$intro         = (string) get_field('intro_paragraph', $post_id);
$whats_it_like = (string) get_field('whats_it_like', $post_id);
$practical     = (string) get_field('practical', $post_id);
$pubs          = get_field('pubs', $post_id);
$walks         = get_field('walks', $post_id);
$things_to_do  = get_field('things_to_do', $post_id);

$facts = [
    'from_london'     => (string) get_field('from_london', $post_id),
    'nearest_station' => (string) get_field('nearest_station', $post_id),
    'pony_density'    => (string) get_field('pony_density', $post_id),
    'best_for'        => (string) get_field('best_for', $post_id),
];
$has_any_fact = !empty(array_filter($facts));

$show_intro_section  = $dek || $intro;
$show_facts          = $has_any_fact;
$show_whats_it_like  = !empty($whats_it_like);
$show_pubs           = is_array($pubs) && !empty($pubs);
$show_walks          = is_array($walks) && !empty($walks);
$show_things_to_do   = is_array($things_to_do) && !empty($things_to_do);
$show_practical      = !empty($practical);

get_header(); ?>

<article class="nfedit-area" data-area-id="<?php echo esc_attr($post_id); ?>" data-area-slug="<?php echo esc_attr($area_slug); ?>">

    <?php get_template_part('template-parts/area/hero', null, ['post_id' => $post_id]); ?>

    <?php if ($show_intro_section): ?>
        <?php get_template_part('template-parts/area/intro', null, ['post_id' => $post_id]); ?>
    <?php endif; ?>

    <?php if ($show_facts): ?>
        <?php get_template_part('template-parts/area/facts', null, ['post_id' => $post_id, 'facts' => $facts]); ?>
    <?php endif; ?>

    <?php if ($show_whats_it_like): ?>
        <?php get_template_part('template-parts/area/whats-it-like', null, ['post_id' => $post_id]); ?>
    <?php endif; ?>

    <?php if ($area_slug): ?>
        <?php get_template_part('template-parts/area/where-to-stay', null, ['post_id' => $post_id, 'area_slug' => $area_slug]); ?>
    <?php endif; ?>

    <?php if ($show_pubs): ?>
        <?php get_template_part('template-parts/area/pubs', null, ['post_id' => $post_id]); ?>
    <?php endif; ?>

    <?php if ($show_walks): ?>
        <?php get_template_part('template-parts/area/walks', null, ['post_id' => $post_id]); ?>
    <?php endif; ?>

    <?php if ($show_things_to_do): ?>
        <?php get_template_part('template-parts/area/things-to-do', null, ['post_id' => $post_id]); ?>
    <?php endif; ?>

    <?php if ($show_practical): ?>
        <?php get_template_part('template-parts/area/practical', null, ['post_id' => $post_id]); ?>
    <?php endif; ?>

    <?php get_template_part('template-parts/area/from-the-edit', null, ['post_id' => $post_id]); ?>

    <?php get_template_part('template-parts/area/nearby', null, ['post_id' => $post_id]); ?>

    <?php get_template_part('template-parts/global/newsletter-compact'); ?>

</article>

<?php get_footer();
