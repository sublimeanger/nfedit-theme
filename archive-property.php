<?php
/**
 * Archive: Cottages (full archive, all filters available).
 */
defined('ABSPATH') || exit;
get_header(); ?>

<div class="nfedit-cottages-archive">
    <div class="nfedit-archive-intro container-edit">
        <h1 class="nfedit-archive-intro__title">Every cottage we&rsquo;ve found worth telling you about.</h1>
        <p class="nfedit-archive-intro__dek">We&rsquo;ve stayed in or visited every editor&rsquo;s-pick property. The rest are hand-vetted from our trusted partners.</p>
    </div>

    <?php get_template_part('template-parts/components/cottages-grid'); ?>

    <section class="nfedit-cottages-footer">
        <div class="container-edit nfedit-cottages-footer__inner">
            <h2 class="nfedit-cottages-footer__heading">Browse another way</h2>
            <div class="nfedit-cottages-footer__links">
                <a href="<?php echo esc_url(home_url('/areas/')); ?>" class="btn-tertiary"><span>By area</span><span class="arrow">&rarr;</span></a>
                <a href="<?php echo esc_url(home_url('/collections/')); ?>" class="btn-tertiary"><span>By feature</span><span class="arrow">&rarr;</span></a>
                <a href="<?php echo esc_url(home_url('/collections/#occasion')); ?>" class="btn-tertiary"><span>By occasion</span><span class="arrow">&rarr;</span></a>
                <a href="<?php echo esc_url(home_url('/collections/#audience')); ?>" class="btn-tertiary"><span>By audience</span><span class="arrow">&rarr;</span></a>
            </div>
        </div>
    </section>

    <?php get_template_part('template-parts/global/oracle-promo-compact'); ?>
    <?php get_template_part('template-parts/global/newsletter-compact'); ?>
</div>

<?php get_footer();
