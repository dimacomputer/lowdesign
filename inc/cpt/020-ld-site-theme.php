<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign — ACF: глобальные цвета (ld_site_config) + переопределения на страницах
 * Работает с ACF Free.
 */
add_action("acf/init", function () {
    if (!function_exists("acf_add_local_field_group")) {
        return;
    }

    // Глобально в твоём CPT: ld_site_config
    acf_add_local_field_group([
        "key" => "group_ld_theme_colors_global",
        "title" => "Theme Colors",
        "fields" => [
            [
                "key" => "field_ld_theme_mode",
                "label" => "Theme Mode",
                "name" => "theme_mode",
                "type" => "select",
                "choices" => [
                    "light" => "Light",
                    "dark" => "Dark",
                    "auto" => "Auto (system)",
                ],
                "default_value" => "light",
                "ui" => 1,
                "wrapper" => ["width" => "33"],
            ],
            [
                "key" => "field_ld_theme_chroma",
                "label" => "Chroma (base)",
                "name" => "theme_chroma",
                "type" => "text",
                "placeholder" => "blue / indigo / green / red / ...",
                "wrapper" => ["width" => "33"],
            ],
            [
                "key" => "field_ld_theme_highlight",
                "label" => "Highlight",
                "name" => "theme_highlight",
                "type" => "text",
                "placeholder" => "soft / vivid / muted / ...",
                "wrapper" => ["width" => "33"],
            ],
            [
                "key" => "field_ld_theme_color",
                "label" => "Custom Color",
                "name" => "theme_color",
                "type" => "color_picker",
                "wrapper" => ["width" => "33"],
            ],
        ],
        "location" => [
            [
                [
                    "param" => "post_type",
                    "operator" => "==",
                    "value" => "ld_site_config",
                ],
            ],
        ],
        "position" => "normal",
        "style" => "seamless",
        "active" => true,
    ]);

    // На страницах (и постах/CPT при желании)
    acf_add_local_field_group([
        "key" => "group_ld_theme_colors_page",
        "title" => "Page Theme Colors",
        "fields" => [
            [
                "key" => "field_ld_page_theme_mode",
                "label" => "Theme Mode",
                "name" => "page_theme_mode",
                "type" => "select",
                "choices" => [
                    "inherit" => "Inherit",
                    "light" => "Light",
                    "dark" => "Dark",
                    "auto" => "Auto (system)",
                ],
                "default_value" => "inherit",
                "ui" => 1,
                "wrapper" => ["width" => "25"],
            ],
            [
                "key" => "field_ld_page_chroma",
                "label" => "Chroma (base)",
                "name" => "page_chroma",
                "type" => "text",
                "placeholder" => "blue / indigo / green / red / ...",
                "wrapper" => ["width" => "25"],
            ],
            [
                "key" => "field_ld_page_highlight",
                "label" => "Highlight",
                "name" => "page_highlight",
                "type" => "text",
                "placeholder" => "soft / vivid / muted / ...",
                "wrapper" => ["width" => "25"],
            ],
            [
                "key" => "field_ld_page_color",
                "label" => "Custom Color",
                "name" => "page_color",
                "type" => "color_picker",
                "wrapper" => ["width" => "25"],
            ],
        ],
        "location" => [
            [["param" => "post_type", "operator" => "==", "value" => "page"]],
        ],
        "position" => "side",
        "style" => "seamless",
        "active" => true,
    ]);
});
