<?php
defined('ABSPATH') || exit;

// Hero pages get the transparent-header variant + body class so .nfedit-main can drop top padding
$is_over_hero = is_front_page() || is_singular(['property', 'area', 'guide']);

add_filter('body_class', function ($classes) use ($is_over_hero) {
    if ($is_over_hero) {
        $classes[] = 'nfedit-over-hero';
    }
    return $classes;
});
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <?php get_template_part('template-parts/global/header', null, ['over_hero' => $is_over_hero]); ?>
    <main class="nfedit-main" id="main">
