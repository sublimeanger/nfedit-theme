<?php
/**
 * ACF Options Pages — Site Settings + 10 sub-pages.
 * Plus their field groups (Sections H.1-H.11 of field-inventory.md).
 */

defined('ABSPATH') || exit;

// ─── Register Options pages on acf/init ─────────────────────────────────
add_action('acf/init', function () {

    if (!function_exists('acf_add_options_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => 'Site Settings',
        'menu_title' => 'Site Settings',
        'menu_slug'  => 'site-settings',
        'capability' => 'edit_posts',
        'redirect'   => true,
        'icon_url'   => 'dashicons-admin-customizer',
        'position'   => 80,
    ]);

    $sub_pages = [
        ['Brand',         'site-settings-brand'],
        ['Header & Footer', 'site-settings-header-footer'],
        ['Homepage',      'site-settings-homepage'],
        ['About Page',    'site-settings-about'],
        ['How We Choose', 'site-settings-how-we-choose'],
        ['Contact Page',  'site-settings-contact'],
        ['Oracle Page',   'site-settings-oracle'],
        ['404 Page',      'site-settings-404'],
        ['Newsletter Page','site-settings-newsletter'],
        ['Legal Pages',   'site-settings-legal'],
    ];

    foreach ($sub_pages as $sp) {
        acf_add_options_sub_page([
            'page_title'  => $sp[0],
            'menu_title'  => $sp[0],
            'menu_slug'   => $sp[1],
            'parent_slug' => 'site-settings',
            'capability'  => 'edit_posts',
        ]);
    }
});

