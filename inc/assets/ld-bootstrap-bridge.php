<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign — Bootstrap Bridge (grid/components only)
 * Цвета и роли приходят из 010..015 наших CSS.
 */
add_action(
    "wp_enqueue_scripts",
    function () {
        $dir = get_stylesheet_directory();
        $uri = get_stylesheet_directory_uri();

        // Локальные файлы (рекомендуется)
        $css_local = $dir . "/assets/vendor/bootstrap/css/bootstrap.min.css";
        $js_local =
            $dir . "/assets/vendor/bootstrap/js/bootstrap.bundle.min.js";

        if (file_exists($css_local)) {
            wp_enqueue_style(
                "bootstrap",
                $uri . "/assets/vendor/bootstrap/css/bootstrap.min.css",
                [],
                filemtime($css_local),
            );
        } else {
            // Fallback CDN
            wp_enqueue_style(
                "bootstrap-cdn",
                "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css",
                [],
                "5.3.3",
            );
        }

        if (file_exists($js_local)) {
            wp_enqueue_script(
                "bootstrap",
                $uri . "/assets/vendor/bootstrap/js/bootstrap.bundle.min.js",
                [],
                filemtime($js_local),
                true,
            );
        } else {
            // Fallback CDN
            wp_enqueue_script(
                "bootstrap-cdn",
                "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js",
                [],
                "5.3.3",
                true,
            );
        }
    },
    5,
);
