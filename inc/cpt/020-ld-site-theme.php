<?php
if (!defined("ABSPATH")) {
    exit();
}

/**
 * LowDesign – ACF groups: Theme Colors (global site_config) + Page overrides
 * Работает с ACF Free.
 */
add_action("acf/init", function () {
    if (!function_exists("acf_add_local_field_group")) {
        return;
    }

    // ГЛОБАЛЬНО: вкладка Theme Colors в CPT site_config
    acf_add_local_field_group([
        "key" => "group_ld_theme_colors_global",
        "title" => "Theme Colors",
        "fields" => [
            [
                "key" => "field_ld_theme_mode",
                "label" => "Theme Mode",
                "name" => "theme_mode",
                "type" => "select",
                "choices" => ["light" => "Light", "dark" => "Dark"],
                "default_value" => "light",
                "ui" => 1,
                "wrapper" => ["width" => "33"],
            ],
            [
                "key" => "field_ld_theme_chroma",
                "label" => "Chroma (base)",
                "name" => "theme_chroma",
                "type" => "text",
                "placeholder" => "blue / indigo / green / red ...",
                "wrapper" => ["width" => "33"],
            ],
            [
                "key" => "field_ld_theme_highlight",
                "label" => "Highlight",
                "name" => "theme_highlight",
                "type" => "text",
                "placeholder" => "soft / vivid / muted ...",
                "wrapper" => ["width" => "33"],
            ],
            [
                "key" => "field_ld_theme_color",
                "label" => "Custom Color",
                "name" => "theme_color",
                "type" => "color_picker",
                "wrapper" => ["width" => "33"],
            ],
            [
                "key" => "field_ld_theme_note",
                "label" => "",
                "name" => "theme_note",
                "type" => "message",
                "message" =>
                    "Глобальные цвета сайта. На страницах можно переопределить.",
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
        "menu_order" => 20,
        "position" => "normal",
        "style" => "seamless",
        "active" => true,
    ]);

    // СТРАНИЦА: локальные переопределения
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
                    "inherit" => "Inherit (use global)",
                    "light" => "Light",
                    "dark" => "Dark",
                ],
                "default_value" => "inherit",
                "ui" => 1,
                "wrapper" => ["width" => "33"],
            ],
            [
                "key" => "field_ld_page_chroma",
                "label" => "Chroma (base)",
                "name" => "page_chroma",
                "type" => "text",
                "placeholder" => "blue / indigo / green / red ...",
                "wrapper" => ["width" => "33"],
            ],
            [
                "key" => "field_ld_page_highlight",
                "label" => "Highlight",
                "name" => "page_highlight",
                "type" => "text",
                "placeholder" => "soft / vivid / muted ...",
                "wrapper" => ["width" => "33"],
            ],
            [
                "key" => "field_ld_page_color",
                "label" => "Custom Color",
                "name" => "page_color",
                "type" => "color_picker",
                "wrapper" => ["width" => "33"],
            ],
        ],
        "location" => [
            [["param" => "post_type", "operator" => "==", "value" => "page"]],
        ],
        "menu_order" => 0,
        "position" => "side",
        "style" => "seamless",
        "active" => true,
    ]);
});
