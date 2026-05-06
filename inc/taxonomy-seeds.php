<?php
/**
 * Seed taxonomy terms — idempotent, safe to re-run.
 */

defined('ABSPATH') || exit;

add_action('after_switch_theme', 'nfedit_seed_taxonomies');
add_action('init', 'nfedit_seed_taxonomies', 99);

function nfedit_seed_taxonomies() {
    if (get_option('nfedit_taxonomies_seeded') === '1') return;

    $sets = [
        'area_taxonomy' => [
            'brockenhurst'   => 'Brockenhurst',
            'lyndhurst'      => 'Lyndhurst',
            'lymington'      => 'Lymington',
            'milford-on-sea' => 'Milford-on-Sea',
            'beaulieu'       => 'Beaulieu',
            'burley'         => 'Burley',
            'fordingbridge'  => 'Fordingbridge',
            'ringwood'       => 'Ringwood',
            'hythe'          => 'Hythe',
            'mudeford'       => 'Mudeford',
        ],
        'forest_or_coast' => [
            'forest' => 'Forest',
            'coast'  => 'Coast',
            'both'   => 'Both',
        ],
        'cluster' => [
            'family'       => 'Family',
            'dog-friendly' => 'Dog-friendly',
            'forest'       => 'Forest',
            'coast'        => 'Coast',
            'seasonal'     => 'Seasonal',
        ],
        'feature' => [
            'log-burner'      => 'Log burner',
            'hot-tub'         => 'Hot tub',
            'dog-friendly'    => 'Dog friendly',
            'parking'         => 'Parking',
            'forest-access'   => 'Forest access',
            'wifi'            => 'WiFi',
            'ev-charging'     => 'EV charging',
            'accessible'      => 'Accessible',
            'enclosed-garden' => 'Enclosed garden',
            'aga'             => 'Aga',
            'walk-from-door'  => 'Walk from the door',
            'near-pub'        => 'Near a good pub',
            'open-fire'       => 'Open fire',
            'big-group'       => 'Big group (8+)',
            'couples'         => 'Couples retreat',
            'sleeps-6'        => 'Sleeps 6+',
        ],
        'guide_category' => [
            'getting-here'  => 'Getting here',
            'with-dogs'     => 'With dogs',
            'with-kids'     => 'With kids',
            'walking'       => 'Walking',
            'wild-swimming' => 'Wild swimming',
            'eating-out'    => 'Eating out',
            'driving'       => 'Driving',
            'seasonal'      => 'Seasonal',
        ],
        'season' => [
            'jan' => 'January', 'feb' => 'February', 'mar' => 'March',
            'apr' => 'April',   'may' => 'May',      'jun' => 'June',
            'jul' => 'July',    'aug' => 'August',   'sep' => 'September',
            'oct' => 'October', 'nov' => 'November', 'dec' => 'December',
        ],
        'surround_kind' => [
            'eat'   => 'Eat',
            'drink' => 'Drink',
            'walk'  => 'Walk',
            'see'   => 'See',
            'shop'  => 'Shop',
            'swim'  => 'Swim',
        ],
    ];

    foreach ($sets as $taxonomy => $terms) {
        if (!taxonomy_exists($taxonomy)) continue;
        foreach ($terms as $slug => $name) {
            if (!term_exists($slug, $taxonomy)) {
                wp_insert_term($name, $taxonomy, ['slug' => $slug]);
            }
        }
    }

    update_option('nfedit_taxonomies_seeded', '1');
}
