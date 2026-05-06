<?php
/**
 * Homepage — The New Forest Edit
 *
 * 10 sections from Lovable's Index.tsx.
 */
defined('ABSPATH') || exit;

get_header(); ?>

<div class="nfedit-home">
    <?php get_template_part('template-parts/home/hero'); ?>
    <?php get_template_part('template-parts/home/trust-strip'); ?>
    <?php get_template_part('template-parts/home/editor-picks'); ?>
    <?php get_template_part('template-parts/home/areas'); ?>
    <?php get_template_part('template-parts/home/from-the-edit'); ?>
    <?php get_template_part('template-parts/home/collections'); ?>
    <?php get_template_part('template-parts/home/oracle-promo'); ?>
    <?php get_template_part('template-parts/home/guides'); ?>
    <?php get_template_part('template-parts/home/newsletter'); ?>
    <?php get_template_part('template-parts/home/press'); ?>
</div>

<?php get_footer();
