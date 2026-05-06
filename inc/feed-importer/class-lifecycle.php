<?php
/**
 * Property lifecycle state machine.
 *
 * States: active | sunsetting | archived | editorially_protected
 * Stored in _nfedit_feed_status post meta.
 */
defined('ABSPATH') || exit;

class NFEdit_Feed_Lifecycle {

    const STATE_ACTIVE                = 'active';
    const STATE_SUNSETTING            = 'sunsetting';
    const STATE_ARCHIVED              = 'archived';
    const STATE_EDITORIALLY_PROTECTED = 'editorially_protected';

    const SUNSET_DAYS  = 30;
    const ARCHIVE_DAYS = 30;

    public function get_state($post_id) {
        $state = (string) get_post_meta($post_id, '_nfedit_feed_status', true);
        if (!$state) return self::STATE_ACTIVE;
        return $state;
    }

    public function transition($post_id, $new_state, $reason = '') {
        $current = $this->get_state($post_id);
        if ($current === $new_state) return false;

        update_post_meta($post_id, '_nfedit_feed_status', $new_state);
        update_post_meta($post_id, '_nfedit_feed_status_updated', current_time('mysql'));

        nfedit_feed_log(
            'lifecycle_transition',
            null,
            sprintf('post #%d: %s -> %s (%s)', $post_id, $current, $new_state, $reason),
            ['post_id' => $post_id, 'from' => $current, 'to' => $new_state, 'reason' => $reason]
        );

        switch ($new_state) {
            case self::STATE_SUNSETTING:
            case self::STATE_ARCHIVED:
            case self::STATE_ACTIVE:
                wp_update_post(['ID' => $post_id, 'post_status' => 'publish']);
                break;
        }

        return true;
    }

    public function check_sunset_transitions() {
        global $wpdb;
        $cutoff = date('Y-m-d', strtotime('-' . self::SUNSET_DAYS . ' days'));

        $stale = $wpdb->get_col($wpdb->prepare("
            SELECT p.ID FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm_lu ON p.ID = pm_lu.post_id AND pm_lu.meta_key = 'feed_last_updated'
            LEFT JOIN {$wpdb->postmeta} pm_st ON p.ID = pm_st.post_id AND pm_st.meta_key = '_nfedit_feed_status'
            WHERE p.post_type = 'property'
            AND p.post_status = 'publish'
            AND pm_lu.meta_value < %s
            AND (pm_st.meta_value IS NULL OR pm_st.meta_value = 'active')
        ", $cutoff));

        $count = 0;
        foreach ($stale as $post_id) {
            $tier = (int) get_field('tier', $post_id);
            if ($tier === 1) {
                $this->transition($post_id, self::STATE_EDITORIALLY_PROTECTED, 'T1 property auto-protected from sunset');
            } else {
                $this->transition($post_id, self::STATE_SUNSETTING, 'feed_last_updated > ' . self::SUNSET_DAYS . ' days');
            }
            $count++;
        }

        return $count;
    }
}
