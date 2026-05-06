<?php
/**
 * Phase 12a fixture cleanup — soft-deletes 12 fixture cottages and
 * clears all their FK references. Writes a Markdown report.
 *
 * Run once: wp nfedit cleanup-fixtures
 * Idempotent.
 */
defined('ABSPATH') || exit;

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('nfedit cleanup-fixtures', 'nfedit_cli_cleanup_fixtures');
}

function nfedit_cli_cleanup_fixtures($args, $assoc_args) {
    $report = ["# Fixture cottage cleanup\n", "Run: " . current_time('mysql') . "\n\n"];

    $fixture_slugs = [
        'the-old-stables', 'salt-cottage', 'the-piggery', 'wren-house',
        'two-bed-near-burley', 'heathside', 'moss-end', 'the-shepherd',
        'anchorage', 'high-coppice', 'lark-rise', 'spit-cottage',
    ];

    $fixture_ids = [];
    foreach ($fixture_slugs as $slug) {
        $p = get_page_by_path($slug, OBJECT, 'property');
        if ($p && $p->post_status !== 'trash') {
            $fixture_ids[$slug] = $p->ID;
        }
    }
    if (empty($fixture_ids)) {
        WP_CLI::success('No fixture cottages found (already cleaned).');
        return;
    }

    $report[] = "## Fixture cottages to soft-delete\n\n";
    foreach ($fixture_ids as $slug => $id) {
        $report[] = "- `$slug` -> ID $id\n";
    }
    $report[] = "\n";

    // 1. Clean ACF Relationship fields on related content types
    $relationship_fields_to_clean = [
        ['guide',     'related_cottages'],
        ['post',      'related_cottages'],     // editorial = native post
        ['post',      'embedded_cottages'],
        ['area',      'featured_cottages'],
    ];

    $report[] = "## Posts modified — relationship fields cleared\n\n";
    $modifications = 0;

    foreach ($relationship_fields_to_clean as $pair) {
        list($post_type, $meta_key) = $pair;
        $posts = get_posts([
            'post_type'      => $post_type,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);
        foreach ($posts as $post_id) {
            $current = get_post_meta($post_id, $meta_key, true);
            if (!is_array($current) && !is_string($current)) continue;

            $array = is_array($current) ? $current : maybe_unserialize($current);
            if (!is_array($array)) continue;

            $cleaned = array_values(array_filter($array, function ($id) use ($fixture_ids) {
                return !in_array((int) $id, $fixture_ids, true);
            }));

            if (count($cleaned) !== count($array)) {
                $removed = count($array) - count($cleaned);
                update_post_meta($post_id, $meta_key, $cleaned);
                $title = get_the_title($post_id);
                $report[] = "- **{$post_type} #{$post_id}** ({$title}) — removed {$removed} fixture cottages from `{$meta_key}`\n";
                $modifications++;
            }
        }
    }

    if ($modifications === 0) {
        $report[] = "(none)\n";
    }
    $report[] = "\n";

    // 2. ACF Options arrays
    $options_to_clean = [
        'options_editor_picks',
        'options_featured_collections',
        'options_featured_editorials',
        'options_featured_guides',
    ];

    $report[] = "## Options arrays cleaned\n\n";
    $options_modified = 0;

    foreach ($options_to_clean as $opt_name) {
        $current = get_option($opt_name);
        if (!$current) continue;
        $array = maybe_unserialize($current);
        if (!is_array($array)) continue;

        $cleaned = array_values(array_filter($array, function ($id) use ($fixture_ids) {
            return !in_array((int) $id, $fixture_ids, true);
        }));

        if (count($cleaned) !== count($array)) {
            $removed = count($array) - count($cleaned);
            update_option($opt_name, $cleaned);
            $report[] = "- `{$opt_name}` — removed {$removed} fixture references\n";
            $options_modified++;
        }
    }

    if ($options_modified === 0) {
        $report[] = "(none — Options arrays did not reference fixtures, or were already clean)\n";
    }
    $report[] = "\n";

    // 3. Detect references in text fields (DO NOT auto-edit, just flag)
    global $wpdb;
    $id_alternation = implode('|', $fixture_ids);
    $text_meta_keys = ['body', 'practical', 'whats_it_like'];

    $report[] = "## Text fields containing fixture IDs (Lauren to manually rewrite)\n\n";
    $text_flags = 0;
    foreach ($text_meta_keys as $meta_key) {
        $rows = $wpdb->get_results($wpdb->prepare("
            SELECT post_id, meta_value
            FROM {$wpdb->postmeta}
            WHERE meta_key = %s
            AND meta_value REGEXP %s
        ", $meta_key, "(^|[^0-9])($id_alternation)([^0-9]|\$)"));

        foreach ($rows as $row) {
            $title = get_the_title($row->post_id);
            $excerpt = preg_replace('/\s+/', ' ', mb_substr(strip_tags((string) $row->meta_value), 0, 150));
            $report[] = "- **post #{$row->post_id}** ({$title}), field `{$meta_key}`: \"...{$excerpt}...\"\n";
            $text_flags++;
        }
    }
    if ($text_flags === 0) {
        $report[] = "(none)\n";
    }
    $report[] = "\n";

    // 4. Soft-delete the fixtures
    $report[] = "## Soft-deletes\n\n";
    foreach ($fixture_ids as $slug => $id) {
        $result = wp_trash_post($id);
        if ($result) {
            $report[] = "- [OK] `{$slug}` (#{$id}) -> trash\n";
        } else {
            $report[] = "- [FAIL] `{$slug}` (#{$id}) — trash FAILED\n";
        }
    }
    $report[] = "\n";

    // 5. Lauren checklist
    $report[] = "## Action items for Lauren\n\n";
    $report[] = "Once Phase 12b imports real cottages:\n\n";
    $report[] = "1. Re-curate **homepage editor_picks** — pick 6-8 real T1 cottages\n";
    $report[] = "2. For each guide modified above, re-curate **related_cottages**\n";
    $report[] = "3. For each editorial modified above, re-curate **related_cottages** and **embedded_cottages**\n";
    $report[] = "4. For each area modified above, re-curate **featured_cottages**\n";
    if ($text_flags > 0) {
        $report[] = "5. **Manually edit** the text fields flagged above — these contain fixture cottage IDs in body copy that I couldn't safely auto-rewrite\n";
    }
    $report[] = "\n";
    $report[] = "Cleaned **{$modifications}** relationship-field references, **{$options_modified}** Options arrays, **{$text_flags}** text-field flags. Deleted **" . count($fixture_ids) . "** fixture cottages (recoverable from trash for 30 days).\n";

    $upload_dir  = wp_upload_dir();
    $report_path = $upload_dir['basedir'] . '/nfedit-fixture-cleanup-' . current_time('Ymd-His') . '.md';
    file_put_contents($report_path, implode('', $report));

    WP_CLI::success(sprintf(
        'Cleaned %d FK refs, %d Options, %d text flags. Deleted %d cottages. Report: %s',
        $modifications, $options_modified, $text_flags, count($fixture_ids), $report_path
    ));
}
