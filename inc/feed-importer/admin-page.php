<?php
/**
 * Admin UI page at Tools → Feed Importer.
 * Phase 12a base + Phase 12c scraper section.
 *
 * Server-rendered HTML, no JS. admin-post.php for form actions.
 */
defined('ABSPATH') || exit;

add_action('admin_menu', function () {
    add_management_page(
        'NFE Feed Importer',
        'Feed Importer',
        'manage_options',
        'nfedit-feed-importer',
        'nfedit_feed_importer_admin_page'
    );
});

add_action('admin_post_nfedit_feed_refresh',        'nfedit_handle_feed_refresh');
add_action('admin_post_nfedit_feed_sync_one',       'nfedit_handle_feed_sync_one');
add_action('admin_post_nfedit_feed_toggle_enabled', 'nfedit_handle_feed_toggle_enabled');
add_action('admin_post_nfedit_scraper_run',         'nfedit_handle_scraper_run');

function nfedit_feed_importer_get_scrapers() {
    $scrapers = [];
    if (class_exists('NFEdit_Scraper_Shorefield')) {
        $scrapers[] = new NFEdit_Scraper_Shorefield();
    }
    return $scrapers;
}

function nfedit_feed_importer_admin_page() {
    if (!current_user_can('manage_options')) wp_die();

    $adapter   = new NFEdit_Feed_Adapter_Awin();
    $conn_test = $adapter->test_connection();

    global $wpdb;
    $advertisers = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}nfedit_advertisers
        ORDER BY membership_status DESC, advertiser_name ASC
    ");
    $log_rows = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}nfedit_feed_log
        ORDER BY id DESC
        LIMIT 30
    ");

    $scrapers = nfedit_feed_importer_get_scrapers();

    ?>
    <div class="wrap nfedit-feed-importer">
        <h1>The New Forest Edit &mdash; Feed Importer</h1>

        <h2>Awin connection</h2>
        <p>
            <?php if (is_wp_error($conn_test)): ?>
                <span style="color: #c00; font-weight: 600;">&#10007; Connection failed:</span>
                <code><?php echo esc_html($conn_test->get_error_message()); ?></code>
            <?php else: ?>
                <span style="color: #0a0; font-weight: 600;">&#10003; Connected to Awin</span>
                &mdash; Publisher ID: <code><?php echo esc_html(NFEDIT_AWIN_PUBLISHER_ID); ?></code>
            <?php endif; ?>
        </p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin: 1em 0;">
            <input type="hidden" name="action" value="nfedit_feed_refresh">
            <?php wp_nonce_field('nfedit_feed_refresh'); ?>
            <button type="submit" class="button">Refresh advertiser list</button>
        </form>

        <h2>Awin advertisers (<?php echo count($advertisers); ?>)</h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Region</th>
                    <th>Membership</th>
                    <th>Products</th>
                    <th>Last imported (remote)</th>
                    <th>Last imported (local)</th>
                    <th>Enabled</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($advertisers as $a):
                    $is_joined = strtolower($a->membership_status) === 'joined';
                ?>
                    <tr style="<?php echo $is_joined ? 'background: #f5fff5;' : 'opacity: 0.7;'; ?>">
                        <td><?php echo (int) $a->advertiser_id; ?></td>
                        <td><strong><?php echo esc_html($a->advertiser_name); ?></strong></td>
                        <td><?php echo esc_html($a->primary_region); ?></td>
                        <td>
                            <?php if ($is_joined): ?>
                                <span style="color: #0a0;">&#10003; Joined</span>
                            <?php else: ?>
                                <span style="color: #999;">Not joined</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format((int) $a->no_of_products); ?></td>
                        <td><?php echo esc_html($a->last_imported_remote ? $a->last_imported_remote : '—'); ?></td>
                        <td><?php echo esc_html($a->last_imported_local ? $a->last_imported_local : '—'); ?></td>
                        <td>
                            <?php if ($is_joined): ?>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline;">
                                    <input type="hidden" name="action" value="nfedit_feed_toggle_enabled">
                                    <input type="hidden" name="advertiser_id" value="<?php echo (int) $a->advertiser_id; ?>">
                                    <?php wp_nonce_field('nfedit_feed_toggle_enabled'); ?>
                                    <button type="submit" class="button">
                                        <?php echo $a->enabled ? '&#9745; Enabled' : '&#9744; Disabled'; ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="color: #999;">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($is_joined && $a->enabled): ?>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline;">
                                    <input type="hidden" name="action" value="nfedit_feed_sync_one">
                                    <input type="hidden" name="advertiser_id" value="<?php echo (int) $a->advertiser_id; ?>">
                                    <?php wp_nonce_field('nfedit_feed_sync_one'); ?>
                                    <button type="submit" class="button button-primary">Sync now</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Scraper sources</h2>
        <p>Partners without product feeds. Scrapes are rate-limited (2-4s/request), respect robots.txt, and cache HTML responses for 24h. Properties are imported as <code>draft</code> with a review-required flag — never auto-published.</p>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Adapter</th>
                    <th>Status</th>
                    <th>Last run</th>
                    <th>Properties imported</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scrapers as $scraper):
                    $test = $scraper->test_connection();
                    $imported_count = (int) get_option('nfedit_scraper_count_' . $scraper->get_id(), 0);
                    $last_run = get_option('nfedit_scraper_last_run_' . $scraper->get_id());
                ?>
                    <tr>
                        <td><strong><?php echo esc_html($scraper->get_label()); ?></strong></td>
                        <td>
                            <?php if (is_wp_error($test)): ?>
                                <span style="color: #c00;">&#10007; <?php echo esc_html($test->get_error_message()); ?></span>
                            <?php else: ?>
                                <span style="color: #0a0;">&#10003; Reachable</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($last_run ? $last_run : '—'); ?></td>
                        <td><?php echo (int) $imported_count; ?></td>
                        <td>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline;">
                                <input type="hidden" name="action" value="nfedit_scraper_run">
                                <input type="hidden" name="scraper_id" value="<?php echo esc_attr($scraper->get_id()); ?>">
                                <?php wp_nonce_field('nfedit_scraper_run'); ?>
                                <button type="submit" class="button button-primary"
                                    onclick="return confirm('Run the scraper now? It will fetch ~5-15 detail pages with 2-4s delays. Total runtime: 1-3 minutes.');">
                                    Run scraper now
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Scraped properties awaiting review</h3>
        <?php
        $awaiting = get_posts([
            'post_type'      => 'property',
            'post_status'    => ['draft', 'pending'],
            'meta_key'       => '_nfedit_scrape_review_required',
            'meta_value'     => 1,
            'posts_per_page' => 50,
        ]);
        if ($awaiting): ?>
            <ul>
                <?php foreach ($awaiting as $p): ?>
                    <li>
                        <a href="<?php echo esc_url(get_edit_post_link($p->ID)); ?>"><?php echo esc_html($p->post_title); ?></a>
                        &mdash; <em>scraped, awaiting Lauren review</em>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p><em>No properties currently awaiting review.</em></p>
        <?php endif; ?>

        <h2>Recent log (last 30 events)</h2>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Advertiser</th>
                    <th>Event</th>
                    <th>Severity</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $colors = ['info' => '', 'warn' => '#f80', 'error' => '#c00'];
                foreach ($log_rows as $log):
                    $color = isset($colors[$log->severity]) ? $colors[$log->severity] : '';
                ?>
                    <tr>
                        <td><?php echo esc_html($log->created); ?></td>
                        <td><?php echo $log->advertiser_id ? (int) $log->advertiser_id : '—'; ?></td>
                        <td><code><?php echo esc_html($log->event_type); ?></code></td>
                        <td style="color: <?php echo esc_attr($color); ?>;"><?php echo esc_html($log->severity); ?></td>
                        <td><?php echo esc_html(mb_substr((string) $log->message, 0, 200)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function nfedit_handle_feed_refresh() {
    check_admin_referer('nfedit_feed_refresh');
    if (!current_user_can('manage_options')) wp_die();
    $importer = new NFEdit_Feed_Importer();
    $sources  = $importer->refresh_feed_list();
    wp_safe_redirect(add_query_arg('refreshed', count($sources), admin_url('tools.php?page=nfedit-feed-importer')));
    exit;
}

function nfedit_handle_feed_sync_one() {
    check_admin_referer('nfedit_feed_sync_one');
    if (!current_user_can('manage_options')) wp_die();
    $advertiser_id = (int) (isset($_POST['advertiser_id']) ? $_POST['advertiser_id'] : 0);
    $importer = new NFEdit_Feed_Importer();
    $stats    = $importer->run_advertiser_sync($advertiser_id);
    wp_safe_redirect(add_query_arg('synced', wp_json_encode($stats), admin_url('tools.php?page=nfedit-feed-importer')));
    exit;
}

function nfedit_handle_feed_toggle_enabled() {
    check_admin_referer('nfedit_feed_toggle_enabled');
    if (!current_user_can('manage_options')) wp_die();
    $advertiser_id = (int) (isset($_POST['advertiser_id']) ? $_POST['advertiser_id'] : 0);
    global $wpdb;
    $current = (int) $wpdb->get_var($wpdb->prepare("
        SELECT enabled FROM {$wpdb->prefix}nfedit_advertisers WHERE advertiser_id = %d
    ", $advertiser_id));
    $wpdb->update(
        $wpdb->prefix . 'nfedit_advertisers',
        ['enabled' => $current ? 0 : 1],
        ['advertiser_id' => $advertiser_id]
    );
    wp_safe_redirect(admin_url('tools.php?page=nfedit-feed-importer'));
    exit;
}

function nfedit_handle_scraper_run() {
    check_admin_referer('nfedit_scraper_run');
    if (!current_user_can('manage_options')) wp_die();

    $scraper_id = sanitize_text_field(isset($_POST['scraper_id']) ? $_POST['scraper_id'] : '');

    $scraper = null;
    foreach (nfedit_feed_importer_get_scrapers() as $s) {
        if ($s->get_id() === $scraper_id) { $scraper = $s; break; }
    }

    if (!$scraper) {
        wp_safe_redirect(add_query_arg('scraper_error', 'unknown_id', admin_url('tools.php?page=nfedit-feed-importer')));
        exit;
    }

    @set_time_limit(0);
    nfedit_feed_log('run_started', null, "Scraper run started: " . $scraper->get_label(), []);

    $mapper = new NFEdit_Feed_Field_Mapper();
    $count  = 0;
    $failed = 0;
    foreach ($scraper->fetch_properties('all') as $canonical) {
        $canonical['_scraped'] = true;
        try {
            $result = $mapper->apply($canonical);
            if (is_wp_error($result)) {
                $failed++;
                nfedit_feed_log('property_failed', null, $result->get_error_message(), ['canonical' => $canonical], 'error');
            } else {
                $count++;
            }
        } catch (Throwable $e) {
            $failed++;
            nfedit_feed_log('property_failed', null, $e->getMessage(), [], 'error');
        }
    }

    update_option('nfedit_scraper_count_' . $scraper->get_id(), $count);
    update_option('nfedit_scraper_last_run_' . $scraper->get_id(), current_time('mysql'));

    nfedit_feed_log('run_completed', null, "Scraper completed: $count imported, $failed failed", []);
    wp_safe_redirect(add_query_arg(['scraper_done' => $count, 'scraper_failed' => $failed], admin_url('tools.php?page=nfedit-feed-importer')));
    exit;
}
