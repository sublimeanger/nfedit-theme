<?php
/**
 * FAQ row — expandable.
 * @var array $args { 'q' => string, 'a' => string }
 */
defined('ABSPATH') || exit;

$q = isset($args['q']) ? (string) $args['q'] : '';
$a = isset($args['a']) ? (string) $args['a'] : '';
if (!$q || !$a) return;
?>
<div class="nfedit-faq" data-nfedit-faq>
    <button class="nfedit-faq__q" type="button" aria-expanded="false">
        <span class="nfedit-faq__q-text"><?php echo esc_html($q); ?></span>
        <span class="nfedit-faq__icon nfedit-faq__icon--plus" aria-hidden="true">
            <?php echo nfedit_lucide_svg('plus', ['width' => 20, 'height' => 20]); ?>
        </span>
        <span class="nfedit-faq__icon nfedit-faq__icon--minus" aria-hidden="true">
            <?php echo nfedit_lucide_svg('minus', ['width' => 20, 'height' => 20]); ?>
        </span>
    </button>
    <div class="nfedit-faq__a">
        <p><?php echo esc_html($a); ?></p>
    </div>
</div>
