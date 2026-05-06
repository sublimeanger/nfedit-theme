<?php
/**
 * Template Name: How We Choose
 */
defined('ABSPATH') || exit;
get_header();

$ctx = 'site-settings-how-we-choose';
$hero_h1  = (string) get_field('hero_h1',  $ctx);
$hero_dek = (string) get_field('hero_dek', $ctx);
$criteria = get_field('criteria',          $ctx);
$tiers    = get_field('tier_explainers',   $ctx);
$wont_do  = get_field('what_we_wont_do',   $ctx);
?>

<article class="nfedit-hwc">

    <section class="nfedit-hwc-hero">
        <div class="container-edit nfedit-hwc-hero__inner">
            <p class="eyebrow nfedit-hwc-hero__eyebrow">How we choose</p>
            <?php if ($hero_h1): ?>
                <h1 class="nfedit-hwc-hero__title"><?php echo esc_html($hero_h1); ?></h1>
            <?php endif; ?>
            <?php if ($hero_dek): ?>
                <p class="nfedit-hwc-hero__dek"><?php echo esc_html($hero_dek); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <?php if (is_array($criteria) && !empty($criteria)): ?>
        <section class="nfedit-hwc-criteria">
            <div class="container-edit">
                <ol class="nfedit-hwc-criteria__list">
                    <?php foreach ($criteria as $i => $c):
                        $n    = isset($c['n']) ? (int) $c['n'] : ($i + 1);
                        $h    = isset($c['h']) ? $c['h'] : '';
                        $body = isset($c['body']) ? $c['body'] : '';
                        if (!$h) continue;
                    ?>
                        <li class="nfedit-hwc-criterion">
                            <p class="nfedit-hwc-criterion__num" aria-hidden="true"><?php printf('%02d', $n); ?></p>
                            <div class="nfedit-hwc-criterion__body">
                                <h2 class="nfedit-hwc-criterion__heading"><?php echo esc_html($h); ?></h2>
                                <?php if ($body): ?>
                                    <p class="nfedit-hwc-criterion__text"><?php echo esc_html($body); ?></p>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </section>
    <?php endif; ?>

    <?php if (is_array($tiers) && !empty($tiers)): ?>
        <section class="nfedit-hwc-tiers">
            <div class="container-edit">
                <p class="eyebrow nfedit-hwc-tiers__eyebrow">The three tiers</p>
                <h2 class="nfedit-hwc-tiers__heading">What our tier badges mean.</h2>
                <div class="nfedit-hwc-tiers__grid">
                    <?php foreach ($tiers as $i => $t):
                        $label = isset($t['tier_label']) ? $t['tier_label'] : '';
                        $desc  = isset($t['description']) ? $t['description'] : '';
                        if (!$label) continue;
                    ?>
                        <div class="nfedit-hwc-tier">
                            <p class="nfedit-hwc-tier__num">T<?php echo (int) ($i + 1); ?></p>
                            <h3 class="nfedit-hwc-tier__label"><?php echo esc_html($label); ?></h3>
                            <p class="nfedit-hwc-tier__desc"><?php echo esc_html($desc); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (is_array($wont_do) && !empty($wont_do)): ?>
        <section class="nfedit-hwc-wont">
            <div class="container-edit nfedit-hwc-wont__inner">
                <p class="eyebrow nfedit-hwc-wont__eyebrow">What we won't do</p>
                <h2 class="nfedit-hwc-wont__heading">Five things, on the record.</h2>
                <ul class="nfedit-hwc-wont__list">
                    <?php foreach ($wont_do as $row):
                        $text = isset($row['text']) ? $row['text'] : '';
                        if (!$text) continue;
                    ?>
                        <li class="nfedit-hwc-wont__item"><?php echo esc_html($text); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    <?php endif; ?>

    <?php get_template_part('template-parts/global/newsletter-compact'); ?>

</article>

<?php get_footer();
