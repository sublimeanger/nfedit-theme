<?php
/**
 * REST endpoints for nfedit theme.
 *
 * GET /wp-json/nfedit/v1/cottages — filtered cottage list
 *   Returns { total, paged, max_pages, html }
 *   The 'html' field is the rendered grid HTML to swap into the DOM.
 */

defined('ABSPATH') || exit;

add_action('rest_api_init', function () {
    register_rest_route('nfedit/v1', '/cottages', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'nfedit_rest_cottages',
        'permission_callback' => '__return_true',
        'args' => [
            'area'         => ['type' => 'string'],
            'feature'      => ['type' => 'string'],
            'forest_coast' => ['type' => 'string'],
            'dogs'         => ['type' => 'string'],
            'min_sleeps'   => ['type' => 'integer'],
            'sort'         => ['type' => 'string'],
            'paged'        => ['type' => 'integer'],
            'per_page'     => ['type' => 'integer'],
        ],
    ]);
});

function nfedit_rest_cottages($request) {
    $params = $request->get_query_params();
    $clean = [
        'area'         => isset($params['area'])         ? sanitize_title($params['area']) : '',
        'feature'      => isset($params['feature'])      ? $params['feature'] : '',
        'forest_coast' => isset($params['forest_coast']) ? sanitize_text_field($params['forest_coast']) : '',
        'dogs'         => isset($params['dogs'])         ? sanitize_text_field($params['dogs']) : '',
        'min_sleeps'   => isset($params['min_sleeps'])   ? (int) $params['min_sleeps'] : 0,
        'sort'         => isset($params['sort'])         ? sanitize_text_field($params['sort']) : 'editor',
        'paged'        => isset($params['paged'])        ? (int) $params['paged'] : 1,
        'per_page'     => isset($params['per_page'])     ? (int) $params['per_page'] : 12,
    ];

    $q = nfedit_property_filter_query($clean);

    ob_start();
    if ($q->have_posts()) {
        echo '<div class="nfedit-cottages-grid__items">';
        while ($q->have_posts()) {
            $q->the_post();
            get_template_part('template-parts/components/property-card', null, [
                'post_id' => get_the_ID(),
                'variant' => 'standard',
            ]);
        }
        echo '</div>';
    } else {
        echo '<div class="nfedit-cottages-grid__empty">';
        echo '<p class="nfedit-cottages-grid__empty-title">Nothing matches that combination yet.</p>';
        echo '<p class="nfedit-cottages-grid__empty-body">Try fewer filters, or <a href="' . esc_url(home_url('/oracle/')) . '">ask the Oracle</a> in plain English.</p>';
        echo '</div>';
    }
    $html = ob_get_clean();
    wp_reset_postdata();

    return new WP_REST_Response([
        'total'     => (int) $q->found_posts,
        'paged'     => (int) $clean['paged'],
        'max_pages' => (int) $q->max_num_pages,
        'html'      => $html,
    ]);
}

// ─── Phase 10A: Contact form submission ──────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('nfedit/v1', '/contact', [
        'methods'             => 'POST',
        'callback'            => 'nfedit_handle_contact_submission',
        'permission_callback' => '__return_true',
        'args' => [
            'name'    => ['required' => true, 'type' => 'string'],
            'email'   => ['required' => true, 'type' => 'string', 'format' => 'email'],
            'subject' => ['required' => true, 'type' => 'string'],
            'message' => ['required' => true, 'type' => 'string'],
        ],
    ]);
    register_rest_route('nfedit/v1', '/oracle-notify', [
        'methods'             => 'POST',
        'callback'            => 'nfedit_handle_oracle_notify',
        'permission_callback' => '__return_true',
        'args' => [
            'email' => ['required' => true, 'type' => 'string', 'format' => 'email'],
            'query' => ['type' => 'string'],
        ],
    ]);
});

function nfedit_handle_contact_submission(WP_REST_Request $request) {
    $name    = sanitize_text_field((string) $request->get_param('name'));
    $email   = sanitize_email((string) $request->get_param('email'));
    $subject = sanitize_text_field((string) $request->get_param('subject'));
    $message = sanitize_textarea_field((string) $request->get_param('message'));

    if (!is_email($email)) {
        return new WP_REST_Response(['error' => 'Invalid email'], 400);
    }
    if (!$name || !$message || !$subject) {
        return new WP_REST_Response(['error' => 'Missing required fields'], 400);
    }

    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    $rate_key = 'nfedit_contact_rate_' . md5($ip);
    $count = (int) get_transient($rate_key);
    if ($count >= 5) {
        return new WP_REST_Response(['error' => 'Too many submissions, try later'], 429);
    }
    set_transient($rate_key, $count + 1, HOUR_IN_SECONDS);

    global $wpdb;
    $table = $wpdb->prefix . 'nfedit_contact_submissions';
    $wpdb->insert($table, [
        'name'       => $name,
        'email'      => $email,
        'subject'    => $subject,
        'message'    => $message,
        'submitted'  => current_time('mysql'),
        'ip'         => $ip,
        'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 255) : '',
    ]);

    $to = (string) get_field('social_email', 'site-settings-header-footer');
    $to = str_replace('mailto:', '', $to);
    if (is_email($to)) {
        wp_mail(
            $to,
            "[NFE Contact] $subject — from $name",
            "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message\n\n— Submitted from " . home_url(),
            ['Reply-To: ' . $email]
        );
    }

    return new WP_REST_Response(['ok' => true], 200);
}

function nfedit_handle_oracle_notify(WP_REST_Request $request) {
    $email = sanitize_email((string) $request->get_param('email'));
    $query = sanitize_textarea_field((string) $request->get_param('query'));
    if (!is_email($email)) {
        return new WP_REST_Response(['error' => 'Invalid email'], 400);
    }
    $subs = get_option('nfedit_oracle_notify_list', []);
    if (!is_array($subs)) $subs = [];
    $key = strtolower($email);
    $subs[$key] = [
        'email'     => $email,
        'query'     => $query,
        'submitted' => current_time('mysql'),
    ];
    update_option('nfedit_oracle_notify_list', $subs, false);
    return new WP_REST_Response(['ok' => true], 200);
}
