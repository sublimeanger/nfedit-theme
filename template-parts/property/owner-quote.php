<?php
/**
 * 6.10 — Owner quote (forest band, T1).
 * @var array $args { 'post_id' => int }
 */
defined('ABSPATH') || exit;

$post_id = (int) $args['post_id'];
$text    = (string) get_field('owner_quote_text', $post_id);
if (!$text) return;
$name    = (string) get_field('owner_quote_name', $post_id);
$role    = (string) get_field('owner_quote_role', $post_id);
$portrait = get_field('owner_portrait', $post_id);
$portrait_id = is_array($portrait) ? (isset($portrait['ID']) ? (int) $portrait['ID'] : 0) : (int) $portrait;
$portrait_url = $portrait_id ? wp_get_attachment_image_url($portrait_id, 'nfedit_portrait') : '';
?>
<section class="nfedit-property-owner-quote">
    <div class="container-edit nfedit-property-owner-quote__inner">
        <?php if ($portrait_url): ?>
            <img src="<?php echo esc_url($portrait_url); ?>" alt="<?php echo esc_attr($name); ?>" class="nfedit-property-owner-quote__portrait" />
        <?php endif; ?>
        <blockquote class="nfedit-property-owner-quote__text">&ldquo;<?php echo esc_html($text); ?>&rdquo;</blockquote>
        <?php if ($name || $role): ?>
            <p class="eyebrow nfedit-property-owner-quote__attribution">
                &mdash; <?php echo esc_html($name); ?><?php if ($role): ?>, <?php echo esc_html($role); ?><?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
</section>
