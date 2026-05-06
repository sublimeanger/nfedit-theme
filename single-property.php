<?php
/**
 * Single Property template — The New Forest Edit
 *
 * Orchestrates 20 sections of a cottage page. Sections are conditionally
 * rendered based on tier visibility + content presence.
 */

defined('ABSPATH') || exit;

if (have_posts()) the_post();

$post_id = get_the_ID();
$tier    = function_exists('get_field') ? (int) get_field('tier', $post_id) : 1;

$show_one_liner      = nfedit_property_show_section($post_id, 'one_liner');
$show_doctrine       = nfedit_property_show_section($post_id, 'doctrine');
$show_owner_quote    = nfedit_property_show_section($post_id, 'owner_quote');
$show_review         = nfedit_property_show_section($post_id, 'review');
$show_surrounds      = nfedit_property_show_section($post_id, 'surrounds');
$show_things_to_do   = nfedit_property_show_section($post_id, 'things_to_do');
$show_owner_recs     = nfedit_property_show_section($post_id, 'owner_recs');
$show_policy         = nfedit_property_show_section($post_id, 'policy');
$show_sustainability = nfedit_property_show_section($post_id, 'sustainability');

get_header(); ?>

<article class="nfedit-property" data-property-id="<?php echo esc_attr($post_id); ?>" data-tier="<?php echo esc_attr($tier); ?>">

    <?php get_template_part('template-parts/property/offer-banner', null, ['post_id' => $post_id]); ?>
    <?php get_template_part('template-parts/property/hero',         null, ['post_id' => $post_id]); ?>
    <?php get_template_part('template-parts/property/title-block',  null, ['post_id' => $post_id]); ?>

    <?php if ($show_one_liner): ?>
        <?php get_template_part('template-parts/property/one-liner', null, ['post_id' => $post_id]); ?>
    <?php endif; ?>

    <?php get_template_part('template-parts/property/features',     null, ['post_id' => $post_id]); ?>
    <?php get_template_part('template-parts/property/editorial',    null, ['post_id' => $post_id]); ?>

    <?php if ($show_doctrine): ?>
        <?php get_template_part('template-parts/property/doctrine', null, ['post_id' => $post_id]); ?>
    <?php endif; ?>

    <?php get_template_part('template-parts/property/booking-cta', null, ['post_id' => $post_id]); ?>
    <?php get_template_part('template-parts/property/gallery',     null, ['post_id' => $post_id]); ?>

    <?php if ($show_owner_quote): ?>
        <?php get_template_part('template-parts/property/owner-quote', null, ['post_id' => $post_id]); ?>
    <?php endif; ?>

    <?php if ($show_review): ?>
        <?php get_template_part('template-parts/property/review', null, ['post_id' => $post_id]); ?>
    <?php endif; ?>

    <?php if ($show_surrounds): ?>
        <?php get_template_part('template-parts/property/surrounds', null, ['post_id' => $post_id]); ?>
    <?php endif; ?>

    <?php if ($show_things_to_do): ?>
        <?php get_template_part('template-parts/property/things-to-do', null, ['post_id' => $post_id]); ?>
    <?php endif; ?>

    <?php if ($show_owner_recs): ?>
        <?php get_template_part('template-parts/property/owner-recs', null, ['post_id' => $post_id]); ?>
    <?php endif; ?>

    <?php get_template_part('template-parts/property/details', null, ['post_id' => $post_id]); ?>

    <?php if ($show_policy): ?>
        <?php get_template_part('template-parts/property/policy', null, ['post_id' => $post_id]); ?>
    <?php endif; ?>

    <?php get_template_part('template-parts/property/faqs', null, ['post_id' => $post_id]); ?>

    <?php if ($show_sustainability): ?>
        <?php get_template_part('template-parts/property/sustainability', null, ['post_id' => $post_id]); ?>
    <?php endif; ?>

    <?php get_template_part('template-parts/property/related',           null, ['post_id' => $post_id]); ?>
    <?php get_template_part('template-parts/property/sticky-mobile-cta', null, ['post_id' => $post_id]); ?>

</article>

<?php get_footer(); ?>
