<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign — Bootstrap Bridge (grid/components only)
 * Цвета и роли приходят из 010..015 наших CSS.
 *
 * Важно: всегда используем один и тот же handle "bootstrap" для CSS,
 * чтобы зависимости (ld-css-*) работали одинаково и для локальных файлов, и для CDN.
 */

function ld_enqueue_bootstrap_css()
{
    $dir = get_stylesheet_directory();
    $uri = get_stylesheet_directory_uri();

    $css_local = $dir . "/assets/vendor/bootstrap/css/bootstrap.min.css";

    if (file_exists($css_local)) {
        wp_enqueue_style(
            "bootstrap",
            $uri . "/assets/vendor/bootstrap/css/bootstrap.min.css",
            [],
            filemtime($css_local)
        );
    } else {
        wp_enqueue_style(
            "bootstrap",
            "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css",
            [],
            "5.3.3"
        );
    }
}

function ld_enqueue_bootstrap_js()
{
    $dir = get_stylesheet_directory();
    $uri = get_stylesheet_directory_uri();

    $js_local = $dir . "/assets/vendor/bootstrap/js/bootstrap.bundle.min.js";

    if (file_exists($js_local)) {
        wp_enqueue_script(
            "bootstrap",
            $uri . "/assets/vendor/bootstrap/js/bootstrap.bundle.min.js",
            [],
            filemtime($js_local),
            true
        );
    } else {
        wp_enqueue_script(
            "bootstrap",
            "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js",
            [],
            "5.3.3",
            true
        );
    }
}

// Front-end
add_action("wp_enqueue_scripts", function () {
    ld_enqueue_bootstrap_css();
    ld_enqueue_bootstrap_js();
}, 5);

// Editor (Gutenberg) — обычно нужен только CSS
add_action("enqueue_block_editor_assets", function () {
    ld_enqueue_bootstrap_css();
}, 5);
