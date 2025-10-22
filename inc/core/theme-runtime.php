<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign — Theme Runtime
 * Источник глобальных настроек — последний опубликованный ld_site_config.
 * На странице разрешаем override.
 * Вешаем классы на <body> и пробрасываем :root переменные.
 */

function ld_get_site_config_post_id()
{
    $q = new WP_Query([
        "post_type" => ["ld_site_config", "site_config", "site-config"],
        "post_status" => "publish",
        "posts_per_page" => 1,
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

function ld_get_theme_context()
{
    $sid = ld_get_site_config_post_id();

    $g_mode = $sid ? get_field("theme_mode", $sid) : null; // light|dark|auto
    $g_chroma = $sid ? get_field("theme_chroma", $sid) : null;
    $g_highlight = $sid ? get_field("theme_highlight", $sid) : null;
    $g_color = $sid ? get_field("theme_color", $sid) : null;

    $p_mode = get_field("page_theme_mode"); // inherit|light|dark|auto|null

    return [
        "mode" =>
            $p_mode && $p_mode !== "inherit" ? $p_mode : ($g_mode ?: "light"),
        "chroma" => get_field("page_chroma") ?: ($g_chroma ?: ""),
        "highlight" => get_field("page_highlight") ?: ($g_highlight ?: ""),
        "color" => get_field("page_color") ?: ($g_color ?: ""),
        "locked" => $p_mode && $p_mode !== "inherit",
    ];
}

add_filter(
    "body_class",
    function ($classes) {
        $t = ld_get_theme_context();
        $classes[] = "ld-theme-" . sanitize_html_class($t["mode"]); // ld-theme-light|dark|auto
        if ($t["chroma"]) {
            $classes[] = "ld-chroma-" . sanitize_html_class($t["chroma"]);
        }
        if ($t["highlight"]) {
            $classes[] = "ld-highlight-" . sanitize_html_class($t["highlight"]);
        }
        if ($t["locked"]) {
            $classes[] = "ld-theme-locked";
        }
        return $classes;
    },
    20,
);

/**
 * :root variables
 * - --ld-page-color (если задан custom color)
 * - Хук на auto-режим делаем CSS-медиавыражением: .ld-theme-auto @media (prefers-color-scheme: dark) {...}
 */
add_action(
    "wp_head",
    function () {
        $t = ld_get_theme_context();
        $vars = [];
        if ($t["color"]) {
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
