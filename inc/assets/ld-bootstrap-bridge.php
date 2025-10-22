<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign — Bootstrap Bridge
 * Включаем bootstrap.css + bootstrap.bundle.js из vendor, НО цвета берём из ld-* (см. 015-ld-bs-mapping.css).
 * Порядок: сначала bootstrap, потом наши 010…015.
 */
add_action(
    "wp_enqueue_scripts",
    function () {
        $base = get_stylesheet_directory();
        $uri = get_stylesheet_directory_uri();

        // пути к Bootstrap из темы (у тебя есть vendor/bootstrap)
        $bs_css = $base . "/vendor/bootstrap/dist/css/bootstrap.min.css";
        $bs_js = $base . "/vendor/bootstrap/dist/js/bootstrap.bundle.min.js";

        if (file_exists($bs_css)) {
            wp_enqueue_style(
                "bootstrap",
                $uri . "/vendor/bootstrap/dist/css/bootstrap.min.css",
                [],
                @filemtime($bs_css) ?: null,
            );
        }

        if (file_exists($bs_js)) {
            wp_enqueue_script(
                "bootstrap",
                $uri . "/vendor/bootstrap/dist/js/bootstrap.bundle.min.js",
                [],
                @filemtime($bs_js) ?: null,
                true,
            );
        }
    },
    10,
); // раньше наших CSS
