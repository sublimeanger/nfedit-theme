<?php
/**
 * Taxonomies.
 */

defined('ABSPATH') || exit;

add_action('init', function () {

    register_taxonomy('area_taxonomy', ['property', 'surround'], [
        'labels' => [
            'name'          => 'Areas',
            'singular_name' => 'Area',
            'menu_name'     => 'Areas',
        ],
        'hierarchical'      => true,
        'public'            => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'cottages/area', 'with_front' => false],
    ]);

    register_taxonomy('forest_or_coast', ['property', 'area'], [
        'labels'            => ['name' => 'Forest or Coast', 'singular_name' => 'Forest/Coast'],
        'hierarchical'      => false,
        'public'            => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => false,
    ]);

    register_taxonomy('cluster', ['post'], [
        'labels'            => ['name' => 'Clusters', 'singular_name' => 'Cluster'],
        'hierarchical'      => false,
        'public'            => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'the-edit', 'with_front' => false],
    ]);

    register_taxonomy('feature', ['property'], [
        'labels'            => ['name' => 'Features', 'singular_name' => 'Feature'],
        'hierarchical'      => false,
        'public'            => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'cottages/by-feature', 'with_front' => false],
    ]);

    register_taxonomy('guide_category', ['guide'], [
        'labels'            => ['name' => 'Guide Categories', 'singular_name' => 'Guide Category'],
        'hierarchical'      => false,
        'public'            => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'guides/category', 'with_front' => false],
    ]);

    register_taxonomy('season', ['post', 'property'], [
        'labels'            => ['name' => 'Seasons', 'singular_name' => 'Season'],
        'hierarchical'      => false,
        'public'            => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => false,
    ]);

    register_taxonomy('surround_kind', ['surround'], [
        'labels'            => ['name' => 'Surround Kinds', 'singular_name' => 'Kind'],
        'hierarchical'      => false,
        'public'            => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => false,
    ]);
});
