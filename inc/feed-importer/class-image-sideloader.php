<?php
/**
 * Sideload remote images into WP Media Library.
 *
 * - Dedup via _nfedit_source_url meta
 * - Resize-cap at 2400px wide
 * - Retry once on failure
 * - Returns attachment ID or 0 (never throws)
 */
defined('ABSPATH') || exit;

class NFEdit_Feed_Image_Sideloader {

    const MAX_DIMENSION = 2400;
    const TIMEOUT       = 30;

    public function sideload($url, $title = null, $parent_post_id = 0) {
        if (!$url) return 0;

        $existing = $this->find_existing($url);
        if ($existing) return $existing;

        if (!function_exists('media_sideload_image')) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $attempt = 0;
        $att_id  = 0;
        while ($attempt < 2 && !$att_id) {
            if ($attempt > 0) sleep(2);
            $attempt++;

            $result = media_sideload_image($url, $parent_post_id, $title, 'id');
            if (is_wp_error($result)) {
                continue;
            }
            $att_id = (int) $result;
        }

        if (!$att_id) return 0;

        update_post_meta($att_id, '_nfedit_source_url', $this->normalize_url($url));
        $this->cap_dimensions($att_id);

        return $att_id;
    }

    protected function find_existing($url) {
        global $wpdb;
        $key = $this->normalize_url($url);
        $att_id = $wpdb->get_var($wpdb->prepare("
            SELECT post_id FROM {$wpdb->postmeta}
            WHERE meta_key = '_nfedit_source_url'
            AND meta_value = %s
            LIMIT 1
        ", $key));
        return (int) $att_id;
    }

    protected function normalize_url($url) {
        return preg_replace('/\?.*$/', '', $url);
    }

    protected function cap_dimensions($att_id) {
        $file = get_attached_file($att_id);
        if (!$file || !file_exists($file)) return;

        $size = @getimagesize($file);
        if (!$size) return;

        list($w, $h) = $size;
        if ($w <= self::MAX_DIMENSION) return;

        $editor = wp_get_image_editor($file);
        if (is_wp_error($editor)) return;

        $ratio  = self::MAX_DIMENSION / $w;
        $new_w  = self::MAX_DIMENSION;
        $new_h  = (int) round($h * $ratio);

        $editor->resize($new_w, $new_h, false);
        $saved = $editor->save($file);
        if (is_wp_error($saved)) return;

        require_once ABSPATH . 'wp-admin/includes/image.php';
        $meta = wp_generate_attachment_metadata($att_id, $file);
        wp_update_attachment_metadata($att_id, $meta);
    }
}
