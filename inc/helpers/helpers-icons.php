<?php
/**
 * Render SVG inline icon
 *
 * @param string $icon_name logical name, e.g. 'social/instagram'
 * @param string $class      Additional classes for <svg>
 *
 * @return string|WP_Error Sanitized SVG markup or error when icon name is invalid
 */
if (!function_exists('render_svg_inline_icon')) {
    function render_svg_inline_icon($icon_name, $class = '') {
        static $cache = [];

        // normalise and validate icon name
        $icon_name = str_replace('\\', '/', $icon_name); // backslashes to forward
        $icon_name = preg_replace('#/+#', '/', $icon_name); // collapse multiple slashes

        if (strpos($icon_name, '..') !== false || !preg_match('#^[a-z0-9/_-]+$#', $icon_name)) {
            return new WP_Error('invalid_icon_name', 'Invalid icon name.');
        }

        $search_paths = [
            get_stylesheet_directory() . '/assets/icons/', // icons in theme
            ABSPATH . 'static/exports/icons/', // CDN/NAS export
        ];

        $allowed_html = [
            'svg'    => [
                'xmlns'       => true,
                'viewBox'     => true,
                'width'       => true,
                'height'      => true,
                'fill'        => true,
                'stroke'      => true,
                'class'       => true,
                'aria-hidden' => true,
                'role'        => true,
            ],
            'path'   => [
                'd'            => true,
                'fill'         => true,
                'stroke'       => true,
                'stroke-width' => true,
                'transform'    => true,
                'fill-rule'    => true,
                'clip-rule'    => true,
            ],
            'g'      => [
                'fill'         => true,
                'stroke'       => true,
                'stroke-width' => true,
                'transform'    => true,
                'clip-path'    => true,
            ],
            'rect'   => [
                'width'        => true,
                'height'       => true,
                'x'            => true,
                'y'            => true,
                'rx'           => true,
                'fill'         => true,
                'stroke'       => true,
                'stroke-width' => true,
                'transform'    => true,
            ],
            'circle' => [
                'cx'           => true,
                'cy'           => true,
                'r'            => true,
                'fill'         => true,
                'stroke'       => true,
                'stroke-width' => true,
            ],
            'polygon' => [
                'points'       => true,
                'fill'         => true,
                'stroke'       => true,
                'stroke-width' => true,
                'transform'    => true,
            ],
            'title'  => [],
            'desc'   => [],
        ];

        foreach ($search_paths as $base) {
            $file = $base . $icon_name . '.svg';

            if (isset($cache[$file])) {
                $svg = $cache[$file];
            } elseif (file_exists($file)) {
                $svg         = wp_kses(file_get_contents($file), $allowed_html);
                $cache[$file] = $svg;
            } else {
                continue;
            }

            // sanitize class attribute
            if ($class) {
                $svg = preg_replace(
                    '/<svg\\b([^>]*)>/',
                    '<svg$1 class="' . esc_attr($class) . '">',
                    $svg,
                    1
                );
            }

            return $svg;
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Icon not found: ' . $icon_name);
        }

        return '<!-- Icon not found: ' . esc_html($icon_name) . ' -->';
    }
}
