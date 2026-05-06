<?php
defined('ABSPATH') || exit;

$post_id      = (int) $args['post_id'];
$cluster_name = isset($args['cluster_name']) ? (string) $args['cluster_name'] : '';
$cluster_link = isset($args['cluster_link']) ? (string) $args['cluster_link'] : '';
?>
<nav class="nfedit-editorial-breadcrumbs container-edit" aria-label="Breadcrumb">
    <ol>
        <li><a href="<?php echo esc_url(home_url('/the-edit/')); ?>">The Edit</a></li>
        <?php if ($cluster_name): ?>
            <li class="nfedit-editorial-breadcrumbs__sep">/</li>
            <li><a href="<?php echo esc_url($cluster_link); ?>"><?php echo esc_html($cluster_name); ?></a></li>
        <?php endif; ?>
        <li class="nfedit-editorial-breadcrumbs__sep">/</li>
        <li class="nfedit-editorial-breadcrumbs__current"><?php echo esc_html(get_the_title($post_id)); ?></li>
    </ol>
</nav>
