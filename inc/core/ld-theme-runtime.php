<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign — Theme Runtime
 * Источник глобальных значений: последний опубликованный Site Config (поддержка разных слугов).
 * Переопределения — на уровне страницы (Page).
 */

function ld_site_config_post_id()
{
    $q = new WP_Query([
        "post_type" => ["site_config", "ld_site_config", "site-config"],
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

function ld_theme_context()
{
    $sid = ld_site_config_post_id();

    // глобальные
    $g_mode = $sid ? get_field("theme_mode", $sid) : null; // auto|light|dark
    $g_chroma = $sid ? get_field("theme_chroma", $sid) : null; // bg
    $g_highlight = $sid ? get_field("theme_highlight", $sid) : null; // btn
    $g_color = $sid ? get_field("theme_color", $sid) : null; // fg/body

    // страница
    $p_mode = get_field("page_theme_mode"); // inherit|auto|light|dark|null

    return [
        "mode" =>
            $p_mode && $p_mode !== "inherit" ? $p_mode : ($g_mode ?: "auto"),
        "chroma" => get_field("page_chroma") ?: ($g_chroma ?: ""),
        "highlight" => get_field("page_highlight") ?: ($g_highlight ?: ""),
        "color" => get_field("page_color") ?: ($g_color ?: ""),
        "locked" => $p_mode && $p_mode !== "inherit",
    ];
}

add_filter(
    "body_class",
    function ($classes) {
        $t = ld_theme_context();

        // режим
        $classes[] = "ld-theme-" . sanitize_html_class($t["mode"]); // ld-theme-auto|light|dark

        // роли
        if ($t["chroma"]) {
            $classes[] = "ld-chroma-" . sanitize_html_class($t["chroma"]);
        } // bg
        if ($t["highlight"]) {
            $classes[] = "ld-highlight-" . sanitize_html_class($t["highlight"]);
        } // btn
        if ($t["color"]) {
            $classes[] = "ld-color-" . sanitize_html_class($t["color"]);
        } // fg/body

        if ($t["locked"]) {
            $classes[] = "ld-theme-locked";
        }

        return $classes;
    },
    20,
);