// ─── Register Options-page field groups ────────────────────────────────
add_action('acf/init', function () {

    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    $loc = function ($slug) {
        return [[['param' => 'options_page', 'operator' => '==', 'value' => $slug]]];
    };

    // ─── H.1 Brand ─────────────────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_global_brand', 'title' => 'Brand', 'location' => $loc('site-settings-brand'),
        'fields' => [
            ['key' => 'field_nfedit_global_wordmark_svg',         'label' => 'Wordmark (SVG override)', 'name' => 'wordmark_svg', 'type' => 'image', 'mime_types' => 'svg', 'instructions' => 'Optional. Leave blank to use the inline wordmark from template-parts/global/wordmark.php.', 'return_format' => 'array'],
            ['key' => 'field_nfedit_global_tagline',              'label' => 'Tagline',                 'name' => 'tagline',      'type' => 'text', 'default_value' => "The cottages we'd actually book ourselves."],
            ['key' => 'field_nfedit_global_default_hero_image',   'label' => 'Default hero image',      'name' => 'default_hero_image', 'type' => 'image', 'instructions' => 'Fallback for templates without a hero.', 'preview_size' => 'medium', 'return_format' => 'array'],
        ],
    ]);

    // ─── H.2 Header & Footer ───────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_global_header_footer', 'title' => 'Header & Footer', 'location' => $loc('site-settings-header-footer'),
        'fields' => [
            ['key' => 'field_nfedit_global_primary_nav', 'label' => 'Primary nav', 'name' => 'primary_nav', 'type' => 'repeater', 'button_label' => 'Add nav item', 'sub_fields' => [
                ['key' => 'field_nfedit_global_nav_label',  'label' => 'Label',  'name' => 'label',  'type' => 'text', 'wrapper' => ['width' => 40]],
                ['key' => 'field_nfedit_global_nav_url',    'label' => 'URL',    'name' => 'url',    'type' => 'url',  'wrapper' => ['width' => 40]],
                ['key' => 'field_nfedit_global_nav_italic', 'label' => 'Italic styling', 'name' => 'italic_styling', 'type' => 'true_false', 'ui' => 1, 'wrapper' => ['width' => 20]],
            ]],
            ['key' => 'field_nfedit_global_oracle_label', 'label' => 'Oracle CTA label', 'name' => 'oracle_label', 'type' => 'text', 'default_value' => 'Ask the Oracle', 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_save_label',   'label' => 'Save CTA label',   'name' => 'save_label',   'type' => 'text', 'default_value' => 'Save', 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_footer_columns', 'label' => 'Footer columns', 'name' => 'footer_columns', 'type' => 'repeater', 'min' => 0, 'max' => 5, 'button_label' => 'Add column', 'sub_fields' => [
                ['key' => 'field_nfedit_global_footer_col_heading', 'label' => 'Heading', 'name' => 'heading', 'type' => 'text'],
                ['key' => 'field_nfedit_global_footer_col_links',   'label' => 'Links',   'name' => 'links',   'type' => 'repeater', 'button_label' => 'Add link', 'sub_fields' => [
                    ['key' => 'field_nfedit_global_footer_link_label',  'label' => 'Label',  'name' => 'label',  'type' => 'text', 'wrapper' => ['width' => 40]],
                    ['key' => 'field_nfedit_global_footer_link_url',    'label' => 'URL',    'name' => 'url',    'type' => 'url',  'wrapper' => ['width' => 40]],
                    ['key' => 'field_nfedit_global_footer_link_italic', 'label' => 'Italic', 'name' => 'italic', 'type' => 'true_false', 'ui' => 1, 'wrapper' => ['width' => 20]],
                ]],
            ]],
            ['key' => 'field_nfedit_global_footer_tagline',     'label' => 'Footer tagline',    'name' => 'footer_tagline',    'type' => 'text'],
            ['key' => 'field_nfedit_global_footer_disclaimer',  'label' => 'Footer disclaimer', 'name' => 'footer_disclaimer', 'type' => 'textarea', 'rows' => 3, 'instructions' => 'Affiliate disclosure paragraph.'],
            ['key' => 'field_nfedit_global_footer_copyright',   'label' => 'Footer copyright',  'name' => 'footer_copyright',  'type' => 'text', 'default_value' => '© 2026 The New Forest Edit.'],
            ['key' => 'field_nfedit_global_social_instagram',   'label' => 'Instagram URL',     'name' => 'social_instagram',  'type' => 'url',  'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_social_email',       'label' => 'Email (mailto:)',   'name' => 'social_email',      'type' => 'url',  'wrapper' => ['width' => 50]],
        ],
    ]);

    // ─── H.4 Homepage ──────────────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_global_homepage', 'title' => 'Homepage Content', 'location' => $loc('site-settings-homepage'),
        'fields' => [
            ['key' => 'field_nfedit_global_hp_hero_video_url', 'label' => 'Hero video URL', 'name' => 'hero_video_url', 'type' => 'url', 'instructions' => 'mp4 / webm', 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_hp_hero_fallback_image', 'label' => 'Hero fallback image', 'name' => 'hero_fallback_image', 'type' => 'image', 'preview_size' => 'medium', 'return_format' => 'array', 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_hp_hero_eyebrow', 'label' => 'Hero eyebrow', 'name' => 'hero_eyebrow', 'type' => 'text', 'default_value' => 'THE NEW FOREST EDIT'],
            ['key' => 'field_nfedit_global_hp_hero_h1',      'label' => 'Hero H1',       'name' => 'hero_h1',      'type' => 'textarea', 'rows' => 2, 'default_value' => "Slow weekends in the forest, the way we'd plan them ourselves."],
            ['key' => 'field_nfedit_global_hp_hero_sub',     'label' => 'Hero sub-headline', 'name' => 'hero_sub', 'type' => 'textarea', 'rows' => 2],
            ['key' => 'field_nfedit_global_hp_trust_strip_items', 'label' => 'Trust strip items', 'name' => 'trust_strip_items', 'type' => 'repeater', 'min' => 0, 'max' => 4, 'button_label' => 'Add item', 'sub_fields' => [
                ['key' => 'field_nfedit_global_hp_trust_icon',     'label' => 'Icon name (Lucide)', 'name' => 'icon_name', 'type' => 'text', 'wrapper' => ['width' => 25]],
                ['key' => 'field_nfedit_global_hp_trust_eyebrow',  'label' => 'Eyebrow',  'name' => 'eyebrow',  'type' => 'text', 'wrapper' => ['width' => 30]],
                ['key' => 'field_nfedit_global_hp_trust_headline', 'label' => 'Headline', 'name' => 'headline', 'type' => 'text', 'wrapper' => ['width' => 45]],
            ]],
            ['key' => 'field_nfedit_global_hp_editor_picks',         'label' => "Editor's picks",      'name' => 'editor_picks',         'type' => 'relationship', 'post_type' => ['property'], 'min' => 0, 'max' => 8, 'return_format' => 'id'],
            ['key' => 'field_nfedit_global_hp_featured_collections', 'label' => 'Featured collections','name' => 'featured_collections', 'type' => 'relationship', 'post_type' => ['collection'], 'min' => 0, 'max' => 16, 'return_format' => 'id'],
            ['key' => 'field_nfedit_global_hp_featured_editorials',  'label' => 'Featured editorials', 'name' => 'featured_editorials',  'type' => 'relationship', 'post_type' => ['post'], 'min' => 0, 'max' => 4, 'return_format' => 'id'],
            ['key' => 'field_nfedit_global_hp_featured_guides',      'label' => 'Featured guides',     'name' => 'featured_guides',      'type' => 'relationship', 'post_type' => ['guide'], 'min' => 0, 'max' => 6, 'return_format' => 'id'],
            ['key' => 'field_nfedit_global_hp_oracle_promo_eyebrow', 'label' => 'Oracle promo eyebrow', 'name' => 'oracle_promo_eyebrow', 'type' => 'text', 'default_value' => "OUR FAVOURITE THING WE'VE BUILT"],
            ['key' => 'field_nfedit_global_hp_oracle_promo_h2',      'label' => 'Oracle promo H2',     'name' => 'oracle_promo_h2',      'type' => 'text', 'default_value' => 'Ask the Oracle.'],
            ['key' => 'field_nfedit_global_hp_oracle_promo_body',    'label' => 'Oracle promo body',   'name' => 'oracle_promo_body',    'type' => 'textarea', 'rows' => 3],
            ['key' => 'field_nfedit_global_hp_newsletter_h2',        'label' => 'Newsletter H2',       'name' => 'newsletter_h2',        'type' => 'text'],
            ['key' => 'field_nfedit_global_hp_newsletter_body',      'label' => 'Newsletter body',     'name' => 'newsletter_body',      'type' => 'textarea', 'rows' => 3],
            ['key' => 'field_nfedit_global_hp_press_logos',          'label' => 'Press logos',         'name' => 'press_logos',          'type' => 'repeater', 'button_label' => 'Add logo', 'sub_fields' => [
                ['key' => 'field_nfedit_global_hp_press_image', 'label' => 'Logo', 'name' => 'logo_image', 'type' => 'image', 'preview_size' => 'thumbnail', 'return_format' => 'array', 'wrapper' => ['width' => 50]],
                ['key' => 'field_nfedit_global_hp_press_name',  'label' => 'Name', 'name' => 'name',       'type' => 'text', 'wrapper' => ['width' => 50]],
            ]],
        ],
    ]);

    // ─── H.5 About ─────────────────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_global_about', 'title' => 'About Page', 'location' => $loc('site-settings-about'),
        'fields' => [
            ['key' => 'field_nfedit_global_about_hero_image', 'label' => 'Hero image', 'name' => 'hero_image', 'type' => 'image', 'preview_size' => 'medium', 'return_format' => 'array', 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_about_portrait_image', 'label' => 'Portrait image', 'name' => 'portrait_image', 'type' => 'image', 'instructions' => 'Wide environmental shot.', 'preview_size' => 'medium', 'return_format' => 'array', 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_about_hero_h1',  'label' => 'Hero H1',  'name' => 'hero_h1',  'type' => 'text', 'default_value' => "We're Jamie and Lauren."],
            ['key' => 'field_nfedit_global_about_hero_dek', 'label' => 'Hero dek', 'name' => 'hero_dek', 'type' => 'text'],
            ['key' => 'field_nfedit_global_about_our_story_paragraphs', 'label' => 'Our story paragraphs', 'name' => 'our_story_paragraphs', 'type' => 'repeater', 'button_label' => 'Add paragraph', 'sub_fields' => [
                ['key' => 'field_nfedit_global_about_paragraph', 'label' => 'Paragraph', 'name' => 'paragraph', 'type' => 'wysiwyg', 'media_upload' => 0, 'tabs' => 'visual,text', 'toolbar' => 'basic'],
            ]],
            ['key' => 'field_nfedit_global_about_principles', 'label' => 'Principles', 'name' => 'principles', 'type' => 'repeater', 'min' => 0, 'max' => 5, 'instructions' => '3 principles: Honest, Local, Independent.', 'button_label' => 'Add principle', 'sub_fields' => [
                ['key' => 'field_nfedit_global_about_principle_label', 'label' => 'Label', 'name' => 'label', 'type' => 'text', 'wrapper' => ['width' => 30]],
                ['key' => 'field_nfedit_global_about_principle_body',  'label' => 'Body',  'name' => 'body',  'type' => 'textarea', 'rows' => 3, 'wrapper' => ['width' => 70]],
            ]],
            ['key' => 'field_nfedit_global_about_team', 'label' => 'Team', 'name' => 'team', 'type' => 'repeater', 'button_label' => 'Add team member', 'sub_fields' => [
                ['key' => 'field_nfedit_global_about_team_name',     'label' => 'Name',     'name' => 'name',     'type' => 'text',     'wrapper' => ['width' => 30]],
                ['key' => 'field_nfedit_global_about_team_role',     'label' => 'Role',     'name' => 'role',     'type' => 'text',     'wrapper' => ['width' => 30]],
                ['key' => 'field_nfedit_global_about_team_portrait', 'label' => 'Portrait', 'name' => 'portrait', 'type' => 'image',    'preview_size' => 'thumbnail', 'return_format' => 'array', 'wrapper' => ['width' => 40]],
                ['key' => 'field_nfedit_global_about_team_bio',      'label' => 'Bio',      'name' => 'bio',      'type' => 'textarea', 'rows' => 3],
            ]],
            ['key' => 'field_nfedit_global_about_faqs', 'label' => 'FAQs', 'name' => 'about_faqs', 'type' => 'repeater', 'button_label' => 'Add FAQ', 'sub_fields' => [
                ['key' => 'field_nfedit_global_about_faq_q', 'label' => 'Question', 'name' => 'q', 'type' => 'text'],
                ['key' => 'field_nfedit_global_about_faq_a', 'label' => 'Answer',   'name' => 'a', 'type' => 'textarea', 'rows' => 3],
            ]],
            ['key' => 'field_nfedit_global_about_press_logos', 'label' => 'Press logos', 'name' => 'press_logos', 'type' => 'repeater', 'button_label' => 'Add logo', 'sub_fields' => [
                ['key' => 'field_nfedit_global_about_press_image', 'label' => 'Logo', 'name' => 'logo_image', 'type' => 'image', 'preview_size' => 'thumbnail', 'return_format' => 'array', 'wrapper' => ['width' => 50]],
                ['key' => 'field_nfedit_global_about_press_name',  'label' => 'Name', 'name' => 'name',       'type' => 'text', 'wrapper' => ['width' => 50]],
            ]],
        ],
    ]);

    // ─── H.6 How We Choose ─────────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_global_how_we_choose', 'title' => 'How We Choose', 'location' => $loc('site-settings-how-we-choose'),
        'fields' => [
            ['key' => 'field_nfedit_global_hwc_hero_h1',  'label' => 'Hero H1',  'name' => 'hero_h1',  'type' => 'text', 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_hwc_hero_dek', 'label' => 'Hero dek', 'name' => 'hero_dek', 'type' => 'text', 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_hwc_criteria', 'label' => 'Criteria', 'name' => 'criteria', 'type' => 'repeater', 'min' => 0, 'max' => 12, 'instructions' => '8 numbered criteria.', 'button_label' => 'Add criterion', 'sub_fields' => [
                ['key' => 'field_nfedit_global_hwc_crit_n',    'label' => 'Number',  'name' => 'n',    'type' => 'number', 'wrapper' => ['width' => 15]],
                ['key' => 'field_nfedit_global_hwc_crit_h',    'label' => 'Heading', 'name' => 'h',    'type' => 'text',   'wrapper' => ['width' => 35]],
                ['key' => 'field_nfedit_global_hwc_crit_body', 'label' => 'Body',    'name' => 'body', 'type' => 'textarea', 'rows' => 3, 'wrapper' => ['width' => 50]],
            ]],
            ['key' => 'field_nfedit_global_hwc_tier_explainers', 'label' => 'Tier explainers', 'name' => 'tier_explainers', 'type' => 'repeater', 'min' => 3, 'max' => 3, 'button_label' => 'Add tier', 'sub_fields' => [
                ['key' => 'field_nfedit_global_hwc_tier_label',       'label' => 'Tier label',  'name' => 'tier_label',  'type' => 'text', 'wrapper' => ['width' => 30]],
                ['key' => 'field_nfedit_global_hwc_tier_description', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'rows' => 3, 'wrapper' => ['width' => 70]],
            ]],
            ['key' => 'field_nfedit_global_hwc_what_we_wont_do', 'label' => "What we won't do", 'name' => 'what_we_wont_do', 'type' => 'repeater', 'min' => 0, 'max' => 8, 'button_label' => 'Add bullet', 'sub_fields' => [
                ['key' => 'field_nfedit_global_hwc_wont_text', 'label' => 'Text', 'name' => 'text', 'type' => 'text'],
            ]],
        ],
    ]);

    // ─── H.7 Contact ───────────────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_global_contact', 'title' => 'Contact', 'location' => $loc('site-settings-contact'),
        'fields' => [
            ['key' => 'field_nfedit_global_contact_hero_h1',  'label' => 'Hero H1',  'name' => 'hero_h1',  'type' => 'text', 'default_value' => 'Hello.', 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_contact_hero_dek', 'label' => 'Hero dek', 'name' => 'hero_dek', 'type' => 'text', 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_contact_path_picker', 'label' => 'Path-picker items', 'name' => 'path_picker_items', 'type' => 'repeater', 'min' => 0, 'max' => 3, 'instructions' => '3 columns.', 'button_label' => 'Add item', 'sub_fields' => [
                ['key' => 'field_nfedit_global_contact_pp_heading',   'label' => 'Heading',   'name' => 'heading',   'type' => 'text'],
                ['key' => 'field_nfedit_global_contact_pp_body',      'label' => 'Body',      'name' => 'body',      'type' => 'textarea', 'rows' => 3],
                ['key' => 'field_nfedit_global_contact_pp_cta_label', 'label' => 'CTA label', 'name' => 'cta_label', 'type' => 'text', 'wrapper' => ['width' => 50]],
                ['key' => 'field_nfedit_global_contact_pp_cta_url',   'label' => 'CTA URL',   'name' => 'cta_url',   'type' => 'url',  'wrapper' => ['width' => 50]],
            ]],
            ['key' => 'field_nfedit_global_contact_form_subjects', 'label' => 'Form subjects', 'name' => 'form_subjects', 'type' => 'repeater', 'button_label' => 'Add subject', 'sub_fields' => [
                ['key' => 'field_nfedit_global_contact_subject', 'label' => 'Subject', 'name' => 'subject', 'type' => 'text'],
            ]],
        ],
    ]);

    // ─── H.8 Oracle ────────────────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_global_oracle', 'title' => 'Oracle', 'location' => $loc('site-settings-oracle'),
        'fields' => [
            ['key' => 'field_nfedit_global_oracle_hero_h1',  'label' => 'Hero H1',  'name' => 'hero_h1',  'type' => 'text', 'default_value' => 'Tell us what you want.', 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_oracle_hero_sub', 'label' => 'Hero sub', 'name' => 'hero_sub', 'type' => 'text', 'default_value' => "We'll find it.", 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_oracle_example_chips', 'label' => 'Example chips', 'name' => 'example_chips', 'type' => 'repeater', 'min' => 0, 'max' => 8, 'button_label' => 'Add chip', 'sub_fields' => [
                ['key' => 'field_nfedit_global_oracle_chip', 'label' => 'Chip text', 'name' => 'text', 'type' => 'text'],
            ]],
            ['key' => 'field_nfedit_global_oracle_steps', 'label' => 'How it works steps', 'name' => 'how_it_works_steps', 'type' => 'repeater', 'min' => 0, 'max' => 5, 'button_label' => 'Add step', 'sub_fields' => [
                ['key' => 'field_nfedit_global_oracle_step_n',    'label' => 'Step #', 'name' => 'step_number', 'type' => 'number', 'wrapper' => ['width' => 15]],
                ['key' => 'field_nfedit_global_oracle_step_body', 'label' => 'Body',   'name' => 'body',        'type' => 'textarea', 'rows' => 2, 'wrapper' => ['width' => 85]],
            ]],
            ['key' => 'field_nfedit_global_oracle_sample_queries', 'label' => 'Sample queries', 'name' => 'sample_queries', 'type' => 'repeater', 'min' => 0, 'max' => 4, 'button_label' => 'Add query', 'sub_fields' => [
                ['key' => 'field_nfedit_global_oracle_sq_query',     'label' => 'Query',             'name' => 'query',             'type' => 'text'],
                ['key' => 'field_nfedit_global_oracle_sq_cottages',  'label' => 'Related cottages',  'name' => 'related_cottages',  'type' => 'relationship', 'post_type' => ['property'], 'min' => 0, 'max' => 4, 'return_format' => 'id'],
            ]],
            ['key' => 'field_nfedit_global_oracle_limitations', 'label' => 'Limitations paragraph', 'name' => 'limitations_paragraph', 'type' => 'wysiwyg', 'media_upload' => 0, 'tabs' => 'visual,text', 'toolbar' => 'basic'],
        ],
    ]);

    // ─── H.9 404 ───────────────────────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_global_404', 'title' => '404 Page', 'location' => $loc('site-settings-404'),
        'fields' => [
            ['key' => 'field_nfedit_global_404_eyebrow', 'label' => 'Eyebrow', 'name' => 'eyebrow', 'type' => 'text', 'default_value' => '404 — LOST IN THE FOREST', 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_404_h1',      'label' => 'H1',      'name' => 'h1',      'type' => 'text', 'default_value' => "We can't find that page.", 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_404_body',    'label' => 'Body',    'name' => 'body',    'type' => 'textarea', 'rows' => 3],
            ['key' => 'field_nfedit_global_404_cta_cards', 'label' => 'CTA cards', 'name' => 'cta_cards', 'type' => 'repeater', 'min' => 0, 'max' => 4, 'button_label' => 'Add CTA', 'sub_fields' => [
                ['key' => 'field_nfedit_global_404_cta_heading', 'label' => 'Heading', 'name' => 'heading', 'type' => 'text', 'wrapper' => ['width' => 50]],
                ['key' => 'field_nfedit_global_404_cta_url',     'label' => 'URL',     'name' => 'url',     'type' => 'url',  'wrapper' => ['width' => 50]],
            ]],
        ],
    ]);

    // ─── H.10 Newsletter Landing ───────────────────────────────────────
    acf_add_local_field_group([
        'key' => 'group_nfedit_global_newsletter', 'title' => 'Newsletter Landing', 'location' => $loc('site-settings-newsletter'),
        'fields' => [
            ['key' => 'field_nfedit_global_nl_hero_image', 'label' => 'Hero image', 'name' => 'hero_image', 'type' => 'image', 'preview_size' => 'medium', 'return_format' => 'array'],
            ['key' => 'field_nfedit_global_nl_hero_h1',  'label' => 'Hero H1',  'name' => 'hero_h1',  'type' => 'text', 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_nl_hero_dek', 'label' => 'Hero dek', 'name' => 'hero_dek', 'type' => 'text', 'wrapper' => ['width' => 50]],
            ['key' => 'field_nfedit_global_nl_value_props', 'label' => 'Value props', 'name' => 'value_props', 'type' => 'repeater', 'min' => 0, 'max' => 4, 'button_label' => 'Add', 'sub_fields' => [
                ['key' => 'field_nfedit_global_nl_vp_h',    'label' => 'Heading', 'name' => 'h',    'type' => 'text', 'wrapper' => ['width' => 30]],
                ['key' => 'field_nfedit_global_nl_vp_body', 'label' => 'Body',    'name' => 'body', 'type' => 'textarea', 'rows' => 3, 'wrapper' => ['width' => 70]],
            ]],
            ['key' => 'field_nfedit_global_nl_sample_image', 'label' => 'Sample issue image', 'name' => 'sample_issue_image', 'type' => 'image', 'preview_size' => 'medium', 'return_format' => 'array'],
            ['key' => 'field_nfedit_global_nl_privacy', 'label' => 'Privacy promise', 'name' => 'privacy_promise', 'type' => 'textarea', 'rows' => 3],
        ],
    ]);

    // ─── H.11 Legal pages (single template, attached to pages with 'legal' parent slug or by slug match) ──
    // For simplicity register the field group on options page; per-legal-page content will come from page editor or from this options page's repeater.
    acf_add_local_field_group([
        'key' => 'group_nfedit_global_legal', 'title' => 'Legal Pages', 'location' => $loc('site-settings-legal'),
        'fields' => [
            ['key' => 'field_nfedit_global_legal_pages', 'label' => 'Legal pages', 'name' => 'legal_pages', 'type' => 'repeater', 'instructions' => 'One row per legal page (privacy, terms, cookies, affiliate).', 'button_label' => 'Add page', 'sub_fields' => [
                ['key' => 'field_nfedit_global_legal_slug',         'label' => 'Slug',         'name' => 'slug',         'type' => 'text', 'instructions' => 'privacy / terms / cookies / affiliate', 'wrapper' => ['width' => 30]],
                ['key' => 'field_nfedit_global_legal_title',        'label' => 'Title',        'name' => 'title',        'type' => 'text', 'wrapper' => ['width' => 40]],
                ['key' => 'field_nfedit_global_legal_last_updated', 'label' => 'Last updated', 'name' => 'last_updated', 'type' => 'date_picker', 'display_format' => 'F Y', 'return_format' => 'Y-m-d', 'wrapper' => ['width' => 30]],
                ['key' => 'field_nfedit_global_legal_body',         'label' => 'Body',         'name' => 'body',         'type' => 'wysiwyg', 'media_upload' => 0, 'tabs' => 'visual,text', 'instructions' => 'Auto-TOC generated from H2s.'],
            ]],
        ],
    ]);

});
