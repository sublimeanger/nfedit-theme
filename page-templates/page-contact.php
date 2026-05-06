<?php
/**
 * Template Name: Contact
 */
defined('ABSPATH') || exit;
get_header();

$ctx = 'site-settings-contact';
$hero_h1  = (string) get_field('hero_h1',  $ctx);
$hero_dek = (string) get_field('hero_dek', $ctx);
$cards    = get_field('path_picker_items', $ctx);
$subjects = get_field('form_subjects',     $ctx);

$pre_subject = isset($_GET['subject']) ? sanitize_text_field($_GET['subject']) : '';
?>

<article class="nfedit-contact">

    <section class="nfedit-contact-hero">
        <div class="container-edit nfedit-contact-hero__inner">
            <p class="eyebrow nfedit-contact-hero__eyebrow">Contact</p>
            <?php if ($hero_h1): ?>
                <h1 class="nfedit-contact-hero__title"><?php echo esc_html($hero_h1); ?></h1>
            <?php endif; ?>
            <?php if ($hero_dek): ?>
                <p class="nfedit-contact-hero__dek"><?php echo esc_html($hero_dek); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <?php if (is_array($cards) && !empty($cards)): ?>
        <section class="nfedit-contact-paths">
            <div class="container-edit">
                <div class="nfedit-contact-paths__grid">
                    <?php foreach ($cards as $card):
                        $heading = isset($card['heading']) ? $card['heading'] : '';
                        $body    = isset($card['body']) ? $card['body'] : '';
                        $cta_l   = isset($card['cta_label']) ? $card['cta_label'] : '';
                        $cta_u   = isset($card['cta_url']) ? $card['cta_url'] : '';
                        if (!$heading) continue;
                        $cta_href = $cta_u ? ((strpos($cta_u, 'http') === 0) ? $cta_u : home_url($cta_u)) : '';
                    ?>
                        <div class="nfedit-contact-path">
                            <h3 class="nfedit-contact-path__heading"><?php echo esc_html($heading); ?></h3>
                            <?php if ($body): ?>
                                <p class="nfedit-contact-path__body"><?php echo esc_html($body); ?></p>
                            <?php endif; ?>
                            <?php if ($cta_l && $cta_href): ?>
                                <a href="<?php echo esc_url($cta_href); ?>" class="nfedit-contact-path__cta">
                                    <?php echo esc_html($cta_l); ?> <span aria-hidden="true">&rarr;</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="nfedit-contact-form-section">
        <div class="container-edit nfedit-contact-form-section__inner">
            <p class="eyebrow nfedit-contact-form-section__eyebrow">Or send a note</p>
            <h2 class="nfedit-contact-form-section__heading">Tell us what's on your mind.</h2>

            <form class="nfedit-contact-form" data-nfedit-contact-form>
                <input type="hidden" name="_nonce" value="<?php echo esc_attr(wp_create_nonce('nfedit_contact')); ?>">

                <div class="nfedit-contact-form__row">
                    <label for="nfedit-contact-name" class="nfedit-contact-form__label">Your name</label>
                    <input id="nfedit-contact-name" type="text" name="name" required class="nfedit-contact-form__input" autocomplete="name" />
                </div>

                <div class="nfedit-contact-form__row">
                    <label for="nfedit-contact-email" class="nfedit-contact-form__label">Email</label>
                    <input id="nfedit-contact-email" type="email" name="email" required class="nfedit-contact-form__input" autocomplete="email" />
                </div>

                <div class="nfedit-contact-form__row">
                    <label for="nfedit-contact-subject" class="nfedit-contact-form__label">What's this about?</label>
                    <select id="nfedit-contact-subject" name="subject" required class="nfedit-contact-form__input">
                        <?php if (is_array($subjects) && !empty($subjects)):
                            foreach ($subjects as $s):
                                $val = isset($s['subject']) ? $s['subject'] : '';
                                if (!$val) continue;
                                $is_pre = ($pre_subject && stripos($val, $pre_subject) !== false);
                        ?>
                            <option value="<?php echo esc_attr($val); ?>" <?php echo $is_pre ? 'selected' : ''; ?>>
                                <?php echo esc_html($val); ?>
                            </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>

                <div class="nfedit-contact-form__row">
                    <label for="nfedit-contact-message" class="nfedit-contact-form__label">Message</label>
                    <textarea id="nfedit-contact-message" name="message" rows="6" required class="nfedit-contact-form__input nfedit-contact-form__textarea"></textarea>
                </div>

                <div class="nfedit-contact-form__row nfedit-contact-form__honeypot" aria-hidden="true">
                    <label>Leave this empty
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </label>
                </div>

                <button type="submit" class="btn-primary nfedit-contact-form__submit">Send</button>
                <p class="nfedit-contact-form__status" data-status hidden></p>
            </form>

            <div class="nfedit-contact-form__success" data-success hidden>
                <p class="eyebrow nfedit-contact-form__success-eyebrow">Got it</p>
                <h3 class="nfedit-contact-form__success-heading">Thanks &mdash; we read everything.</h3>
                <p class="nfedit-contact-form__success-body">We'll reply within two days, usually sooner. <a href="<?php echo esc_url(home_url('/')); ?>">Back to the homepage</a>.</p>
            </div>
        </div>
    </section>

    <?php get_template_part('template-parts/global/newsletter-compact'); ?>

</article>

<?php get_footer();
