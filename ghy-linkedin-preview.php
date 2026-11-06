<?php
/**
 * Essential Character quiz: LinkedIn preview card per personality.
 *
 * Share links look like:
 *   https://www.ghy.com/trade-guides/trade-personality-quiz/?ec=scale&utm_source=linkedin...
 * When ?ec= names a personality, this page tells LinkedIn to use that personality's card
 * (scale.png, vault.png and so on, already in the Media Library). With no ?ec=, it uses quiz.png.
 *
 * Add with the Code Snippets plugin (Run everywhere) or at the end of the child theme's functions.php.
 * Page links with an unrecognised query string such as ?ec= are not cached by WP Rocket by default,
 * so each link gets its own card.
 */

add_action('wp_head', function () {
    if (!is_page('trade-personality-quiz')) {
        return;
    }

    $cards = ['compass', 'microscope', 'calculator', 'scale', 'vault', 'fire'];
    $key   = isset($_GET['ec']) ? sanitize_key(wp_unslash($_GET['ec'])) : '';
    $file  = in_array($key, $cards, true) ? $key : 'quiz';

    // Find the uploaded PNG by file name (also matches renamed copies such as scale-1.png).
    $url = get_transient('ec_card_' . $file);
    if (!$url) {
        $found = get_posts([
            'post_type'      => 'attachment',
            'post_mime_type' => 'image/png',
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'fields'         => 'ids',
            'meta_query'     => [[
                'key'     => '_wp_attached_file',
                'value'   => '(^|/)' . $file . '(-[0-9]+)?\.png$',
                'compare' => 'REGEXP',
            ]],
        ]);
        if (!$found) {
            return;
        }
        $url = wp_get_attachment_url($found[0]);
        set_transient('ec_card_' . $file, $url, DAY_IN_SECONDS);
    }

    $titles = [
        'compass'    => 'I was classified as The Compass (HTS 9014.10.90.00)',
        'microscope' => 'I was classified as The Microscope (HTS 9011.80.00.00)',
        'calculator' => 'I was classified as The Calculator (HTS 8470.21.00.00)',
        'scale'      => 'I was classified as The Scale (HTS 8423.10.00.10)',
        'vault'      => 'I was classified as The Vault (HTS 8303.00.00.00)',
        'fire'       => 'I was classified as The Fire Extinguisher (HTS 8424.10.00.00)',
    ];

    echo "\n<!-- Essential Character LinkedIn card -->\n";
    echo '<meta property="og:image" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:image:secure_url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:image:width" content="1200">' . "\n";
    echo '<meta property="og:image:height" content="627">' . "\n";
    echo '<meta property="og:image:type" content="image/png">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    if (isset($titles[$file])) {
        echo '<meta property="og:image:alt" content="' . esc_attr($titles[$file]) . '">' . "\n";
    }
}, 1);
