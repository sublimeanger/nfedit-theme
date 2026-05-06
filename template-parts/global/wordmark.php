<?php
/**
 * Wordmark — The New Forest Edit
 * @param array $args { 'size' => 'sm'|'md'|'lg' }
 */
defined('ABSPATH') || exit;

$size  = isset($args['size']) ? $args['size'] : 'md';
$class = 'nfedit-wordmark nfedit-wordmark--' . esc_attr($size);
?>
<span class="<?php echo $class; ?>" aria-label="The New Forest Edit">
    <span class="nfedit-wordmark__main">The New Forest </span><span class="nfedit-wordmark__edit">Edit</span>
</span>
