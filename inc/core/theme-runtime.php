<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign – Theme Runtime (semantic only)
 * Берёт Site Config (CPT) как глобальные настройки.
 * Разрешает override на уровне страницы.
 * Назначает классы на <body> и прокидывает :root vars.
 */

// найти последний опубликованный site_config (или site-config)
function ld_get_site_config_post_id()
{
    $q = new WP_Query([
        "post_type" => ["site_config", "site-config"],
        "posts_per_page" => 1,
        "post_status" => "publish",
        "orderby" => "date",
        "order" => "DESC",
        "no_found_rows" => true,
    ]);
    $id = 0;
    if ($q->have_posts()) {
        $id = (int) $q->posts[0]->ID;
    }
    wp_reset_postdata();
    return $id;
}

// собрать итоговый контекст
function ld_get_theme_context()
{
    $sid = ld_get_site_config_post_id();

    $g_mode = $sid ? get_field("theme_mode", $sid) : null;
    $g_chroma = $sid ? get_field("theme_chroma", $sid) : null;
    $g_highlight = $sid ? get_field("theme_highlight", $sid) : null;
    $g_color = $sid ? get_field("theme_color", $sid) : null;

    $p_mode = get_field("page_theme_mode"); // inherit|light|dark|null

    return [
        "mode" =>
            $p_mode && $p_mode !== "inherit" ? $p_mode : ($g_mode ?: "light"),
        "chroma" => get_field("page_chroma") ?: ($g_chroma ?: ""),
        "highlight" => get_field("page_highlight") ?: ($g_highlight ?: ""),
        "color" => get_field("page_color") ?: ($g_color ?: ""),
        "locked" => $p_mode && $p_mode !== "inherit",
    ];
}

// <body> classes
add_filter(
    "body_class",
    function ($classes) {
        $t = ld_get_theme_context();
        $classes[] = "ld-theme-" . sanitize_html_class($t["mode"]);
        if (!empty($t["chroma"])) {
            $classes[] = "ld-chroma-" . sanitize_html_class($t["chroma"]);
        }
        if (!empty($t["highlight"])) {
            $classes[] = "ld-highlight-" . sanitize_html_class($t["highlight"]);
        }
        if (!empty($t["locked"])) {
            $classes[] = "ld-theme-locked";
        }
        return $classes;
    },
    20,
);

// :root переменные
add_action(
    "wp_head",
    function () {
        $t = ld_get_theme_context();
        $vars = [];
        if (!empty($t["color"])) {
            $vars[] = "--ld-page-color:" . esc_attr($t["color"]);
        }
        if ($vars) {
            echo "<style id='ld-theme-vars'>:root{" .
                implode(";", $vars) .
                "}</style>\n";
        }
    },
    40,
);
