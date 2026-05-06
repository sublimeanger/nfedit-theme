<?php
/**
 * Template Name: The Edit Index
 *
 * Magazine-style index for editorial posts. Sticky cluster filter strip.
 */
defined('ABSPATH') || exit;
get_header();

$issue_number = function_exists('get_field') ? (int) get_field('the_edit_issue_number', 'option') : 0;
if (!$issue_number) $issue_number = 1;
$issue_month = function_exists('get_field') ? (string) get_field('the_edit_issue_month', 'option') : '';
if (!$issue_month) $issue_month = date('F Y');

$all = new WP_Query([
    'post_type'      => 'post',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post_status'    => 'publish',
]);

$all_ids = [];
foreach ($all->posts as $p) { $all_ids[] = (int) $p->ID; }
$hero_id        = !empty($all_ids) ? $all_ids[0] : null;
$secondary_ids  = array_slice($all_ids, 1, 2);
$rest_ids       = array_slice($all_ids, 1);
wp_reset_postdata();

$cluster_terms = get_terms([
    'taxonomy'   => 'cluster',
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
]);
if (is_wp_error($cluster_terms)) $cluster_terms = [];

$guides = new WP_Query([
    'post_type'      => 'guide',
    'posts_per_page' => 3,
    'orderby'        => 'date',
    'order'          => 'DESC',
]);
?>

