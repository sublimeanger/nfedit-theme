<?php
/**
 * 5.9 — Newsletter signup.
 */
defined('ABSPATH') || exit;
?>
<section id="newsletter" class="nfedit-home-newsletter">
    <div class="container-edit nfedit-home-newsletter__inner">
        <p class="eyebrow nfedit-home-newsletter__eyebrow">Join us</p>
        <h2 class="nfedit-home-newsletter__heading">The newsletter we&rsquo;d actually want to read.</h2>
        <p class="nfedit-home-newsletter__body">
            Twice a month: the cottages we&rsquo;ve added, the walks we&rsquo;ve discovered, and the pubs we&rsquo;ve made detours for. No tracking, no upsells.
        </p>
        <form class="nfedit-home-newsletter__form" data-nfedit-newsletter onsubmit="return false;">
            <label class="screen-reader-text" for="nfedit-home-newsletter-email">Email address</label>
            <input id="nfedit-home-newsletter-email" type="email" name="email" required placeholder="you@elsewhere.com" class="nfedit-home-newsletter__input" autocomplete="email" />
            <button type="submit" class="btn-primary nfedit-home-newsletter__submit">Subscribe</button>
        </form>
        <p class="nfedit-home-newsletter__note">We won&rsquo;t share your email. Unsubscribe in one click.</p>
    </div>
</section>
