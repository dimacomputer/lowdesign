<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign — ACF Theme Colors (Dropdowns)
 * Все значения читаем из твоих CSS-токенов.
 */

function ld_extract_tokens($file, $prefix)
{
    $path = get_stylesheet_directory() . "/assets/css/" . $file;
    if (!file_exists($path)) {
        return [];
    }
    $css = file_get_contents($path);
    preg_match_all(
        "/--" . preg_quote($prefix, "/") . "-([a-z0-9-]+)/i",
        $css,
        $matches,
    );
    $tokens = array_unique($matches[1] ?? []);
    sort($tokens);
    $out = [];
    foreach ($tokens as $token) {
        $out[$token] = ucfirst($token);
    }
    return $out;
}

// читаем из твоих файлов
$chroma_choices = ld_extract_tokens("012-ld_chroma.css", "ld-chroma");
$highlight_choices = ld_extract_tokens("013-ld_highlight.css", "ld-highlight");
$color_choices = ld_extract_tokens("011-ld-color.css", "ld-color");

add_action("acf/init", function () use (
    $chroma_choices,
    $highlight_choices,
    $color_choices,
) {
    if (!function_exists("acf_add_local_field_group")) {
        return;
    }

    // --- глобальные (Site Config) ---
    acf_add_local_field_group([
        "key" => "group_ld_theme_global",
        "title" => "Theme Colors",
        "fields" => [
            [
                "key" => "field_ld_theme_mode",
                "label" => "Theme Mode",
                "name" => "theme_mode",
                "type" => "select",
                "choices" => [
                    "auto" => "Auto (system)",
                    "light" => "Light",
                    "dark" => "Dark",
                ],
                "default_value" => "auto",
                "ui" => 1,
                "wrapper" => ["width" => "33"],
            ],
            [
                "key" => "field_ld_theme_chroma",
                "label" => "Chroma (base)",
                "name" => "theme_chroma",
                "type" => "select",
                "choices" => $chroma_choices,
                "ui" => 1,
                "allow_null" => 0,
                "wrapper" => ["width" => "33"],
            ],
            [
                "key" => "field_ld_theme_highlight",
                "label" => "Highlight",
                "name" => "theme_highlight",
                "type" => "select",
                "choices" => $highlight_choices,
                "ui" => 1,
                "allow_null" => 0,
                "wrapper" => ["width" => "33"],
            ],
        ],
        "location" => [
            [
                [
                    "param" => "post_type",
                    "operator" => "==",
                    "value" => "site_config",
                ],
            ],
        ],
        "style" => "seamless",
        "active" => true,
    ]);

    // --- страница (override) ---
    acf_add_local_field_group([
        "key" => "group_ld_theme_page",
        "title" => "Page Theme Colors",
        "fields" => [
            [
                "key" => "field_ld_page_theme_mode",
                "label" => "Theme Mode",
                "name" => "page_theme_mode",
                "type" => "select",
                "choices" => [
                    "inherit" => "Inherit",
                    "auto" => "Auto (system)",
                    "light" => "Light",
                    "dark" => "Dark",
                ],
                "default_value" => "inherit",
                "ui" => 1,
                "wrapper" => ["width" => "25"],
            ],
            [
                "key" => "field_ld_page_chroma",
                "label" => "Chroma (base)",
                "name" => "page_chroma",
                "type" => "select",
                "choices" => $chroma_choices,
                "ui" => 1,
                "allow_null" => 1,
                "wrapper" => ["width" => "25"],
            ],
            [
                "key" => "field_ld_page_highlight",
                "label" => "Highlight",
                "name" => "page_highlight",
                "type" => "select",
                "choices" => $highlight_choices,
                "ui" => 1,
                "allow_null" => 1,
                "wrapper" => ["width" => "25"],
            ],
            [
                "key" => "field_ld_page_color",
                "label" => "Color",
                "name" => "page_color",
                "type" => "select",
                "choices" => $color_choices,
                "ui" => 1,
                "allow_null" => 1,
                "wrapper" => ["width" => "25"],
            ],
        ],
        "location" => [
            [["param" => "post_type", "operator" => "==", "value" => "page"]],
        ],
        "style" => "seamless",
        "active" => true,
    ]);
});
