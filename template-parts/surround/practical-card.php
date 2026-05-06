<?php
/**
 * Surround practical card — address, hours, dog policy, price, walking distance,
 * what to order, best for, practical notes.
 *
 * Each row hides individually if its data is empty. Card hides entirely if nothing populated.
 */
defined('ABSPATH') || exit;
$post_id = isset($args['post_id']) ? (int) $args['post_id'] : (int) get_the_ID();

$address     = (string) get_field('address',                     $post_id);
$website     = (string) get_field('website',                     $post_id);
$phone       = (string) get_field('phone',                       $post_id);
$hours       = get_field('opening_hours',                        $post_id);
$dog_policy  = (string) get_field('dog_policy',                  $post_id);
$price_tier  = (string) get_field('price_tier',                  $post_id);
$what_order  = (string) get_field('what_to_order',               $post_id);
$walking     = get_field('walking_distance_from_parks',          $post_id);
$practical   = (string) get_field('practical_notes',             $post_id);
$best_for    = get_field('best_for',                             $post_id);

$dog_labels = [
    'welcomed'           => 'Dogs welcomed',
    'allowed_outside'    => 'Dogs allowed outside',
    'allowed_some_areas' => 'Dogs allowed in some areas',
    'not_allowed'        => 'No dogs',
    'na'                 => '',
];

$has_anything = $address || $website || $phone || !empty($hours) || $dog_policy
             || $price_tier || $what_order || !empty($walking) || $practical || !empty($best_for);
if (!$has_anything) return;

$day_names = ['mon'=>'Mon','tue'=>'Tue','wed'=>'Wed','thu'=>'Thu','fri'=>'Fri','sat'=>'Sat','sun'=>'Sun'];
?>
<div class="nfedit-surround-card">
    <h2 class="nfedit-surround-card__title">The practical bit</h2>

    <?php if ($what_order): ?>
        <div class="nfedit-surround-card__row nfedit-surround-card__row--what-to-order">
            <span class="nfedit-surround-card__label eyebrow">What to order</span>
            <span class="nfedit-surround-card__value"><?php echo esc_html($what_order); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($address): ?>
        <div class="nfedit-surround-card__row">
            <span class="nfedit-surround-card__label eyebrow">Address</span>
            <span class="nfedit-surround-card__value"><?php echo esc_html($address); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($website):
        $host = parse_url($website, PHP_URL_HOST);
    ?>
        <div class="nfedit-surround-card__row">
            <span class="nfedit-surround-card__label eyebrow">Website</span>
            <span class="nfedit-surround-card__value">
                <a href="<?php echo esc_url($website); ?>" rel="nofollow noopener" target="_blank"><?php echo esc_html($host ? $host : $website); ?></a>
            </span>
        </div>
    <?php endif; ?>

    <?php if ($phone): ?>
        <div class="nfedit-surround-card__row">
            <span class="nfedit-surround-card__label eyebrow">Phone</span>
            <span class="nfedit-surround-card__value">
                <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
            </span>
        </div>
    <?php endif; ?>

    <?php if ($price_tier && $price_tier !== 'na'): ?>
        <div class="nfedit-surround-card__row">
            <span class="nfedit-surround-card__label eyebrow">Price</span>
            <span class="nfedit-surround-card__value"><?php echo esc_html($price_tier); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($dog_policy && !empty($dog_labels[$dog_policy])): ?>
        <div class="nfedit-surround-card__row">
            <span class="nfedit-surround-card__label eyebrow">Dogs</span>
            <span class="nfedit-surround-card__value"><?php echo esc_html($dog_labels[$dog_policy]); ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($hours) && is_array($hours)): ?>
        <div class="nfedit-surround-card__row nfedit-surround-card__row--hours">
            <span class="nfedit-surround-card__label eyebrow">Hours</span>
            <ul class="nfedit-surround-card__hours-list">
                <?php foreach ($hours as $row):
                    $day_key = isset($row['day']) ? $row['day'] : '';
                    $day = isset($day_names[$day_key]) ? $day_names[$day_key] : '';
                    $h   = isset($row['hours']) ? $row['hours'] : '';
                    if (!$day && !$h) continue;
                ?>
                    <li>
                        <span class="nfedit-surround-card__day"><?php echo esc_html($day); ?></span>
                        <span class="nfedit-surround-card__hour"><?php echo esc_html($h); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($walking) && is_array($walking)): ?>
        <div class="nfedit-surround-card__row nfedit-surround-card__row--walking">
            <span class="nfedit-surround-card__label eyebrow">Walk from</span>
            <ul class="nfedit-surround-card__walking-list">
                <?php foreach ($walking as $w):
                    $park  = isset($w['park_name']) ? $w['park_name'] : '';
                    $mins  = isset($w['minutes_walk']) ? $w['minutes_walk'] : '';
                    $notes = isset($w['notes']) ? $w['notes'] : '';
                    if (!$park) continue;
                ?>
                    <li>
                        <span class="nfedit-surround-card__park"><?php echo esc_html($park); ?></span>
                        <?php if ($mins !== ''): ?>
                            &mdash; <span class="nfedit-surround-card__mins"><?php echo esc_html($mins); ?> min</span>
                        <?php endif; ?>
                        <?php if ($notes): ?>
                            <span class="nfedit-surround-card__note">(<?php echo esc_html($notes); ?>)</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($best_for) && is_array($best_for)): ?>
        <div class="nfedit-surround-card__row nfedit-surround-card__row--best-for">
            <span class="nfedit-surround-card__label eyebrow">Best for</span>
            <ul class="nfedit-surround-card__best-for">
                <?php foreach ($best_for as $bf):
                    $tag = isset($bf['tag']) ? trim($bf['tag']) : '';
                    if (!$tag) continue;
                ?>
                    <li><?php echo esc_html($tag); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($practical): ?>
        <div class="nfedit-surround-card__row nfedit-surround-card__row--practical">
            <span class="nfedit-surround-card__label eyebrow">Notes</span>
            <p class="nfedit-surround-card__notes"><?php echo esc_html($practical); ?></p>
        </div>
    <?php endif; ?>
</div>
