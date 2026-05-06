<?php
/**
 * Custom Post Types: property, area, guide, collection, surround.
 */

defined('ABSPATH') || exit;

add_action('init', function () {

    // ---- PROPERTY ----
    register_post_type('property', [
        'labels' => [
            'name'               => 'Cottages',
            'singular_name'      => 'Cottage',
            'add_new'            => 'Add Cottage',
            'add_new_item'       => 'Add New Cottage',
            'edit_item'          => 'Edit Cottage',
            'new_item'           => 'New Cottage',
            'view_item'          => 'View Cottage',
            'search_items'       => 'Search Cottages',
            'not_found'          => 'No cottages found',
            'not_found_in_trash' => 'No cottages in trash',
            'menu_name'          => 'Cottages',
        ],
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => ['slug' => 'cottages', 'with_front' => false],
        'show_in_rest'  => true,
        'menu_icon'     => 'dashicons-admin-home',
        'menu_position' => 5,
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
        'taxonomies'    => ['area_taxonomy', 'feature', 'forest_or_coast', 'season'],
    ]);

    // ---- AREA ----
    register_post_type('area', [
        'labels' => [
            'name'          => 'Areas',
            'singular_name' => 'Area',
            'add_new_item'  => 'Add New Area',
            'edit_item'     => 'Edit Area',
            'menu_name'     => 'Areas',
        ],
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => ['slug' => 'areas', 'with_front' => false],
        'show_in_rest'  => true,
        'menu_icon'     => 'dashicons-location-alt',
        'menu_position' => 6,
        'supports'      => ['title', 'editor', 'thumbnail', 'revisions'],
    ]);

    // ---- GUIDE ----
    register_post_type('guide', [
        'labels' => [
            'name'          => 'Guides',
            'singular_name' => 'Guide',
            'menu_name'     => 'Guides',
        ],
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => ['slug' => 'guides', 'with_front' => false],
        'show_in_rest'  => true,
        'menu_icon'     => 'dashicons-book',
        'menu_position' => 7,
        'supports'      => ['title', 'thumbnail', 'excerpt', 'revisions'],
        'taxonomies'    => ['guide_category'],
    ]);

    // ---- COLLECTION ----
    register_post_type('collection', [
        'labels' => [
            'name'          => 'Collections',
            'singular_name' => 'Collection',
            'menu_name'     => 'Collections',
        ],
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => ['slug' => 'collections', 'with_front' => false],
        'show_in_rest'  => true,
        'menu_icon'     => 'dashicons-category',
        'menu_position' => 8,
        'supports'      => ['title', 'thumbnail', 'revisions'],
    ]);

    // ---- SURROUND ----
    register_post_type('surround', [
        'labels' => [
            'name'          => 'Surrounds',
            'singular_name' => 'Surround',
            'menu_name'     => 'Surrounds',
        ],
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => ['slug' => 'surrounds', 'with_front' => false],
        'show_in_rest'  => true,
        'menu_icon'     => 'dashicons-location',
        'menu_position' => 9,
        'supports'      => ['title', 'thumbnail', 'revisions'],
        'taxonomies'    => ['area_taxonomy', 'surround_kind'],
    ]);
});
