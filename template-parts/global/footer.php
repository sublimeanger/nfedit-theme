<?php
/**
 * Global footer — Options-driven (Phase 10A).
 */
defined('ABSPATH') || exit;

$ctx        = 'site-settings-header-footer';
$columns    = get_field('footer_columns',   $ctx);
$tagline    = (string) get_field('footer_tagline',    $ctx);
$disclaimer = (string) get_field('footer_disclaimer', $ctx);
$copyright  = (string) get_field('footer_copyright',  $ctx);
$instagram  = (string) get_field('social_instagram',  $ctx);
$email      = (string) get_field('social_email',      $ctx);

$wordmark_id = get_field('wordmark_svg', 'site-settings-brand');
if (is_array($wordmark_id) && isset($wordmark_id['ID'])) $wordmark_id = $wordmark_id['ID'];
$wordmark_url = $wordmark_id ? wp_get_attachment_url((int) $wordmark_id) : '';
?>
<footer class="nfedit-footer">
    <div class="container-edit nfedit-footer__inner">

        <div class="nfedit-footer__top">
            <div class="nfedit-footer__brand">
                <?php if ($wordmark_url): ?>
                    <img src="<?php echo esc_url($wordmark_url); ?>" alt="The New Forest Edit" class="nfedit-footer__wordmark" />
                <?php else: ?>
                    <div class="nfedit-footer__wordmark-fallback">
                        <?php get_template_part('template-parts/global/wordmark', null, ['size' => 'lg']); ?>
                    </div>
                <?php endif; ?>
                <?php if ($tagline): ?>
                    <p class="nfedit-footer__tagline"><?php echo esc_html($tagline); ?></p>
                <?php endif; ?>
            </div>
            <?php if ($instagram || $email): ?>
                <div class="nfedit-footer__social">
                    <?php if ($instagram): ?>
                        <a href="<?php echo esc_url($instagram); ?>" rel="noopener noreferrer" target="_blank" aria-label="Instagram" class="nfedit-footer__social-link">
                            <?php echo nfedit_lucide_svg('instagram', ['width' => 20, 'height' => 20]); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($email): ?>
                        <a href="<?php echo esc_url($email); ?>" aria-label="Email us" class="nfedit-footer__social-link">
                            <?php echo nfedit_lucide_svg('mail', ['width' => 20, 'height' => 20]); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (is_array($columns) && !empty($columns)): ?>
            <div class="nfedit-footer__columns">
                <?php foreach ($columns as $col):
                    $heading = isset($col['heading']) ? $col['heading'] : '';
                    $links   = isset($col['links']) && is_array($col['links']) ? $col['links'] : [];
                ?>
                    <div class="nfedit-footer__col">
                        <?php if ($heading): ?>
                            <p class="nfedit-footer__col-heading eyebrow"><?php echo esc_html($heading); ?></p>
                        <?php endif; ?>
                        <ul class="nfedit-footer__col-links">
                            <?php foreach ($links as $link):
                                $l_label  = isset($link['label']) ? $link['label'] : '';
                                $l_url    = isset($link['url']) ? $link['url'] : '#';
                                $l_italic = !empty($link['italic']);
                                if (!$l_label) continue;
                                $l_href = (strpos($l_url, 'http') === 0) ? $l_url : home_url($l_url);
                            ?>
                                <li>
                                    <a href="<?php echo esc_url($l_href); ?>" class="nfedit-footer__link <?php echo $l_italic ? 'is-italic' : ''; ?>">
                                        <?php if ($l_italic && $l_label === 'The Edit'): ?>
                                            The <span class="edit-italic">Edit</span>
                                        <?php elseif ($l_italic): ?>
                                            <span class="edit-italic"><?php echo esc_html($l_label); ?></span>
                                        <?php else: ?>
                                            <?php echo esc_html($l_label); ?>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="nfedit-footer__bottom">
            <?php if ($disclaimer): ?>
                <p class="nfedit-footer__disclaimer"><?php echo esc_html($disclaimer); ?></p>
            <?php endif; ?>
            <?php if ($copyright): ?>
                <p class="nfedit-footer__copyright"><?php echo wp_kses_post($copyright); ?></p>
            <?php endif; ?>
        </div>

    </div>
</footer>