<div class="nfedit-the-edit">

    <!-- 1. Masthead -->
    <section class="nfedit-the-edit-masthead container-edit">
        <p class="eyebrow nfedit-the-edit-masthead__issue">The Edit &middot; Issue <?php echo (int) $issue_number; ?> &middot; <?php echo esc_html($issue_month); ?></p>
        <h1 class="nfedit-the-edit-masthead__title">
            Stories, walks and where we&rsquo;d send <span class="nfedit-the-edit-masthead__italic">our friends</span>.
        </h1>
        <p class="nfedit-the-edit-masthead__dek">
            Twice-monthly dispatches from inside the National Park. Pubs, walks, cottages we&rsquo;ve fallen for, and the seasonal stuff we don&rsquo;t want you to miss.
        </p>
    </section>

    <!-- 2. Above-the-fold cover spread -->
    <?php if ($hero_id): ?>
        <section class="nfedit-the-edit-cover container-edit">
            <div class="nfedit-the-edit-cover__grid">
                <div class="nfedit-the-edit-cover__hero">
                    <?php get_template_part('template-parts/components/editorial-card', null, [
                        'post_id' => $hero_id,
                        'aspect'  => 'landscape',
                        'large'   => true,
                    ]); ?>
                </div>
                <?php if (!empty($secondary_ids)): ?>
                    <div class="nfedit-the-edit-cover__secondary">
                        <?php foreach ($secondary_ids as $sid): ?>
                            <?php get_template_part('template-parts/components/editorial-card', null, [
                                'post_id' => $sid,
                                'aspect'  => 'landscape',
                                'large'   => false,
                            ]); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- 3. Cluster filter strip (sticky) -->
    <nav class="nfedit-the-edit-filters" data-the-edit-filters aria-label="Browse editorials by cluster">
        <div class="container-edit nfedit-the-edit-filters__inner scroll-rail">
            <span class="eyebrow nfedit-the-edit-filters__label">Browse by</span>
            <ul>
                <li>
                    <button class="nfedit-the-edit-filter is-active" data-cluster-filter="all" type="button">Everything</button>
                </li>
                <?php foreach ($cluster_terms as $term): ?>
                    <li>
                        <button class="nfedit-the-edit-filter" data-cluster-filter="<?php echo esc_attr($term->slug); ?>" type="button">
                            <?php echo esc_html($term->name); ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>

    <!-- 4. Filtered grid -->
    <section class="nfedit-the-edit-grid container-edit" data-the-edit-grid>
        <p class="eyebrow nfedit-the-edit-grid__count" data-grid-count hidden>
            <span data-count-num>0</span> stor<span data-count-suffix>ies</span>
            in <span data-count-label></span>
        </p>
        <?php if (!empty($rest_ids)): ?>
            <div class="nfedit-the-edit-grid__items">
                <?php foreach ($rest_ids as $rid):
                    $clusters_for = get_the_terms($rid, 'cluster');
                    $cluster_slug = ($clusters_for && !is_wp_error($clusters_for)) ? $clusters_for[0]->slug : '';
                ?>
                    <div class="nfedit-the-edit-grid__item" data-grid-item-cluster="<?php echo esc_attr($cluster_slug); ?>">
                        <?php get_template_part('template-parts/components/editorial-card', null, [
                            'post_id' => $rid,
                            'aspect'  => 'portrait',
                            'large'   => false,
                        ]); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="nfedit-the-edit-grid__empty">No editorials yet.</p>
        <?php endif; ?>
        <p class="nfedit-the-edit-grid__no-results" data-no-results hidden>
            Nothing in this cluster yet &mdash; check back soon.
        </p>
    </section>

    <!-- 5. Cluster mood entry points -->
    <?php if (!empty($cluster_terms)): ?>
        <section class="nfedit-the-edit-clusters">
            <div class="container-edit">
                <p class="eyebrow nfedit-the-edit-clusters__eyebrow">Or by mood</p>
                <h2 class="nfedit-the-edit-clusters__heading">Pick a thread and pull.</h2>
                <div class="nfedit-the-edit-clusters__grid">
                    <?php foreach (array_slice($cluster_terms, 0, 5) as $term):
                        $thumb = '';
                        $thumb_q = new WP_Query([
                            'post_type'      => 'post',
                            'posts_per_page' => 1,
                            'fields'         => 'ids',
                            'tax_query'      => [[
                                'taxonomy' => 'cluster',
                                'field'    => 'term_id',
                                'terms'    => $term->term_id,
                            ]],
                        ]);
                        if ($thumb_q->have_posts()) {
                            $tid = (int) $thumb_q->posts[0];
                            $img_id = function_exists('get_field') ? get_field('hero_image', $tid) : 0;
                            $img_id = is_array($img_id) ? (isset($img_id['ID']) ? (int) $img_id['ID'] : 0) : (int) $img_id;
                            if (!$img_id) $img_id = (int) get_post_thumbnail_id($tid);
                            if ($img_id) {
                                $thumb = wp_get_attachment_image_url($img_id, 'nfedit_card_1_1');
                            }
                        }
                        wp_reset_postdata();
                    ?>
                        <a href="<?php echo esc_url(home_url('/the-edit/in/' . $term->slug . '/')); ?>" class="nfedit-the-edit-cluster">
                            <div class="nfedit-the-edit-cluster__media">
                                <?php if ($thumb): ?>
                                    <img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($term->name); ?>" loading="lazy" class="img-muted" />
                                <?php endif; ?>
                            </div>
                            <h3 class="nfedit-the-edit-cluster__name"><?php echo esc_html($term->name); ?></h3>
                            <?php if (!empty($term->description)): ?>
                                <p class="nfedit-the-edit-cluster__dek"><?php echo esc_html($term->description); ?></p>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- 6. Editor's letter -->
    <section class="nfedit-the-edit-letter">
        <div class="container-edit content-edit nfedit-the-edit-letter__inner">
            <p class="eyebrow nfedit-the-edit-letter__eyebrow">The editor&rsquo;s letter</p>
            <p class="nfedit-the-edit-letter__quote">
                &ldquo;We started The Edit because the booking sites had stopped sounding like people. This is what we&rsquo;d actually tell you over a pint.&rdquo;
            </p>
            <p class="nfedit-the-edit-letter__byline">&mdash; Jamie &amp; Lauren</p>
        </div>
    </section>

    <!-- 7. Guides pair -->
    <?php if ($guides->have_posts()): ?>
        <section class="nfedit-the-edit-guides">
            <div class="container-edit">
                <div class="nfedit-the-edit-guides__head">
                    <div>
                        <p class="eyebrow nfedit-the-edit-guides__eyebrow">Practical, not romantic</p>
                        <h2 class="nfedit-the-edit-guides__heading">From the guides desk.</h2>
                    </div>
                    <a href="<?php echo esc_url(get_post_type_archive_link('guide')); ?>" class="btn-tertiary nfedit-the-edit-guides__link">
                        <span>All guides</span><span class="arrow">&rarr;</span>
                    </a>
                </div>
                <div class="nfedit-the-edit-guides__grid">
                    <?php while ($guides->have_posts()): $guides->the_post();
                        $gid = get_the_ID();
                        $cat_terms = get_the_terms($gid, 'guide_category');
                        $cat = ($cat_terms && !is_wp_error($cat_terms)) ? $cat_terms[0]->name : '';
                        $dek = function_exists('get_field') ? (string) get_field('dek', $gid) : '';
                        $mins = function_exists('get_field') ? (int) get_field('read_minutes', $gid) : 0;
                        $updated = function_exists('get_field') ? (string) get_field('last_updated', $gid) : '';
                    ?>
                        <a href="<?php echo esc_url(get_permalink($gid)); ?>" class="nfedit-the-edit-guide-card">
                            <?php if ($cat): ?>
                                <p class="eyebrow nfedit-the-edit-guide-card__cat"><?php echo esc_html($cat); ?></p>
                            <?php endif; ?>
                            <h3 class="nfedit-the-edit-guide-card__title"><?php echo esc_html(get_the_title($gid)); ?></h3>
                            <?php if ($dek): ?>
                                <p class="nfedit-the-edit-guide-card__dek"><?php echo esc_html($dek); ?></p>
                            <?php endif; ?>
                            <?php if ($updated || $mins > 0): ?>
                                <p class="nfedit-the-edit-guide-card__meta">
                                    <?php if ($updated): ?>Updated <?php echo esc_html($updated); ?><?php endif; ?>
                                    <?php if ($updated && $mins > 0): ?> &middot; <?php endif; ?>
                                    <?php if ($mins > 0): ?><?php echo (int) $mins; ?> min<?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </a>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php get_template_part('template-parts/global/oracle-promo-compact'); ?>
    <?php get_template_part('template-parts/global/newsletter-compact'); ?>
</div>

<?php get_footer();
