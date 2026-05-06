<?php
/**
 * 5.7 — Oracle promo (forest band, dark Oracle widget).
 */
defined('ABSPATH') || exit;
?>
<section class="nfedit-home-oracle">
    <div class="container-edit nfedit-home-oracle__inner">
        <p class="eyebrow nfedit-home-oracle__eyebrow">Our favourite thing we&rsquo;ve built</p>
        <h2 class="nfedit-home-oracle__heading">Ask the Oracle.</h2>
        <p class="nfedit-home-oracle__body">
            Tell us what you actually want &mdash; &ldquo;a forest cottage, walk to a pub, dogs welcome, October half-term&rdquo; &mdash; and the Oracle reads our entire collection and returns a real shortlist.
        </p>
        <div class="nfedit-home-oracle__widget">
            <?php get_template_part('template-parts/components/oracle', null, ['variant' => 'dark']); ?>
        </div>
        <a href="<?php echo esc_url(get_post_type_archive_link('property')); ?>" class="nfedit-home-oracle__alt-link">
            Or browse by hand &rarr;
        </a>
    </div>
</section>
