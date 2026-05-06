<?php
/**
 * Seed default FAQs and details on new property creation.
 */

defined('ABSPATH') || exit;

function nfedit_get_default_faqs() {
    return [
        ['q' => 'Can we bring our dog?',                'a' => ''],
        ['q' => 'What time is check-in/out?',           'a' => ''],
        ['q' => 'Is there parking?',                    'a' => ''],
        ['q' => "What's the wifi like?",                'a' => ''],
        ['q' => 'Are there shops nearby?',              'a' => ''],
        ['q' => 'Is it suitable for young children?',   'a' => ''],
        ['q' => 'How do we book?',                      'a' => 'Through the booking link on this page. We earn a small affiliate fee at no cost to you.'],
        ['q' => "What's the cancellation policy?",      'a' => ''],
    ];
}

function nfedit_get_default_details() {
    return [
        ['label' => 'Sleeps',       'value' => ''],
        ['label' => 'Bathrooms',    'value' => ''],
        ['label' => 'Kitchen',      'value' => ''],
        ['label' => 'Living',       'value' => ''],
        ['label' => 'Outside',      'value' => ''],
        ['label' => 'Connectivity', 'value' => ''],
        ['label' => 'Heating',      'value' => ''],
        ['label' => 'Parking',      'value' => ''],
        ['label' => 'Pets',         'value' => ''],
        ['label' => 'Children',     'value' => ''],
        ['label' => 'Min stay',     'value' => ''],
    ];
}

add_action('save_post_property', function ($post_id, $post, $update) {
    if ($update) return;
    if (wp_is_post_revision($post_id)) return;
    if ($post->post_status === 'auto-draft') return;
    if (!function_exists('get_field')) return;

    $existing_faqs = get_field('faqs', $post_id);
    if (empty($existing_faqs)) {
        update_field('faqs', nfedit_get_default_faqs(), $post_id);
    }

    $existing_details = get_field('details', $post_id);
    if (empty($existing_details)) {
        update_field('details', nfedit_get_default_details(), $post_id);
    }
}, 10, 3);
