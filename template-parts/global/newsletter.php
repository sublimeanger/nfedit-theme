<?php
/**
 * Newsletter signup.
 * @param array $args { 'compact' => bool }
 */
defined('ABSPATH') || exit;

$compact = !empty($args['compact']);
$class   = 'nfedit-newsletter' . ($compact ? ' nfedit-newsletter--compact' : '');
$heading = $compact ? 'Get The Edit in your inbox.' : 'The newsletter we&rsquo;d actually want to read.';
?>
<section class="<?php echo esc_attr($class); ?>">
    <div class="container-edit nfedit-newsletter__inner">
        <p class="eyebrow nfedit-newsletter__eyebrow">Join us</p>
        <h2 class="nfedit-newsletter__heading"><?php echo $heading; ?></h2>
        <?php if (!$compact): ?>
            <p class="nfedit-newsletter__body">Twice a month: the cottages we&rsquo;ve added, the walks we&rsquo;ve discovered, and the pubs we&rsquo;ve made detours for. No tracking, no upsells.</p>
        <?php endif; ?>
        <form class="nfedit-newsletter__form" data-nfedit-newsletter>
            <label class="screen-reader-text" for="nfedit-newsletter-email">Email address</label>
            <input
                id="nfedit-newsletter-email"
                type="email"
                name="email"
                placeholder="you@elsewhere.com"
                required
                class="nfedit-newsletter__input"
                autocomplete="email"
            >
            <button type="submit" class="btn-primary">Subscribe</button>
        </form>
        <p class="nfedit-newsletter__privacy">We won&rsquo;t share your email. Unsubscribe in one click.</p>
    </div>
</section>
