<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign — подключаем все .css из /assets/css по естественному порядку имён:
 * 009-, 010-, 011-, ... (natsort).
 *
 * Важно:
 * - Эти файлы должны грузиться ПОСЛЕ Bootstrap (handle: "bootstrap"),
 *   потому что 015-ld-bs-mapping.css переопределяет Bootstrap CSS variables.
 * - Подключаем и на фронте, и в редакторе блоков, чтобы результат был одинаковый.
 */

function ld_enqueue_ld_css_bundle()
{
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

    // идём после Bootstrap CSS
    $deps = ["bootstrap"];

    foreach ($files as $path) {
        $name = basename($path);
        $handle = "ld-css-" . sanitize_title($name);
        $ver = @filemtime($path) ?: null;

        wp_enqueue_style($handle, $uri . "/" . $name, $deps, $ver);

        // соблюдаем порядок (каждый следующий зависит от предыдущего)
        $deps = [$handle];
    }
}

// Front-end
add_action("wp_enqueue_scripts", function () {
    ld_enqueue_ld_css_bundle();
}, 10);

// Block editor (Gutenberg)
add_action("enqueue_block_editor_assets", function () {
    ld_enqueue_ld_css_bundle();
}, 10);
