<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign — Theme Runtime (FSE)
 *
 * Responsibilities:
 * - Compute active theme name (LD theme key) from Site Config + per-page overrides.
 * - Emit selector attributes on <html>:
 *     data-ld-theme="<theme>"
 *     data-bs-theme="light|dark" (optional sync)
 *
 * Notes:
 * - LD CSS expects :root[data-ld-theme="…"] selectors (from ld_chroma.css exports).
 * - We keep using `language_attributes` filter because it affects the <html> tag.
 */

function ld_get_active_theme_key(): string
{
    // Defaults
    $theme = "default";

    // Site config (ACF options)
    if (function_exists("get_field")) {
        $opt = get_field("ld_theme_default", "option");
        if (is_string($opt) && $opt !== "") {
            $theme = $opt;
        }

        // Per-page override
        $override = get_field("ld_theme_override");
        if (is_string($override) && $override !== "") {
            $theme = $override;
        }
    }

    return sanitize_key($theme);
}

function ld_theme_to_bs_theme(string $ld_theme): string
{
    // Minimal mapping. Extend if you introduce more LD themes.
    if (in_array($ld_theme, ["dark", "night"], true)) {
        return "dark";
    }
    return "light";
}

add_filter("language_attributes", function ($output) {
    $ld_theme = ld_get_active_theme_key();
    $bs_theme = ld_theme_to_bs_theme($ld_theme);

    // Ensure we don't duplicate attributes if another filter adds them.
    $output = preg_replace('/\sdata-ld-theme="[^"]*"/', "", $output);
    $output = preg_replace('/\sdata-bs-theme="[^"]*"/', "", $output);

    $output .= ' data-ld-theme="' . esc_attr($ld_theme) . '"';
    $output .= ' data-bs-theme="' . esc_attr($bs_theme) . '"';

    return $output;
}, 20);
