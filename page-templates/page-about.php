<?php
/**
 * Template Name: About
 */
defined('ABSPATH') || exit;
get_header();

$ctx = 'site-settings-about';
$hero_h1  = (string) get_field('hero_h1',  $ctx);
$hero_dek = (string) get_field('hero_dek', $ctx);

$hero_id = get_field('hero_image', $ctx);
if (is_array($hero_id) && isset($hero_id['ID'])) $hero_id = $hero_id['ID'];
$hero_url = $hero_id ? wp_get_attachment_image_url((int) $hero_id, 'nfedit_hero_xl') : '';

$story      = get_field('our_story_paragraphs', $ctx);
$principles = get_field('principles',           $ctx);
$team       = get_field('team',                 $ctx);
$faqs       = get_field('about_faqs',           $ctx);
$press      = get_field('press_logos',          $ctx);
?>

<article class="nfedit-about">

    <section class="nfedit-about-hero">
        <?php if ($hero_url): ?>
            <img src="<?php echo esc_url($hero_url); ?>" alt="" class="nfedit-about-hero__image" />
        <?php endif; ?>
        <div class="nfedit-about-hero__gradient"></div>
        <div class="container-edit nfedit-about-hero__caption">
            <p class="eyebrow nfedit-about-hero__eyebrow">About</p>
            <?php if ($hero_h1): ?>
                <h1 class="nfedit-about-hero__title"><?php echo esc_html($hero_h1); ?></h1>
            <?php endif; ?>
            <?php if ($hero_dek): ?>
                <p class="nfedit-about-hero__dek"><?php echo esc_html($hero_dek); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <?php if (is_array($story) && !empty($story)): ?>
        <section class="nfedit-about-story">
            <div class="container-edit nfedit-about-story__inner editorial-prose">
                <?php foreach ($story as $row):
                    $p = isset($row['paragraph']) ? $row['paragraph'] : '';
                    if (!$p) continue;
                    echo wp_kses_post($p);
                endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (is_array($principles) && !empty($principles)): ?>
        <section class="nfedit-about-principles">
            <div class="container-edit">
                <p class="eyebrow nfedit-about-principles__eyebrow">What we believe</p>
                <h2 class="nfedit-about-principles__heading">Three principles, in plain English.</h2>
                <div class="nfedit-about-principles__grid">
                    <?php foreach ($principles as $i => $p):
                        $label = isset($p['label']) ? $p['label'] : '';
                        $body  = isset($p['body']) ? $p['body'] : '';
                        if (!$label) continue;
                    ?>
                        <div class="nfedit-about-principles__item">
                            <p class="nfedit-about-principles__num"><?php printf('%02d', $i + 1); ?></p>
                            <h3 class="nfedit-about-principles__name"><?php echo esc_html($label); ?></h3>
                            <p class="nfedit-about-principles__body"><?php echo esc_html($body); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (is_array($team) && !empty($team)): ?>
        <section class="nfedit-about-team">
            <div class="container-edit">
                <p class="eyebrow nfedit-about-team__eyebrow">The team</p>
                <h2 class="nfedit-about-team__heading">Three of us, mostly.</h2>
                <div class="nfedit-about-team__grid">
                    <?php foreach ($team as $member):
                        $name = isset($member['name']) ? $member['name'] : '';
                        $role = isset($member['role']) ? $member['role'] : '';
                        $bio  = isset($member['bio']) ? $member['bio'] : '';
                        $portrait_id = isset($member['portrait']) ? $member['portrait'] : 0;
                        if (is_array($portrait_id) && isset($portrait_id['ID'])) $portrait_id = $portrait_id['ID'];
                        $portrait_url = $portrait_id ? wp_get_attachment_image_url((int) $portrait_id, 'nfedit_team_portrait') : '';
                        if (!$name) continue;
                    ?>
                        <div class="nfedit-about-team__member">
                            <div class="nfedit-about-team__portrait-wrap">
                                <?php if ($portrait_url): ?>
                                    <img src="<?php echo esc_url($portrait_url); ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy" class="nfedit-about-team__portrait img-muted" />
                                <?php else: ?>
                                    <div class="nfedit-about-team__portrait-placeholder" aria-hidden="true"><?php echo esc_html(mb_substr($name, 0, 1)); ?></div>
                                <?php endif; ?>
                            </div>
                            <p class="eyebrow nfedit-about-team__role"><?php echo esc_html($role); ?></p>
                            <h3 class="nfedit-about-team__name"><?php echo esc_html($name); ?></h3>
                            <p class="nfedit-about-team__bio"><?php echo esc_html($bio); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (is_array($faqs) && !empty($faqs)): ?>
        <section class="nfedit-about-faqs">
            <div class="container-edit nfedit-about-faqs__inner">
                <p class="eyebrow nfedit-about-faqs__eyebrow">FAQs</p>
                <h2 class="nfedit-about-faqs__heading">Things people ask.</h2>
                <dl class="nfedit-about-faqs__list">
                    <?php foreach ($faqs as $faq):
                        $q = isset($faq['q']) ? $faq['q'] : '';
                        $a = isset($faq['a']) ? $faq['a'] : '';
                        if (!$q) continue;
                    ?>
                        <div class="nfedit-about-faqs__item">
                            <dt class="nfedit-about-faqs__q"><?php echo esc_html($q); ?></dt>
                            <dd class="nfedit-about-faqs__a"><?php echo wp_kses_post($a); ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </div>
        </section>
    <?php endif; ?>

    <?php get_template_part('template-parts/global/newsletter-compact'); ?>

</article>

<?php get_footer();
