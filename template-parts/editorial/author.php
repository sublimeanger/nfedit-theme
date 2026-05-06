<?php
defined('ABSPATH') || exit;

$author = isset($args['author']) ? (string) $args['author'] : '';
if (!$author) return;

$bios = [
    'Jamie'  => 'Co-editor of The New Forest Edit. Lives ten minutes from the cattle grid.',
    'jamie'  => 'Co-editor of The New Forest Edit. Lives ten minutes from the cattle grid.',
    'Lauren' => 'Co-editor of The New Forest Edit. Music teacher by day, walks-spotter most weekends.',
    'lauren' => 'Co-editor of The New Forest Edit. Music teacher by day, walks-spotter most weekends.',
];
$bio_key = isset($bios[$author]) ? $author : ucfirst(strtolower($author));
$bio = isset($bios[$bio_key]) ? $bios[$bio_key] : 'Co-editor of The New Forest Edit.';

$initial = $author ? strtoupper(mb_substr($author, 0, 1)) : '?';
$display_name = ucfirst(strtolower($author));
?>
<section class="nfedit-editorial-author">
    <div class="container-edit content-edit nfedit-editorial-author__inner">
        <div class="nfedit-editorial-author__avatar"><?php echo esc_html($initial); ?></div>
        <div class="nfedit-editorial-author__body">
            <p class="eyebrow nfedit-editorial-author__label">Filed by</p>
            <p class="nfedit-editorial-author__name"><?php echo esc_html($display_name); ?></p>
            <p class="nfedit-editorial-author__bio"><?php echo esc_html($bio); ?></p>
        </div>
    </div>
</section>
