<?php
/**
 * Template Name: Oracle
 *
 * Phase 10A: coming-soon stub when nfedit_oracle_coming_soon is true.
 * Phase 11: flips wp_option to false; same template, real backend.
 */
defined('ABSPATH') || exit;
get_header();

$ctx = 'site-settings-oracle';
$hero_h1     = (string) get_field('hero_h1',  $ctx);
$hero_sub    = (string) get_field('hero_sub', $ctx);
$chips       = get_field('example_chips',          $ctx);
$steps       = get_field('how_it_works_steps',     $ctx);
$samples     = get_field('sample_queries',         $ctx);
$limitations = (string) get_field('limitations_paragraph', $ctx);
$coming_soon = (bool) get_option('nfedit_oracle_coming_soon');
?>

<article class="nfedit-oracle <?php echo $coming_soon ? 'is-coming-soon' : ''; ?>" data-coming-soon="<?php echo $coming_soon ? '1' : '0'; ?>">

    <section class="nfedit-oracle-hero">
        <div class="container-edit nfedit-oracle-hero__inner">
            <p class="eyebrow nfedit-oracle-hero__eyebrow">Ask the Oracle</p>
            <?php if ($hero_h1): ?>
                <h1 class="nfedit-oracle-hero__title"><?php echo esc_html($hero_h1); ?></h1>
            <?php endif; ?>
            <?php if ($hero_sub): ?>
                <p class="nfedit-oracle-hero__sub"><?php echo esc_html($hero_sub); ?></p>
            <?php endif; ?>

            <form class="nfedit-oracle-form" data-nfedit-oracle-form>
                <textarea name="query" rows="3" placeholder="Tell us what you're looking for..." required class="nfedit-oracle-form__input"></textarea>
                <button type="submit" class="btn-primary nfedit-oracle-form__submit">
                    <?php echo $coming_soon ? esc_html__("Notify me when it's ready", 'nfedit') : esc_html__('Ask the Oracle', 'nfedit'); ?>
                </button>
                <?php if ($coming_soon): ?>
                    <input type="email" name="notify_email" placeholder="Your email (optional)" class="nfedit-oracle-form__input nfedit-oracle-form__email" />
                <?php endif; ?>
            </form>

            <?php if ($coming_soon): ?>
                <p class="nfedit-oracle-hero__notice">
                    The Oracle is launching soon. In the meantime, browse <a href="<?php echo esc_url(home_url('/cottages/')); ?>">all cottages</a> or by <a href="<?php echo esc_url(home_url('/areas/')); ?>">area</a>.
                </p>
            <?php endif; ?>

            <p class="nfedit-oracle-form__status" data-oracle-status hidden></p>
        </div>
    </section>

    <?php if (is_array($chips) && !empty($chips)): ?>
        <section class="nfedit-oracle-chips">
            <div class="container-edit">
                <p class="eyebrow nfedit-oracle-chips__eyebrow">For example</p>
                <ul class="nfedit-oracle-chips__list">
                    <?php foreach ($chips as $chip):
                        $t = isset($chip['text']) ? $chip['text'] : '';
                        if (!$t) continue;
                    ?>
                        <li>
                            <button type="button" class="nfedit-oracle-chip" data-chip="<?php echo esc_attr($t); ?>">
                                <?php echo esc_html($t); ?>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </section>
    <?php endif; ?>

    <?php if (is_array($steps) && !empty($steps)): ?>
        <section class="nfedit-oracle-how">
            <div class="container-edit">
                <p class="eyebrow nfedit-oracle-how__eyebrow">How it works</p>
                <h2 class="nfedit-oracle-how__heading">Three steps. No login required.</h2>
                <ol class="nfedit-oracle-how__list">
                    <?php foreach ($steps as $step):
                        $n    = isset($step['step_number']) ? (int) $step['step_number'] : 0;
                        $body = isset($step['body']) ? $step['body'] : '';
                        if (!$body) continue;
                    ?>
                        <li class="nfedit-oracle-how__item">
                            <span class="nfedit-oracle-how__num"><?php printf('%02d', $n); ?></span>
                            <p class="nfedit-oracle-how__body"><?php echo esc_html($body); ?></p>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($limitations): ?>
        <section class="nfedit-oracle-limitations">
            <div class="container-edit nfedit-oracle-limitations__inner editorial-prose">
                <p class="eyebrow nfedit-oracle-limitations__eyebrow">Honesty</p>
                <h2 class="nfedit-oracle-limitations__heading">What it can't do.</h2>
                <?php echo wp_kses_post($limitations); ?>
            </div>
        </section>
    <?php endif; ?>

    <?php get_template_part('template-parts/global/newsletter-compact'); ?>

</article>

<?php get_footer();
