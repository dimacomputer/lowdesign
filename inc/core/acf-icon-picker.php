<?php
/**
 * Auto-fill ACF Select field with SVG icons from /assets/icons/
 */

add_filter('acf/load_field/name=menu_icon', function ($field) {
    $icons_dir = get_stylesheet_directory() . '/assets/icons/';
    $icons = [];

    if (is_dir($icons_dir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($icons_dir)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'svg') {
                $path = str_replace($icons_dir, '', $file->getPathname());
                $path = str_replace('\\', '/', $path); // Windows fix
                $path = preg_replace('/\.svg$/i', '', $path); // remove extension

                $icons[$path] = $path;
            }
        }
    }

    $field['choices'] = $icons;
    return $field;
});