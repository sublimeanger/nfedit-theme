<?php
/**
 * 404 — bespoke not-found page (Phase 10B).
 */
defined('ABSPATH') || exit;

http_response_code(404);

get_header();

$eyebrow   = (string) get_field('eyebrow', 'site-settings-404');
$h1        = (string) get_field('h1',      'site-settings-404');
$body_text = (string) get_field('body',    'site-settings-404');
$cta_cards = get_field('cta_cards',        'site-settings-404');

if (!$eyebrow)   $eyebrow   = 'A WRONG TURN IN THE FOREST';
if (!$h1)        $h1        = "This path doesn't exist.";
if (!$body_text) $body_text = "Try one of the routes below, or head back to the homepage.";

$discovery = new WP_Query([
    'post_type'      => 'property',
    'posts_per_page' => 3,
    'meta_query'     => [['key' => 'tier', 'value' => 1]],
    'orderby'        => 'rand',
]);
?>

<article class="nfedit-404">

    <section class="container-edit nfedit-404-hero">
        <p class="nfedit-404-hero__big" aria-hidden="true">404</p>
        <p class="eyebrow nfedit-404-hero__eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h1 class="nfedit-404-hero__title"><?php echo esc_html($h1); ?></h1>
        <p class="nfedit-404-hero__body"><?php echo esc_html($body_text); ?></p>
        <div class="nfedit-404-hero__actions">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-primary">Back to the homepage</a>
            <a href="<?php echo esc_url(home_url('/cottages/')); ?>" class="btn-tertiary">
                <span>All cottages</span> <span class="arrow">&rarr;</span>
            </a>
        </div>
    </section>

    <?php if (is_array($cta_cards) && !empty($cta_cards)): ?>
        <section class="nfedit-404-destinations">
            <div class="container-edit">
                <p class="eyebrow nfedit-404-destinations__eyebrow">You might be looking for</p>
                <h2 class="nfedit-404-destinations__heading">A few likely-better paths.</h2>
                <div class="nfedit-404-destinations__grid">
                    <?php foreach ($cta_cards as $card):
                        $heading = isset($card['heading']) ? $card['heading'] : '';
                        $url     = isset($card['url']) ? $card['url'] : '#';
                        if (!$heading) continue;
                        $href = (strpos($url, 'http') === 0) ? $url : home_url($url);
                    ?>
                        <a href="<?php echo esc_url($href); ?>" class="nfedit-404-destination">
                            <h3 class="nfedit-404-destination__heading"><?php echo esc_html($heading); ?></h3>
                            <span class="nfedit-404-destination__arrow" aria-hidden="true">&rarr;</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($discovery->have_posts()): ?>
        <section class="nfedit-404-discovery">
            <div class="container-edit">
                <p class="eyebrow nfedit-404-discovery__eyebrow">Or, since you&rsquo;re here</p>
                <h2 class="nfedit-404-discovery__heading">Three cottages we love anyway.</h2>
                <div class="nfedit-404-discovery__grid">
                    <?php while ($discovery->have_posts()): $discovery->the_post(); ?>
                        <?php get_template_part('template-parts/components/property-card', null, [
                            'post_id' => get_the_ID(),
                            'variant' => 'standard',
                        ]); ?>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php get_template_part('template-parts/global/newsletter-compact'); ?>

</article>

<?php get_footer();
