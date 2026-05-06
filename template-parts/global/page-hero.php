<?php
/**
 * PageHero — accepts both Phase 2 and Phase 6 arg names.
 * Phase 2: image (attachment ID or URL), heading, height as '40'/'50'/'60'
 * Phase 6: image_url (URL only), title, height as '40vh'/'50vh' etc.
 *
 * @param array $args {
 *   'eyebrow'   => string,
 *   'title'     => string  (preferred — Phase 6)
 *   'heading'   => string  (Phase 2 alias)
 *   'dek'       => string,
 *   'image'     => attachment ID OR URL  (Phase 2)
 *   'image_url' => string URL            (Phase 6)
 *   'height'    => '40vh'|'50vh'|'60vh'  OR  '40'|'50'|'60'
 * }
 */
defined('ABSPATH') || exit;

$args = wp_parse_args(isset($args) ? $args : [], [
    'eyebrow'   => '',
    'title'     => '',
    'heading'   => '',
    'dek'       => '',
    'image'     => '',
    'image_url' => '',
    'height'    => '50vh',
]);

$title = $args['title'] !== '' ? $args['title'] : $args['heading'];

$bg = $args['image_url'];
if (!$bg && $args['image']) {
    $bg = is_numeric($args['image'])
        ? wp_get_attachment_image_url((int) $args['image'], 'nfedit_hero_xl')
        : $args['image'];
}

$h = (string) $args['height'];
if (preg_match('/^\d+$/', $h)) $h .= 'vh';

$style = 'height: ' . esc_attr($h) . ';';
if ($bg) $style .= ' background-image: url(\'' . esc_url($bg) . '\');';
?>
<section class="nfedit-page-hero" style="<?php echo $style; ?>">
    <div class="nfedit-page-hero__overlay"></div>
    <div class="container-edit nfedit-page-hero__inner">
        <?php if ($args['eyebrow']): ?>
            <p class="eyebrow nfedit-page-hero__eyebrow"><?php echo esc_html($args['eyebrow']); ?></p>
        <?php endif; ?>
        <?php if ($title): ?>
            <h1 class="nfedit-page-hero__heading"><?php echo wp_kses_post($title); ?></h1>
        <?php endif; ?>
        <?php if ($args['dek']): ?>
            <p class="nfedit-page-hero__dek"><?php echo esc_html($args['dek']); ?></p>
        <?php endif; ?>
    </div>
</section>
