<?php
/**
 * Single surround — eat / drink / walk / see / shop / swim
 *
 * Section order:
 *  1. Hero (image + title + dek + kind eyebrow + area)
 *  2. Body (post_content — the editorial)
 *  3. Practical card (address, hours, dog policy, price, walking distance, what to order)
 *  4. Map (lat/lng → embed)
 *  5. Cottages rail (reverse lookup — properties that link this surround)
 *  6. Related rail (other surrounds same kind / same area)
 */
defined('ABSPATH') || exit;

get_header();

while (have_posts()):
    the_post();
    $post_id = get_the_ID();
?>
<article id="surround-<?php echo esc_attr($post_id); ?>" class="nfedit-surround nfedit-surround--single">

    <?php get_template_part('template-parts/surround/hero', null, ['post_id' => $post_id]); ?>

    <div class="nfedit-surround__layout container-edit">
        <div class="nfedit-surround__main">
            <?php get_template_part('template-parts/surround/body', null, ['post_id' => $post_id]); ?>
            <?php get_template_part('template-parts/surround/map', null, ['post_id' => $post_id]); ?>
        </div>
        <aside class="nfedit-surround__aside">
            <?php get_template_part('template-parts/surround/practical-card', null, ['post_id' => $post_id]); ?>
        </aside>
    </div>

    <?php get_template_part('template-parts/surround/cottages-rail', null, ['post_id' => $post_id]); ?>
    <?php get_template_part('template-parts/surround/related-rail', null, ['post_id' => $post_id]); ?>

</article>
<?php
endwhile;

get_footer();
