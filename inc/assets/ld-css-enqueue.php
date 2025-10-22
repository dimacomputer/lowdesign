<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign – CSS enqueue (semantic-only)
 * Подключает все .css из /assets/css по естественной сортировке имён:
 * 010-*, 011-*, ..., 014-*, 015-*
 */
add_action(
    "wp_enqueue_scripts",
    function () {
        $base_dir = get_stylesheet_directory() . "/assets/css";
        $base_uri = get_stylesheet_directory_uri() . "/assets/css";
        if (!is_dir($base_dir)) {
            return;
        }

        $files = glob($base_dir . "/*.css");
        if (empty($files)) {
            return;
        }

        natsort($files);
        $deps = [];
        foreach ($files as $path) {
            $name = basename($path);
            $handle = "ld-css-" . sanitize_title($name);
            $ver = @filemtime($path) ?: null;

            wp_enqueue_style($handle, $base_uri . "/" . $name, $deps, $ver);
            $deps[] = $handle; // поддерживаем порядок – каждый зависит от предыдущего
        }
    },
    20,
);
