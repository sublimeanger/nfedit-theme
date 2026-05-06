<?php
/**
 * Newsletter — compact variant. Thin wrapper around newsletter.php.
 */
defined('ABSPATH') || exit;
get_template_part('template-parts/global/newsletter', null, ['compact' => true]);
