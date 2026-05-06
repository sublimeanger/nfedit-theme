<?php
/**
 * Surround map — embed using lat/lng if both present.
 * No JS dependency; iframe to OpenStreetMap (no API key required).
 * Stay22 (Phase 14) will eventually replace this if/when desired.
 */
defined('ABSPATH') || exit;
$post_id = isset($args['post_id']) ? (int) $args['post_id'] : (int) get_the_ID();
$lat = (float) get_field('latitude',  $post_id);
$lng = (float) get_field('longitude', $post_id);
if (!$lat || !$lng) return;

// Bounding box ~0.01° around point (~1km)
$bbox = sprintf('%.6f,%.6f,%.6f,%.6f', $lng - 0.01, $lat - 0.01, $lng + 0.01, $lat + 0.01);
$marker = sprintf('%.6f,%.6f', $lat, $lng);
?>
<div class="nfedit-surround__map">
    <h2 class="nfedit-surround__map-title">Where it is</h2>
    <iframe
        src="https://www.openstreetmap.org/export/embed.html?bbox=<?php echo esc_attr($bbox); ?>&amp;layer=mapnik&amp;marker=<?php echo esc_attr($marker); ?>"
        width="100%" height="350" frameborder="0" loading="lazy"
        title="<?php echo esc_attr(get_the_title($post_id)); ?> on the map"
        style="border:0"></iframe>
    <p class="nfedit-surround__map-link">
        <a href="https://www.openstreetmap.org/?mlat=<?php echo esc_attr($lat); ?>&amp;mlon=<?php echo esc_attr($lng); ?>#map=16/<?php echo esc_attr($lat); ?>/<?php echo esc_attr($lng); ?>"
           rel="nofollow noopener" target="_blank">View larger map</a>
    </p>
</div>
