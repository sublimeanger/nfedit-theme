<?php
/**
 * Shared cottages grid + filter bar + sort dropdown + load-more.
 *
 * @var array $args {
 *   'scope_area'    => string|null  (e.g. 'brockenhurst' if locked by area)
 *   'scope_feature' => array|null   (e.g. ['slug'=>'hot-tub','label'=>'Hot tub'] if locked by feature)
 * }
 */
defined('ABSPATH') || exit;

$scope_area    = isset($args['scope_area']) ? $args['scope_area'] : null;
$scope_feature = isset($args['scope_feature']) ? $args['scope_feature'] : null;

// Build params: start from $_GET, then overlay scope locks
$params = nfedit_property_filter_params_from_get($_GET);
if ($scope_area) {
    $params['area'] = sanitize_title($scope_area);
}
if ($scope_feature && !empty($scope_feature['slug'])) {
    $existing = isset($params['feature']) ? $params['feature'] : '';
    if (is_string($existing)) {
        $existing = array_filter(explode(',', $existing));
    }
    $existing = is_array($existing) ? $existing : [];
    if (!in_array($scope_feature['slug'], $existing, true)) {
        $existing[] = $scope_feature['slug'];
    }
    $params['feature'] = $existing;
}

$query        = nfedit_property_filter_query($params);
$total        = (int) $query->found_posts;
$pills        = nfedit_property_filter_pills();
$sorts        = nfedit_property_sort_options();
$current_sort = isset($params['sort']) ? $params['sort'] : 'editor';
$current_paged = isset($params['paged']) ? (int) $params['paged'] : 1;
if ($current_paged < 1) $current_paged = 1;

// Determine active pills
$active_pills = [];
foreach ($pills as $pill) {
    $param = $pill['param'];
    $value = $pill['value'];
    if ($param === 'feature') {
        $features = isset($params['feature']) ? (array) $params['feature'] : [];
        if (!empty($features) && is_string($features[0])) {
            $features = array_filter(array_map('trim', explode(',', $features[0])));
        }
        if (in_array($value, $features, true)) $active_pills[] = $pill['id'];
    } elseif (isset($params[$param]) && (string) $params[$param] === (string) $value) {
        $active_pills[] = $pill['id'];
    }
}
?>
<div
    class="nfedit-cottages-grid"
    data-cottages-grid
    data-scope-area="<?php echo esc_attr($scope_area ? $scope_area : ''); ?>"
    data-scope-feature="<?php echo esc_attr(isset($scope_feature['slug']) ? $scope_feature['slug'] : ''); ?>"
>
    <div class="container-edit">
        <?php get_template_part('template-parts/components/cottages-filter-bar', null, [
            'pills'         => $pills,
            'active_pills'  => $active_pills,
            'sorts'         => $sorts,
            'current_sort'  => $current_sort,
            'scope_feature' => $scope_feature,
        ]); ?>

        <p class="nfedit-cottages-grid__count" data-cottages-count>
            <?php echo (int) $total; ?> cottage<?php echo $total === 1 ? '' : 's'; ?>
        </p>

        <div class="nfedit-cottages-grid__results" data-cottages-results>
            <?php if ($query->have_posts()): ?>
                <div class="nfedit-cottages-grid__items">
                    <?php while ($query->have_posts()): $query->the_post(); ?>
                        <?php get_template_part('template-parts/components/property-card', null, [
                            'post_id' => get_the_ID(),
                            'variant' => 'standard',
                        ]); ?>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="nfedit-cottages-grid__empty">
                    <p class="nfedit-cottages-grid__empty-title">Nothing matches that combination yet.</p>
                    <p class="nfedit-cottages-grid__empty-body">
                        Try fewer filters, or <a href="<?php echo esc_url(home_url('/oracle/')); ?>">ask the Oracle</a> in plain English.
                    </p>
                </div>
            <?php endif; wp_reset_postdata(); ?>
        </div>

        <?php if ($query->max_num_pages > $current_paged):
            $next = $current_paged + 1;
            $next_params = $params;
            $next_params['paged'] = $next;
            // For arrays, encode as csv for URL
            if (isset($next_params['feature']) && is_array($next_params['feature'])) {
                $next_params['feature'] = implode(',', $next_params['feature']);
            }
            $next_url = add_query_arg($next_params);
        ?>
            <div class="nfedit-cottages-grid__load-more">
                <a
                    href="<?php echo esc_url($next_url); ?>"
                    class="btn-secondary"
                    data-cottages-load-more
                    data-next-page="<?php echo (int) $next; ?>"
                    data-max-pages="<?php echo (int) $query->max_num_pages; ?>"
                >Load more</a>
            </div>
        <?php endif; ?>
    </div>
</div>
