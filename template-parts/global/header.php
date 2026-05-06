<?php
/**
 * Global header — Options-driven (Phase 10A).
 * @param array $args { 'over_hero' => bool }
 */
defined('ABSPATH') || exit;

$over_hero    = !empty($args['over_hero']);
$header_class = 'nfedit-header' . ($over_hero ? ' nfedit-header--over-hero' : '');

$ctx          = 'site-settings-header-footer';
$primary_nav  = get_field('primary_nav', $ctx);
$oracle_label = (string) get_field('oracle_label', $ctx);
if (!$oracle_label) $oracle_label = 'Ask the Oracle';
$save_label   = (string) get_field('save_label', $ctx);
if (!$save_label) $save_label = 'Save';

if (!is_array($primary_nav) || empty($primary_nav)) {
    // Fallback if Options not yet seeded
    $primary_nav = [
        ['label' => 'Cottages', 'url' => '/cottages/', 'italic_styling' => false],
        ['label' => 'The Edit', 'url' => '/the-edit/', 'italic_styling' => true],
        ['label' => 'Areas',    'url' => '/areas/',    'italic_styling' => false],
        ['label' => 'Guides',   'url' => '/guides/',   'italic_styling' => false],
        ['label' => 'About',    'url' => '/about/',    'italic_styling' => false],
    ];
}

$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
?>
<header class="<?php echo esc_attr($header_class); ?>" data-over-hero="<?php echo $over_hero ? '1' : '0'; ?>">
    <div class="container-edit nfedit-header__inner">

        <a href="<?php echo esc_url(home_url('/')); ?>" class="nfedit-header__logo" aria-label="The New Forest Edit, home">
            <?php get_template_part('template-parts/global/wordmark', null, ['size' => 'md']); ?>
        </a>

        <nav class="nfedit-header__nav" aria-label="Primary">
            <?php foreach ($primary_nav as $item):
                $label  = isset($item['label']) ? $item['label'] : '';
                $url    = isset($item['url']) ? $item['url'] : '#';
                $italic = !empty($item['italic_styling']);
                if (!$label) continue;
                $href = (strpos($url, 'http') === 0) ? $url : home_url($url);
                $is_current = ($url !== '/' && strpos($request_uri, rtrim($url, '/') . '/') === 0);
            ?>
                <a href="<?php echo esc_url($href); ?>"
                   class="nfedit-header__nav-link <?php echo $is_current ? 'is-current' : ''; ?>"
                   <?php echo $is_current ? 'aria-current="page"' : ''; ?>>
                    <?php if ($italic && $label === 'The Edit'): ?>
                        The <span class="edit-italic">Edit</span>
                    <?php elseif ($italic): ?>
                        <span class="edit-italic"><?php echo esc_html($label); ?></span>
                    <?php else: ?>
                        <?php echo esc_html($label); ?>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="nfedit-header__actions">
            <a href="<?php echo esc_url(home_url('/oracle/')); ?>" class="nfedit-header__action nfedit-header__action--oracle">
                <svg class="nfedit-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5z"/>
                </svg>
                <?php echo esc_html($oracle_label); ?>
            </a>
            <button class="nfedit-header__action nfedit-header__action--save" aria-label="<?php echo esc_attr($save_label); ?>">
                <svg class="nfedit-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                </svg>
                <?php echo esc_html($save_label); ?>
            </button>
        </div>

        <button class="nfedit-header__mobile-toggle" aria-label="Open menu" aria-controls="nfedit-mobile-nav" aria-expanded="false">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>

    </div>

    <div class="nfedit-mobile-nav" id="nfedit-mobile-nav" hidden>
        <div class="container-edit nfedit-mobile-nav__head">
            <?php get_template_part('template-parts/global/wordmark', null, ['size' => 'md']); ?>
            <button class="nfedit-mobile-nav__close" aria-label="Close menu">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <nav class="container-edit nfedit-mobile-nav__list" aria-label="Mobile primary">
            <?php foreach ($primary_nav as $item):
                $label  = isset($item['label']) ? $item['label'] : '';
                $url    = isset($item['url']) ? $item['url'] : '#';
                $italic = !empty($item['italic_styling']);
                if (!$label) continue;
                $href = (strpos($url, 'http') === 0) ? $url : home_url($url);
            ?>
                <a href="<?php echo esc_url($href); ?>" class="nfedit-mobile-nav__link">
                    <?php if ($italic && $label === 'The Edit'): ?>
                        The <span class="edit-italic">Edit</span>
                    <?php elseif ($italic): ?>
                        <span class="edit-italic"><?php echo esc_html($label); ?></span>
                    <?php else: ?>
                        <?php echo esc_html($label); ?>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
            <a href="<?php echo esc_url(home_url('/oracle/')); ?>" class="nfedit-mobile-nav__link">
                Ask the <span class="edit-italic">Oracle</span>
            </a>
        </nav>
    </div>
</header>
