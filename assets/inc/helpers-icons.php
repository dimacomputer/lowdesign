<?php
/**
 * Render SVG inline icon
 *
 * @param string $icon_name logical name, e.g. 'social/instagram'
 * @param string $class additional classes for <svg>
 * @return string
 */
function render_svg_inline_icon($icon_name, $class = '') {
    $search_paths = [
        get_stylesheet_directory() . '/assets/icons/', // icons in theme
        ABSPATH . 'static/exports/icons/', // CDN/NAS export
    ];

    foreach ($search_paths as $base) {
        $file = $base . $icon_name . '.svg';
        if (file_exists($file)) {
            $svg = file_get_contents($file);

            // sanitize class attribute
            if ($class) {
                $svg = preg_replace(
                    '/<svg\b([^>]*)>/',
                    '<svg$1 class="' . esc_attr($class) . '">',
                    $svg,
                    1
                );
            }

            return $svg;
        }
    }

    return '<!-- Icon not found: ' . esc_html($icon_name) . ' -->';
}