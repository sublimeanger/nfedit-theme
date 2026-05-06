<?php
defined('ABSPATH') || exit;
$post_id       = $args['post_id'];
$category      = $args['category'];
$category_link = $args['category_link'];
?>
<nav class="nfedit-guide-breadcrumbs container-edit" aria-label="Breadcrumb">
    <ol>
        <li><a href="<?php echo esc_url(get_post_type_archive_link('guide')); ?>">Guides</a></li>
        <?php if ($category): ?>
            <li class="nfedit-guide-breadcrumbs__sep">/</li>
            <li><a href="<?php echo esc_url($category_link); ?>"><?php echo esc_html($category); ?></a></li>
        <?php endif; ?>
        <li class="nfedit-guide-breadcrumbs__sep">/</li>
        <li class="nfedit-guide-breadcrumbs__current"><?php echo esc_html(get_the_title($post_id)); ?></li>
    </ol>
</nav>
