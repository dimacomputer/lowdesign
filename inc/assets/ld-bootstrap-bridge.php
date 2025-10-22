<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign — Bootstrap Bridge (подключение сетки и компонентов)
 * Цвета переопределяются через 015-ld-bs-mapping.css.
 */
add_action(
    "wp_enqueue_scripts",
    function () {
        $theme_dir = get_stylesheet_directory();
        $theme_uri = get_stylesheet_directory_uri();

        $css_path = $theme_dir . "/vendor/bootstrap/css/bootstrap.min.css";
        $js_path = $theme_dir . "/vendor/bootstrap/js/bootstrap.bundle.min.js";

        if (file_exists($css_path)) {
            wp_enqueue_style(
                "bootstrap",
                $theme_uri . "/vendor/bootstrap/css/bootstrap.min.css",
                [],
                filemtime($css_path),
            );
        }

        if (file_exists($js_path)) {
            wp_enqueue_script(
                "bootstrap",
                $theme_uri . "/vendor/bootstrap/js/bootstrap.bundle.min.js",
                [],
                filemtime($js_path),
                true,
            );
        }
    },
    5,
); // раньше твоих ld-css
