<?php
/**
 * Template Name: Legal
 *
 * Single template handling /privacy/, /terms/, /cookies/, /affiliate/.
 * Reads its own slug from the page post, matches against legal_pages
 * repeater on site-settings-legal Options page.
 *
 * Auto-TOC generated server-side from <h2> elements in the wysiwyg body.
 */
defined('ABSPATH') || exit;
get_header();

$post_id    = get_the_ID();
$slug       = get_post_field('post_name', $post_id);
$page_title = get_the_title($post_id);

$legal_pages = get_field('legal_pages', 'site-settings-legal');

$current_row = null;
if (is_array($legal_pages)) {
    foreach ($legal_pages as $row) {
        if (isset($row['slug']) && $row['slug'] === $slug) {
            $current_row = $row;
            break;
        }
    }
}

if (!$current_row) {
    $current_row = [
        'slug'         => $slug,
        'title'        => $page_title,
        'last_updated' => '',
        'body'         => '<p>This page is being prepared. Please check back soon.</p>',
    ];
}

$title    = isset($current_row['title']) ? $current_row['title'] : $page_title;
$updated  = isset($current_row['last_updated']) ? $current_row['last_updated'] : '';
$body_raw = isset($current_row['body']) ? $current_row['body'] : '';

list($body_html, $toc_items) = nfedit_legal_build_toc_and_body($body_raw);

$updated_display = '';
if ($updated) {
    $ts = strtotime($updated);
    if ($ts) $updated_display = date_i18n('F Y', $ts);
}
?>

<article class="nfedit-legal" data-legal-slug="<?php echo esc_attr($slug); ?>">

    <section class="container-edit nfedit-legal-masthead">
        <nav class="nfedit-legal-masthead__breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
                <li class="nfedit-legal-masthead__sep">/</li>
                <li class="nfedit-legal-masthead__current"><?php echo esc_html($title); ?></li>
            </ol>
        </nav>
        <?php if ($updated_display): ?>
            <p class="eyebrow nfedit-legal-masthead__updated">Last updated <?php echo esc_html($updated_display); ?></p>
        <?php endif; ?>
        <h1 class="nfedit-legal-masthead__title"><?php echo esc_html($title); ?></h1>
    </section>

    <section class="container-edit nfedit-legal-content">
        <div class="nfedit-legal-content__inner">

            <?php if (!empty($toc_items)): ?>
                <aside class="nfedit-legal-toc" aria-label="On this page">
                    <details class="nfedit-legal-toc__details" open>
                        <summary class="nfedit-legal-toc__summary">
                            <span class="eyebrow">On this page</span>
                            <span class="nfedit-legal-toc__chevron" aria-hidden="true">&#9662;</span>
                        </summary>
                        <nav class="nfedit-legal-toc__nav">
                            <ol class="nfedit-legal-toc__list" data-legal-toc>
                                <?php foreach ($toc_items as $item): ?>
                                    <li class="nfedit-legal-toc__item">
                                        <a href="#<?php echo esc_attr($item['id']); ?>"
                                           class="nfedit-legal-toc__link"
                                           data-toc-target="<?php echo esc_attr($item['id']); ?>">
                                            <?php echo esc_html($item['text']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </nav>
                    </details>
                </aside>
            <?php endif; ?>

            <div class="nfedit-legal-body editorial-prose">
                <?php echo $body_html; ?>
            </div>

        </div>
    </section>

    <?php get_template_part('template-parts/global/newsletter-compact'); ?>

</article>

<?php get_footer();
