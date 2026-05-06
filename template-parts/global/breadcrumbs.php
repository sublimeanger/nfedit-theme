<?php
/**
 * Breadcrumbs.
 * @param array $args { 'crumbs' => [['label' => string, 'url' => string|null]] }
 */
defined('ABSPATH') || exit;

$crumbs = isset($args['crumbs']) ? $args['crumbs'] : [];
if (empty($crumbs)) {
    return;
}
$last_index = count($crumbs) - 1;
?>
<nav class="nfedit-breadcrumbs" aria-label="Breadcrumb">
    <div class="container-edit nfedit-breadcrumbs__inner">
        <ol>
            <?php foreach ($crumbs as $i => $crumb): ?>
                <li>
                    <?php if (!empty($crumb['url']) && $i < $last_index): ?>
                        <a href="<?php echo esc_url($crumb['url']); ?>"><?php echo esc_html($crumb['label']); ?></a>
                    <?php else: ?>
                        <span aria-current="page"><?php echo esc_html($crumb['label']); ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>
</nav>
