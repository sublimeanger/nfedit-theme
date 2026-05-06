<?php
defined('ABSPATH') || exit;

$post_id      = (int) $args['post_id'];
$mid_cottages = isset($args['mid_cottages']) ? $args['mid_cottages'] : [];

// Render filtered post_content
$content = apply_filters('the_content', get_post_field('post_content', $post_id));

// Inject mid-article rail after the 3rd </p>
if (!empty($mid_cottages)) {
    $rail_html = nfedit_render_mid_article_rail($mid_cottages);

    $count = 0;
    $offset = 0;
    $insert_pos = false;
    while (($pos = strpos($content, '</p>', $offset)) !== false) {
        $count++;
        if ($count === 3) {
            $insert_pos = $pos + 4;
            break;
        }
        $offset = $pos + 4;
    }

    if ($insert_pos !== false) {
        $content = substr($content, 0, $insert_pos) . $rail_html . substr($content, $insert_pos);
    } else {
        // Fewer than 3 paragraphs — append after content
        $content .= $rail_html;
    }
}
?>
<section class="nfedit-editorial-body">
    <div class="container-edit content-edit editorial-prose">
        <?php
        // $content is already filter-sanitised via apply_filters('the_content', ...).
        // Mid-article rail is built from trusted templates, not user input.
        echo $content;
        ?>
    </div>
</section>
