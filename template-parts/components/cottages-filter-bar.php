<?php
/**
 * Filter pill bar + sort dropdown.
 *
 * @var array $args { 'pills' => array, 'active_pills' => array, 'sorts' => array, 'current_sort' => string, 'scope_feature' => array|null }
 */
defined('ABSPATH') || exit;

$pills         = $args['pills'];
$active_pills  = $args['active_pills'];
$sorts         = $args['sorts'];
$current_sort  = $args['current_sort'];
$scope_feature = isset($args['scope_feature']) ? $args['scope_feature'] : null;
?>
<div class="nfedit-cottages-filter">
    <div class="nfedit-cottages-filter__pills">
        <?php if ($scope_feature): ?>
            <span class="nfedit-cottages-filter__pill nfedit-cottages-filter__pill--locked">
                <?php echo esc_html($scope_feature['label']); ?>
                <span class="nfedit-cottages-filter__pill-locked-mark">(locked)</span>
            </span>
        <?php endif; ?>

        <?php foreach ($pills as $p):
            // Skip the pill if it's the scope-locked feature
            if ($scope_feature && $p['param'] === 'feature' && isset($scope_feature['slug']) && $p['value'] === $scope_feature['slug']) {
                continue;
            }

            $is_active = in_array($p['id'], $active_pills, true);

            // Build href — toggle this pill's value via URL query string
            $current_get = nfedit_property_filter_params_from_get($_GET);
            $param = $p['param'];
            $value = $p['value'];

            if ($param === 'feature') {
                $features = isset($current_get['feature']) ? (array) $current_get['feature'] : [];
                if (!empty($features) && is_string($features[0])) {
                    $features = array_filter(array_map('trim', explode(',', $features[0])));
                }
                if ($is_active) {
                    $features = array_filter($features, function ($f) use ($value) { return $f !== $value; });
                } else {
                    $features[] = $value;
                }
                $current_get['feature'] = !empty($features) ? implode(',', $features) : null;
            } else {
                if ($is_active) {
                    unset($current_get[$param]);
                } else {
                    $current_get[$param] = $value;
                }
            }
            unset($current_get['paged']); // reset pagination on filter change

            // Build href: strip current params, then add the new combination
            $href = remove_query_arg(['paged', 'area', 'feature', 'forest_coast', 'dogs', 'min_sleeps', 'sort']);
            foreach ($current_get as $k => $v) {
                if ($v !== null && $v !== '' && $v !== []) {
                    $href = add_query_arg($k, $v, $href);
                }
            }
        ?>
            <a
                href="<?php echo esc_url($href); ?>"
                class="nfedit-cottages-filter__pill<?php echo $is_active ? ' is-active' : ''; ?>"
                data-pill-id="<?php echo esc_attr($p['id']); ?>"
                data-pill-param="<?php echo esc_attr($p['param']); ?>"
                data-pill-value="<?php echo esc_attr($p['value']); ?>"
            >
                <?php echo esc_html($p['label']); ?>
            </a>
        <?php endforeach; ?>
    </div>

    <label class="nfedit-cottages-filter__sort">
        <span class="nfedit-cottages-filter__sort-label">Sort:</span>
        <select data-cottages-sort name="sort">
            <?php foreach ($sorts as $value => $label): ?>
                <option value="<?php echo esc_attr($value); ?>"<?php echo $current_sort === $value ? ' selected' : ''; ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php echo nfedit_lucide_svg('chevron-down', ['width' => 16, 'height' => 16, 'class' => 'nfedit-cottages-filter__sort-chevron']); ?>
    </label>
</div>
