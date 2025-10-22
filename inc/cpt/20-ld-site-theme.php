<?php
/**
 * LowDesign – Theme Colors (integrated into Site Config CPT)
 */
add_action("acf/init", function () {
    if (!function_exists("acf_add_local_field_group")) {
        return;
    }

    acf_add_local_field_group([
        "key" => "group_ld_theme_colors",
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
                "placeholder" => "blue / red / green / indigo...",
                "wrapper" => ["width" => "33"],
            ],
            [
                "key" => "field_ld_theme_highlight",
                "label" => "Highlight",
                "name" => "theme_highlight",
                "type" => "text",
                "placeholder" => "soft / vivid / muted...",
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
                "name" => "",
                "type" => "message",
                "message" =>
                    "These colors control the global theme appearance. Pages can override them.",
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
});
