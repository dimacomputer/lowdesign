<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign — подключаем все .css из /assets/css по естественному порядку имён:
 * 010-, 011-, 012-, 013-, 014-, 015- ...
 */
add_action(
    "wp_enqueue_scripts",
    function () {
        $dir = get_stylesheet_directory() . "/assets/css";
        $uri = get_stylesheet_directory_uri() . "/assets/css";
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir . "/*.css");
        if (!$files) {
            return;
        }

        natsort($files);
        $deps = ["bootstrap"]; // идём после bootstrap (или bootstrap-cdn)
        foreach ($files as $path) {
            $name = basename($path);
            $handle = "ld-css-" . sanitize_title($name);
            $ver = @filemtime($path) ?: null;
            wp_enqueue_style($handle, $uri . "/" . $name, $deps, $ver);
            $deps[] = $handle; // соблюдаем порядок
        }
    },
    10,
);
