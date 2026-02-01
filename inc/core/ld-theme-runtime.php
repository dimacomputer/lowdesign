<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign — Theme Runtime
 * Source of global values: last published Site Config.
 * Overrides: per-page fields.
 *
 * IMPORTANT:
 * - Theme classes must be applied on <html> (root), not only <body>,
 *   because runtime CSS switches variables on html.ld-theme-* selectors.
 * - ACF uses "default" for light mode; map it to "light".
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

/**
 * Normalize mode values to the canonical set used by CSS:
 * auto | light | dark
 */
function ld_normalize_theme_mode($mode)
{
    $mode = is_string($mode) ? trim($mode) : "";
    if ($mode === "" || $mode === "inherit") {
        return "";
    }

    // ACF legacy / UI value:
    if ($mode === "default") {
        return "light";
    }

    // Allow "light" explicitly if you later change ACF:
    if ($mode === "light" || $mode === "dark" || $mode === "auto") {
        return $mode;
    }

    // Fallback:
    return "auto";
}

function ld_theme_context()
{
    $sid = ld_site_config_post_id();

    // global
    $g_mode = $sid ? get_field("theme_mode", $sid) : null; // auto|default|dark
    $g_chroma = $sid ? get_field("theme_chroma", $sid) : null; // bg
    $g_highlight = $sid ? get_field("theme_highlight", $sid) : null; // btn
    $g_color = $sid ? get_field("theme_color", $sid) : null; // fg/body

    // page override
    $p_mode = get_field("page_theme_mode"); // inherit|auto|light|dark|default|null

    $mode_raw =
        $p_mode && $p_mode !== "inherit" ? $p_mode : ($g_mode ?: "auto");

    return [
        "mode" => ld_normalize_theme_mode($mode_raw),
        "chroma" => get_field("page_chroma") ?: ($g_chroma ?: ""),
        "highlight" => get_field("page_highlight") ?: ($g_highlight ?: ""),
        "color" => get_field("page_color") ?: ($g_color ?: ""),
        "locked" => $p_mode && $p_mode !== "inherit",
    ];
}

function ld_theme_classes()
{
    $t = ld_theme_context();

    $classes = [];

    // mode
    $classes[] = "ld-theme-" . sanitize_html_class($t["mode"]); // auto|light|dark

    // roles
    if (!empty($t["chroma"])) {
        $classes[] = "ld-chroma-" . sanitize_html_class($t["chroma"]);
    }
    if (!empty($t["highlight"])) {
        $classes[] = "ld-highlight-" . sanitize_html_class($t["highlight"]);
    }
    if (!empty($t["color"])) {
        $classes[] = "ld-color-" . sanitize_html_class($t["color"]);
    }

    if (!empty($t["locked"])) {
        $classes[] = "ld-theme-locked";
    }

    return $classes;
}

/**
 * Apply classes to <body> (kept for compatibility)
 */
add_filter(
    "body_class",
    function ($classes) {
        return array_values(
            array_unique(array_merge($classes, ld_theme_classes())),
        );
    },
    20,
);

/**
 * Apply classes to <html> via language_attributes filter
 * so runtime CSS that targets html.ld-theme-* actually works.
 */
add_filter(
    "language_attributes",
    function ($output) {
        $classes = implode(" ", ld_theme_classes());

        // If class attribute already exists, append. Otherwise add new.
        if (preg_match('/\sclass=("|\')(.*?)\1/', $output, $m)) {
            $existing = trim($m[2]);
            $merged = trim($existing . " " . $classes);
            $output = preg_replace(
                '/\sclass=("|\')(.*?)\1/',
                ' class="' . esc_attr($merged) . '"',
                $output,
                1,
            );
        } else {
            $output .= ' class="' . esc_attr($classes) . '"';
        }

        return $output;
    },
    20,
);
